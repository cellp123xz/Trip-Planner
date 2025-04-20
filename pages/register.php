<?php
require_once '../includes/config.php';
require_once '../includes/send_email.php';
require_once '../includes/alerts.php';
require_once '../includes/auth_functions.php';
require_once '../includes/csrf_protection.php';
require_once '../includes/password_policy.php';
require_once '../includes/security_functions.php';



setSecurityHeaders();

$errors = [];
$passwordErrors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!csrfCheck()) {
        $errors[] = "Security token validation failed. Please try again.";
    } else {
        
        $name = sanitizeString(trim($_POST['name'] ?? ''));
        $email = sanitizeEmail(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $security_question = sanitizeString(trim($_POST['security_question'] ?? ''));
        $security_answer = trim($_POST['security_answer'] ?? '');

        
        if (empty($name)) {
            $errors[] = "Name is required";
        }
        
        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!$email) { // sanitizeEmail returns false if invalid
            $errors[] = "Invalid email format";
        }
        
        if (empty($password)) {
            $errors[] = "Password is required";
        } else {
            // Validate password strength
            $passwordValidation = validatePasswordStrength($password);
            if (!$passwordValidation['success']) {
                $errors[] = "Password does not meet security requirements";
                $passwordErrors = $passwordValidation['errors'];
            }
        }
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }

        if (empty($errors)) {
            $user = getUserByEmail($email);
            if ($user) {
                $errors[] = "Email already registered";
            }
        }
    }

    if (empty($errors)) {
        try {
            $result = registerUser($name, $email, $password);
            if ($result['success']) {
                $_SESSION['user_id'] = $result['user_id'];
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['alert'] = [
                    'title' => 'Registration Successful',
                    'message' => 'Your account has been created and automatically verified!',
                    'type' => 'success'
                ];
                header('Location: dashboard.php');
                exit;
            } else {
                $errors[] = $result['message'];
                if (isset($result['password_errors'])) {
                    $passwordErrors = $result['password_errors'];
                }
            }
        } catch (Exception $e) {
            $errors[] = "Registration failed: " . $e->getMessage();
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
                    <h2 class="text-center mb-4">Create an Account</h2>
                    <?php 
    if (!empty($errors)) {
        showErrorAlert($errors[0]);
    }
    ?>

                    <form method="POST" action="" class="needs-validation" novalidate>
                        <?php csrfTokenField(); ?>
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo escapeHtml($name ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo escapeHtml($email ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div id="passwordStrength" class="mt-2"></div>
                            <div class="form-text">
                                Password must meet the following requirements:
                                <ul class="mt-1 mb-0 ps-3">
                                    <li id="length-check">At least 8 characters long</li>
                                    <li id="uppercase-check">At least one uppercase letter</li>
                                    <li id="lowercase-check">At least one lowercase letter</li>
                                    <li id="number-check">At least one number</li>
                                    <li id="special-check">At least one special character</li>
                                </ul>
                                <?php if (!empty($passwordErrors)): ?>
                                    <div class="text-danger mt-2">
                                        <?php foreach ($passwordErrors as $error): ?>
                                            <div><?php echo escapeHtml($error); ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            <div id="password-match" class="form-text"></div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Create Account</button>
                        </div>
                    </form>
                    <p class="text-center mt-4 mb-0">
                        Already have an account? <a href="login.php">Sign in</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const passwordStrengthDiv = document.getElementById('passwordStrength');
    const passwordMatchDiv = document.getElementById('password-match');
    const lengthCheck = document.getElementById('length-check');
    const uppercaseCheck = document.getElementById('uppercase-check');
    const lowercaseCheck = document.getElementById('lowercase-check');
    const numberCheck = document.getElementById('number-check');
    const specialCheck = document.getElementById('special-check');
    function updatePasswordStrength(password) {
        let strength = 0;
        let feedback = '';
        if (password.length >= 8) {
            lengthCheck.classList.add('text-success');
            strength++;
        } else {
            lengthCheck.classList.remove('text-success');
        }
        if (/[A-Z]/.test(password)) {
            uppercaseCheck.classList.add('text-success');
            strength++;
        } else {
            uppercaseCheck.classList.remove('text-success');
        }
        if (/[a-z]/.test(password)) {
            lowercaseCheck.classList.add('text-success');
            strength++;
        } else {
            lowercaseCheck.classList.remove('text-success');
        }
        if (/[0-9]/.test(password)) {
            numberCheck.classList.add('text-success');
            strength++;
        } else {
            numberCheck.classList.remove('text-success');
        }
        if (/[^A-Za-z0-9]/.test(password)) {
            specialCheck.classList.add('text-success');
            strength++;
        } else {
            specialCheck.classList.remove('text-success');
        }
        let strengthClass = '';
        let strengthText = '';
        if (password.length === 0) {
            strengthText = '';
            strengthClass = '';
        } else if (strength < 2) {
            strengthText = 'Very Weak';
            strengthClass = 'bg-danger';
        } else if (strength < 3) {
            strengthText = 'Weak';
            strengthClass = 'bg-warning';
        } else if (strength < 4) {
            strengthText = 'Medium';
            strengthClass = 'bg-info';
        } else if (strength < 5) {
            strengthText = 'Strong';
            strengthClass = 'bg-primary';
        } else {
            strengthText = 'Very Strong';
            strengthClass = 'bg-success';
        }
        if (password.length > 0) {
            passwordStrengthDiv.innerHTML = `
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar ${strengthClass}" role="progressbar" style="width: ${strength * 20}%" 
                         aria-valuenow="${strength * 20}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="mt-1 small ${strengthClass.replace('bg-', 'text-')}">${strengthText}</div>
            `;
        } else {
            passwordStrengthDiv.innerHTML = '';
        }
    }
    function checkPasswordsMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        if (confirmPassword.length === 0) {
            passwordMatchDiv.textContent = '';
            passwordMatchDiv.className = 'form-text';
        } else if (password === confirmPassword) {
            passwordMatchDiv.textContent = 'Passwords match';
            passwordMatchDiv.className = 'form-text text-success';
        } else {
            passwordMatchDiv.textContent = 'Passwords do not match';
            passwordMatchDiv.className = 'form-text text-danger';
        }
    }
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            updatePasswordStrength(this.value);
            if (confirmPasswordInput.value.length > 0) {
                checkPasswordsMatch();
            }
        });
    }
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', checkPasswordsMatch);
    }
});

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
