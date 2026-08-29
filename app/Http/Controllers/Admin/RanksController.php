<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use App\Rank;
use Illuminate\Http\Request;

class RanksController extends Controller
{
    public function index()
    {
        return view('admin.ranks.index');
    }


    public function edit($id)
    {
        $rank = Rank::query()->find($id);
        if (!$rank) {
            return redirect()->back();
        }
        
        return view('admin.ranks.edit', compact('rank'));
    }
}
