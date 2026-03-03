<!-- Footer -->
<footer class="bg-dark text-white py-5">
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-lg-4"><h5 class="fw-bold mb-3"><i class="bi bi-hexagon-fill me-2"></i> {{ $getValue('site_name', config('app.name', 'Laravel')) }}
                </h5>
                <p class="text-muted"> {{ $getValue('footer_description', 'The modern platform for businesses to manage, grow, and scale with confidence.') }} </p>
                <div class="d-flex gap-3"><a href="{{ $getValue('footer_facebook', '#') }}" class="text-muted text-decoration-none"> <i
                                class="bi bi-facebook fs-5"></i> </a> <a href="{{ $getValue('footer_twitter', '#') }}" class="text-muted text-decoration-none">
                        <i class="bi bi-twitter fs-5"></i> </a> <a href="{{ $getValue('footer_linkedin', '#') }}" class="text-muted text-decoration-none"> <i
                                class="bi bi-linkedin fs-5"></i> </a> <a href="{{ $getValue('footer_github', '#') }}" class="text-muted text-decoration-none">
                        <i class="bi bi-github fs-5"></i> </a></div>
            </div>
            <div class="col-lg-2 col-md-4"><h6 class="fw-semibold mb-3">Product</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#features" class="text-muted text-decoration-none">Features</a></li>
                    <li class="mb-2"><a href="#pricing" class="text-muted text-decoration-none">Pricing</a></li>
                    <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Integrations</a></li>
                    <li class="mb-2"><a href="#" class="text-muted text-decoration-none">API</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-4"><h6 class="fw-semibold mb-3">Company</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#about" class="text-muted text-decoration-none">About</a></li>
                    <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Blog</a></li>
                    <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Careers</a></li>
                    <li class="mb-2"><a href="#contact" class="text-muted text-decoration-none">Contact</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-4"><h6 class="fw-semibold mb-3">Account</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route('login') }}" class="text-muted text-decoration-none">Login</a></li> @if (Route::has('register'))
                        <li class="mb-2"><a href="{{ route('register') }}" class="text-muted text-decoration-none">Register</a></li> @endif
                    <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Forgot Password</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-4"><h6 class="fw-semibold mb-3">Legal</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Privacy Policy</a></li>
                    <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Terms of Service</a></li>
                    <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Cookie Policy</a></li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start"><p class="text-muted mb-0">
                    &copy; {{ date('Y') }} {{ $getValue('site_name', config('app.name', 'Laravel')) }}. All rights reserved. </p></div>
            <div class="col-md-6 text-center text-md-end"><p
                        class="text-muted mb-0"> {{ $getValue('footer_copyright', 'Made with love using Laravel & Bootstrap 5') }} </p></div>
        </div>
    </div>
</footer>