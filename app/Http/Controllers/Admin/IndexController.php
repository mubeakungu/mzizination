<?php

namespace App\Http\Controllers\Admin;

use App\Game;
use App\User;
use App\Payment;
use App\Profit;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class IndexController extends Controller
{
    public function index()
    {
        $profitDice = Profit::query()->find(1)->earn_dice;
        $profitMines = Profit::query()->find(1)->earn_mines;
        $profitBubbles = Profit::query()->find(1)->earn_bubbles;

        return view('admin.index', compact('profitDice', 'profitMines', 'profitBubbles'));
    }
    public function getUserByMonth() {
        $chart = User::select(DB::raw('DATE_FORMAT(created_at, "%d.%m") as date'), DB::raw('count(*) as count'))
            ->where('is_bot', 0)
            ->whereMonth('created_at', '=', date('m'))
            ->groupBy('date')
            ->get();
        
        return $chart;
    }
    
    public function getDepsByMonth() {
        $chart = Payment::where('status', 1)->where('fake', 0)->select(DB::raw('DATE_FORMAT(created_at, "%d.%m") as date'), DB::raw('SUM(sum) as sum'))
            ->whereMonth('created_at', '=', date('m'))
            ->groupBy('date')
            ->get();
        
        return $chart;
    }
    public function getMerchant() {
        $shop_id = $this->config->kassa_id;
        $api_key = $this->config->kassa_key;
        $data = [
            'shopId' => $shop_id,
            'nonce' => time(),
        ];
        ksort($data);
        $sign = hash_hmac('sha256', implode('|', $data), $api_key);
        $data['signature'] = $sign;
    
        $request = json_encode($data);
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.freekassa.ru/v1/balance');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FAILONERROR, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        $result = trim(curl_exec($ch));
        curl_close($ch);
    
        $res = json_decode($result, true);
        return (isset($res['type'])) ? ($res['type'] == 'error') ? $res['message'] : $res['balance'][0]['value'] : $res['msg'];
    }
    public function getVK(Request $r)
    {
        $id = $r->vk_id;

        $info = file_get_contents("https://vk.com/foaf.php?id={$id}");
        $data = preg_match('|ya:created dc:date="(.*?)"|si', $info, $arr);

        return date("d.m.Y H:i:s", strtotime($arr[1]));
    }

    public function getCountry(Request $r)
    {
        $ip = $r->user_ip;

        $curl = curl_init("http://ip-api.com/json/{$ip}?lang=ru");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $response = curl_exec($curl);
        curl_close($curl);
         
        $content = json_decode($response, true);

        return $content['status'] == 'fail' ? $content['message'] : $content['city'];
    }
}
