<?php

namespace App\Http\Controllers;

use App\User;
use App\Setting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Cookie;
use App\Http\Middleware\EncryptCookies;
use Auth;
use App\Payment;
use App\Rank;
use App\Services\RankService;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $config;
    protected $rankService;
    protected $OPERATOR_ID = 20942;
    protected $YT_OPERATOR_ID = 20943;

    public function __construct()
    {
        $this->config = Setting::query()->find(1);
        view()->share('settings', $this->config);
        
        $this->middleware(function ($request, $next) {
            $this->user = Auth::User();
            $this->rankService = new RankService($this->user);

            view()->share('u', $this->user);
            return $next($request);
        });
    }

    public function curl($url) 
    {
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        curl_close($ch);
        return json_decode($res, true);
	}

    public function getIp() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif(!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    public function getWord($type, $value, $words = null, $show = true)
    {
        if($type == 'hour') $words = ['час', 'часа', 'часов'];
        if($type == 'min') $words = ['минуту', 'минуты', 'минут'];
        if($type == 'sec') $words = ['секунду', 'секунды', 'секунд'];

        $num = $value % 100;
        if ($num > 19) { 
            $num = $num % 10; 
        }
        
        $out = ($show) ?  $value . ' ' : '';
        switch ($num) {
            case 1:  $out .= $words[0]; break;
            case 2: 
            case 3: 
            case 4:  $out .= $words[1]; break;
            default: $out .= $words[2]; break;
        }
        
        return $out;
    }

    public function remainingTime($ss)
    {
        $sec = $ss%60;
        $min = floor(($ss%3600)/60);
        $hour = floor(($ss%86400)/3600);

        if($hour) {
            return $this->getWord('hour', $hour);
        }

        if($min) {
            return $this->getWord('min', $min);
        }

        if($sec) {
            return $this->getWord('sec', $sec);
        }
    }

    public function isChannelMember()
    {
        $data = [
            'chat_id' => $this->config->telegram_chat_id, 
            'user_id' => $this->user->tg_id
        ];
        $response = $this->curl('https://api.telegram.org/bot' . $this->config->telegram_token . '/getChatMember?' . http_build_query($data));
    
        if(!$response['ok']) return false;
        if($response['result']['status'] == 'left') return false;

        return true;
    }

    public function isGroupMember()
    {
        $data = [
            'group_id'     => $this->config->vk_id,
            'user_id'      => $this->user->vk_id,
            'access_token' => $this->config->vk_token,
            'v'            => 5.131
        ];
        $response = $this->curl('https://api.vk.com/method/groups.isMember?' . http_build_query($data));

        if(isset($response['error'])) return false;
        if(!$response['response']) return false;

        return true;
    }

    public function getAccessBySilent($params)
    {
        $data = [
            'token' => $params['payload']['token'],
            'uuid' => $params['payload']['uuid'],
            'access_token' => $this->config->vk_service_token,
            'v' => 5.131
        ];

        $response = $this->curl('https://api.vk.com/method/auth.exchangeSilentAuthToken?' . http_build_query($data));
        return $response;
    }

    public function getUserByAccessToken($token, $user_id)
    {
        $fields = ['id', 'email', 'first_name', 'last_name', 'screen_name', 'photo_200'];
        $data = [
            'user_id' => $user_id,
            'fields' => implode(',', $fields),
            'lang' => 'ru',
            'access_token' => $this->config->vk_service_token,
            'v' => 5.131
        ];

        $response = $this->curl('https://api.vk.com/method/users.get?' . http_build_query($data));
        return $response;
    }
}
