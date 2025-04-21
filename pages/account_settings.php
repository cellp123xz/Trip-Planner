<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/alerts.php';
require_once '../includes/csrf_protection.php';
require_once '../includes/security_functions.php';
require_once '../includes/password_policy.php';

// Require login to access account settings
requireLogin();

$user = getUserById($_SESSION['user_id']);
$errors = [];
$success = false;
$passwordErrors = [];

// Initialize notification settings if not set
if (!isset($_SESSION['notification_settings'])) {
    $_SESSION['notification_settings'] = [
        'trip_reminders' => true,
        'price_alerts' => true,
        'travel_tips' => false,
        'marketing_emails' => false
    ];
}

// Get user's IP and device info for session display
$userIP = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$deviceInfo = 'Unknown';

if (strpos($userAgent, 'Windows') !== false) {
    $deviceInfo = 'Windows PC';
} elseif (strpos($userAgent, 'Mac') !== false) {
    $deviceInfo = 'Mac';
} elseif (strpos($userAgent, 'iPhone') !== false) {
    $deviceInfo = 'iPhone';
} elseif (strpos($userAgent, 'Android') !== false) {
    $deviceInfo = 'Android';
} elseif (strpos($userAgent, 'iPad') !== false) {
    $deviceInfo = 'iPad';
}

