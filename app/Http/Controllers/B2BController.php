<?php

namespace App\Http\Controllers;

use App\User;
use App\Slot;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;

class B2BController extends Controller
{
    public function parse()
    {
        $providers = [
            "Netent",
            "YggDrasil",
            "Igrosoft",
            "Pragmatic Play",
            "Novomatic Deluxe",
            "BET IN HELL",
            "Belatra",
            "Unicum",
            "Megajack",
            "Playtech",
            "Play'n GO",
            "Microgaming"
        ];

        $get = $this->curl("https://int.apiforb2b.com/frontendsrv/apihandler.api?cmd=".json_encode([
            "api" => "ls-games-by-operator-id-get",
            "operator_id" => $this->config->b2b_provider_id
        ]));

        $i = 0;
        $list = [];

        Slot::where('provider_type', 'B2B')->delete();

        foreach($get['locator']['groups'] as $i => $info) {
            if(in_array($info['gr_title'], $providers)) {
                foreach($info['games'] as $games) {
                    Slot::create([
                        'game_id' => $games['gm_bk_id'],
                        'title' => $games['gm_title'],
                        'icon' => 'https://int.apiforb2b.com/game/icons/' . $games['icons'][0]['ic_name'],
                        'provider_type' => 'B2B',
                        'provider' => $info['gr_title'],
                        'show' => 1,
                    ]);
                }
            }
        }

        return 'OK';
    }

    public function callback(Request $request) 
    {
        if($_SERVER['HTTP_CF_CONNECTING_IP'] != '190.2.132.67') return 'hacking attempt!';
        
        try
        {
            switch($request->api) 
            {
                case 'do-auth-user-ingame':
                    $data = app('App\Http\Controllers\Slots\AuthController')->initAuth($request);
                    return json_encode($data);
                break;

                case 'do-debit-user-ingame':
                    $data = app('App\Http\Controllers\Slots\DebitController')->debit($request);
                    return json_encode($data);
                break;
    
                case 'do-credit-user-ingame':
                    $data = app('App\Http\Controllers\Slots\CreditController')->credit($request);
                    return json_encode($data);
                break;
            
                case 'do-rollback-user-ingame':
                    $data = app('App\Http\Controllers\Slots\RollbackController')->rollback($request);
                    return json_encode($data);
                break;        
    
                case 'do-get-features-user-ingame':
                    $data = app('App\Http\Controllers\Slots\FeaturesController')->getFeatures($request);
                    return json_encode($data);
                break;
    
                case 'do-activate-features-user-ingame':
                    $data = app('App\Http\Controllers\Slots\FeaturesController')->activateFeatures($request);
                    return json_encode($data);
                break;	

                case 'do-update-features-user-ingame':
                    $data = app('App\Http\Controllers\Slots\FeaturesController')->updateFeatures($request);
                    return json_encode($data);
                break;

                case 'do-end-features-user-ingame':
                    $data = app('App\Http\Controllers\Slots\FeaturesController')->endFeatures($request);
                    return json_encode($data);
                break;
    
                default :
                    throw new Exception("Unknown api");
            }
        }
        catch (Exception $e)
        {
            $response = (object) [];
            $response->answer = (object) [];
            $response->answer->error_code = 1;
            $response->answer->error_description = $e->getMessage();
            $response->answer->timestamp = '"'.time().'"';   
            $response->api = $request->api;
            $response->success = true;
            
            return json_encode($response);       
        }
    }
}
