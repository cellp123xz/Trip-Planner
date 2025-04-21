<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/send_email.php';
require_once '../includes/alerts.php';
require_once '../includes/csrf_protection.php';
require_once '../includes/security_functions.php';

setSecurityHeaders();

$errors = [];
$success = false;
$email = '';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck()) {
        $errors[] = "Security token validation failed. Please try again.";
    } else {
        $email = sanitizeEmail(trim($_POST['email'] ?? ''));
        
        if (empty($email)) {
            $errors[] = "Email address is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        } else {
            $result = generatePasswordResetToken($email);
            
            if ($result['success']) {
                if (isset($result['token'])) {
                    $_SESSION['dev_reset_info'] = [
                        'email' => $result['email'],
                        'token' => $result['token'],
                        'url' => $result['url']
                    ];
                }
                
                $success = true;
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Reset Password</h2>
                    
                    <?php if (!empty($errors)): ?>
                        <?php foreach ($errors as $error): ?>
                            <?php showErrorAlert($error); ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <h4 class="alert-heading">Check Your Email</h4>
                            <p>If an account exists with the email you provided, we've sent instructions to reset your password.</p>
                            <p>Please check your inbox and spam folder.</p>
                            <hr>
                            <p class="mb-0">Return to <a href="login.php" class="alert-link">Login</a></p>
                        </div>
                        
                        <?php if (isset($_SESSION['dev_reset_info'])): ?>
                            <div class="alert alert-info mt-4">
                                <h4 class="alert-heading">Development Mode: Password Reset Link</h4>
                                <p>Since email sending is disabled in development, use this link to reset your password:</p>
                                <hr>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['dev_reset_info']['email']); ?></p>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['dev_reset_info']['url']); ?>" id="resetUrl" readonly>
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyResetUrl()">Copy</button>
                                </div>
                                <div class="d-grid gap-2">
                                    <a href="<?php echo htmlspecialchars($_SESSION['dev_reset_info']['url']); ?>" class="btn btn-success">Reset Password Now</a>
                                </div>
                            </div>
                            <script>
                                function copyResetUrl() {
                                    var copyText = document.getElementById("resetUrl");
                                    copyText.select();
                                    copyText.setSelectionRange(0, 99999);
                                    navigator.clipboard.writeText(copyText.value);
                                    alert('Reset URL copied to clipboard');
                                }
                            </script>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-center mb-4">Enter your email address and we'll send you instructions to reset your password.</p>
                        
                        <form method="POST" action="" class="needs-validation" novalidate>
                            <?php csrfTokenField(); ?>
                            
                            <div class="mb-4">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control form-control-lg" id="email" 
                                       name="email" required value="<?php echo escapeHtml($email ?? ''); ?>" 
                                       placeholder="Enter your email address" autofocus>
                                <div class="form-text">We'll send you a link to reset your password.</div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Reset Password</button>
                                <a href="login.php" class="btn btn-link">Back to Login</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php include '../includes/footer.php'; ?>
