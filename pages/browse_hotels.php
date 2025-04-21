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

// Get user's trips for the select dropdown
$userTrips = getTripsByUserId($_SESSION['user_id']);

// Process hotel booking
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book_hotel') {
    $hotelId = isset($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : 0;
    $tripOption = isset($_POST['trip_option']) ? $_POST['trip_option'] : '';
    $tripId = isset($_POST['trip_id']) ? (int)$_POST['trip_id'] : 0;
    
    // For new trip option
    $destination = isset($_POST['destination']) ? trim($_POST['destination']) : '';
    $startDate = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
    $endDate = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';
    
    if ($hotelId <= 0) {
        $error = 'Invalid hotel selection';
    } elseif ($tripOption === 'new') {
        // Validate new trip data
        if (empty($destination) || empty($startDate) || empty($endDate)) {
            $error = 'Please fill in all required fields for the new trip';
        } elseif ($startDate > $endDate) {
            $error = 'End date must be after start date';
        } else {
            // Create new trip with the hotel
            $tripId = createTrip(
                $_SESSION['user_id'],
                $destination,
                $startDate,
                $endDate,
                '',  // activities
                '',  // notes
                $hotelId,
                []   // tourist_sites
            );
            
            if ($tripId) {
                $success = true;
                $_SESSION['alert'] = [
                    'title' => 'Success!',
                    'message' => 'Hotel booked successfully with a new trip.',
                    'type' => 'success'
                ];
                header('Location: view_trip.php?id=' . $tripId);
                exit;
            } else {
                $error = 'Failed to create new trip';
            }
        }
    } elseif ($tripOption === 'existing' && $tripId > 0) {
        // Add hotel to existing trip
        $tripFound = false;
        foreach ($_SESSION['db']['trips'] as $key => $trip) {
            if ($trip['id'] == $tripId && $trip['user_id'] == $_SESSION['user_id']) {
                $_SESSION['db']['trips'][$key]['hotel_id'] = $hotelId;
                $_SESSION['db']['trips'][$key]['updated_at'] = date('Y-m-d H:i:s');
                $tripFound = true;
                saveSessionData();
                break;
            }
        }
        
        if ($tripFound) {
            $success = true;
            $_SESSION['alert'] = [
                'title' => 'Success!',
                'message' => 'Hotel added to your existing trip.',
                'type' => 'success'
            ];
            header('Location: view_trip.php?id=' . $tripId);
            exit;
        } else {
            $error = 'Trip not found or you do not have permission to modify it';
        }
    } else {
        $error = 'Please select a valid trip option';
    }
}

$hotels = $_SESSION['db']['hotels'];


