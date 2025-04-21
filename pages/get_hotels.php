<?php
require_once '../includes/config.php';

$destination = isset($_GET['destination']) ? trim($_GET['destination']) : '';

if (empty($destination)) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

$hotels = getRecommendedHotels($destination, 5);

if (empty($hotels)) {
    $hotels = generateDestinationHotels($destination);
}

header('Content-Type: application/json');
echo json_encode($hotels);
?>
