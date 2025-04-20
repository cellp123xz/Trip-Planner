<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Check if user is already logged out
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

include '../includes/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        title: 'Logging Out',
        text: 'Are you sure you want to log out?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, log me out',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Logging out...',
                text: 'Please wait',
                icon: 'info',
                showConfirmButton: false,
                allowOutsideClick: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Redirect to logout script
            window.location.href = '../includes/logout.php';
        } else {
            // Redirect back to previous page or dashboard
            window.location.href = document.referrer || 'dashboard.php';
        }
    });
});
</script>

<!-- Fallback content if JavaScript is disabled -->
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <h2>Logging Out</h2>
            <p>Please wait while we log you out...</p>
            <noscript>
                <div class="alert alert-warning">
                    JavaScript is required for the best experience. 
                    <a href="../includes/logout.php" class="btn btn-primary mt-3">Click here to logout</a>
                </div>
            </noscript>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
