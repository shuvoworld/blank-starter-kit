<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>  @yield('title', $getValue('site_name', config('app.name', 'Laravel')) . ' - Login/Register')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])


    @stack('styles')
</head>
<body class="login-page bg-body-secondary">
    <div class="login-box">
        @hasSection('content-top')
            @yield('content-top')
        @else
            {{-- Default fallback content --}}
            <div class="login-logo">
                <a href="{{ url('/') }}">{{ config('app.name', 'Laravel') }}</a>
            </div>
        @endif

        @yield('content')
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @stack('scripts')
</body>
</html>
