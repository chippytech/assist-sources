<style>
        :root {
            --brand-dark: #17272c;
            --brand-primary: #007bff;
        }
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .hero-section { padding: 100px 0; background: linear-gradient(135deg, #fff 0%, #e9ecef 100%); }
        .btn-brand { background-color: var(--brand-primary); color: white; border-radius: 8px; font-weight: 600; transition: transform 0.2s; }
        .btn-brand:hover { transform: translateY(-2px); color: white; opacity: 0.9; }
        .feature-icon { font-size: 2rem; color: var(--brand-primary); margin-bottom: 1rem; }
    </style>
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="/assist_logo.png" height="40" alt="Logo" class="me-2">
                <span class="fw-bold" style="color: var(--brand-dark);">Assist</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link mx-2" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link mx-2" href="https://chippytime.com/assist">About</a></li>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-outline-primary px-4" href="/auth">Sign In</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 text-center text-lg-start mb-5 mb-lg-0">
                    <h1 class="display-4 fw-bold mb-3" style="color: var(--brand-dark);">
                        Bringing reliable AI experiences to everyone.
                    </h1>
                    <p class="lead text-muted mb-4">
                        Assist brings the most powerful AI models together in one clean, seamless interface. 
                        No multiple accounts. No switching platforms. Just fast, intelligent conversations.
                    </p>
                    <div class="d-grid gap-3 d-sm-flex justify-content-sm-center justify-content-lg-start">
                        <a href="/auth" class="btn btn-brand btn-lg px-5">Get Started Free</a>
                        <a href="#features" class="btn btn-light btn-lg px-5 border">Explore Features</a>
                    </div>
                </div>
                <div class="col-lg-5 offset-lg-1">
                    <div class="card border-0 shadow-lg text-center p-4 p-md-5" style="border-radius: 22px;">
                        <img src="/assist_logo.png" height="50" alt="Assist logo" class="mx-auto mb-3">
                        <h2 class="h4 fw-bold">Try Assist Now</h2>
                        <p class="text-muted small">One unified chat for all your favorite models.</p>
                        <hr class="my-4 mx-auto" style="width: 30%;">
                        <div class="text-start mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-success-subtle text-success rounded-pill me-2">✓</span>
                                <small>No Credit Card Required</small>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-success-subtle text-success rounded-pill me-2">✓</span>
                                <small>GPT-4-level Models Included</small>
                            </div>
                        </div>
                        <a href="/auth" class="btn btn-primary w-100 py-3 fw-bold" style="border-radius: 12px;">Create Account</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
<section id="models" class="py-5 bg-light">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Models Available on Assist</h2>
            <p class="text-muted">Access powerful AI models instantly from one unified interface.</p>
        </div>

        <div class="row g-4 justify-content-center">

            <div class="col-md-4 col-lg-2 text-center">
                <div class="card border-0 shadow-sm h-100 p-4" style="border-radius:16px;">
                    <div class="feature-icon mb-3">
                        <i class="fa-solid fa-gauge"></i>
                    </div>
                    <h5 class="fw-bold">GPT-4o</h5>
                    <p class="text-muted small">Fast multimodal intelligence for everyday conversations and creative work.</p>
                </div>
            </div>

            <div class="col-md-4 col-lg-2 text-center">
                <div class="card border-0 shadow-sm h-100 p-4" style="border-radius:16px;">
                    <div class="feature-icon mb-3">
                        <i class="fa-solid fa-brain"></i>
                    </div>
                    <h5 class="fw-bold">GPT-4.1</h5>
                    <p class="text-muted small">Advanced reasoning model designed for complex analysis and large context.</p>
                </div>
            </div>

 
        </div>
    </div>
</section>
    <section id="features" class="py-5 bg-white">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Why use Assist?</h2>
                <p class="text-muted">Built for speed, flexibility, and simplicity.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <div class="p-4">
                        <div class="feature-icon">🚀</div>
                        <h4>Zero Friction</h4>
                        <p class="text-muted">Start chatting in seconds. We've removed the hurdles between you and the world's best AI.</p>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="p-4">
                        <div class="feature-icon">🛡️</div>
                        <h4>Privacy First</h4>
                        <p class="text-muted">Your data is yours. We focus on providing a secure, reliable environment for your ideas.</p>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="p-4">
                        <div class="feature-icon">🌐</div>
                        <h4>Unified Access</h4>
                        <p class="text-muted">Switch between open-source and premium models without ever logging out.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white py-5">
        <div class="container text-center">
            <img src="https://chippytime.com/images/Untitled%20design%20(2).png?v=1769220226" height="40" alt="Assist" class="mb-3 filter-white">
            <p class="mb-4">ChippyTime.com Web Services</p>
            <div class="mb-4">
                <a href="/tos" class="text-white-50 mx-2 text-decoration-none">Terms</a>
                <a href="/privacy" class="text-white-50 mx-2 text-decoration-none">Privacy</a>
                <a href="mailto:contact@chippytime.com" class="text-white-50 mx-2 text-decoration-none">Contact</a>
            </div>
            <p class="small text-white-50">&copy; 2026 ChippyTime.com. All rights reserved.</p>
        </div>
    </footer>