<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Generate auth token if it does not exist
if (!isset($_SESSION['auth_token'])) {
    $_SESSION['auth_token'] = bin2hex(random_bytes(32));
}

$token = $_SESSION['auth_token'];
?>

<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="card border-0 shadow-lg" style="max-width: 450px; width: 100%; border-radius: 22px; overflow: hidden;">
        
        <div class="card-header border-0 text-center py-4" style="background: #fff;">
            <div class="mb-2">
                <img src="/assist_logo.png" height="50px" alt="Assist logo">
            </div>
            <h2 class="fw-bold" style="color: #17272c;">Assist</h2>
            <p class="text-muted mb-0" id="auth-subtitle">Welcome back! Please login to continue.</p>
        </div>

        <div class="card-body p-4">
            
<?php if(isset($_GET['error'])): ?>
    <div class="alert alert-danger py-2 small" role="alert">
        <i class="fa-solid fa-circle-exclamation me-2"></i>
        <?php echo htmlspecialchars($_GET['error']); ?>
    </div>
<?php endif; ?>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success py-2 small" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i>
        <?php echo htmlspecialchars($_GET['success']); ?>
    </div>
<?php endif; ?>

            <!-- LOGIN FORM -->
            <form id="login-form" action="login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fa-solid fa-user text-muted"></i>
                        </span>
                        <input type="text" class="form-control bg-light border-start-0" name="username" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-secondary">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fa-solid fa-lock text-muted"></i>
                        </span>
                        <input type="password" class="form-control bg-light border-start-0" name="password" required>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="cf-turnstile" data-sitekey="0x4AAAAAACgGUHUCZE37XAsn"></div>
                </div>
                        <input type="hidden" name="auth_token" id="auth_token" value="<?php echo $token; ?>">

                <button type="submit" class="btn w-100 py-2 fw-bold shadow-sm"
                        style="background: #000; color: white; border-radius: 12px;">
                    Sign In
                </button>

                <div class="text-center mt-4">
                    <p class="small text-muted">
                        Don't have an account?
                        <a href="javascript:void(0)" onclick="toggleAuth()" 
                           class="text-decoration-none fw-bold">
                           Register
                        </a>
                    </p>
                </div>
            </form>

            <!-- REGISTER FORM -->
            <form id="register-form" action="signup.php" method="POST" style="display: none;">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Username</label>
                    <input type="text" class="form-control bg-light" name="username" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Email Address</label>
                    <input type="email" class="form-control bg-light" name="email" required>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-secondary">Password</label>
                    <input type="password" class="form-control bg-light" name="password" minlength="8" required>
                </div>

                <div class="mb-4">
                    <div class="cf-turnstile" data-sitekey="0x4AAAAAACgGUHUCZE37XAsn"></div>
                </div>
                        <input type="hidden" name="auth_token" id="auth_token" value="<?php echo $token; ?>">



                <button type="submit" class="btn w-100 py-2 fw-bold shadow-sm"
                        style="background: #000; color: white; border-radius: 12px;">
                    Create Account
                </button>

                <div class="text-center mt-4">
                    <p class="small text-muted">
                        Already have an account?
                        <a href="javascript:void(0)" onclick="toggleAuth()" 
                           class="text-decoration-none fw-bold">
                           Login
                        </a>
                    </p>
                </div>
            </form>

        </div>
    </div>
</div>

<center>
    <a href="/tos">Terms of Service</a> -
    <a href="/privacy">Privacy Policy</a>
</center>

<script>
function toggleAuth() {
    const loginForm = document.getElementById('login-form');
    const regForm = document.getElementById('register-form');
    const subtitle = document.getElementById('auth-subtitle');

    if (loginForm.style.display === 'none') {
        loginForm.style.display = 'block';
        regForm.style.display = 'none';
        subtitle.innerText = "Welcome back! Please login to continue.";
    } else {
        loginForm.style.display = 'none';
        regForm.style.display = 'block';
        subtitle.innerText = "Join us to start chatting with AI.";
    }
}
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/blueimp-md5/2.19.0/js/md5.min.js"></script>


