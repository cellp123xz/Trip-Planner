<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';


if (isset($_SESSION['show_logout_message'])) {
    $logoutMessage = $_SESSION['logout_message'] ?? 'You have been successfully logged out.';
    unset($_SESSION['show_logout_message']);
    unset($_SESSION['logout_message']);
    
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Logged Out',
                text: '{$logoutMessage}',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        });
    </script>";
}

$errors = [];
$success = false;


if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    
    if (empty($email)) {
        $errors[] = "Email is required";
    }
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    if (empty($errors)) {
        
        $result = loginUser($email, $password);
        
        if ($result['success']) {
            
            $redirectTo = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'dashboard.php';
            unset($_SESSION['redirect_after_login']);
            
            
            $_SESSION['alert'] = [
                'title' => 'Welcome back!',
                'message' => 'Login successful',
                'type' => 'success'
            ];
            
            
            header("Location: $redirectTo");
            exit;
        } else {
            if (isset($result['needs_verification'])) {
                $_SESSION['verify_email'] = $email;
                header("Location: verify-email.php");
                exit;
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
                    <h2 class="text-center mb-4">Sign In</h2>
                    
                    <?php 
    if (!empty($errors)) {
        showErrorAlert($errors[0]);
    }
    ?>
                    
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text text-end">
                                <a href="forgot-password.php" class="text-decoration-none">Forgot Password?</a>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Sign In</button>
                        </div>
                    </form>
                    
                    <p class="text-center mt-4 mb-0">
                        Don't have an account? <a href="register.php">Create one</a>
                    </p>
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


<?php if (!empty($errors)): ?>
Swal.fire({
    title: 'Error',
    text: '<?php echo addslashes($errors[0]); ?>',
    icon: 'error',
    confirmButtonText: 'OK'
});
<?php endif; ?>
</script>

<?php include '../includes/footer.php'; ?>
