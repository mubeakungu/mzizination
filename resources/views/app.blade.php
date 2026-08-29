
@auth
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
        <link rel="shortcut icon" href="/image/icon.png?v={{time()}}">
        <title>{{ $settings->title }}</title>
        <meta name="description" content="{{ $settings->description }}">
        <meta name="keywords" content="{{ $settings->keywords }}">
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600&display=swap" rel="stylesheet">

        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    </head>
    <body>
        <noscript>You need to enable JavaScript to run this app.</noscript>
        <div id="root"></div>
        <div class="errors" style="display: none">{{ session('error') }}</div>
    </body>
    @php
        if(isset($_GET['invite'])) {
            session_start();
            $_SESSION['ref'] = $_GET['invite'];
        }
    @endphp
    <script src="/js/app.js?v={{ $settings->file_version }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}?v={{ $settings->file_version }}">
    <link rel="stylesheet" class="theme" href="#">
    <link rel="stylesheet" href="{{ asset('assets/wheel.css') }}?v={{ $settings->file_version }}">
    <script src="/js/theme.js?v={{ $settings->file_version }}"></script>
</html>
@else
@include('confirm')
@endif