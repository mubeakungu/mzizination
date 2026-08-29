@extends('admin/layout')

@section('content')
<script src="/dash/js/dtables.js?v={{time()}}" type="text/javascript"></script>
<div class="kt-subheader kt-grid__item" id="kt_subheader">
    <div class="kt-subheader__main">
        <h3 class="kt-subheader__title">Турниры</h3>
    </div>
</div>

<div class="kt-content kt-grid__item kt-grid__item--fluid" id="kt_content">
    <div class="kt-portlet kt-portlet--mobile">
        <div class="kt-portlet__head kt-portlet__head--lg">
            <div class="kt-portlet__head-label">
                <span class="kt-portlet__head-icon">
                    <i class="kt-font-brand flaticon2-trophy"></i>
                </span>
                <h3 class="kt-portlet__head-title">
                    Список турниров
                </h3>
            </div>
            <div class="kt-portlet__head-toolbar">
                <div class="kt-portlet__head-wrapper">
                    <div class="kt-portlet__head-actions">
                        <a href="{{ route('admin.tournaments.create') }}" class="btn btn-success btn-elevate btn-icon-sm">
                            <i class="la la-plus"></i>
                            Добавить турнир
                        </a>
                    </div>  
                </div>
            </div>
        </div>
        <div class="kt-portlet__body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <!--begin: Datatable -->
            <table class="table table-striped- table-bordered table-hover table-checkable" id="tournaments">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Игры</th>
                        <th>Призы</th>
                        <th>Активен</th>
                        <th>Начало</th>
                        <th>Окончание</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(\App\Tournament::orderBy('id', 'desc')->get() as $tournament)
                        <tr>
                            <td>{{ $tournament->id }}</td>
                            <td>{{ $tournament->title }}</td>
                            <td>
                                @php
                                    $games = is_array($tournament->games) ? $tournament->games : json_decode($tournament->games, true);
                                @endphp
                                @if($games)
                                    {{ implode(', ', $games) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @php
                                    $awards = is_array($tournament->awards) ? $tournament->awards : json_decode($tournament->awards, true);
                                    $totalAwards = is_array($awards) ? array_sum($awards) : 0;
                                @endphp
                                {{ number_format($totalAwards, 0, ',', ' ') }} ₽ ({{ $tournament->awards_count }} мест)
                            </td>
                            <td>
                                @if($tournament->active)
                                    <span class="badge badge-success">Да</span>
                                @else
                                    <span class="badge badge-secondary">Нет</span>
                                @endif
                            </td>
                            <td>{{ $tournament->started_at ? date('d.m.Y H:i', strtotime($tournament->started_at)) : '-' }}</td>
                            <td>{{ $tournament->finished_at ? date('d.m.Y H:i', strtotime($tournament->finished_at)) : '-' }}</td>
                            <td>
                                <a href="{{ route('admin.tournaments.edit', $tournament->id) }}" class="btn btn-sm btn-clean btn-icon btn-icon-md" title="Редактировать">
                                    <i class="la la-edit"></i>
                                </a>
                                <a href="{{ route('admin.tournaments.delete', $tournament->id) }}" class="btn btn-sm btn-clean btn-icon btn-icon-md" title="Удалить" onclick="return confirm('Вы уверены? Это действие удалит турнир и всех участников!');">
                                    <i class="la la-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!--end: Datatable -->
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#tournaments').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'desc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Russian.json'
            }
        });
    });
</script>
@endsection

