<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Tournament;
use Illuminate\Http\Request;

class TournamentsController extends Controller
{
    public function index()
    {
        return view('admin.tournaments.index');
    }

    public function create()
    {
        return view('admin.tournaments.create');
    }

    public function createPost(Request $request)
    {
        $request->validate([
            'title' => 'required|max:50',
            'games' => 'required',
            'awards' => 'required',
            'banner' => 'required|max:500',
            'preview' => 'required|max:500',
            'started_at' => 'required|date',
            'finished_at' => 'required|date|after:started_at',
        ]);

        // Преобразуем строку с играми в JSON массив
        $games = is_array($request->games) ? $request->games : explode(',', $request->games);
        $games = array_map('trim', $games);
        $gamesJson = json_encode($games);

        // Преобразуем строку с призами в JSON массив
        $awards = is_array($request->awards) ? $request->awards : explode(',', $request->awards);
        $awards = array_map('trim', $awards);
        $awards = array_map('floatval', $awards);
        $awardsJson = json_encode($awards);

        Tournament::create([
            'title' => $request->title,
            'games' => $gamesJson,
            'awards' => $awardsJson,
            'awards_count' => count($awards),
            'banner' => $request->banner,
            'preview' => $request->preview,
            'active' => $request->has('active') ? 1 : 0,
            'started_at' => $request->started_at,
            'finished_at' => $request->finished_at,
        ]);

        return redirect('/admin/tournaments')->with('success', 'Турнир успешно создан!');
    }

    public function edit($id)
    {
        $tournament = Tournament::find($id);

        if (!$tournament) {
            return redirect('/admin/tournaments')->with('error', 'Турнир не найден');
        }

        return view('admin.tournaments.edit', compact('tournament'));
    }

    public function editPost($id, Request $request)
    {
        $tournament = Tournament::find($id);

        if (!$tournament) {
            return redirect('/admin/tournaments')->with('error', 'Турнир не найден');
        }

        $request->validate([
            'title' => 'required|max:50',
            'games' => 'required',
            'awards' => 'required',
            'banner' => 'required|max:500',
            'preview' => 'required|max:500',
            'started_at' => 'required|date',
            'finished_at' => 'required|date|after:started_at',
        ]);

        // Преобразуем строку с играми в JSON массив
        $games = is_array($request->games) ? $request->games : explode(',', $request->games);
        $games = array_map('trim', $games);
        $gamesJson = json_encode($games);

        // Преобразуем строку с призами в JSON массив
        $awards = is_array($request->awards) ? $request->awards : explode(',', $request->awards);
        $awards = array_map('trim', $awards);
        $awards = array_map('floatval', $awards);
        $awardsJson = json_encode($awards);

        $tournament->update([
            'title' => $request->title,
            'games' => $gamesJson,
            'awards' => $awardsJson,
            'awards_count' => count($awards),
            'banner' => $request->banner,
            'preview' => $request->preview,
            'active' => $request->has('active') ? 1 : 0,
            'started_at' => $request->started_at,
            'finished_at' => $request->finished_at,
        ]);

        return redirect('/admin/tournaments')->with('success', 'Турнир успешно обновлен!');
    }

    public function delete($id)
    {
        $tournament = Tournament::find($id);

        if (!$tournament) {
            return redirect('/admin/tournaments')->with('error', 'Турнир не найден');
        }

        // Удаляем всех участников
        \App\TournamentPlayers::where('tournament_id', $id)->delete();
        
        // Удаляем турнир
        $tournament->delete();

        return redirect('/admin/tournaments')->with('success', 'Турнир успешно удален!');
    }
}

