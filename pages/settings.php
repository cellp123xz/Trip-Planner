<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';

requireLogin();

$user = getUserById($_SESSION['user_id']);
if (!$user) {
    logoutUser();
    header("Location: login.php");
    exit;
}

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'profile') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        if (empty($name)) {
            $errors[] = "Name is required";
        }
        
        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
        
        if (empty($errors)) {
            foreach ($_SESSION['db']['users'] as &$dbUser) {
                if ($dbUser['id'] === $_SESSION['user_id']) {
                    $dbUser['name'] = $name;
                    $dbUser['email'] = $email;
                    break;
                }
            }
            
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            
            $success = true;
            $_SESSION['alert'] = [
                'title' => 'Success',
                'message' => 'Your profile has been updated successfully.',
                'type' => 'success'
            ];
        }
    } elseif ($action === 'password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password)) {
            $errors[] = "Current password is required";
        }
        
        if (empty($new_password)) {
            $errors[] = "New password is required";
        } elseif (strlen($new_password) < 8) {
            $errors[] = "New password must be at least 8 characters long";
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        }
        
        if (empty($errors)) {
            $userFound = false;
            foreach ($_SESSION['db']['users'] as &$dbUser) {
                if ($dbUser['id'] === $_SESSION['user_id']) {
                    if (password_verify($current_password, $dbUser['password'])) {
                        $dbUser['password'] = password_hash($new_password, PASSWORD_DEFAULT);
                        $userFound = true;
                        
                        $success = true;
                        $_SESSION['alert'] = [
                            'title' => 'Success',
                            'message' => 'Your password has been changed successfully.',
                            'type' => 'success'
                        ];
                    } else {
                        $errors[] = "Current password is incorrect";
                    }
                    break;
                }
            }
            
            if (!$userFound) {
                $errors[] = "User not found";
            }
        }
    } elseif ($action === 'preferences') {
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        $default_currency = $_POST['default_currency'] ?? 'USD';
        $default_language = $_POST['default_language'] ?? 'en';
        
        $_SESSION['user_preferences'] = [
            'email_notifications' => $email_notifications,
            'default_currency' => $default_currency,
            'default_language' => $default_language
        ];
        
        $success = true;
        $_SESSION['alert'] = [
            'title' => 'Success',
            'message' => 'Your preferences have been updated successfully.',
            'type' => 'success'
        ];
    }
}

