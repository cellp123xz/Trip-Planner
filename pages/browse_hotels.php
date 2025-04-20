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


    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-6">
                    <label for="filter" class="form-label">Search Hotels</label>
                    <input type="text" class="form-control" id="filter" name="filter" 
                           placeholder="Hotel name or location" value="<?php echo htmlspecialchars($filter); ?>">
                </div>
                <div class="col-md-4">
                    <label for="price" class="form-label">Price Range</label>
                    <select class="form-select" id="price" name="price">
                        <option value="">All Prices</option>
                        <option value="$" <?php echo $priceRange === '$' ? 'selected' : ''; ?>>$ (Budget)</option>
                        <option value="$$" <?php echo $priceRange === '$$' ? 'selected' : ''; ?>>$$ (Moderate)</option>
                        <option value="$$$" <?php echo $priceRange === '$$$' ? 'selected' : ''; ?>>$$$ (Upscale)</option>
                        <option value="$$$$" <?php echo $priceRange === '$$$$' ? 'selected' : ''; ?>>$$$$ (Luxury)</option>
                        <option value="$$$$$" <?php echo $priceRange === '$$$$$' ? 'selected' : ''; ?>>$$$$$ (Ultra Luxury)</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Filter
                    </button>
                </div>
            </form>
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
                    <div class="card h-100 shadow-sm hotel-card">
                        <div class="card-img-top hotel-image" style="background-color: #0D6EFD; color: white; display: flex; align-items: center; justify-content: center; height: 200px;">
                            <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($hotel['name']); ?></h5>
                                <span class="badge bg-primary"><?php echo $hotel['price_range']; ?></span>
                            </div>
                            <p class="card-text small text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($hotel['address']); ?>
                            </p>
                            <div class="mb-3">
                                <span class="text-warning">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-<?php echo $i <= $hotel['rating'] ? 'star' : ($i - 0.5 <= $hotel['rating'] ? 'star-half-alt' : 'star text-muted'); ?>"></i>
                                    <?php endfor; ?>
                                </span>
                                <span class="ms-1"><?php echo $hotel['rating']; ?></span>
                            </div>
                            <?php if (isset($hotel['amenities'])): ?>
                                <p class="card-text small">
                                    <strong>Amenities:</strong> <?php echo htmlspecialchars($hotel['amenities']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <button class="btn btn-sm btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#hotelModal<?php echo $hotel['id']; ?>">
                                <i class="fas fa-info-circle me-1"></i>View Details
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Hotel Details Modal -->
                <div class="modal fade" id="hotelModal<?php echo $hotel['id']; ?>" tabindex="-1" aria-labelledby="hotelModalLabel<?php echo $hotel['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="hotelModalLabel<?php echo $hotel['id']; ?>"><?php echo htmlspecialchars($hotel['name']); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="rounded mb-3" style="background-color: #0D6EFD; color: white; display: flex; align-items: center; justify-content: center; height: 200px;">
                                            <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Hotel Information</h5>
                                        <p><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($hotel['address']); ?></p>
                                        <p>
                                            <i class="fas fa-star text-warning me-2"></i>Rating: <?php echo $hotel['rating']; ?>/5
                                        </p>
                                        <p><i class="fas fa-tag me-2"></i>Price Range: <?php echo $hotel['price_range']; ?></p>
                                        <?php if (isset($hotel['amenities'])): ?>
                                            <p><i class="fas fa-concierge-bell me-2"></i>Amenities: <?php echo htmlspecialchars($hotel['amenities']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <h5>About this Hotel</h5>
                                    <p>This is a sample description for <?php echo htmlspecialchars($hotel['name']); ?>. In a real application, this would contain detailed information about the hotel, its history, services, and unique selling points.</p>
                                    <p>The hotel is conveniently located in <?php echo htmlspecialchars(explode(',', $hotel['address'])[1] ?? ''); ?> and offers easy access to local attractions and transportation.</p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary">Book Now</button>
                            </div>
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
.hotel-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.hotel-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}
.hotel-card .card-img-top {
    height: 200px;
    object-fit: cover;
}
</style>

<?php include '../includes/footer.php'; ?>
