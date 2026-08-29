<?php

namespace App\Http\Controllers\Admin;

use App\Admin;
use App\Setting;
use App\User;
use App\BonusLevel;
use App\Withdraw;
use App\Payment;
use App\Rank;
use App\Promocode;
use App\PromocodeActivation;
use App\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Yajra\DataTables\DataTables;

class AdminController extends Controller
{
    public function versionUpdate() {
        Setting::where('id', 1)->update([
            'file_version' => time()
        ]);
        return response()->json(['success' => true, 'msg' => 'Версия обновлена!']);
    }

    public function load(Request $request)
    {
        switch($request->type) {

            case 'users':
                return datatables(User::query())->toJson();
            break;

            case 'bots':
                return datatables(User::query()->where('is_bot', '=', 1))->toJson();
            break;

            case 'promocodes':
                $promocodes = Promocode::query()
                    ->leftJoin('promocode_activations', function ($join) {
                        $join->on('promocodes.id', '=', 'promocode_activations.promo_id');
                    })
                    ->select('promocodes.*', DB::raw('count(promocode_activations.id) as activated'))
                    ->groupBy('promocodes.id');

                return Datatables::of($promocodes)->make(true);
            break;

            case 'ranks':
                return datatables(Rank::query())->toJson();
            break;
            
            case 'bonus':
                return datatables(BonusLevel::all())->toJson();
            break;

            case 'withdraws':
                $withdraws = Withdraw::where('status', $request->status)
                    ->join('users', 'users.id', '=', 'withdraws.user_id')
                    ->select('users.id as user_id', 'users.username as username', 'users.is_youtuber as youtuber', 'withdraws.*')
                    ->get();

                return Datatables::of($withdraws)->make(true);
            break;

            case 'transactions':
                $transactions = Transaction::where('user_id', $request->user_id)
                    ->join('users', 'users.id', '=', 'transactions.user_id')
                    ->select('users.id as user_id', 'users.username as username', 'transactions.*')
                    ->get();

                return Datatables::of($transactions)->make(true);
            break;

            case 'deposits':
                $deposits = Payment::where('status', 1)->where('fake', 0)
                    ->join('users', 'users.id', '=', 'payments.user_id')
                    ->select('users.id as user_id', 'users.username as username', 'payments.*')
                    ->get();

                return Datatables::of($deposits)->make(true);
            break;
        }
    }
}
