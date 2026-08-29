@extends('admin/layout')

@section('content')
<div class="kt-subheader kt-grid__item" id="kt_subheader">
    <div class="kt-subheader__main">
        <h3 class="kt-subheader__title">Редактировать турнир</h3>
    </div>
</div>

<div class="kt-content kt-grid__item kt-grid__item--fluid" id="kt_content">
    <div class="kt-portlet">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title">Редактирование турнира #{{ $tournament->id }}</h3>
            </div>
        </div>
        
        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $games = is_array($tournament->games) ? $tournament->games : json_decode($tournament->games, true);
            $awards = is_array($tournament->awards) ? $tournament->awards : json_decode($tournament->awards, true);
            $gamesStr = $games ? implode(', ', $games) : '';
            $awardsStr = $awards ? implode(', ', $awards) : '';
            $startedAt = $tournament->started_at ? date('Y-m-d\TH:i', strtotime($tournament->started_at)) : '';
            $finishedAt = $tournament->finished_at ? date('Y-m-d\TH:i', strtotime($tournament->finished_at)) : '';
        @endphp

        <form method="POST" action="{{ route('admin.tournaments.edit', $tournament->id) }}">
            @csrf
            <div class="kt-portlet__body">
                <div class="form-group">
                    <label>Название турнира <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="title" value="{{ old('title', $tournament->title) }}" required maxlength="50" placeholder="Например: Новогодний турнир 2024">
                    <span class="form-text text-muted">Максимум 50 символов</span>
                </div>

                <div class="form-group">
                    <label>ID игр (через запятую) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="games" value="{{ old('games', $gamesStr) }}" required placeholder="1, 2, 3, 5, 10">
                    <span class="form-text text-muted">Введите ID игр через запятую. Например: 1, 2, 3, 5</span>
                </div>

                <div class="form-group">
                    <label>Призы по местам (через запятую, в рублях) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="awards" value="{{ old('awards', $awardsStr) }}" required placeholder="10000, 5000, 3000, 2000, 1000">
                    <span class="form-text text-muted">Введите призы через запятую, начиная с 1 места. Например: 10000, 5000, 3000, 2000, 1000</span>
                </div>

                <div class="form-group">
                    <label>URL баннера <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="banner" value="{{ old('banner', $tournament->banner) }}" required maxlength="500" placeholder="/assets/image/tournaments/banner.jpg">
                    <span class="form-text text-muted">Путь к изображению баннера турнира</span>
                </div>

                <div class="form-group">
                    <label>URL превью <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="preview" value="{{ old('preview', $tournament->preview) }}" required maxlength="500" placeholder="/assets/image/tournaments/preview.jpg">
                    <span class="form-text text-muted">Путь к превью изображению турнира</span>
                </div>

                <div class="form-group">
                    <label class="kt-checkbox">
                        <input type="checkbox" name="active" value="1" {{ old('active', $tournament->active) ? 'checked' : '' }}>
                        Активен (можно присоединиться)
                        <span></span>
                    </label>
                    <span class="form-text text-muted">Если не отмечено, пользователи не смогут присоединиться к турниру</span>
                </div>

                <div class="form-group">
                    <label>Дата и время начала <span class="text-danger">*</span></label>
                    <input type="datetime-local" class="form-control" name="started_at" value="{{ old('started_at', $startedAt) }}" required>
                    <span class="form-text text-muted">Когда начнется турнир</span>
                </div>

                <div class="form-group">
                    <label>Дата и время окончания <span class="text-danger">*</span></label>
                    <input type="datetime-local" class="form-control" name="finished_at" value="{{ old('finished_at', $finishedAt) }}" required>
                    <span class="form-text text-muted">Когда закончится турнир (должно быть позже начала)</span>
                </div>

                <div class="alert alert-info">
                    <strong>Участников:</strong> {{ \App\TournamentPlayers::where('tournament_id', $tournament->id)->count() }}
                </div>
            </div>

            <div class="kt-portlet__foot">
                <div class="kt-form__actions">
                    <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                    <a href="{{ route('admin.tournaments') }}" class="btn btn-secondary">Отмена</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

