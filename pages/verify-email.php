<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/alerts.php';

if (!isset($_SESSION['verify_email'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['verify_email'];
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['verification_code'] ?? '');
    
    if (empty($code)) {
        $errors[] = "Verification code is required";
    } else {
        $result = verifyUserEmail($email, $code);
        
        if ($result['success']) {
            $success = true;
            unset($_SESSION['verify_email']);
            
            setAlert(
                'Success!',
                'Your email has been verified. You can now log in.',
                'success',
                [
                    'confirmButtonText' => 'Continue to Login',
                    'then' => 'function() { window.location.href = "login.php"; }'
                ]
            );
            exit;
        } else {
            $errors[] = $result['message'] ?? "Invalid verification code";
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
                    <h2 class="text-center mb-4">Verify Your Email</h2>
                    <p class="text-center mb-4">
                        We've sent a verification code to:<br>
                        <strong><?php echo htmlspecialchars($email); ?></strong>
                    </p>
                    
                    <?php 
    if (!empty($errors)) {
        showErrorAlert($errors[0]);
    }
    ?>
                    
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="mb-4">
                            <label for="verification_code" class="form-label">Verification Code</label>
                            <input type="text" class="form-control form-control-lg text-center" 
                                   id="verification_code" name="verification_code" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Verify Email</button>
                        </div>
                    </form>
                    
                    <p class="text-center mt-4 mb-0">
                        Didn't receive the code? <a href="#" onclick="resendCode()">Resend Code</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function resendCode() {
    Swal.fire({
        title: 'Sending...',
        text: 'Sending a new verification code to your email',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch('resend-verification.php', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Code Sent',
                text: data.message
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to send verification code. Please try again.'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to send verification code. Please try again.'
        });
    });
}
</script>

<?php include '../includes/footer.php'; ?>
