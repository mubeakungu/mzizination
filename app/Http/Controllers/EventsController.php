<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\User;
use DB;

class EventsController extends Controller
{
    protected $confirm_key = "7b93d9ea";
    protected $secret_key = "SUPE1RSECRET28";

    public function handle(Request $request)
    {
        if($request->secret != $this->secret_key) {
            return 'bad key';
        }
        

        switch($request->type) {
            case 'wall_post_new':
                $user_id = $request['object']['signer_id'];

                $user = User::where('vk_id', $user_id)->first();
                $user->increment('balance', 50);
                $user->increment('wager', 150);
            break;
            case 'confirmation':
                return $this->confirm_key;
            break;
        }

        return 'OK';
    }
} 