<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Thin client for The Odds API (https://the-odds-api.com), free tier.
 *
 * The free plan gives 500 credits a month, so every call that costs credits
 * is cached. /sports is free; /odds costs 1 credit per region per market;
 * /scores with daysFrom costs 2.
 */
class OddsApiService
{
    protected $key;
    protected $base;
    protected $region;
    protected $ttl;

    public function __construct()
    {
        $this->key = config('services.odds_api.key');
        $this->base = rtrim(config('services.odds_api.base_url'), '/');
        $this->region = config('services.odds_api.region', 'eu');
        $this->ttl = (int) config('services.odds_api.cache_minutes', 15);
    }

    /**
     * Always use a persistent store: with CACHE_DRIVER=array (as on Render)
     * every page view would otherwise spend API credits.
     */
    protected function cache()
    {
        $store = config('cache.default') === 'array' ? 'file' : null;
        return Cache::store($store);
    }

    public function enabled()
    {
        return !empty($this->key);
    }

    /** In-season sports, optionally limited to the ODDS_API_SPORTS allowlist. */
    public function sports()
    {
        $sports = $this->cache()->remember('oddsapi:sports', now()->addHours(6), function () {
            return $this->get('/sports') ?: [];
        });

        $allow = array_filter(array_map('trim', explode(',', (string) config('services.odds_api.sports'))));

        return array_values(array_filter($sports, function ($s) use ($allow) {
            if (!empty($s['has_outrights'])) return false;
            return empty($allow) || in_array($s['key'], $allow);
        }));
    }

    /**
     * Upcoming events for a sport with one decimal price per outcome
     * (the average across bookmakers, rounded to 2 dp).
     */
    public function events($sport)
    {
        $raw = $this->cache()->remember("oddsapi:odds:$sport", now()->addMinutes($this->ttl), function () use ($sport) {
            return $this->get("/sports/$sport/odds", [
                'regions' => $this->region,
                'markets' => 'h2h',
                'oddsFormat' => 'decimal',
                'dateFormat' => 'iso',
            ]) ?: [];
        });

        $events = [];
        foreach ($raw as $e) {
            $prices = [];
            foreach ($e['bookmakers'] ?? [] as $book) {
                foreach ($book['markets'] ?? [] as $market) {
                    if ($market['key'] !== 'h2h') continue;
                    foreach ($market['outcomes'] as $o) {
                        $prices[$o['name']][] = (float) $o['price'];
                    }
                }
            }
            if (empty($prices)) continue;

            $outcomes = [];
            foreach ($prices as $name => $list) {
                $outcomes[] = ['name' => $name, 'price' => round(array_sum($list) / count($list), 2)];
            }

            $events[] = [
                'id' => $e['id'],
                'sport_key' => $e['sport_key'],
                'sport_title' => $e['sport_title'] ?? $sport,
                'commence_time' => $e['commence_time'],
                'home_team' => $e['home_team'],
                'away_team' => $e['away_team'],
                'outcomes' => $outcomes,
            ];
        }

        usort($events, function ($a, $b) {
            return strcmp($a['commence_time'], $b['commence_time']);
        });

        return $events;
    }

    public function findEvent($sport, $eventId)
    {
        foreach ($this->events($sport) as $e) {
            if ($e['id'] === $eventId) return $e;
        }
        return null;
    }

    /** Results for the last 3 days (max the API allows). Not cached: used by the settle job. */
    public function scores($sport)
    {
        return $this->get("/sports/$sport/scores", ['daysFrom' => 3, 'dateFormat' => 'iso']) ?: [];
    }

    protected function get($path, array $query = [])
    {
        if (!$this->enabled()) return null;

        $url = $this->base . $path . '?' . http_build_query(array_merge(['apiKey' => $this->key], $query));

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HEADER => true,
        ]);
        $res = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        if ($res === false || $status !== 200) {
            Log::warning('Odds API request failed', ['path' => $path, 'status' => $status]);
            return null;
        }

        $headers = substr($res, 0, $headerSize);
        if (preg_match('/x-requests-remaining:\s*(\d+)/i', $headers, $m)) {
            $this->cache()->put('oddsapi:remaining', (int) $m[1], now()->addDay());
        }

        return json_decode(substr($res, $headerSize), true);
    }
}
