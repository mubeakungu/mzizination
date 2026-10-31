# Sportsbook (The Odds API)

The `/sports` page lets players bet on real fixtures (1X2 / moneyline) using their normal balance.
Odds come from [The Odds API](https://the-odds-api.com) free tier (500 credits a month).

## Setup

1. Get a free key at https://the-odds-api.com and set `ODDS_API_KEY`.
2. Optional settings:

| Env var | Default | What it does |
|---|---|---|
| `ODDS_API_SPORTS` | `soccer_epl,soccer_uefa_champs_league,soccer_spain_la_liga,basketball_nba` | Sports shown. Empty = every in-season sport (uses more credits). |
| `ODDS_API_REGION` | `eu` | Bookmaker region the prices are averaged from. |
| `ODDS_API_CACHE_MINUTES` | `15` | How long odds are cached. |
| `SPORTS_MIN_BET` / `SPORTS_MAX_BET` | `10` / `100000` | Stake limits. |

3. Run `php artisan migrate` (creates `sport_bets`). `scripts/start.sh` does this on Render.
4. The scheduler must run: `scripts/start.sh` now starts a `schedule:run` loop, which runs `sports:settle` every 30 minutes.

## How it works

- The price for each outcome is the average across bookmakers in the region. The server always re-reads the price from its own cache when a bet is placed, so clients can't send their own odds.
- Betting closes at kick-off.
- `php artisan sports:settle` pulls results from `/scores` for matches that started 90+ minutes ago. Winning bets are paid `stake × odds`. Bets with no result after 3 days (postponed or cancelled) are refunded.
- Every stake, win and refund is written to `transactions`.

## Credit budget (free tier = 500/month)

- Odds: 1 credit per sport per refresh. With 4 sports and a 15-minute cache, worst case is about 4 × 96 = 384 credits a day if someone is on the page all day. On the free tier, keep the sport list short or raise `ODDS_API_CACHE_MINUTES` (for example to 120). For real traffic you'll need a paid plan.
- Settlement: 2 credits per sport that has open bets, every 30 minutes.