$additionalHotels = [

    [
        'id' => 3,
        'name' => 'Shangri-La Boracay Resort & Spa',
        'address' => 'Barangay Yapak, Boracay Island, Malay, Aklan, Philippines',
        'rating' => 4.9,
        'price_range' => '₱₱₱₱',
        'amenities' => 'Private Beach, Infinity Pool, Spa, Multiple Restaurants, Water Sports',
        'image' => APP_URL . '/assets/images/hotels/beach-resort.jpg'
    ],
    [
        'id' => 4,
        'name' => 'El Nido Resorts Lagen Island',
        'address' => 'Lagen Island, El Nido, Palawan, Philippines',
        'rating' => 4.8,
        'price_range' => '₱₱₱',
        'amenities' => 'Eco-Sanctuary, Lagoon, Diving, Island Hopping, Spa',
        'image' => APP_URL . '/assets/images/hotels/mountain-lodge.jpg'
    ],
    [
        'id' => 5,
        'name' => 'The Farm at San Benito',
        'address' => 'Barangay Tipakan, Lipa City, Batangas, Philippines',
        'rating' => 4.7,
        'price_range' => '₱₱₱₱',
        'amenities' => 'Wellness Retreat, Vegan Restaurant, Holistic Spa, Yoga, Meditation',
        'image' => APP_URL . '/assets/images/hotels/urban-luxury.jpg'
    ],
    [
        'id' => 6,
        'name' => 'Henann Resort Alona Beach',
        'address' => 'Alona Beach, Panglao Island, Bohol, Philippines',
        'rating' => 4.6,
        'price_range' => '₱₱₱',
        'amenities' => 'Beachfront, Multiple Pools, Restaurants, Spa, Water Activities',
        'image' => APP_URL . '/assets/images/hotels/historic-hotel.jpg'
    ],
    [
        'id' => 7,
        'name' => 'Siargao Bleu Resort & Spa',
        'address' => 'Tourism Road, General Luna, Siargao Island, Philippines',
        'rating' => 4.5,
        'price_range' => '₱₱',
        'amenities' => 'Surfing Lessons, Pool, Restaurant, Airport Transfers, WiFi',
        'image' => APP_URL . '/assets/images/hotels/budget-inn.jpg'
    ],
    [
        'id' => 8,
        'name' => 'Marco Polo Davao',
        'address' => 'CM Recto Street, Davao City, Philippines',
        'rating' => 4.7,
        'price_range' => '₱₱₱',
        'amenities' => 'City Views, Pool, Multiple Restaurants, Spa, Business Center',
        'image' => APP_URL . '/assets/images/hotels/desert-resort.jpg'
    ],
    [
        'id' => 9,
        'name' => 'Seda Centrio',
        'address' => 'Cagayan de Oro City, Philippines',
        'rating' => 4.6,
        'price_range' => '₱₱',
        'amenities' => 'Mall Access, Business Center, Restaurant, Gym, Free WiFi',
        'image' => APP_URL . '/assets/images/hotels/urban-luxury.jpg'
    ],

    [
        'id' => 10,
        'name' => 'Marina Bay Sands',
        'address' => '10 Bayfront Avenue, Singapore',
        'rating' => 4.8,
        'price_range' => '$$$$$',
        'amenities' => 'Infinity Pool, Casino, Luxury Shopping, SkyPark Observation Deck',
        'image' => APP_URL . '/assets/images/hotels/historic-hotel.jpg'
    ],
    [
        'id' => 11,
        'name' => 'Burj Al Arab Jumeirah',
        'address' => 'Jumeirah Beach Road, Dubai, UAE',
        'rating' => 4.9,
        'price_range' => '$$$$$',
        'amenities' => 'Private Beach, Helipad, Underwater Restaurant, Butler Service',
        'image' => APP_URL . '/assets/images/hotels/budget-inn.jpg'
    ],
    [
        'id' => 12,
        'name' => 'The Ritz Paris',
        'address' => '15 Place Vendôme, 75001 Paris, France',
        'rating' => 4.9,
        'price_range' => '$$$$$',
        'amenities' => 'Michelin Star Restaurant, Luxury Spa, Historic Suites, Bar Hemingway',
        'image' => APP_URL . '/assets/images/hotels/desert-resort.jpg'
    ],
    [
        'id' => 13,
        'name' => 'Aman Tokyo',
        'address' => 'The Otemachi Tower, 1-5-6 Otemachi, Tokyo, Japan',
        'rating' => 4.8,
        'price_range' => '$$$$',
        'amenities' => 'Urban Sanctuary, Traditional Onsen, Panoramic Views, Spa',
        'image' => APP_URL . '/assets/images/hotels/urban-luxury.jpg'
    ],
    [
        'id' => 14,
        'name' => 'Jade Mountain Resort',
        'address' => 'Soufriere, St. Lucia, Caribbean',
        'rating' => 4.9,
        'price_range' => '$$$$$',
        'amenities' => 'Private Infinity Pools, Open-air Sanctuaries, Organic Cuisine',
        'image' => APP_URL . '/assets/images/hotels/beach-resort.jpg'
    ],
    [
        'id' => 15,
        'name' => 'The Plaza Hotel',
        'address' => '768 5th Ave, New York, NY, USA',
        'rating' => 4.7,
        'price_range' => '$$$$',
        'amenities' => 'Iconic Landmark, Central Park Views, Luxury Shopping, Fine Dining',
        'image' => APP_URL . '/assets/images/hotels/historic-hotel.jpg'
    ],
    [
        'id' => 16,
        'name' => 'Four Seasons Resort Bora Bora',
        'address' => 'Motu Tehotu BP 547, Bora Bora, French Polynesia',
        'rating' => 4.9,
        'price_range' => '$$$$$',
        'amenities' => 'Overwater Bungalows, Lagoon, Spa, Snorkeling, Private Beach',
        'image' => APP_URL . '/assets/images/hotels/beach-resort.jpg'
    ],
    [
        'id' => 17,
        'name' => 'The Savoy',
        'address' => 'Strand, London WC2R 0EZ, United Kingdom',
        'rating' => 4.8,
        'price_range' => '$$$$',
        'amenities' => 'Historic Luxury, Thames Views, Gordon Ramsay Restaurant, Afternoon Tea',
        'image' => APP_URL . '/assets/images/hotels/historic-hotel.jpg'
    ],
    [
        'id' => 18,
        'name' => 'Atlantis The Palm',
        'address' => 'Crescent Road, The Palm, Dubai, UAE',
        'rating' => 4.7,
        'price_range' => '$$$$',
        'amenities' => 'Aquaventure Waterpark, Lost Chambers Aquarium, Dolphin Bay, Restaurants',
        'image' => APP_URL . '/assets/images/hotels/desert-resort.jpg'
    ],
    [
        'id' => 19,
        'name' => 'Bellagio',
        'address' => '3600 S Las Vegas Blvd, Las Vegas, NV, USA',
        'rating' => 4.7,
        'price_range' => '$$$$',
        'amenities' => 'Fountain Show, Casino, Fine Dining, Gallery of Fine Art, Botanical Gardens',
        'image' => APP_URL . '/assets/images/hotels/urban-luxury.jpg'
    ],
    [
        'id' => 20,
        'name' => 'The Peninsula Hong Kong',
        'address' => 'Salisbury Road, Tsim Sha Tsui, Hong Kong',
        'rating' => 4.8,
        'price_range' => '$$$$$',
        'amenities' => 'Rolls-Royce Fleet, Helicopter, Spa, Michelin-Starred Dining, Harbor Views',
        'image' => APP_URL . '/assets/images/hotels/historic-hotel.jpg'
    ]
];


