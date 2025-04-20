<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/alerts.php';
require_once '../includes/csrf_protection.php';
require_once '../includes/security_functions.php';

// Only guests can access password reset
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$errors = [];
$success = false;
$validToken = false;
$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';

// Verify token
if (!empty($token) && !empty($email)) {
    $result = verifyPasswordResetToken($email, $token);
    
    if ($result['success']) {
        $validToken = true;
        $user = $result['user'];
    } else {
        setAlert(
            'Invalid Link',
            'This password reset link is invalid or has expired. Please request a new one.',
            'error',
            ['then' => 'function() { window.location.href = "forgot-password.php"; }']
        );
        exit;
    }
} else {
    header("Location: forgot-password.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($errors)) {
        // Reset the password using our function
        $result = resetPassword($email, $token, $password);
        
        if ($result['success']) {
            setAlert(
                'Password Updated',
                'Your password has been successfully reset. You can now log in with your new password.',
                'success',
                ['then' => 'function() { window.location.href = "login.php"; }']
            );
            exit;
        } else {
            $errors[] = $result['message'];
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
                    <h2 class="text-center mb-4">Create New Password</h2>
                    
                    <?php if ($validToken): ?>
                        <?php if (!empty($errors)): ?>
                            <?php foreach ($errors as $error): ?>
                                <?php showErrorAlert($error); ?>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <form method="POST" action="" class="needs-validation" novalidate>
                            <?php csrfTokenField(); ?>
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control form-control-lg" 
                                       id="password" name="password" required minlength="8">
                                <div class="form-text">Must be at least 8 characters long</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control form-control-lg" 
                                       id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Update Password</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Password match validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (password !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php include '../includes/footer.php'; ?>
