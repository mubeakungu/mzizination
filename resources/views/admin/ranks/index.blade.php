@extends('admin/layout')

@section('content')
<script src="/dash/js/dtables.js?v={{time()}}" type="text/javascript"></script>
<div class="kt-subheader kt-grid__item" id="kt_subheader">
    <div class="kt-subheader__main">
        <h3 class="kt-subheader__title">Ранги</h3>
    </div>
</div>

<div class="kt-content kt-grid__item kt-grid__item--fluid" id="kt_content">
    <div class="kt-portlet kt-portlet--mobile">
        <div class="kt-portlet__head kt-portlet__head--lg">
            <div class="kt-portlet__head-label">
                <span class="kt-portlet__head-icon">
                    <i class="kt-font-brand flaticon2-menu-2"></i>
                </span>
                <h3 class="kt-portlet__head-title">
                    Список рангов
                </h3>
            </div>
            <div class="kt-portlet__head-toolbar">
                <div class="kt-portlet__head-wrapper">
                    <div class="kt-portlet__head-actions">
                        <a data-toggle="modal" href="#new" class="btn btn-success btn-elevate btn-icon-sm">
                            <i class="la la-plus"></i>
                            Добавить
                        </a>
                    </div>  
                </div>
            </div>
        </div>
        <div class="kt-portlet__body">

            <!--begin: Datatable -->
            <table class="table table-striped- table-bordered table-hover table-checkable" id="ranks">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Ставок</th>
                        <td>Пополнений</td>
                        <td>Мин. Бонус</td>
                        <td>Макс. Бонус</td>
                        <td>Комиссия</td>
                        <th>Cashback</th>
                        <th>Награда</th>
                        <th>Действия</th>
                    </tr>
                </thead>
            </table>

            <!--end: Datatable -->
        </div>
    </div>
</div>
<style>
    @media (max-width:1100px) {
        .col-lg-4 {
            margin-top: 20px;
        }
    }
</style>
@endsection
