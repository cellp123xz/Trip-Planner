<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function showAlert($title, $message, $type = 'info', $options = []) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    
    $defaultOptions = [
        'confirmButtonText' => 'OK',
        'timer' => null,
        'position' => 'center',
        'showConfirmButton' => true
    ];
    $options = array_merge($defaultOptions, $options);
    
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: '" . addslashes($title) . "',
                text: '" . addslashes($message) . "',
                icon: '" . $type . "',
                confirmButtonText: '" . addslashes($options['confirmButtonText']) . "',
                position: '" . $options['position'] . "',
                showConfirmButton: " . ($options['showConfirmButton'] ? 'true' : 'false') . 
                ($options['timer'] ? ", timer: " . $options['timer'] : "") . "
            })" . (isset($options['then']) ? ".then(" . $options['then'] . ")" : "") . ";
        });
    </script>";
}

function showSuccessAlert($message, $options = []) {
    showAlert('Success', $message, 'success', $options);
}

function showErrorAlert($message, $options = []) {
    showAlert('Error', $message, 'error', $options);
}

function showWarningAlert($message, $options = []) {
    showAlert('Warning', $message, 'warning', $options);
}

function showConfirmation($title, $message, $confirmCallback, $options = []) {
    $defaultOptions = [
        'confirmButtonText' => 'Yes',
        'cancelButtonText' => 'No',
        'icon' => 'question'
    ];
    $options = array_merge($defaultOptions, $options);
    
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: '" . addslashes($title) . "',
                text: '" . addslashes($message) . "',
                icon: '" . $options['icon'] . "',
                showCancelButton: true,
                confirmButtonText: '" . addslashes($options['confirmButtonText']) . "',
                cancelButtonText: '" . addslashes($options['cancelButtonText']) . "'
            }).then((result) => {
                if (result.isConfirmed) {
                    " . $confirmCallback . "
                }
            });
        });
    </script>";
}

function showToast($message, $type = 'success', $options = []) {
    $defaultOptions = [
        'position' => 'top-end',
        'timer' => 3000,
        'showConfirmButton' => false
    ];
    $options = array_merge($defaultOptions, $options);
    
    showAlert('', $message, $type, $options);
}

function setAlert($title, $message, $type = 'info', $options = []) {
    $_SESSION['sweet_alert'] = [
        'title' => $title,
        'message' => $message,
        'type' => $type,
        'options' => $options
    ];
}

function showAlertIfExists() {
    if (isset($_SESSION['sweet_alert'])) {
        $alert = $_SESSION['sweet_alert'];
        showAlert($alert['title'], $alert['message'], $alert['type'], $alert['options'] ?? []);
        unset($_SESSION['sweet_alert']);
    }
}
?>
