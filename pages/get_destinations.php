<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';

requireLogin();

$country = isset($_GET['country']) ? trim($_GET['country']) : '';

$destinations = [];
if (!empty($country)) {
    $destinations = getDestinationsForCountry($country);
}

header('Content-Type: application/json');
echo json_encode($destinations);
?>
