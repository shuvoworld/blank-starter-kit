<?php
use App\Models\LandingPageSection;

// Get all landing page sections grouped by section
$sections = LandingPageSection::getGroupedBySection();

// Helper function to get section value
$getValue = fn (string $key, mixed $default = '') => LandingPageSection::getValue($key, $default);
?>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $getValue('site_description', config('app.name')) }}">
    <title>{{ $getValue('site_name', config('app.name', 'Laravel')) }} - Home</title>

    @vite(['resources/scss/app.scss', 'resources/js/app.js'])

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
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="/">
                <i class="bi bi-hexagon-fill me-2"></i>
                {{ $getValue('site_name', config('app.name', 'Laravel')) }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#pricing">Pricing</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                </ul>
                <div class="d-flex gap-2 mt-3 mt-lg-0">
                    <a href="{{ route('login') }}" class="btn btn-outline-primary">Login</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-gradient text-white py-5">
        <div class="container py-lg-5">
            <div class="row align-items-center min-vh-75">
                <div class="col-lg-6 py-5">
                    <span class="badge bg-white text-primary mb-3 px-3 py-2 rounded-pill">
                        <i class="bi bi-stars me-1"></i> {{ $getValue('hero_badge_text', 'New Features Available') }}
                    </span>
                    <h1 class="display-3 fw-bold mb-4">
                        {{ $getValue('hero_headline', 'Transform Your Business with Smart Solutions') }}
                    </h1>
                    <p class="lead mb-5 opacity-90">
                        {{ $getValue('hero_subtitle', 'Streamline operations, boost productivity, and scale effortlessly. The all-in-one platform designed for modern businesses to succeed in the digital age.') }}
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-light btn-lg px-4 fw-semibold">
                                <i class="bi bi-rocket-takeoff me-2"></i>{{ $getValue('hero_primary_button_text', 'Get Started Free') }}
                            </a>
                        @endif
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-4">
                            <i class="bi bi-box-arrow-in-right me-2"></i>{{ $getValue('hero_secondary_button_text', 'Sign In') }}
                        </a>
                    </div>
                    <div class="mt-5 d-flex align-items-center gap-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill fs-4 me-2"></i>
                            <span class="small">{{ $getValue('hero_trust_1', 'No credit card required') }}</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill fs-4 me-2"></i>
                            <span class="small">{{ $getValue('hero_trust_2', '14-day free trial') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <div class="position-relative">
                        <div class="bg-white bg-opacity-10 rounded-4 p-5 backdrop-blur">
                            <div class="bg-white rounded-3 shadow-lg p-4">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="bg-primary rounded-circle p-3 me-3">
                                        <i class="bi bi-bar-chart-fill text-white fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Dashboard Overview</h6>
                                        <small class="text-muted">Real-time analytics</small>
                                    </div>
                                </div>
                                <div class="row g-3 mb-4">
                                    <div class="col-6">
                                        <div class="bg-light rounded p-3 text-center">
                                            <h4 class="mb-0 text-primary">{{ $getValue('stats_users_count', '2.4K') }}</h4>
                                            <small class="text-muted">{{ $getValue('stats_users_label', 'Users') }}</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-light rounded p-3 text-center">
                                            <h4 class="mb-0 text-success">+28%</h4>
                                            <small class="text-muted">Growth</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-light rounded p-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <small class="text-muted">Revenue Progress</small>
                                        <small class="text-muted">78%</small>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 78%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="position-absolute bottom-0 end-0 bg-white rounded-4 shadow-lg p-4 d-flex align-items-center gap-3" style="transform: translateY(30%);">
                            <div class="bg-success bg-opacity-10 rounded-circle p-2">
                                <i class="bi bi-check-lg text-success fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Task Completed!</h6>
                                <small class="text-muted">Just now</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Trusted By Section -->
    <section class="py-4 bg-white border-bottom">
        <div class="container">
            <p class="text-center text-muted mb-4 small text-uppercase fw-semibold">Trusted by 10,000+ businesses worldwide</p>
            <div class="row justify-content-center align-items-center g-4">
                <div class="col-auto"><div class="text-muted opacity-50 fw-bold">TechCorp</div></div>
                <div class="col-auto"><div class="text-muted opacity-50 fw-bold">InnovateCo</div></div>
                <div class="col-auto"><div class="text-muted opacity-50 fw-bold">ScaleUp</div></div>
                <div class="col-auto"><div class="text-muted opacity-50 fw-bold">GlobalTech</div></div>
                <div class="col-auto"><div class="text-muted opacity-50 fw-bold">FutureLabs</div></div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container py-lg-5">
            <div class="text-center mb-5">
                <span class="badge bg-primary bg-opacity-10 text-primary mb-3 px-3 py-2 rounded-pill">Features</span>
                <h2 class="display-5 fw-bold mb-3">{{ $getValue('features_title', 'Everything You Need to Succeed') }}</h2>
                <p class="text-muted lead mx-auto" style="max-width: 600px;">
                    {{ $getValue('features_subtitle', 'Powerful features to help you manage, grow, and scale your business with confidence.') }}
                </p>
            </div>
            <div class="row g-4">
                <!-- Feature 1 -->
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card card h-100 border-0 shadow-sm p-4">
                        <div class="bg-primary bg-opacity-10 rounded-3 d-inline-flex p-3 mb-4">
                            <i class="bi bi-{{ $getValue('feature_1_icon', 'speedometer2') }} text-primary fs-3"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-3">{{ $getValue('feature_1_title', 'Real-Time Dashboard') }}</h4>
                        <p class="text-muted mb-0">
                            {{ $getValue('feature_1_description', 'Monitor your business metrics in real-time with intuitive charts and actionable insights at your fingertips.') }}
                        </p>
                    </div>
                </div>
                <!-- Feature 2 -->
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card card h-100 border-0 shadow-sm p-4">
                        <div class="bg-success bg-opacity-10 rounded-3 d-inline-flex p-3 mb-4">
                            <i class="bi bi-{{ $getValue('feature_2_icon', 'shield-check') }} text-success fs-3"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-3">{{ $getValue('feature_2_title', 'Enterprise Security') }}</h4>
                        <p class="text-muted mb-0">
                            {{ $getValue('feature_2_description', 'Bank-level encryption and security protocols to keep your data safe and compliant with industry standards.') }}
                        </p>
                    </div>
                </div>
                <!-- Feature 3 -->
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card card h-100 border-0 shadow-sm p-4">
                        <div class="bg-info bg-opacity-10 rounded-3 d-inline-flex p-3 mb-4">
                            <i class="bi bi-{{ $getValue('feature_3_icon', 'people') }} text-info fs-3"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-3">{{ $getValue('feature_3_title', 'Team Collaboration') }}</h4>
                        <p class="text-muted mb-0">
                            {{ $getValue('feature_3_description', 'Work seamlessly with your team using role-based access, shared workspaces, and real-time updates.') }}
                        </p>
                    </div>
                </div>
                <!-- Feature 4 -->
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card card h-100 border-0 shadow-sm p-4">
                        <div class="bg-warning bg-opacity-10 rounded-3 d-inline-flex p-3 mb-4">
                            <i class="bi bi-{{ $getValue('feature_4_icon', 'graph-up') }} text-warning fs-3"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-3">{{ $getValue('feature_4_title', 'Advanced Analytics') }}</h4>
                        <p class="text-muted mb-0">
                            {{ $getValue('feature_4_description', 'Deep dive into your data with comprehensive reports, custom filters, and exportable insights.') }}
                        </p>
                    </div>
                </div>
                <!-- Feature 5 -->
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card card h-100 border-0 shadow-sm p-4">
                        <div class="bg-danger bg-opacity-10 rounded-3 d-inline-flex p-3 mb-4">
                            <i class="bi bi-{{ $getValue('feature_5_icon', 'gear') }} text-danger fs-3"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-3">{{ $getValue('feature_5_title', 'Customizable Workflows') }}</h4>
                        <p class="text-muted mb-0">
                            {{ $getValue('feature_5_description', 'Automate repetitive tasks and create custom workflows that adapt to your unique business processes.') }}
                        </p>
                    </div>
                </div>
                <!-- Feature 6 -->
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card card h-100 border-0 shadow-sm p-4">
                        <div class="bg-indigo bg-opacity-10 rounded-3 d-inline-flex p-3 mb-4" style="background-color: rgba(99, 102, 241, 0.1);">
                            <i class="bi bi-{{ $getValue('feature_6_icon', 'plug') }} text-primary fs-3" style="color: #6366f1 !important;"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-3">{{ $getValue('feature_6_title', 'Seamless Integrations') }}</h4>
                        <p class="text-muted mb-0">
                            {{ $getValue('feature_6_description', 'Connect with your favorite tools and services through our extensive API and pre-built integrations.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About / Product Details Section -->
    <section id="about" class="py-5 bg-light">
        <div class="container py-lg-5">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="badge bg-primary bg-opacity-10 text-primary mb-3 px-3 py-2 rounded-pill">About Us</span>
                    <h2 class="display-5 fw-bold mb-4">{{ $getValue('about_title', 'Built for Modern Businesses') }}</h2>
                    <p class="text-muted lead mb-4">
                        {{ $getValue('about_description', "We understand the challenges of running a business in today's fast-paced world. That's why we built our platform to help you work smarter, not harder.") }}
                    </p>
                    <div class="d-flex gap-3 mb-4">
                        <div class="bg-white rounded p-3 shadow-sm flex-shrink-0">
                            <i class="bi bi-check-circle-fill text-success fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-2">{{ $getValue('about_benefit_1_title', 'Easy to Use') }}</h5>
                            <p class="text-muted mb-0">{{ $getValue('about_benefit_1_desc', 'Intuitive interface designed with user experience in mind. No technical expertise required.') }}</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3 mb-4">
                        <div class="bg-white rounded p-3 shadow-sm flex-shrink-0">
                            <i class="bi bi-lightning-charge-fill text-warning fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-2">{{ $getValue('about_benefit_2_title', 'Lightning Fast') }}</h5>
                            <p class="text-muted mb-0">{{ $getValue('about_benefit_2_desc', 'Built on cutting-edge technology for optimal performance and reliability.') }}</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="bg-white rounded p-3 shadow-sm flex-shrink-0">
                            <i class="bi bi-headset text-primary fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-2">{{ $getValue('about_benefit_3_title', '24/7 Support') }}</h5>
                            <p class="text-muted mb-0">{{ $getValue('about_benefit_3_desc', 'Our dedicated support team is always ready to help you succeed.') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="position-relative">
                        <div class="bg-white rounded-4 shadow-lg p-4">
                            <img src="https://placehold.co/600x400/e0e7ff/1e1b4b?text=Product+Dashboard" alt="Product Dashboard" class="img-fluid rounded-3">
                        </div>
                        <div class="position-absolute top-0 end-0 bg-white rounded-4 shadow-lg p-4 d-none d-md-block" style="transform: translate(20%, -20%);">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-success bg-opacity-10 rounded-circle p-2">
                                    <i class="bi bi-check-lg text-success"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Automated</h6>
                                    <small class="text-muted">Workflows</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5 hero-gradient-alt text-white">
        <div class="container py-lg-4">
            <div class="row text-center g-4">
                <div class="col-md-3 col-6">
                    <h3 class="display-4 fw-bold mb-2">{{ $getValue('stats_users_count', '10K+') }}</h3>
                    <p class="mb-0 opacity-75">{{ $getValue('stats_users_label', 'Active Users') }}</p>
                </div>
                <div class="col-md-3 col-6">
                    <h3 class="display-4 fw-bold mb-2">{{ $getValue('stats_uptime_count', '99.9%') }}</h3>
                    <p class="mb-0 opacity-75">{{ $getValue('stats_uptime_label', 'Uptime') }}</p>
                </div>
                <div class="col-md-3 col-6">
                    <h3 class="display-4 fw-bold mb-2">{{ $getValue('stats_integrations_count', '50+') }}</h3>
                    <p class="mb-0 opacity-75">{{ $getValue('stats_integrations_label', 'Integrations') }}</p>
                </div>
                <div class="col-md-3 col-6">
                    <h3 class="display-4 fw-bold mb-2">{{ $getValue('stats_rating_count', '4.9/5') }}</h3>
                    <p class="mb-0 opacity-75">{{ $getValue('stats_rating_label', 'User Rating') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-5">
        <div class="container py-lg-5">
            <div class="text-center mb-5">
                <span class="badge bg-primary bg-opacity-10 text-primary mb-3 px-3 py-2 rounded-pill">Pricing</span>
                <h2 class="display-5 fw-bold mb-3">{{ $getValue('pricing_title', 'Simple, Transparent Pricing') }}</h2>
                <p class="text-muted lead mx-auto" style="max-width: 600px;">
                    {{ $getValue('pricing_subtitle', 'Choose the plan that fits your needs. No hidden fees, cancel anytime.') }}
                </p>
            </div>
            <div class="row justify-content-center g-4">
                <!-- Basic Plan -->
                <div class="col-lg-4 col-md-6">
                    <div class="pricing-card card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <h5 class="text-muted mb-2">{{ $getValue('pricing_basic_name', 'Basic') }}</h5>
                                <div class="display-4 fw-bold mb-2">{{ $getValue('pricing_basic_price', '$0') }}</div>
                                <small class="text-muted">{{ $getValue('pricing_basic_period', 'Forever free') }}</small>
                            </div>
                            <ul class="list-unstyled mb-4">
                                <li class="d-flex align-items-center mb-3">
                                    <i class="bi bi-check2 text-success me-3 fs-5"></i>
                                    <span>Up to 3 users</span>
                                </li>
                                <li class="d-flex align-items-center mb-3">
                                    <i class="bi bi-check2 text-success me-3 fs-5"></i>
                                    <span>Basic analytics</span>
                                </li>
                                <li class="d-flex align-items-center mb-3">
                                    <i class="bi bi-check2 text-success me-3 fs-5"></i>
                                    <span>5GB storage</span>
                                </li>
                                <li class="d-flex align-items-center mb-3">
                                    <i class="bi bi-check2 text-success me-3 fs-5"></i>
                                    <span>Email support</span>
                                </li>
                                <li class="d-flex align-items-center text-muted">
                                    <i class="bi bi-x text-muted me-3 fs-5"></i>
                                    <span>Advanced features</span>
                                </li>
                            </ul>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-outline-primary w-100 py-2">Get Started</a>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- Pro Plan -->
                <div class="col-lg-4 col-md-6">
                    <div class="pricing-card popular card h-100 shadow-lg">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <h5 class="text-primary mb-2">{{ $getValue('pricing_pro_name', 'Pro') }}</h5>
                                <div class="display-4 fw-bold mb-2">{{ $getValue('pricing_pro_price', '$29') }}</div>
                                <small class="text-muted">{{ $getValue('pricing_pro_period', 'per month') }}</small>
                            </div>
                            <ul class="list-unstyled mb-4">
                                <li class="d-flex align-items-center mb-3">
                                    <i class="bi bi-check2-circle text-primary me-3 fs-5"></i>
                                    <span>Up to 25 users</span>
                                </li>
                                <li class="d-flex align-items-center mb-3">
                                    <i class="bi bi-check2-circle text-primary me-3 fs-5"></i>
                                    <span>Advanced analytics</span>
                                </li>
                                <li class="d-flex align-items-center mb-3">
                                    <i class="bi bi-check2-circle text-primary me-3 fs-5"></i>
                                    <span>100GB storage</span>
                                </li>
                                <li class="d-flex align-items-center mb-3">
                                    <i class="bi bi-check2-circle text-primary me-3 fs-5"></i>
                                    <span>Priority support</span>
                                </li>
                                <li class="d-flex align-items-center mb-3">
                                    <i class="bi bi-check2-circle text-primary me-3 fs-5"></i>
                                    <span>API access</span>
                                </li>
                            </ul>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-primary w-100 py-2">Get Started</a>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- Enterprise Plan -->
                <div class="col-lg-4 col-md-6">
                    <div class="pricing-card card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <h5 class="text-muted mb-2">{{ $getValue('pricing_enterprise_name', 'Enterprise') }}</h5>
                                <div class="display-4 fw-bold mb-2">{{ $getValue('pricing_enterprise_price', '$99') }}</div>
                                <small class="text-muted">{{ $getValue('pricing_enterprise_period', 'per month') }}</small>
                            </div>
                            <ul class="list-unstyled mb-4">
                                <li class="d-flex align-items-center mb-3">
                                    <i class="bi bi-check2 text-success me-3 fs-5"></i>
                                    <span>Unlimited users</span>
                                </li>
                                <li class="d-flex align-items-center mb-3">
                                    <i class="bi bi-check2 text-success me-3 fs-5"></i>
                                    <span>Custom analytics</span>
                                </li>
                                <li class="d-flex align-items-center mb-3">
                                    <i class="bi bi-check2 text-success me-3 fs-5"></i>
                                    <span>Unlimited storage</span>
                                </li>
                                <li class="d-flex align-items-center mb-3">
                                    <i class="bi bi-check2 text-success me-3 fs-5"></i>
                                    <span>24/7 phone support</span>
                                </li>
                                <li class="d-flex align-items-center mb-3">
                                    <i class="bi bi-check2 text-success me-3 fs-5"></i>
                                    <span>Custom integrations</span>
                                </li>
                            </ul>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-outline-primary w-100 py-2">Contact Sales</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-5 bg-light">
        <div class="container py-lg-5">
            <div class="text-center mb-5">
                <span class="badge bg-primary bg-opacity-10 text-primary mb-3 px-3 py-2 rounded-pill">Testimonials</span>
                <h2 class="display-5 fw-bold mb-3">Loved by Thousands</h2>
                <p class="text-muted lead mx-auto" style="max-width: 600px;">
                    See what our customers have to say about their experience.
                </p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm p-4">
                        <div class="mb-3">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                        <p class="text-muted mb-4">
                            "{{ $getValue('site_name', config('app.name')) }} has transformed how we manage our business. The intuitive interface and powerful features have saved us countless hours."
                        </p>
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                                <i class="bi bi-person-fill text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Sarah Johnson</h6>
                                <small class="text-muted">CEO, TechCorp</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm p-4">
                        <div class="mb-3">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                        <p class="text-muted mb-4">
                            "The customer support is exceptional. Anytime we have a question, the team responds quickly with helpful solutions."
                        </p>
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success bg-opacity-10 rounded-circle p-2">
                                <i class="bi bi-person-fill text-success"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Michael Chen</h6>
                                <small class="text-muted">CTO, InnovateCo</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm p-4">
                        <div class="mb-3">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                        <p class="text-muted mb-4">
                            "We've tried many solutions, but {{ $getValue('site_name', config('app.name')) }} is by far the best. It's reliable, fast, and does exactly what we need."
                        </p>
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-info bg-opacity-10 rounded-circle p-2">
                                <i class="bi bi-person-fill text-info"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Emily Rodriguez</h6>
                                <small class="text-muted">Founder, ScaleUp</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section text-white py-5">
        <div class="container py-lg-5 text-center">
            <h2 class="display-4 fw-bold mb-4">{{ $getValue('cta_title', 'Ready to Get Started?') }}</h2>
            <p class="lead mb-5 opacity-75" style="max-width: 600px; margin-left: auto; margin-right: auto;">
                {{ $getValue('cta_description', "Join thousands of businesses already using our platform to grow their business. Start your free trial today.") }}
            </p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn btn-light btn-lg px-5 fw-semibold">
                        {{ $getValue('cta_primary_button', 'Start Free Trial') }}
                    </a>
                @endif
                <a href="#contact" class="btn btn-outline-light btn-lg px-5">
                    {{ $getValue('cta_secondary_button', 'Contact Sales') }}
                </a>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5">
        <div class="container py-lg-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="text-center mb-5">
                        <span class="badge bg-primary bg-opacity-10 text-primary mb-3 px-3 py-2 rounded-pill">Contact Us</span>
                        <h2 class="display-5 fw-bold mb-3">{{ $getValue('contact_title', 'Get in Touch') }}</h2>
                        <p class="text-muted lead">
                            {{ $getValue('contact_subtitle', "Have questions? We'd love to hear from you.") }}
                        </p>
                    </div>
                    <div class="card border-0 shadow-lg">
                        <div class="card-body p-4 p-lg-5">
                            <form>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">First Name</label>
                                        <input type="text" class="form-control" placeholder="John">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" class="form-control" placeholder="Doe">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" class="form-control" placeholder="john@example.com">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Subject</label>
                                        <select class="form-select">
                                            <option>General Inquiry</option>
                                            <option>Sales Question</option>
                                            <option>Technical Support</option>
                                            <option>Partnership</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Message</label>
                                        <textarea class="form-control" rows="5" placeholder="How can we help you?"></textarea>
                                    </div>
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn btn-primary btn-lg px-5">
                                            <i class="bi bi-send me-2"></i>Send Message
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="row mt-4 g-3">
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="bi bi-envelope-fill text-primary fs-3 mb-2"></i>
                                <p class="mb-0">{{ $getValue('contact_email', 'contact@example.com') }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="bi bi-telephone-fill text-success fs-3 mb-2"></i>
                                <p class="mb-0">{{ $getValue('contact_phone', '+1 (555) 123-4567') }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="bi bi-geo-alt-fill text-danger fs-3 mb-2"></i>
                                <p class="mb-0 small">{{ $getValue('contact_address', '123 Business Street') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-hexagon-fill me-2"></i>
                        {{ $getValue('site_name', config('app.name', 'Laravel')) }}
                    </h5>
                    <p class="text-muted">
                        {{ $getValue('footer_description', 'The modern platform for businesses to manage, grow, and scale with confidence.') }}
                    </p>
                    <div class="d-flex gap-3">
                        <a href="{{ $getValue('footer_facebook', '#') }}" class="text-muted text-decoration-none">
                            <i class="bi bi-facebook fs-5"></i>
                        </a>
                        <a href="{{ $getValue('footer_twitter', '#') }}" class="text-muted text-decoration-none">
                            <i class="bi bi-twitter fs-5"></i>
                        </a>
                        <a href="{{ $getValue('footer_linkedin', '#') }}" class="text-muted text-decoration-none">
                            <i class="bi bi-linkedin fs-5"></i>
                        </a>
                        <a href="{{ $getValue('footer_github', '#') }}" class="text-muted text-decoration-none">
                            <i class="bi bi-github fs-5"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h6 class="fw-semibold mb-3">Product</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#features" class="text-muted text-decoration-none">Features</a></li>
                        <li class="mb-2"><a href="#pricing" class="text-muted text-decoration-none">Pricing</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Integrations</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">API</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h6 class="fw-semibold mb-3">Company</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#about" class="text-muted text-decoration-none">About</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Blog</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Careers</a></li>
                        <li class="mb-2"><a href="#contact" class="text-muted text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h6 class="fw-semibold mb-3">Account</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="{{ route('login') }}" class="text-muted text-decoration-none">Login</a></li>
                        @if (Route::has('register'))
                            <li class="mb-2"><a href="{{ route('register') }}" class="text-muted text-decoration-none">Register</a></li>
                        @endif
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Forgot Password</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h6 class="fw-semibold mb-3">Legal</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Privacy Policy</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Terms of Service</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Cookie Policy</a></li>
                    </ul>
                </div>
            </div>
            <hr class="border-secondary">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="text-muted mb-0">
                        &copy; {{ date('Y') }} {{ $getValue('site_name', config('app.name', 'Laravel')) }}. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="text-muted mb-0">
                        {{ $getValue('footer_copyright', 'Made with love using Laravel & Bootstrap 5') }}
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Close mobile menu on link click -->
    <script>
        document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
            link.addEventListener('click', () => {
                const navbarCollapse = document.getElementById('navbarNav');
                if (navbarCollapse.classList.contains('show')) {
                    new bootstrap.Collapse(navbarCollapse).hide();
                }
            });
        });
    </script>

</body>
</html>
