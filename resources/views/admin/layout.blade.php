<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>VALUBA Admin</title>
        <meta name="description" content="Admin Panel">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Icons -->
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css">
        <link href="/dash/css/line-awesome.css" rel="stylesheet" type="text/css" />
        <link href="/dash/css/flaticon.css" rel="stylesheet" type="text/css" />
        <link href="/dash/css/flaticon2.css" rel="stylesheet" type="text/css" />

        <!-- Core CSS -->
        <link href="/dash/css/style.bundle.css" rel="stylesheet" type="text/css" />
        <link href="/dash/css/perfect-scrollbar.css" rel="stylesheet" type="text/css" />
        <link href="/dash/css/datatables.bundle.min.css" rel="stylesheet" type="text/css" />
        <link href="/dash/css/notify.css" rel="stylesheet" />
        
        <!-- Custom Redesign CSS -->
        <link href="/dash/css/custom.css?v={{ time() }}" rel="stylesheet" type="text/css" />
        
        <script src="/dash/js/jquery.min.js" type="text/javascript"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.1.1/socket.io.js"></script>
        <script src="/dash/js/wnoty.js" type="text/javascript"></script>
        <script src="/dash/js/popper.min.js" type="text/javascript"></script>
        <script src="/dash/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="/dash/js/perfect-scrollbar.min.js" type="text/javascript"></script>
        <script src="/dash/js/scripts.bundle.js" type="text/javascript"></script>
        <script src="/dash/js/datatables.bundle.min.js" type="text/javascript"></script>
        <script src="/dash/js/adminActions.js?v={{ $settings->file_version }}" type="text/javascript"></script>
    </head>
    <body class="kt-header--fixed kt-header-mobile--fixed kt-subheader--fixed kt-subheader--enabled kt-subheader--solid kt-aside--enabled kt-aside--fixed kt-page--loading">
        
        <!-- Mobile Header -->
        <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed ">
            <div class="kt-header-mobile__logo">
                <a href="/admin">
                    <span style="color: white; font-weight: 700; font-size: 20px;">VALUBA</span>
                </a>
            </div>
            <div class="kt-header-mobile__toolbar">
                <button class="kt-header-mobile__toggler kt-header-mobile__toggler--left" id="kt_aside_mobile_toggler"><span></span></button>
                <button class="kt-header-mobile__toggler" id="kt_header_mobile_toggler"><span></span></button>
                <button class="kt-header-mobile__topbar-toggler" id="kt_header_mobile_topbar_toggler"><i class="flaticon-more"></i></button>
            </div>
        </div>

        <div class="kt-grid kt-grid--hor kt-grid--root">
            <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver kt-page">

                <!-- Sidebar -->
                <button class="kt-aside-close " id="kt_aside_close_btn"><i class="la la-close"></i></button>
                <div class="kt-aside  kt-aside--fixed  kt-grid__item kt-grid kt-grid--desktop kt-grid--hor-desktop" id="kt_aside">
                    
                    <div class="kt-aside__brand kt-grid__item " id="kt_aside_brand">
                        <div class="kt-aside__brand-logo">
                            <a href="/admin" style="color: white; font-weight: 700; font-size: 20px; text-decoration: none;">
                                VALUBA <span style="color: #3b82f6;">ADMIN</span>
                            </a>
                        </div>
                    </div>

                    <div class="kt-aside-menu-wrapper kt-grid__item kt-grid__item--fluid" id="kt_aside_menu_wrapper">
                        <div id="kt_aside_menu" class="kt-aside-menu " data-ktmenu-vertical="1" data-ktmenu-scroll="1">
                            <ul class="kt-menu__nav ">
                                
                                <li class="kt-menu__item {{ Request::is('admin') ? 'kt-menu__item--active' : '' }}">
                                    <a href="/admin" class="kt-menu__link">
                                        <i class="kt-menu__link-icon fas fa-home"></i>
                                        <span class="kt-menu__link-text">Дашборд</span>
                                    </a>
                                </li>

                                <li class="kt-menu__section">
                                    <h4 class="kt-menu__section-text">Управление</h4>
                                </li>

                                <li class="kt-menu__item {{ (Request::is('admin/users*') || Request::is('admin/user/*')) ? 'kt-menu__item--active' : '' }}">
                                    <a href="/admin/users" class="kt-menu__link">
                                        <i class="kt-menu__link-icon fas fa-users"></i>
                                        <span class="kt-menu__link-text">Пользователи</span>
                                    </a>
                                </li>
                                <li class="kt-menu__item {{ Request::is('admin/bots*') ? 'kt-menu__item--active' : '' }}">
                                    <a href="{{ route('admin.bots') }}" class="kt-menu__link">
                                        <i class="kt-menu__link-icon fas fa-robot"></i>
                                        <span class="kt-menu__link-text">Боты</span>
                                    </a>
                                </li>
                                <li class="kt-menu__item {{ Request::is('admin/promocodes*') ? 'kt-menu__item--active' : '' }}">
                                    <a href="{{ route('admin.promocodes') }}" class="kt-menu__link">
                                        <i class="kt-menu__link-icon fas fa-ticket-alt"></i>
                                        <span class="kt-menu__link-text">Промокоды</span>
                                    </a>
                                </li>
                                <li class="kt-menu__item {{ Request::is('admin/tournaments*') ? 'kt-menu__item--active' : '' }}">
                                    <a href="{{ route('admin.tournaments') }}" class="kt-menu__link">
                                        <i class="kt-menu__link-icon fas fa-trophy"></i>
                                        <span class="kt-menu__link-text">Турниры</span>
                                    </a>
                                </li>
                                <li class="kt-menu__item {{ Request::is('admin/ranks*') ? 'kt-menu__item--active' : '' }}">
                                    <a href="{{ route('admin.ranks') }}" class="kt-menu__link">
                                        <i class="kt-menu__link-icon fas fa-star"></i>
                                        <span class="kt-menu__link-text">Ранги</span>
                                    </a>
                                </li>

                                <li class="kt-menu__section">
                                    <h4 class="kt-menu__section-text">Финансы</h4>
                                </li>

                                <li class="kt-menu__item {{ Request::is('admin/deposits') ? 'kt-menu__item--active' : '' }}">
                                    <a href="{{ route('admin.deposits') }}" class="kt-menu__link">
                                        <i class="kt-menu__link-icon fas fa-coins"></i>
                                        <span class="kt-menu__link-text">Пополнения</span>
                                    </a>
                                </li>
                                <li class="kt-menu__item {{ Request::is('admin/withdraws') ? 'kt-menu__item--active' : '' }}">
                                    <a href="{{ route('admin.withdraws') }}" class="kt-menu__link">
                                        <i class="kt-menu__link-icon fas fa-money-bill-wave"></i>
                                        <span class="kt-menu__link-text">Выплаты</span>
                                        @if(\App\Withdraw::where('status', 0)->count() > 0)
                                            <span class="menu-badge">{{ \App\Withdraw::where('status', 0)->count() }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li class="kt-menu__item {{ Request::is('admin/bonus') ? 'kt-menu__item--active' : '' }}">
                                    <a href="{{ route('admin.bonus') }}" class="kt-menu__link">
                                        <i class="kt-menu__link-icon fas fa-gift"></i>
                                        <span class="kt-menu__link-text">Бонусы</span>
                                    </a>
                                </li>
                                <li class="kt-menu__item {{ Request::is('admin/antiminus') ? 'kt-menu__item--active' : '' }}">
                                    <a href="/admin/antiminus" class="kt-menu__link">
                                        <i class="kt-menu__link-icon fas fa-shield-alt"></i>
                                        <span class="kt-menu__link-text">Антиминус</span>
                                    </a>
                                </li>

                                <li class="kt-menu__section">
                                    <h4 class="kt-menu__section-text">Система</h4>
                                </li>

                                <li class="kt-menu__item {{ Request::is('admin/settings') ? 'kt-menu__item--active' : '' }}">
                                    <a href="{{ route('admin.settings') }}" class="kt-menu__link">
                                        <i class="kt-menu__link-icon fas fa-cog"></i>
                                        <span class="kt-menu__link-text">Настройки</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Wrapper -->
                <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-wrapper" id="kt_wrapper">
                    
                    <!-- Header -->
                    <div id="kt_header" class="kt-header kt-grid__item  kt-header--fixed ">
                        
                        <!-- Header Menu (Left) -->
                        <div class="kt-header-menu-wrapper" id="kt_header_menu_wrapper">
                            <div class="header-search">
                                <i class="flaticon2-search-1"></i>
                                <input type="text" placeholder="Поиск пользователя (ID, Login)...">
                            </div>
                        </div>

                        <!-- Header Topbar (Right) -->
                        <div class="kt-header__topbar">
                            
                            <!-- System Status -->
                            <div class="kt-header__topbar-item">
                                <div class="kt-header__topbar-wrapper" style="padding: 0 10px;">
                                    <button type="button" class="btn btn-sm btn-light versionUpdate">
                                        <i class="flaticon-refresh"></i> Кэш
                                    </button>
                                </div>
                            </div>

                            <!-- User Profile -->
                            <div class="kt-header__topbar-item kt-header__topbar-item--user">
                                <div class="kt-header__topbar-wrapper" data-toggle="dropdown" data-offset="0px,0px">
                                    <div class="kt-header__topbar-user">
                                        <span class="kt-header__topbar-icon" style="margin-right: 10px;">
                                            <i class="flaticon2-user" style="font-size: 20px; color: #64748b;"></i>
                                        </span>
                                        <span class="kt-header__topbar-username">{{$u->username}}</span>
                                        <i class="flaticon2-down" style="font-size: 10px; margin-left: 8px; color: #a1a5b7;"></i>
                                    </div>
                                </div>
                                <div class="dropdown-menu dropdown-menu-fit dropdown-menu-right dropdown-menu-anim dropdown-menu-top-unround dropdown-menu-xl">
                                    <div class="kt-user-card kt-user-card--skin-dark kt-notification-item-padding-x" style="background: var(--primary-gradient);">
                                        <div class="kt-user-card__name">
                                            {{$u->username}}
                                        </div>
                                    </div>
                                    <div class="kt-notification">
                                        <a href="/admin/users/edit/{{$u->id}}" class="kt-notification__item">
                                            <div class="kt-notification__item-icon">
                                                <i class="flaticon2-calendar-3 kt-font-success"></i>
                                            </div>
                                            <div class="kt-notification__item-details">
                                                <div class="kt-notification__item-title kt-font-bold">
                                                    Мой профиль
                                                </div>
                                            </div>
                                        </a>
                                        <div class="kt-notification__custom">
                                            <a href="/" class="btn btn-outline-danger btn-sm btn-bold">Выйти на сайт</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor">
                        @yield('content')
                    </div>

                    <div class="kt-footer kt-grid__item kt-grid kt-grid--desktop kt-grid--ver-desktop">
                        <div class="kt-footer__copyright" style="color: var(--text-muted);">
                            2024 © VALUBA Admin Panel
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Multi Checker Modal -->
        <div class="modal fade" id="multiChecker" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Проверка на мультиаккаунты</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form class="kt-form-new" action="#" id="save">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Логин:</label>
                                <input type="text" class="form-control" id="login_multi" disabled readonly />
                            </div>
                            <div class="form-group">
                                <label>Найдено мультиаккаунтов: <span id="count_multi" class="badge badge-danger"></span></label>
                                <textarea type="text" class="form-control" id="list_multi" readonly disabled rows="3"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Депозиты:</label>
                                        <input type="text" class="form-control" id="multi_deposit" disabled readonly/>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Выплаты:</label>
                                        <input type="text" class="form-control" id="multi_withdraw" disabled readonly/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div id="kt_scrolltop" class="kt-scrolltop">
            <i class="fa fa-arrow-up"></i>
        </div>

        <script>
            const multiChecker = user_id => {
                $.post('/admin/users/checker', {
                    user_id
                })
                .then(response => {
                    $('#list_multi').val('Не найдено')
                    $('#count_multi').html(0)
                    $('#login_multi').val(response.user.username)
                    $('#multi_deposit').val(response.deposit + ' ₽')
                    $('#multi_withdraw').val(response.withdraw + ' ₽')

                    let multi = response.list.map(item => item.username)
                    if(multi.length !== 0) {
                        $('#list_multi').val(multi.join(', '))
                        $('#count_multi').html(multi.length)
                    }

                    $('#multiChecker').modal('show')
                })
            }
        </script>

        @if(session('error'))
            <script>
            $.notify({
                type: 'error',
                message: "{{ session('error') }}"
            });
            </script>
        @elseif(session('success'))
            <script>
            $.notify({
                type: 'success',
                message: "{{ session('success') }}"
            });
            </script>
        @endif
    </body>
</html>