<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_functions.php';
require_once __DIR__ . '/session_manager.php';
require_once __DIR__ . '/alerts.php';

showAlertIfExists();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TripPlanner - Plan Your Next Adventure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/style.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/landing.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/images.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/logo.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo APP_URL; ?>/index.php">
                <div class="me-2">
                    <img src="<?php echo APP_URL; ?>/assets/images/logo/logo.svg" alt="Trip Planner Logo" width="40" height="40">
                </div>
                <div>
                    <span class="text-primary fw-bold">TripPlanner</span>
                </div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo APP_URL; ?>/index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo APP_URL; ?>/pages/about.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo APP_URL; ?>/pages/contact.php">Contact</a>
                    </li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="exploreDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Explore
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="exploreDropdown">
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/pages/browse_hotels.php">Browse Hotels</a></li>
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/pages/explore_sites.php">Tourist Sites</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="tripsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                My Trips
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="tripsDropdown">
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/pages/dashboard.php">Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/pages/planned-trips.php">Planned Trips</a></li>
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/pages/trip-history.php">Trip History</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL; ?>/pages/settings.php">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL; ?>/includes/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL; ?>/pages/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary text-white ms-2 px-3" href="<?php echo APP_URL; ?>/pages/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container my-4">
