<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/config.php';
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
    <link href="<?php echo APP_URL; ?>/assets/css/logo.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/images.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/hero.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/testimonials.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/destinations.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/cta.css" rel="stylesheet">
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
                <ul class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL; ?>/pages/dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL; ?>/includes/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL; ?>/pages/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL; ?>/pages/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>


    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h1 class="hero-title">Plan Your Perfect Trip</h1>
                    <p class="hero-subtitle">Discover amazing destinations, create detailed itineraries, and make your travel dreams come true with our easy-to-use planning tools.</p>
                    <div class="hero-buttons">
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <a href="<?php echo APP_URL; ?>/pages/dashboard.php" class="btn btn-primary btn-lg">My Dashboard</a>
                            <a href="<?php echo APP_URL; ?>/pages/create_trip.php" class="btn btn-outline-primary btn-lg">Plan New Trip</a>
                        <?php else: ?>
                            <a href="<?php echo APP_URL; ?>/pages/register.php" class="btn btn-primary btn-lg">Get Started</a>
                            <a href="<?php echo APP_URL; ?>/pages/login.php" class="btn btn-outline-primary btn-lg">Login</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block text-center">
                    <div class="logo-hero-container p-4 bg-light rounded shadow-lg">
                        <img src="<?php echo APP_URL; ?>/assets/images/logo/logo.svg" alt="Trip Planner Logo" class="img-fluid mb-3" style="max-width: 200px;">
                        <h3 class="text-dark mb-2">Trip Planner</h3>
                        <p class="text-warning fw-bold">TURN YOUR TRAVEL DREAMS INTO PLANS</p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section class="features-section">
        <div class="container">
            <h2 class="text-center mb-5">Why Choose TripPlanner?</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="fas fa-map-marked-alt fa-3x text-primary mb-3"></i>
                        <h3 class="feature-title">Plan Your Route</h3>
                        <p class="feature-description">Create detailed itineraries with maps, directions, and estimated travel times between destinations.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="fas fa-hotel fa-3x text-primary mb-3"></i>
                        <h3 class="feature-title">Find Hotels</h3>
                        <p class="feature-description">Discover and compare accommodations that fit your budget and preferences in any destination.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="fas fa-camera fa-3x text-primary mb-3"></i>
                        <h3 class="feature-title">Discover Sites</h3>
                        <p class="feature-description">Explore popular attractions, hidden gems, and must-visit locations at your destination.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section class="destinations-section">
        <div class="container">
            <div class="section-title">
                <h2>Popular Destinations in the Philippines</h2>
                <p>Explore the most beautiful and sought-after destinations across the Philippine archipelago</p>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="destination-card">
                        <div class="destination-img">
                            <div class="destination-img-placeholder">Boracay Island</div>
                        </div>
                        <div class="destination-content">
                            <h3 class="destination-title">Boracay Island</h3>
                            <div class="destination-location">
                                <i class="fas fa-map-marker-alt"></i> Malay, Aklan
                            </div>
                            <p class="destination-description">Famous for its pristine white sand beaches and crystal-clear waters. Perfect for swimming, sunbathing, and water sports.</p>
                            <div class="destination-meta">
                                <div class="destination-rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="destination-price">From ₱5,000</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="destination-card">
                        <div class="destination-img">
                            <div class="destination-img-placeholder">Palawan</div>
                        </div>
                        <div class="destination-content">
                            <h3 class="destination-title">El Nido, Palawan</h3>
                            <div class="destination-location">
                                <i class="fas fa-map-marker-alt"></i> Palawan
                            </div>
                            <p class="destination-description">Known for its stunning limestone cliffs, hidden lagoons, and diverse marine life. Ideal for island hopping and snorkeling.</p>
                            <div class="destination-meta">
                                <div class="destination-rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="destination-price">From ₱6,500</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="destination-card">
                        <div class="destination-img">
                            <div class="destination-img-placeholder">Chocolate Hills</div>
                        </div>
                        <div class="destination-content">
                            <h3 class="destination-title">Chocolate Hills</h3>
                            <div class="destination-location">
                                <i class="fas fa-map-marker-alt"></i> Bohol
                            </div>
                            <p class="destination-description">A geological formation of more than 1,200 hills that turn chocolate-brown during the dry season. A unique natural wonder.</p>
                            <div class="destination-meta">
                                <div class="destination-rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <div class="destination-price">From ₱4,000</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="destination-card">
                        <div class="destination-img">
                            <div class="destination-img-placeholder">Siargao Island</div>
                        </div>
                        <div class="destination-content">
                            <h3 class="destination-title">Siargao Island</h3>
                            <div class="destination-location">
                                <i class="fas fa-map-marker-alt"></i> Surigao del Norte
                            </div>
                            <p class="destination-description">Known as the surfing capital of the Philippines with its famous Cloud 9 wave. Perfect for surfing enthusiasts and beach lovers.</p>
                            <div class="destination-meta">
                                <div class="destination-rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="destination-price">From ₱4,500</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="destination-card">
                        <div class="destination-img">
                            <div class="destination-img-placeholder">Mayon Volcano</div>
                        </div>
                        <div class="destination-content">
                            <h3 class="destination-title">Mayon Volcano</h3>
                            <div class="destination-location">
                                <i class="fas fa-map-marker-alt"></i> Albay, Bicol Region
                            </div>
                            <p class="destination-description">Famous for its perfect cone shape, this active volcano is one of the Philippines' most iconic natural landmarks.</p>
                            <div class="destination-meta">
                                <div class="destination-rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <div class="destination-price">From ₱3,500</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="destination-card">
                        <div class="destination-img">
                            <div class="destination-img-placeholder">Banaue Rice Terraces</div>
                        </div>
                        <div class="destination-content">
                            <h3 class="destination-title">Banaue Rice Terraces</h3>
                            <div class="destination-location">
                                <i class="fas fa-map-marker-alt"></i> Ifugao, Cordillera
                            </div>
                            <p class="destination-description">Often called the "Eighth Wonder of the World," these 2,000-year-old terraces were carved into the mountains by ancestors of the indigenous people.</p>
                            <div class="destination-meta">
                                <div class="destination-rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="destination-price">From ₱4,800</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="see-all-link">
                <a href="<?php echo APP_URL; ?>/pages/destinations.php" class="btn btn-outline-primary">See All Destinations</a>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="container">
            <h2 class="text-center mb-5">What Our Travelers Say</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="quote-icon">
                            <i class="fas fa-quote-left"></i>
                        </div>
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-content">"Trip Planner made organizing our family vacation to Boracay so easy! The hotel recommendations were perfect for our budget, and the itinerary tool helped us make the most of our time."</p>
                        <div class="testimonial-author">
                            <div class="testimonial-avatar">M</div>
                            <div class="testimonial-info">
                                <h5>Maria Santos</h5>
                                <p>Manila, Philippines</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="quote-icon">
                            <i class="fas fa-quote-left"></i>
                        </div>
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <p class="testimonial-content">"As a solo traveler exploring Palawan, this platform was invaluable. The tourist site recommendations led me to hidden gems I would have never found on my own!"</p>
                        <div class="testimonial-author">
                            <div class="testimonial-avatar">R</div>
                            <div class="testimonial-info">
                                <h5>Ryan Mendoza</h5>
                                <p>Cebu City, Philippines</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="quote-icon">
                            <i class="fas fa-quote-left"></i>
                        </div>
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-content">"Planning our honeymoon in Siargao was stress-free thanks to Trip Planner. The interface is user-friendly, and the customer service team was very responsive when we had questions."</p>
                        <div class="testimonial-author">
                            <div class="testimonial-avatar">A</div>
                            <div class="testimonial-info">
                                <h5>Ana & Paolo Reyes</h5>
                                <p>Davao, Philippines</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2 class="cta-title">Ready to Start Your Philippine Adventure?</h2>
                <p class="cta-description">Join thousands of travelers who use Trip Planner to discover the beauty of the Philippines. Create your free account today and start planning your dream vacation!</p>
                <div class="cta-buttons">
                    <?php if(!isset($_SESSION['user_id'])): ?>
                        <a href="<?php echo APP_URL; ?>/pages/register.php" class="btn btn-light btn-lg">Sign Up Free</a>
                        <a href="<?php echo APP_URL; ?>/pages/login.php" class="btn btn-outline-light btn-lg">Login</a>
                    <?php else: ?>
                        <a href="<?php echo APP_URL; ?>/pages/create_trip.php" class="btn btn-light btn-lg">Plan Your Trip Now</a>
                        <a href="<?php echo APP_URL; ?>/pages/dashboard.php" class="btn btn-outline-light btn-lg">Go to Dashboard</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>


    <footer class="footer mt-auto py-4 bg-dark text-white">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <div class="d-flex align-items-center mb-3">
                        <img src="<?php echo APP_URL; ?>/assets/images/logo/logo.svg" alt="Trip Planner Logo" width="50" height="50" class="me-2">
                        <h5 class="mb-0 text-white">Trip Planner</h5>
                    </div>
                    <p class="text-light small">TURN YOUR TRAVEL DREAMS INTO PLANS</p>
                    <p>Plan your next adventure with our easy-to-use tools.</p>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo APP_URL; ?>/index.php" class="text-white">Home</a></li>
                        <li><a href="<?php echo APP_URL; ?>/pages/login.php" class="text-white">Login</a></li>
                        <li><a href="<?php echo APP_URL; ?>/pages/register.php" class="text-white">Register</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contact</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-envelope me-2"></i> info@tripplanner.com</li>
                        <li><i class="fas fa-phone me-2"></i> (123) 456-7890</li>
                    </ul>
                </div>
            </div>
            <hr class="mt-4">
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> TripPlanner. All rights reserved.</p>
            </div>
        </div>
    </footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/main.js"></script>
</body>
</html>
