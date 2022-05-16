<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Kdrm') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        @if (Request::is('admin') or Request::is('user/*'))
            <link rel="stylesheet" href="{{ asset('css/counter.css') }}">
        @endif

        @if (Request::is('admin/user/*'))
            <link rel="stylesheet" href="{{ asset('css/user.css') }}">
        @endif

        @if (Request::is('admin/users') or Request::is('admin/users/*'))
            <link rel="stylesheet" href="{{ asset('css/users.css') }}">
        @endif


        @if (Request::is('dashboard') or Request::is('dashboard/*') or Request::is('admin/servers') or Request::is('admin/servers/*'))
            <link rel="stylesheet" href="{{ asset('css/serverdisplay.css') }}">
        @endif

        <!-- CSS only -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <!-- Scripts -->
        {!!  GoogleReCaptchaV3::init() !!}
        <script src="{{ asset('js/app.js') }}" defer></script>
        <script src="https://kit.fontawesome.com/d8b4764d55.js" crossorigin="anonymous"></script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