$preferences = $_SESSION['user_preferences'] ?? [
    'email_notifications' => 1,
    'default_currency' => 'PHP',
    'default_language' => 'en'
];

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="mb-0">Account Settings</h1>
            <p class="text-muted">Manage your account preferences and profile information</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="dashboard.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="list-group">
                <a href="#profile" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                    <i class="fas fa-user me-2"></i>Profile Information
                </a>
                <a href="#password" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-key me-2"></i>Change Password
                </a>
                <a href="#preferences" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-cog me-2"></i>Preferences
                </a>
                <a href="#notifications" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-bell me-2"></i>Notification Settings
                </a>
                <a href="#privacy" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-shield-alt me-2"></i>Privacy & Security
                </a>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="tab-content">

                <div class="tab-pane fade show active" id="profile">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Profile Information</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($errors) && $_POST['action'] === 'profile'): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($success && $_POST['action'] === 'profile'): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>Your profile has been updated successfully.
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="profile">
                                
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
                                
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                    </div>
                </div>
                

                <div class="tab-pane fade" id="password">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Change Password</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($errors) && $_POST['action'] === 'password'): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($success && $_POST['action'] === 'password'): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>Your password has been changed successfully.
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="password">
                                
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" 
                                           minlength="8" required>
                                    <div class="form-text">Password must be at least 8 characters long</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Change Password</button>
                            </form>
                        </div>
                    </div>
                </div>
                

                <div class="tab-pane fade" id="preferences">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Preferences</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($success && $_POST['action'] === 'preferences'): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>Your preferences have been updated successfully.
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="preferences">
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="email_notifications" 
                                               name="email_notifications" <?php echo $preferences['email_notifications'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="email_notifications">
                                            Receive Email Notifications
                                        </label>
                                    </div>
                                    <div class="form-text">Receive emails about trip updates, reminders, and promotions</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="default_currency" class="form-label">Default Currency</label>
                                    <select class="form-select" id="default_currency" name="default_currency">
                                        <option value="PHP" <?php echo $preferences['default_currency'] === 'PHP' ? 'selected' : ''; ?>>Philippine Peso (PHP)</option>
                                        <option value="USD" <?php echo $preferences['default_currency'] === 'USD' ? 'selected' : ''; ?>>US Dollar (USD)</option>
                                        <option value="EUR" <?php echo $preferences['default_currency'] === 'EUR' ? 'selected' : ''; ?>>Euro (EUR)</option>
                                        <option value="GBP" <?php echo $preferences['default_currency'] === 'GBP' ? 'selected' : ''; ?>>British Pound (GBP)</option>
                                        <option value="JPY" <?php echo $preferences['default_currency'] === 'JPY' ? 'selected' : ''; ?>>Japanese Yen (JPY)</option>
                                        <option value="AUD" <?php echo $preferences['default_currency'] === 'AUD' ? 'selected' : ''; ?>>Australian Dollar (AUD)</option>
                                        <option value="CAD" <?php echo $preferences['default_currency'] === 'CAD' ? 'selected' : ''; ?>>Canadian Dollar (CAD)</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="default_language" class="form-label">Default Language</label>
                                    <select class="form-select" id="default_language" name="default_language">
                                        <option value="en" <?php echo $preferences['default_language'] === 'en' ? 'selected' : ''; ?>>English</option>
                                        <option value="es" <?php echo $preferences['default_language'] === 'es' ? 'selected' : ''; ?>>Spanish</option>
                                        <option value="fr" <?php echo $preferences['default_language'] === 'fr' ? 'selected' : ''; ?>>French</option>
                                        <option value="de" <?php echo $preferences['default_language'] === 'de' ? 'selected' : ''; ?>>German</option>
                                        <option value="it" <?php echo $preferences['default_language'] === 'it' ? 'selected' : ''; ?>>Italian</option>
                                        <option value="ja" <?php echo $preferences['default_language'] === 'ja' ? 'selected' : ''; ?>>Japanese</option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Save Preferences</button>
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
                            
                            <form>
                                <div class="mb-3">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="trip_reminders" checked>
                                        <label class="form-check-label" for="trip_reminders">
                                            Trip Reminders
                                        </label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="price_alerts" checked>
                                        <label class="form-check-label" for="price_alerts">
                                            Price Alerts
                                        </label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="travel_tips">
                                        <label class="form-check-label" for="travel_tips">
                                            Travel Tips & Recommendations
                                        </label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="marketing_emails">
                                        <label class="form-check-label" for="marketing_emails">
                                            Marketing Emails
                                        </label>
                                    </div>
                                </div>
                                
                                <button type="button" class="btn btn-primary" disabled>Save Notification Settings</button>
                                <div class="form-text mt-2">This feature will be available in a future update.</div>
                            </form>
                        </div>
                    </div>
                </div>
                

                <div class="tab-pane fade" id="privacy">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Privacy & Security</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-4">Manage your privacy and security settings</p>
                            
                            <div class="mb-4">
                                <h6>Two-Factor Authentication</h6>
                                <p class="text-muted">Add an extra layer of security to your account</p>
                                <button type="button" class="btn btn-outline-primary" disabled>Enable 2FA</button>
                                <div class="form-text mt-2">This feature will be available in a future update.</div>
                            </div>
                            
                            <div class="mb-4">
                                <h6>Login History</h6>
                                <p class="text-muted">View your recent login activity</p>
                                <div class="list-group">
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">Current Session</h6>
                                            <small class="text-success">Active Now</small>
                                        </div>
                                        <p class="mb-1">
                                            <small>
                                                <i class="fas fa-desktop me-1"></i>Windows PC
                                                <i class="fas fa-map-marker-alt ms-3 me-1"></i>Your Location
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h6>Delete Account</h6>
                                <p class="text-muted">Permanently delete your account and all associated data</p>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                                    Delete Account
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAccountModalLabel">Delete Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone.
                </div>
                <p>Are you sure you want to delete your account? All your data, including trips, preferences, and settings will be permanently removed.</p>
                <div class="mb-3">
                    <label for="delete_confirmation" class="form-label">Type "DELETE" to confirm</label>
                    <input type="text" class="form-control" id="delete_confirmation" placeholder="DELETE">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" disabled>Delete Account</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle tab navigation from URL hash
    const hash = window.location.hash;
    if (hash) {
        const tab = document.querySelector(`a[href="${hash}"]`);
        if (tab) {
            tab.click();
        }
    }
    
    // Enable delete account button only when confirmation is correct
    const deleteConfirmInput = document.getElementById('delete_confirmation');
    const deleteAccountBtn = document.querySelector('#deleteAccountModal .btn-danger');
    
    if (deleteConfirmInput && deleteAccountBtn) {
        deleteConfirmInput.addEventListener('input', function() {
            deleteAccountBtn.disabled = this.value !== 'DELETE';
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>
