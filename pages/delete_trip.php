<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';

// Require login to delete trip
requireLogin();

// Get trip ID from URL
$tripId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$tripId) {
    $_SESSION['alert'] = [
        'title' => 'Error',
        'message' => 'Invalid trip ID',
        'type' => 'error'
    ];
    header('Location: dashboard.php');
    exit;
}

// Find the trip in the session storage
$tripFound = false;
$tripIndex = -1;

foreach ($_SESSION['db']['trips'] as $key => $trip) {
    if ($trip['id'] == $tripId && $trip['user_id'] == $_SESSION['user_id']) {
        $tripFound = true;
        $tripIndex = $key;
        break;
    }
}

// If trip not found or doesn't belong to the user
if (!$tripFound) {
    $_SESSION['alert'] = [
        'title' => 'Error',
        'message' => 'Trip not found or you do not have permission to delete it',
        'type' => 'error'
    ];
    header('Location: dashboard.php');
    exit;
}

// Delete the trip from session storage
array_splice($_SESSION['db']['trips'], $tripIndex, 1);

// Set success message and redirect
$_SESSION['alert'] = [
    'title' => 'Success!',
    'message' => 'Your trip has been deleted successfully.',
    'type' => 'success'
];

header('Location: dashboard.php');
exit;
?>
