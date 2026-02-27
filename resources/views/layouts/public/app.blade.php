<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- SEO --}}
    <meta name="description" content="{{ $getValue('site_description', config('app.name')) }}">

    {{-- Title --}}
    <title>
        @yield('title', $getValue('site_name', config('app.name', 'Laravel')) . ' - Home')
    </title>
    {{-- Assets --}}
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    {{-- Default Layout CSS --}}
    <style>
        /* Hero gradient background */
        .hero-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .hero-gradient-alt {
            background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%);
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Feature card hover effect */
        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        /* Pricing card */
        .pricing-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .pricing-card:hover {
            transform: scale(1.03);
        }
        .pricing-card.popular {
            border: 2px solid #6366f1;
            position: relative;
        }
        .pricing-card.popular::before {
            content: 'RECOMMENDED';
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: #6366f1;
            color: white;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
        }

        /* Section spacing */
        section {
            scroll-margin-top: 76px;
        }
    </style>
    {{-- Page Specific CSS --}}
    @stack('styles')
</head>

<body class="bg-light">

{{-- ================= NAVBAR ================= --}}
@includeIf('layouts.public.partials.navbar')

{{-- Optional Top Section --}}
@yield('content-top')

{{-- ================= MAIN CONTENT ================= --}}
<main>
    @hasSection('content')
        @yield('content')
    @else
        {{-- Default fallback content --}}
        <section class="py-5 text-center">
            <div class="container">
                <h1 class="fw-bold">
                    Welcome to {{ $getValue('site_name', config('app.name')) }}
                </h1>
                <p class="lead text-muted mt-3">
                    {{ $getValue('site_description', 'Your modern Laravel platform.') }}
                </p>
            </div>
        </section>
    @endif
</main>

{{-- ================= FOOTER ================= --}}
@includeIf('layouts.public.partials.footer')

{{-- Page Specific JS --}}
@stack('scripts')

</body>
</html>