$hotels = array_merge($hotels, $additionalHotels);


$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$priceRange = isset($_GET['price']) ? $_GET['price'] : '';

// Pagination settings
$itemsPerPage = 6; // Number of hotels per page
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;

if (!empty($filter)) {
    $filteredHotels = [];
    foreach ($hotels as $hotel) {
        if (stripos($hotel['name'], $filter) !== false || 
            stripos($hotel['address'], $filter) !== false) {
            $filteredHotels[] = $hotel;
        }
    }
    $hotels = $filteredHotels;
}

if (!empty($priceRange)) {
    $filteredHotels = [];
    foreach ($hotels as $hotel) {
        if ($hotel['price_range'] === $priceRange) {
            $filteredHotels[] = $hotel;
        }
    }
    $hotels = $filteredHotels;
}

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="mb-0">Browse Hotels</h1>
            <p class="text-muted">Find the perfect accommodation for your trip</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="dashboard.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <div class="booking-search-container mb-4">
        <div class="container-fluid py-4 bg-primary text-white">
            <div class="row">
                <div class="col-12">
                    <h3 class="mb-3">Find deals on hotels, homes, and much more...</h3>
                    <p>From cozy country homes to funky city apartments</p>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <form action="" method="GET" class="booking-search-form bg-white p-3 rounded shadow">
                        <div class="row g-2">
                            <div class="col-md-5">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-map-marker-alt text-primary"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0" id="filter" name="filter" 
                                           placeholder="Where are you going?" value="<?php echo htmlspecialchars($filter); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-dollar-sign text-primary"></i>
                                    </span>
                                    <select class="form-select border-start-0" id="price" name="price">
                                        <option value="">Any price range</option>
                                        <option value="$" <?php echo $priceRange === '$' ? 'selected' : ''; ?>>$ (Budget)</option>
                                        <option value="$$" <?php echo $priceRange === '$$' ? 'selected' : ''; ?>>$$ (Moderate)</option>
                                        <option value="$$$" <?php echo $priceRange === '$$$' ? 'selected' : ''; ?>>$$$ (Upscale)</option>
                                        <option value="$$$$" <?php echo $priceRange === '$$$$' ? 'selected' : ''; ?>>$$$$ (Luxury)</option>
                                        <option value="$$$$$" <?php echo $priceRange === '$$$$$' ? 'selected' : ''; ?>>$$$$$ (Ultra Luxury)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-star text-primary"></i>
                                    </span>
                                    <select class="form-select border-start-0" id="rating" name="rating">
                                        <option value="">Any rating</option>
                                        <option value="3" <?php echo $rating === '3' ? 'selected' : ''; ?>>3+ rated</option>
                                        <option value="4" <?php echo $rating === '4' ? 'selected' : ''; ?>>4+ rated</option>
                                        <option value="4.5" <?php echo $rating === '4.5' ? 'selected' : ''; ?>>4.5+ rated</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100 py-2">
                                    <i class="fas fa-search me-2"></i>Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <?php if (empty($hotels)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>No hotels found matching your criteria. Try adjusting your filters.
                </div>
            </div>
        <?php else: ?>
            <?php 
            // Calculate total pages and ensure current page is valid
            $totalHotels = count($hotels);
            $totalPages = ceil($totalHotels / $itemsPerPage);
            if ($currentPage > $totalPages) $currentPage = $totalPages;
            
            // Get hotels for current page
            $startIndex = ($currentPage - 1) * $itemsPerPage;
            $pageHotels = array_slice($hotels, $startIndex, $itemsPerPage);
            
            foreach ($pageHotels as $hotel): 
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card hotel-card h-100 shadow-sm border-0 overflow-hidden">
                        <div class="position-relative">
                            <img src="<?php echo $hotel['image'] ?? APP_URL . '/assets/images/hotels/default.jpg'; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($hotel['name']); ?>">
                            <?php if ($hotel['rating'] >= 4.5): ?>
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-success py-2 px-3">Top Rated</span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($hotel['name']); ?></h5>
                                <div class="rating-badge ms-2">
                                    <span class="badge bg-primary rounded-pill px-2 py-1">
                                        <?php echo $hotel['rating']; ?>
                                    </span>
                                </div>
                            </div>
                            <p class="card-text small text-muted mb-1">
                                <i class="fas fa-map-marker-alt me-1 text-primary"></i><?php echo htmlspecialchars($hotel['address']); ?>
                            </p>
                            <div class="d-flex align-items-center mb-2">
                                <div class="me-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= floor($hotel['rating'])): ?>
                                            <i class="fas fa-star text-warning"></i>
                                        <?php elseif ($i - 0.5 <= $hotel['rating']): ?>
                                            <i class="fas fa-star-half-alt text-warning"></i>
                                        <?php else: ?>
                                            <i class="far fa-star text-warning"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <span class="small text-muted">(<?php echo rand(50, 500); ?> reviews)</span>
                            </div>
                            <?php if (!empty($hotel['amenities'])): ?>
                            <div class="amenities mb-3">
                                <?php 
                                $amenitiesArray = explode(',', $hotel['amenities']);
                                $displayAmenities = array_slice($amenitiesArray, 0, 3);
                                foreach ($displayAmenities as $amenity): 
                                    $amenity = trim($amenity);
                                ?>
                                <span class="badge bg-light text-dark me-1 mb-1"><?php echo htmlspecialchars($amenity); ?></span>
                                <?php endforeach; ?>
                                <?php if (count($amenitiesArray) > 3): ?>
                                <span class="badge bg-light text-dark me-1 mb-1">+<?php echo count($amenitiesArray) - 3; ?> more</span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between align-items-end mt-auto">
                                <div>
                                    <div class="price-label small text-muted">Price per night</div>
                                    <div class="price fw-bold fs-5"><?php echo $hotel['price_range']; ?></div>
                                </div>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#hotelModal<?php echo $hotel['id']; ?>">
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hotel Modal -->
                <div class="modal fade" id="hotelModal<?php echo $hotel['id']; ?>" tabindex="-1" aria-labelledby="hotelModalLabel<?php echo $hotel['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="hotelModalLabel<?php echo $hotel['id']; ?>"><?php echo htmlspecialchars($hotel['name']); ?></h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">
                                <!-- Hotel Gallery -->
                                <div class="hotel-gallery bg-light p-3">
                                    <div class="row g-2">
                                        <div class="col-md-8">
                                            <div class="main-image rounded overflow-hidden" style="height: 350px;">
                                                <img src="<?php echo $hotel['image'] ?? APP_URL . '/assets/images/hotels/default.jpg'; ?>" class="w-100 h-100" style="object-fit: cover;" alt="<?php echo htmlspecialchars($hotel['name']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="row g-2">
                                                <div class="col-6 col-md-12">
                                                    <div class="thumbnail rounded overflow-hidden" style="height: 170px;">
                                                        <img src="<?php echo APP_URL . '/assets/images/hotels/beach-resort.jpg'; ?>" class="w-100 h-100" style="object-fit: cover;" alt="Thumbnail">
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-12">
                                                    <div class="thumbnail rounded overflow-hidden" style="height: 170px;">
                                                        <img src="<?php echo APP_URL . '/assets/images/hotels/mountain-lodge.jpg'; ?>" class="w-100 h-100" style="object-fit: cover;" alt="Thumbnail">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="container py-4">
                                    <div class="row">
                                        <!-- Hotel Details -->
                                        <div class="col-md-8">
                                            <div class="hotel-header d-flex justify-content-between align-items-start mb-4">
                                                <div>
                                                    <h3 class="mb-1"><?php echo htmlspecialchars($hotel['name']); ?></h3>
                                                    <div class="d-flex align-items-center mb-2">
                                                        <div class="me-2">
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <?php if ($i <= floor($hotel['rating'])): ?>
                                                                    <i class="fas fa-star text-warning"></i>
                                                                <?php elseif ($i - 0.5 <= $hotel['rating']): ?>
                                                                    <i class="fas fa-star-half-alt text-warning"></i>
                                                                <?php else: ?>
                                                                    <i class="far fa-star text-warning"></i>
                                                                <?php endif; ?>
                                                            <?php endfor; ?>
                                                        </div>
                                                        <span class="badge bg-primary rounded-pill px-2 py-1 me-2"><?php echo $hotel['rating']; ?></span>
                                                        <span class="text-muted">(<?php echo rand(50, 500); ?> reviews)</span>
                                                    </div>
                                                    <p class="mb-2">
                                                        <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                                        <?php echo htmlspecialchars($hotel['address']); ?>
                                                        <a href="#" class="ms-2 small">Show on map</a>
                                                    </p>
                                                </div>
                                                <div class="text-end">
                                                    <div class="price-label text-muted">Price per night</div>
                                                    <div class="price fs-3 fw-bold text-primary"><?php echo $hotel['price_range']; ?></div>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <h5 class="mb-3">About this property</h5>
                                                <p>Experience luxury and comfort at <?php echo htmlspecialchars($hotel['name']); ?>. Nestled in the heart of <?php echo htmlspecialchars(explode(',', $hotel['address'])[0] ?? ''); ?>, this stunning property offers a perfect blend of elegance and modern amenities.</p>
                                                <p>The hotel is conveniently located near major attractions and transportation hubs, making it an ideal choice for both business and leisure travelers.</p>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <h5 class="mb-3">Most popular facilities</h5>
                                                <div class="row">
                                                    <?php 
                                                    if (!empty($hotel['amenities'])) {
                                                        $amenitiesArray = explode(',', $hotel['amenities']);
                                                        foreach ($amenitiesArray as $amenity): 
                                                            $amenity = trim($amenity);
                                                            $icon = 'check-circle';
                                                            if (stripos($amenity, 'pool') !== false) $icon = 'swimming-pool';
                                                            if (stripos($amenity, 'wifi') !== false) $icon = 'wifi';
                                                            if (stripos($amenity, 'restaurant') !== false) $icon = 'utensils';
                                                            if (stripos($amenity, 'spa') !== false) $icon = 'spa';
                                                            if (stripos($amenity, 'beach') !== false) $icon = 'umbrella-beach';
                                                    ?>
                                                    <div class="col-md-6 mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-<?php echo $icon; ?> text-success me-2"></i>
                                                            <span><?php echo htmlspecialchars($amenity); ?></span>
                                                        </div>
                                                    </div>
                                                    <?php 
                                                        endforeach;
                                                    } else {
                                                        // Default amenities if none provided
                                                        $defaultAmenities = ['Free WiFi', '24-hour front desk', 'Air conditioning', 'Non-smoking rooms'];
                                                        foreach ($defaultAmenities as $amenity):
                                                    ?>
                                                    <div class="col-md-6 mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-check-circle text-success me-2"></i>
                                                            <span><?php echo $amenity; ?></span>
                                                        </div>
                                                    </div>
                                                    <?php 
                                                        endforeach;
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <h5 class="mb-3">Availability</h5>
                                                <div class="alert alert-success">
                                                    <i class="fas fa-check-circle me-2"></i>
                                                    <strong>Good availability!</strong> We recommend booking soon to secure your stay.
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Booking Box -->
                                        <div class="col-md-4">
                                            <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                                                <div class="card-header bg-primary text-white py-3">
                                                    <h5 class="mb-0">Book your stay</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Check-in / Check-out</label>
                                                        <div class="input-group mb-2">
                                                            <span class="input-group-text bg-white">
                                                                <i class="fas fa-calendar-alt text-primary"></i>
                                                            </span>
                                                            <input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                                                        </div>
                                                        <div class="input-group">
                                                            <span class="input-group-text bg-white">
                                                                <i class="fas fa-calendar-alt text-primary"></i>
                                                            </span>
                                                            <input type="date" class="form-control" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>">
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-4">
                                                        <label class="form-label">Guests</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text bg-white">
                                                                <i class="fas fa-user text-primary"></i>
                                                            </span>
                                                            <select class="form-select">
                                                                <option>1 adult</option>
                                                                <option selected>2 adults</option>
                                                                <option>3 adults</option>
                                                                <option>4 adults</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="price-summary mb-4 p-3 bg-light rounded">
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span>7 nights</span>
                                                            <span><?php echo str_repeat(substr($hotel['price_range'], 0, 1), 7); ?></span>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span>Taxes and fees</span>
                                                            <span><?php echo substr($hotel['price_range'], 0, 1); ?></span>
                                                        </div>
                                                        <hr>
                                                        <div class="d-flex justify-content-between fw-bold">
                                                            <span>Total</span>
                                                            <span class="text-primary"><?php echo str_repeat(substr($hotel['price_range'], 0, 1), 8); ?></span>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="booking-options">
                                                        <?php if (!empty($userTrips)): ?>
                                                        <div class="dropdown mb-2 w-100">
                                                            <button class="btn btn-outline-primary dropdown-toggle w-100" type="button" id="addToTripDropdown<?php echo $hotel['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                                Add to Existing Trip
                                                            </button>
                                                            <ul class="dropdown-menu w-100" aria-labelledby="addToTripDropdown<?php echo $hotel['id']; ?>">
                                                                <?php foreach ($userTrips as $trip): ?>
                                                                    <li>
                                                                        <form method="POST" action="" class="dropdown-item p-0">
                                                                            <input type="hidden" name="action" value="book_hotel">
                                                                            <input type="hidden" name="hotel_id" value="<?php echo $hotel['id']; ?>">
                                                                            <input type="hidden" name="trip_option" value="existing">
                                                                            <input type="hidden" name="trip_id" value="<?php echo $trip['id']; ?>">
                                                                            <button type="submit" class="dropdown-item">
                                                                                <?php echo htmlspecialchars($trip['destination']); ?> (<?php echo date('M d, Y', strtotime($trip['start_date'])); ?>)
                                                                            </button>
                                                                        </form>
                                                                    </li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        </div>
                                                        <?php endif; ?>
                                                        <a href="create_trip.php?hotel_id=<?php echo $hotel['id']; ?>" class="btn btn-primary w-100">
                                                            <i class="fas fa-calendar-plus me-2"></i>Book Now
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="card-footer bg-white text-center">
                                                    <small class="text-muted">You'll be charged after your stay</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                            
                            <!-- Book Hotel Modal -->
                            <div class="modal fade" id="bookHotelModal<?php echo $hotel['id']; ?>" tabindex="-1" aria-labelledby="bookHotelModalLabel<?php echo $hotel['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="bookHotelModalLabel<?php echo $hotel['id']; ?>">Book <?php echo htmlspecialchars($hotel['name']); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST" action="" id="bookHotelForm<?php echo $hotel['id']; ?>">
                                                <input type="hidden" name="action" value="book_hotel">
                                                <input type="hidden" name="hotel_id" value="<?php echo $hotel['id']; ?>">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Add to Trip</label>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="radio" name="trip_option" id="newTrip<?php echo $hotel['id']; ?>" value="new" checked>
                                                        <label class="form-check-label" for="newTrip<?php echo $hotel['id']; ?>">
                                                            Create a new trip
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="trip_option" id="existingTrip<?php echo $hotel['id']; ?>" value="existing" <?php echo empty($userTrips) ? 'disabled' : ''; ?>>
                                                        <label class="form-check-label" for="existingTrip<?php echo $hotel['id']; ?>">
                                                            Add to existing trip <?php echo empty($userTrips) ? '(No trips available)' : ''; ?>
                                                        </label>
                                                    </div>
                                                </div>
                                                
                                                <!-- New Trip Fields -->
                                                <div id="newTripFields<?php echo $hotel['id']; ?>" class="mb-3">
                                                    <div class="mb-3">
                                                        <label for="destination<?php echo $hotel['id']; ?>" class="form-label">Destination</label>
                                                        <input type="text" class="form-control" id="destination<?php echo $hotel['id']; ?>" name="destination" value="<?php echo htmlspecialchars(explode(',', $hotel['address'])[0] ?? ''); ?>">
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="start_date<?php echo $hotel['id']; ?>" class="form-label">Start Date</label>
                                                            <input type="date" class="form-control" id="start_date<?php echo $hotel['id']; ?>" name="start_date" value="<?php echo date('Y-m-d'); ?>">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="end_date<?php echo $hotel['id']; ?>" class="form-label">End Date</label>
                                                            <input type="date" class="form-control" id="end_date<?php echo $hotel['id']; ?>" name="end_date" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Existing Trip Dropdown -->
                                                <div id="existingTripFields<?php echo $hotel['id']; ?>" class="mb-3" style="display: none;">
                                                    <label for="trip_id<?php echo $hotel['id']; ?>" class="form-label">Select Trip</label>
                                                    <select class="form-select" id="trip_id<?php echo $hotel['id']; ?>" name="trip_id">
                                                        <?php foreach ($userTrips as $trip): ?>
                                                            <option value="<?php echo $trip['id']; ?>">
                                                                <?php echo htmlspecialchars($trip['destination']); ?> (<?php echo date('M d, Y', strtotime($trip['start_date'])); ?> - <?php echo date('M d, Y', strtotime($trip['end_date'])); ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                
                                                <div class="d-grid gap-2">
                                                    <button type="submit" class="btn btn-primary">Confirm Booking</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const newTripRadio<?php echo $hotel['id']; ?> = document.getElementById('newTrip<?php echo $hotel['id']; ?>');
                                    const existingTripRadio<?php echo $hotel['id']; ?> = document.getElementById('existingTrip<?php echo $hotel['id']; ?>');
                                    const newTripFields<?php echo $hotel['id']; ?> = document.getElementById('newTripFields<?php echo $hotel['id']; ?>');
                                    const existingTripFields<?php echo $hotel['id']; ?> = document.getElementById('existingTripFields<?php echo $hotel['id']; ?>');
                                    
                                    if (newTripRadio<?php echo $hotel['id']; ?> && existingTripRadio<?php echo $hotel['id']; ?>) {
                                        newTripRadio<?php echo $hotel['id']; ?>.addEventListener('change', function() {
                                            if (this.checked) {
                                                newTripFields<?php echo $hotel['id']; ?>.style.display = 'block';
                                                existingTripFields<?php echo $hotel['id']; ?>.style.display = 'none';
                                            }
                                        });
                                        
                                        existingTripRadio<?php echo $hotel['id']; ?>.addEventListener('change', function() {
                                            if (this.checked) {
                                                newTripFields<?php echo $hotel['id']; ?>.style.display = 'none';
                                                existingTripFields<?php echo $hotel['id']; ?>.style.display = 'block';
                                            }
                                        });
                                    }
                                });
                            </script>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            

            <?php if ($totalPages > 1): ?>
            <div class="col-12 mt-4">
                <nav aria-label="Hotel pagination">
                    <ul class="pagination justify-content-center">

                        <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $currentPage - 1; ?><?php echo !empty($filter) ? '&filter=' . urlencode($filter) : ''; ?><?php echo !empty($priceRange) ? '&price=' . urlencode($priceRange) : ''; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($currentPage == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($filter) ? '&filter=' . urlencode($filter) : ''; ?><?php echo !empty($priceRange) ? '&price=' . urlencode($priceRange) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        

                        <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $currentPage + 1; ?><?php echo !empty($filter) ? '&filter=' . urlencode($filter) : ''; ?><?php echo !empty($priceRange) ? '&price=' . urlencode($priceRange) : ''; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    

    <?php if (!empty($hotels)): ?>
    <div class="row mt-3">
        <div class="col-12 text-center text-muted">
            <small>Showing <?php echo min($startIndex + 1, $totalHotels); ?> to <?php echo min($startIndex + $itemsPerPage, $totalHotels); ?> of <?php echo $totalHotels; ?> hotels</small>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
/* Booking.com inspired styles */
.booking-search-container {
    margin-top: -1.5rem;
}

.booking-search-form {
    margin-top: 1rem;
    border-radius: 8px;
}

.hotel-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 8px;
}

.hotel-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

.hotel-card .card-img-top {
    height: 200px;
    object-fit: cover;
}

.rating-badge .badge {
    font-weight: bold;
}

.price-label {
    font-size: 0.8rem;
}

.amenities .badge {
    font-size: 0.75rem;
    font-weight: normal;
}

/* Custom styling for form elements */
.input-group-text {
    color: #0071c2;
}

.form-control:focus, .form-select:focus {
    border-color: #0071c2;
    box-shadow: 0 0 0 0.25rem rgba(0, 113, 194, 0.25);
}

/* Pagination styling */
.pagination .page-link {
    color: #0071c2;
}

.pagination .page-item.active .page-link {
    background-color: #0071c2;
    border-color: #0071c2;
}
</style>

<?php include '../includes/footer.php'; ?>
