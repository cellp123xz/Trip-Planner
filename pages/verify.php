<?php
require_once '../includes/config.php';

$message = '';
$messageType = '';


if (isset($_GET['code']) && !empty($_GET['code'])) {
    $code = trim($_GET['code']);
    
    try {
        $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE verification_code = ? AND email_verified = 0");
        $stmt->execute([$code]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $updateStmt = $pdo->prepare("UPDATE users SET email_verified = 1, verification_code = NULL WHERE id = ?");
            if ($updateStmt->execute([$user['id']])) {
                $messageType = 'success';
                $message = 'Your email has been successfully verified! You can now log in to your account.';
                
                $_SESSION['verification_success'] = true;
            } else {
                $messageType = 'danger';
                $message = 'Failed to verify email. Please try again.';
            }
        } else {
            $messageType = 'danger';
            $message = 'Invalid verification code or account already verified.';
        }
    } catch (PDOException $e) {
        $messageType = 'danger';
        $message = 'Verification failed: ' . $e->getMessage();
    }
}

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body p-4 text-center">
                    <h2 class="mb-4">Email Verification</h2>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?> mb-4">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                        
                        <div class="mt-4">
                            <?php if ($messageType === 'success'): ?>
                                <a href="login.php" class="btn btn-primary">Proceed to Login</a>
                            <?php else: ?>
                                <a href="register.php" class="btn btn-primary">Back to Registration</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Invalid or missing verification code.
                            If you haven't registered yet, please <a href="register.php">sign up</a>.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($messageType === 'success'): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        title: 'Success!',
        text: 'Your email has been verified successfully. You can now log in to your account.',
        icon: 'success',
        confirmButtonText: 'Continue to Login'
    }).then((result) => {
        window.location.href = 'login.php';
    });
});
</script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
