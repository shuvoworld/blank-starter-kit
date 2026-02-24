<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Admin Panel'))</title>

    @vite(['resources/scss/app.scss', 'resources/js/app.js'])

    @stack('styles')
</head>
<body>
    <div class="d-flex" id="wrapper">
        {{-- Sidebar --}}
        <aside class="bg-dark text-white" id="sidebar" style="width: 250px; min-height: 100vh;">
            <div class="sidebar-header p-3 border-bottom border-secondary">
                <h4 class="mb-0">{{ config('app.name', 'Admin') }}</h4>
            </div>
            <nav class="nav flex-column p-3">
                @yield('sidebar')
            </nav>
        </aside>

        {{-- Page Content --}}
        <div class="flex-grow-1">
            {{-- Top Navbar --}}
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4">
                <div class="container-fluid">
                    <button class="btn btn-link" id="sidebarToggle">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="d-flex align-items-center ms-auto">
                        @yield('navbar-right')
                    </div>
                </div>
            </nav>

            {{-- Main Content --}}
            <main class="p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
