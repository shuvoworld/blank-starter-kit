<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @yield('title', config('app.name', 'Laravel'))
    </title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>

    <!-- Styles -->
    @vite(['resources/scss/app.scss', 'resources/css/project.css','resources/js/app.js'])
    @stack('styles')
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">
    <!-- Header -->
@hasSection('navbar')
    @yield('navbar')
@else
    @include('layouts.dashboard.partials.navbar')
@endif

<!-- Sidebar -->
@hasSection('sidebar')
    @yield('sidebar')
@else
    @include('layouts.dashboard.partials.sidebar')
@endif

<!-- Main Content -->
    <main class="app-main">
        <!-- Page Header -->
        @hasSection('content-top')
            <div class="app-content-header">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            @yield('header')
                        </div>
                        <div class="col-sm-6">
                            @hasSection('breadcrumb')
                                <ol class="breadcrumb float-sm-end">
                                    @yield('breadcrumb')
                                </ol>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

    <!-- Page Content -->
        <div class="app-content">
            <div class="container-fluid">
                @include('layouts.dashboard.partials.session-message')

                @yield('content')
            </div>
        </div>
    </main>
    <!-- Footer -->
    @hasSection('footer')
        @yield('footer')
    @else
        @include('layouts.dashboard.partials.footer')
    @endif

</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@stack('scripts')
</body>
</html>
