@extends('admin/layout')

@section('content')
<script type="text/javascript" src="/dash/js/chart.min.js"></script>
<div class="kt-subheader kt-grid__item" id="kt_subheader">
    <div class="kt-subheader__main">
        <h3 class="kt-subheader__title">Обзор статистики</h3>
    </div>
</div>

<div class="kt-content  kt-grid__item kt-grid__item--fluid" id="kt_content">
    
    <!-- Row 1: Deposits -->
    <h5 class="mb-3" style="color: var(--text-secondary); font-weight: 600;">Пополнения</h5>
    <div class="row">
        <div class="col-md-12 col-lg-6 col-xl-3 mb-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-card-title">За сегодня</div>
                        <div class="stat-card-value text-success">
                            {{ App\Payment::query()->where('fake', 0)->whereMonth('created_at', '=', date('m'))->where([['created_at', '>=', \Carbon\Carbon::today()], ['status', 1]])->sum('sum') }} ₽
                        </div>
                    </div>
                    <div class="stat-card-icon bg-success-light">
                        <i class="flaticon-coins"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-lg-6 col-xl-3 mb-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-card-title">За 7 дней</div>
                        <div class="stat-card-value text-success">
                            {{ App\Payment::query()->where('fake', 0)->whereMonth('created_at', '=', date('m'))->where([['created_at', '>=', \Carbon\Carbon::today()->subDays(7)], ['status', 1]])->sum('sum') }} ₽
                        </div>
                    </div>
                    <div class="stat-card-icon bg-success-light">
                        <i class="flaticon-calendar-with-a-clock-time-tools"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-lg-6 col-xl-3 mb-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-card-title">За месяц</div>
                        <div class="stat-card-value text-success">
                            {{ App\Payment::query()->where('fake', 0)->whereMonth('created_at', '=', date('m'))->where('status', 1)->sum('sum') }} ₽
                        </div>
                    </div>
                    <div class="stat-card-icon bg-success-light">
                        <i class="flaticon2-calendar-1"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-lg-6 col-xl-3 mb-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-card-title">За все время</div>
                        <div class="stat-card-value text-success">
                            {{ App\Payment::query()->where('fake', 0)->where('status', 1)->sum('sum') }} ₽
                        </div>
                    </div>
                    <div class="stat-card-icon bg-success-light">
                        <i class="flaticon-graph"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Withdraws -->
    <h5 class="mb-3 mt-2" style="color: var(--text-secondary); font-weight: 600;">Выплаты</h5>
    <div class="row">
        <div class="col-md-12 col-lg-6 col-xl-3 mb-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-card-title">За сегодня</div>
                        <div class="stat-card-value text-danger">
                            {{ App\Withdraw::query()->whereMonth('created_at', '=', date('m'))->where([['fake', 0], ['created_at', '>=', \Carbon\Carbon::today()], ['status', 1]])->sum('sum') }} ₽
                        </div>
                    </div>
                    <div class="stat-card-icon bg-danger-light">
                        <i class="flaticon2-check-mark"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-lg-6 col-xl-3 mb-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-card-title">За 7 дней</div>
                        <div class="stat-card-value text-danger">
                           {{ App\Withdraw::query()->whereMonth('created_at', '=', date('m'))->where([['fake', 0], ['created_at', '>=', \Carbon\Carbon::today()->subDays(7)], ['status', 1]])->sum('sum') }} ₽
                        </div>
                    </div>
                    <div class="stat-card-icon bg-danger-light">
                        <i class="flaticon-calendar-with-a-clock-time-tools"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-lg-6 col-xl-3 mb-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-card-title">За месяц</div>
                        <div class="stat-card-value text-danger">
                            {{ App\Withdraw::query()->whereMonth('created_at', '=', date('m'))->where([['fake', 0], ['status', 1]])->sum('sum') }} ₽
                        </div>
                    </div>
                    <div class="stat-card-icon bg-danger-light">
                        <i class="flaticon2-calendar-1"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-lg-6 col-xl-3 mb-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-card-title">За все время</div>
                        <div class="stat-card-value text-danger">
                            {{ App\Withdraw::query()->where([['fake', 0], ['status', 1]])->sum('sum') }} ₽
                        </div>
                    </div>
                    <div class="stat-card-icon bg-danger-light">
                        <i class="flaticon-graph"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: General Stats -->
    <h5 class="mb-3 mt-2" style="color: var(--text-secondary); font-weight: 600;">Общее</h5>
    <div class="row">
        <div class="col-md-12 col-lg-6 col-xl-4 mb-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-card-title">Всего пользователей</div>
                        <div class="stat-card-value text-primary">
                             {{ App\User::query()->count('id') }}
                        </div>
                    </div>
                    <div class="stat-card-icon bg-primary-light">
                        <i class="flaticon2-user"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-lg-6 col-xl-4 mb-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-card-title">Заявки на вывод</div>
                        <div class="stat-card-value text-warning">
                            {{ App\Withdraw::query()->where('status', 0)->sum('sum') }} ₽
                        </div>
                    </div>
                    <div class="stat-card-icon bg-warning-light">
                        <i class="flaticon-time-2"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-12 col-lg-6 col-xl-4 mb-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-card-title">Баланс FreeKassa</div>
                        <div class="stat-card-value text-info">
                            <span id="fkBal"><i class="fa fa-spinner fa-spin" style="font-size: 20px;"></i></span>
                        </div>
                    </div>
                    <div class="stat-card-icon bg-primary-light">
                        <i class="flaticon2-protected"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 4: Game Profit -->
    <h5 class="mb-3 mt-2" style="color: var(--text-secondary); font-weight: 600;">Профит игр</h5>
    <div class="row">
        <div class="col-md-12 col-lg-12 col-xl-4 mb-4">
            <div class="stat-card">
                <div class="stat-card-title">Профит Dice</div>
                <div class="stat-card-value {{ ($profitDice >= 0) ? 'text-success' : 'text-danger' }}">
                    {{ round($profitDice, 2) }} ₽
                </div>
            </div>
        </div>
        <div class="col-md-12 col-lg-12 col-xl-4 mb-4">
            <div class="stat-card">
                <div class="stat-card-title">Профит Mines</div>
                <div class="stat-card-value {{ ($profitMines >= 0) ? 'text-success' : 'text-danger' }}">
                    {{ round($profitMines, 2) }} ₽
                </div>
            </div>
        </div>
        <div class="col-md-12 col-lg-12 col-xl-4 mb-4">
            <div class="stat-card">
                <div class="stat-card-title">Профит Bubbles</div>
                <div class="stat-card-value {{ ($profitBubbles >= 0) ? 'text-success' : 'text-danger' }}">
                    {{ round($profitBubbles, 2) }} ₽
                </div>
            </div>
        </div>
    </div>

    <!-- Row 5: Charts -->
    <div class="row mt-3">
        <div class="col-xl-6">
            <div class="chart-container">
                <div class="chart-header">
                    <div class="chart-title">Регистрации (текущий месяц)</div>
                </div>
                <div style="height:250px;">
                    <canvas id="authChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="chart-container">
                <div class="chart-header">
                    <div class="chart-title">Пополнения (текущий месяц)</div>
                </div>
                <div style="height:250px;">
                    <canvas id="depsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Shared chart options
    const commonOptions = {
        legend: { display: false },
        maintainAspectRatio: false,
        tooltips: {
            backgroundColor: '#1e293b',
            titleFontColor: '#fff',
            bodyFontColor: '#cbd5e1',
            cornerRadius: 8,
            xPadding: 12,
            yPadding: 12,
            displayColors: false,
            intersect: false,
            mode: 'index'
        },
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    fontColor: '#94a3b8',
                    padding: 10
                },
                gridLines: { 
                    color: "#f1f5f9",
                    zeroLineColor: '#f1f5f9',
                    drawBorder: false
                }
            }],
            xAxes: [{
                ticks: {
                    fontColor: '#94a3b8',
                    padding: 10
                },
                gridLines: {
                    display: false
                }
            }]
        }
    };

    $.ajax({
        method: 'POST',
        url: '/admin/getUserByMonth',
        success: function (res) {
            var authChart = 'authChart';
            if ($('#'+authChart).length > 0) {
                var months = [];
                var users = [];

                $.each(res, function(index, data) {
                    months.push(data.date);
                    users.push(data.count);
                });

                var ctx = document.getElementById(authChart).getContext("2d");
                
                // Gradient
                var gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
                gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: months,
                        datasets: [{
                            label: "Пользователей",
                            tension: 0.4,
                            backgroundColor: gradient,
                            borderColor: '#3b82f6',
                            borderWidth: 2,
                            pointRadius: 4,
                            pointBackgroundColor: '#3b82f6',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointHoverRadius: 6,
                            data: users,
                        }]
                    },
                    options: commonOptions
                });
            }
        }
    });

    $.ajax({
        method: 'POST',
        url: '/admin/getDepsByMonth',
        success: function (res) {
            var depsChart = 'depsChart';
            if ($('#'+depsChart).length > 0) {
                var months = [];
                var deps = [];

                $.each(res, function(index, data) {
                    months.push(data.date);
                    deps.push(data.sum);
                });

                var ctx = document.getElementById(depsChart).getContext("2d");

                // Gradient
                var gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
                gradient.addColorStop(1, 'rgba(16, 185, 129, 0)');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: months,
                        datasets: [{
                            label: "Сумма",
                            tension: 0.4,
                            backgroundColor: gradient,
                            borderColor: '#10b981',
                            borderWidth: 2,
                            pointRadius: 4,
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointHoverRadius: 6,
                            data: deps,
                        }]
                    },
                    options: commonOptions
                });
            }
        }
    });
});
</script>
@endsection