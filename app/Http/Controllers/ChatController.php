<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Chat;
use App\Game;
use App\User;
use Auth;
use Carbon\Carbon;

class ChatController extends Controller
{
    public function getHistory() {
        $messages = Chat::with('user')->orderBy('id', 'desc')->limit(50)->get()->reverse()->values();
        return $messages;
    }

    public function sendMessage(Request $request) {
        if(!Auth::check()) return ['error' => true, 'message' => 'Авторизуйтесь'];
        
        $msg = htmlspecialchars($request->message);
        
        if(strlen($msg) < 1 || strlen($msg) > 200) {
            return ['error' => true, 'message' => 'Длина сообщения от 1 до 200 символов'];
        }

        // Simple spam check (optional, can be improved)
        $lastMsg = Chat::where('user_id', Auth::id())->orderBy('id', 'desc')->first();
        if($lastMsg && Carbon::now()->diffInSeconds($lastMsg->created_at) < 1) {
             return ['error' => true, 'message' => 'Не так часто'];
        }

        $chat = Chat::create([
            'user_id' => Auth::id(),
            'message' => $msg
        ]);

        $chat->load('user');

        return ['success' => true, 'message' => $chat];
    }

    public function getLiveBets() {
        // Fetch recent games, e.g., last 20
        $games = Game::orderBy('id', 'desc')
            ->limit(20)
            ->join('users', 'games.user_id', '=', 'users.id')
            ->select('games.*', 'users.username', 'users.avatar')
            ->get();
            
        return $games;
    }
}
