<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use App\User;
use App\ReferralProfit;

class IndexController extends Controller
{
    public function index()
    {
        return view('app');
    }
}