// Get approximate location based on IP (simplified for demo)
$location = 'Unknown';
if ($userIP == '127.0.0.1' || $userIP == '::1') {
    $location = 'Local';
} else {
    $location = 'Remote';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck()) {
        $errors[] = "Security token validation failed. Please try again.";
    } else {
        $action = $_POST['action'] ?? '';
        
        // Update profile information
        if ($action === 'update_profile') {
            $name = sanitizeString(trim($_POST['name'] ?? ''));
            $email = sanitizeEmail(trim($_POST['email'] ?? ''));
            
            $updateData = [
                'name' => $name,
                'email' => $email
            ];
            
            $result = updateUser($_SESSION['user_id'], $updateData);
            
            if ($result['success']) {
                $success = true;
                $_SESSION['alert'] = [
                    'title' => 'Success!',
                    'message' => $result['message'],
                    'type' => 'success'
                ];
                header('Location: account_settings.php');
                exit;
            } else {
                $errors[] = $result['message'];
            }
        }
        
        // Save notification settings
        if ($action === 'save_notifications') {
            // Update notification settings
            $_SESSION['notification_settings'] = [
                'trip_reminders' => isset($_POST['trip_reminders']),
                'price_alerts' => isset($_POST['price_alerts']),
                'travel_tips' => isset($_POST['travel_tips']),
                'marketing_emails' => isset($_POST['marketing_emails'])
            ];
            
            $_SESSION['alert'] = [
                'title' => 'Success!',
                'message' => 'Notification settings updated successfully',
                'type' => 'success'
            ];
            header('Location: account_settings.php#notifications');
            exit;
        }
        
        // Change password
        if ($action === 'change_password') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($currentPassword)) {
                $errors[] = "Current password is required";
            }
            
            if (empty($newPassword)) {
                $errors[] = "New password is required";
            }
            
            if ($newPassword !== $confirmPassword) {
                $errors[] = "New passwords do not match";
            }
            
            if (empty($errors)) {
                $updateData = [
                    'new_password' => $newPassword
                ];
                
                $result = updateUser($_SESSION['user_id'], $updateData, $currentPassword);
                
                if ($result['success']) {
                    $success = true;
                    $_SESSION['alert'] = [
                        'title' => 'Success!',
                        'message' => 'Password updated successfully',
                        'type' => 'success'
                    ];
                    header('Location: account_settings.php');
                    exit;
                } else {
                    $errors[] = $result['message'];
                    if (isset($result['errors'])) {
                        $passwordErrors = $result['errors'];
                    }
                }
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-3">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center text-center mb-4">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px; font-size: 2.5rem;">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                        <h4><?php echo htmlspecialchars($user['name']); ?></h4>
                        <p class="text-muted mb-1"><?php echo htmlspecialchars($user['email']); ?></p>
                        <p class="text-muted mb-0">
                            <small>Member since: <?php echo date('M d, Y', strtotime($user['created_at'])); ?></small>
                        </p>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="#profile" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                            <i class="fas fa-user me-2"></i> Profile
                        </a>
                        <a href="#security" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fas fa-lock me-2"></i> Security
                        </a>
                        <a href="#notifications" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fas fa-bell me-2"></i> Notifications
                        </a>
                        <a href="#privacy" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fas fa-shield-alt me-2"></i> Privacy & Security
                        </a>
                        <a href="dashboard.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9">
            <?php if (isset($_SESSION['alert'])): ?>
                <div class="alert alert-<?php echo $_SESSION['alert']['type']; ?> alert-dismissible fade show" role="alert">
                    <strong><?php echo $_SESSION['alert']['title']; ?></strong> <?php echo $_SESSION['alert']['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['alert']); ?>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="tab-content">
                <!-- Profile Settings -->
                <div class="tab-pane fade show active" id="profile">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Profile Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <?php csrfTokenField(); ?>
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Update Profile</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Security Settings -->
                <div class="tab-pane fade" id="security">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <?php csrfTokenField(); ?>
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <div class="form-text">
                                        Password must be at least 8 characters long and include uppercase, lowercase, numbers, and special characters.
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    <div id="password-match-feedback" class="form-text"></div>
                                </div>
                                
                                <div class="mb-4">
                                    <div id="password-strength-container">
                                        <div class="password-requirements mb-2">
                                            <div id="length-check" class="requirement"><i class="fas fa-check-circle me-2"></i>At least 8 characters</div>
                                            <div id="uppercase-check" class="requirement"><i class="fas fa-check-circle me-2"></i>At least 1 uppercase letter</div>
                                            <div id="lowercase-check" class="requirement"><i class="fas fa-check-circle me-2"></i>At least 1 lowercase letter</div>
                                            <div id="number-check" class="requirement"><i class="fas fa-check-circle me-2"></i>At least 1 number</div>
                                            <div id="special-check" class="requirement"><i class="fas fa-check-circle me-2"></i>At least 1 special character</div>
                                        </div>
                                        <div id="password-strength-meter" class="mb-2"></div>
                                    </div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Change Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Notification Settings -->
                <div class="tab-pane fade" id="notifications">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Notification Settings</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-4">Choose which notifications you'd like to receive</p>
                            
                            <form method="POST" action="">
                                <?php csrfTokenField(); ?>
                                <input type="hidden" name="action" value="save_notifications">
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="trip_reminders" name="trip_reminders" 
                                               <?php echo $_SESSION['notification_settings']['trip_reminders'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="trip_reminders">Trip Reminders</label>
                                        <div class="form-text">Get notified about upcoming trips and important travel dates</div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="price_alerts" name="price_alerts"
                                               <?php echo $_SESSION['notification_settings']['price_alerts'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="price_alerts">Price Alerts</label>
                                        <div class="form-text">Receive notifications about price drops for hotels and flights</div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="travel_tips" name="travel_tips"
                                               <?php echo $_SESSION['notification_settings']['travel_tips'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="travel_tips">Travel Tips & Recommendations</label>
                                        <div class="form-text">Get personalized travel tips and destination recommendations</div>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="marketing_emails" name="marketing_emails"
                                               <?php echo $_SESSION['notification_settings']['marketing_emails'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="marketing_emails">Marketing Emails</label>
                                        <div class="form-text">Receive promotional emails and special offers</div>
                                    </div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Save Notification Settings</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Privacy & Security -->
                <div class="tab-pane fade" id="privacy">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Privacy & Security</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-4">Manage your privacy and security settings</p>
                            
                            <h6 class="fw-bold mb-3">Two-Factor Authentication</h6>
                            <p class="text-muted">Add an extra layer of security to your account</p>
                            <button class="btn btn-outline-primary mb-4" disabled>Enable 2FA</button>
                            <div class="text-muted small fst-italic mb-4">This feature will be available in a future update.</div>
                            
                            <hr class="my-4">
                            
                            <h6 class="fw-bold mb-3">Current Session</h6>
                            <p class="text-muted">View your current login session information</p>
                            
                            <div class="card bg-light mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <i class="fas fa-desktop me-2 text-primary"></i>
                                            <strong><?php echo htmlspecialchars($deviceInfo); ?></strong>
                                        </div>
                                        <span class="badge bg-success">Active Now</span>
                                    </div>
                                    <div class="text-muted small">
                                        <div class="mb-1"><i class="fas fa-map-marker-alt me-2"></i> Location: <?php echo htmlspecialchars($location); ?></div>
                                        <div class="mb-1"><i class="fas fa-clock me-2"></i> Login time: <?php echo date('M d, Y H:i', $_SESSION['last_activity'] ?? time()); ?></div>
                                        <div><i class="fas fa-globe me-2"></i> IP Address: <?php echo htmlspecialchars($userIP); ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <h6 class="fw-bold text-danger mb-3">Delete Account</h6>
                            <p class="text-muted">Permanently delete your account and all associated data</p>
                            <button class="btn btn-outline-danger" disabled>Delete Account</button>
                            <div class="text-muted small fst-italic mt-2">This feature will be available in a future update.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const passwordMatchFeedback = document.getElementById('password-match-feedback');
    const passwordStrengthMeter = document.getElementById('password-strength-meter');
    
    const lengthCheck = document.getElementById('length-check');
    const uppercaseCheck = document.getElementById('uppercase-check');
    const lowercaseCheck = document.getElementById('lowercase-check');
    const numberCheck = document.getElementById('number-check');
    const specialCheck = document.getElementById('special-check');
    
    function updatePasswordStrength(password) {
        let strength = 0;
        
        // Check length
        if (password.length >= 8) {
            lengthCheck.classList.add('text-success');
            strength++;
        } else {
            lengthCheck.classList.remove('text-success');
        }
        
        // Check uppercase
        if (/[A-Z]/.test(password)) {
            uppercaseCheck.classList.add('text-success');
            strength++;
        } else {
            uppercaseCheck.classList.remove('text-success');
        }
        
        // Check lowercase
        if (/[a-z]/.test(password)) {
            lowercaseCheck.classList.add('text-success');
            strength++;
        } else {
            lowercaseCheck.classList.remove('text-success');
        }
        
        // Check numbers
        if (/[0-9]/.test(password)) {
            numberCheck.classList.add('text-success');
            strength++;
        } else {
            numberCheck.classList.remove('text-success');
        }
        
        // Check special characters
        if (/[^A-Za-z0-9]/.test(password)) {
            specialCheck.classList.add('text-success');
            strength++;
        } else {
            specialCheck.classList.remove('text-success');
        }
        
        // Update strength meter
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
            passwordStrengthMeter.innerHTML = `
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar ${strengthClass}" role="progressbar" style="width: ${strength * 20}%" 
                         aria-valuenow="${strength * 20}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="mt-1 small ${strengthClass.replace('bg-', 'text-')}">${strengthText}</div>
            `;
        } else {
            passwordStrengthMeter.innerHTML = '';
        }
    }
    
    function checkPasswordsMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (confirmPassword.length === 0) {
            passwordMatchFeedback.textContent = '';
            passwordMatchFeedback.className = 'form-text';
        } else if (password === confirmPassword) {
            passwordMatchFeedback.textContent = 'Passwords match';
            passwordMatchFeedback.className = 'form-text text-success';
        } else {
            passwordMatchFeedback.textContent = 'Passwords do not match';
            passwordMatchFeedback.className = 'form-text text-danger';
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
    
    // Handle tab navigation
    const tabLinks = document.querySelectorAll('.list-group-item-action');
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.getAttribute('href').startsWith('#')) {
                e.preventDefault();
                
                // Remove active class from all tabs
                tabLinks.forEach(tab => {
                    tab.classList.remove('active');
                });
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Show the corresponding tab content
                const tabId = this.getAttribute('href');
                document.querySelectorAll('.tab-pane').forEach(pane => {
                    pane.classList.remove('show', 'active');
                });
                document.querySelector(tabId).classList.add('show', 'active');
            }
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
