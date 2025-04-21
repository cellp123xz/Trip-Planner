<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';

// Require login to access this page
requireLogin();

// Get user data
$user = getUserById($_SESSION['user_id']);
if (!$user) {
    // If user not found, redirect to login
    logoutUser();
    header("Location: login.php");
    exit;
}

// Get all tourist sites from session storage
$sites = $_SESSION['db']['tourist_sites'];

// Add more sample tourist sites for a better user experience
$additionalSites = [
    // Philippine Tourist Sites
    [
        'id' => 3,
        'name' => 'Chocolate Hills',
        'description' => 'A geological formation of more than 1,200 hills that turn chocolate-brown during the dry season',
        'address' => 'Carmen, Bohol, Philippines',
        'category' => 'Natural Wonder',
        'image' => APP_URL . '/assets/images/sites/statue-of-liberty.jpg'
    ],
    [
        'id' => 4,
        'name' => 'Mayon Volcano',
        'description' => 'Active volcano known for its perfect cone shape and one of the most iconic landmarks in the Philippines',
        'address' => 'Albay, Bicol Region, Philippines',
        'category' => 'Natural Wonder',
        'image' => APP_URL . '/assets/images/sites/great-wall.jpg'
    ],
    [
        'id' => 5,
        'name' => 'Banaue Rice Terraces',
        'description' => 'Ancient terraces carved into the mountains by ancestors of the indigenous people, often called the "Eighth Wonder of the World"',
        'address' => 'Ifugao, Cordillera, Philippines',
        'category' => 'UNESCO Heritage Site',
        'image' => APP_URL . '/assets/images/sites/machu-picchu.jpg'
    ],
    [
        'id' => 6,
        'name' => 'Underground River',
        'description' => 'One of the New 7 Wonders of Nature, this navigable underground river features stunning limestone formations',
        'address' => 'Puerto Princesa, Palawan, Philippines',
        'category' => 'UNESCO Heritage Site',
        'image' => APP_URL . '/assets/images/sites/taj-mahal.jpg'
    ],
    [
        'id' => 7,
        'name' => 'Taal Volcano',
        'description' => 'The smallest active volcano in the world, located in the middle of Taal Lake',
        'address' => 'Batangas, Philippines',
        'category' => 'Natural Wonder',
        'image' => APP_URL . '/assets/images/sites/grand-canyon.jpg'
    ],
    [
        'id' => 8,
        'name' => 'Boracay White Beach',
        'description' => 'Famous for its powdery white sand and crystal-clear waters, consistently rated as one of the best beaches in the world',
        'address' => 'Malay, Aklan, Philippines',
        'category' => 'Beach',
        'image' => APP_URL . '/assets/images/sites/santorini.jpg'
    ],
    [
        'id' => 9,
        'name' => 'Intramuros',
        'description' => 'Historic walled area within Manila, featuring Spanish colonial architecture and Fort Santiago',
        'address' => 'Manila, Philippines',
        'category' => 'Historical Site',
        'image' => APP_URL . '/assets/images/sites/statue-of-liberty.jpg'
    ],
    // International Tourist Sites
    [
        'id' => 10,
        'name' => 'Eiffel Tower',
        'description' => 'Iconic iron lattice tower on the Champ de Mars, a global cultural icon of France and one of the most recognizable structures in the world',
        'address' => 'Champ de Mars, 5 Avenue Anatole France, Paris, France',
        'category' => 'Monument',
        'image' => APP_URL . '/assets/images/sites/grand-canyon.jpg'
    ],
    [
        'id' => 11,
        'name' => 'Santorini',
        'description' => 'Stunning Greek island known for its whitewashed, cube-shaped buildings with blue accents, steep cliffs and amazing sunsets',
        'address' => 'Cyclades Islands, Greece',
        'category' => 'Island',
        'image' => APP_URL . '/assets/images/sites/santorini.jpg'
    ],
    [
        'id' => 12,
        'name' => 'Statue of Liberty',
        'description' => 'Colossal neoclassical sculpture on Liberty Island, a universal symbol of freedom and democracy',
        'address' => 'Liberty Island, New York Harbor, USA',
        'category' => 'Monument',
        'image' => APP_URL . '/assets/images/sites/statue-of-liberty.jpg'
    ],
    [
        'id' => 13,
        'name' => 'Great Wall of China',
        'description' => 'Ancient defensive structure built across the historical northern borders of China to protect against invasions',
        'address' => 'Huairou District, Beijing, China',
        'category' => 'Historical Site',
        'image' => APP_URL . '/assets/images/sites/great-wall.jpg'
    ],
    [
        'id' => 14,
        'name' => 'Machu Picchu',
        'description' => '15th-century Inca citadel situated on a mountain ridge above the Sacred Valley, an iconic symbol of the Inca civilization',
        'address' => 'Cusco Region, Peru',
        'category' => 'Archaeological Site',
        'image' => APP_URL . '/assets/images/sites/machu-picchu.jpg'
    ],
    [
        'id' => 15,
        'name' => 'Taj Mahal',
        'description' => 'Ivory-white marble mausoleum on the southern bank of the Yamuna river, a symbol of eternal love',
        'address' => 'Agra, Uttar Pradesh, India',
        'category' => 'Monument',
        'image' => APP_URL . '/assets/images/sites/taj-mahal.jpg'
    ],
    [
        'id' => 16,
        'name' => 'Grand Canyon',
        'description' => 'Steep-sided canyon carved by the Colorado River, known for its visually overwhelming size and intricate landscape',
        'address' => 'Arizona, USA',
        'category' => 'Natural Wonder',
        'image' => APP_URL . '/assets/images/sites/grand-canyon.jpg'
    ],
    [
        'id' => 17,
        'name' => 'Colosseum',
        'description' => 'Oval amphitheatre in the centre of Rome, the largest ancient amphitheatre ever built',
        'address' => 'Piazza del Colosseo, Rome, Italy',
        'category' => 'Historical Site',
        'image' => APP_URL . '/assets/images/sites/statue-of-liberty.jpg'
    ],
    [
        'id' => 18,
        'name' => 'Petra',
        'description' => 'Ancient city carved into rose-colored rock, famous for its rock-cut architecture and water conduit system',
        'address' => 'Ma\'an Governorate, Jordan',
        'category' => 'Archaeological Site',
        'image' => APP_URL . '/assets/images/sites/machu-picchu.jpg'
    ],
    [
        'id' => 19,
        'name' => 'Angkor Wat',
        'description' => 'Largest religious monument in the world, originally constructed as a Hindu temple dedicated to the god Vishnu',
        'address' => 'Siem Reap, Cambodia',
        'category' => 'Temple Complex',
        'image' => APP_URL . '/assets/images/sites/taj-mahal.jpg'
    ],
    [
        'id' => 20,
        'name' => 'Great Barrier Reef',
        'description' => 'World\'s largest coral reef system composed of over 2,900 individual reefs and 900 islands',
        'address' => 'Queensland, Australia',
        'category' => 'Natural Wonder',
        'image' => APP_URL . '/assets/images/sites/santorini.jpg'
    ],
    [
        'id' => 21,
        'name' => 'Serengeti National Park',
        'description' => 'Famous for its annual migration of over 1.5 million wildebeest and 250,000 zebra',
        'address' => 'Tanzania',
        'category' => 'National Park',
        'image' => APP_URL . '/assets/images/sites/grand-canyon.jpg'
    ],
    [
        'id' => 22,
        'name' => 'Northern Lights',
        'description' => 'Natural light display in the Earth\'s sky, predominantly seen in high-latitude regions',
        'address' => 'Tromsø, Norway',
        'category' => 'Natural Phenomenon',
        'image' => APP_URL . '/assets/images/sites/santorini.jpg'
    ]
];

// Merge the additional sites with the existing ones
$sites = array_merge($sites, $additionalSites);

// Filter functionality
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Pagination settings
$itemsPerPage = 6; // Number of sites per page
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;

if (!empty($filter)) {
    $filteredSites = [];
    foreach ($sites as $site) {
        if (stripos($site['name'], $filter) !== false || 
            stripos($site['description'], $filter) !== false || 
            stripos($site['address'], $filter) !== false) {
            $filteredSites[] = $site;
        }
    }
    $sites = $filteredSites;
}

if (!empty($category)) {
    $filteredSites = [];
    foreach ($sites as $site) {
        if ($site['category'] === $category) {
            $filteredSites[] = $site;
        }
    }
    $sites = $filteredSites;
}

// Get unique categories for filter dropdown
$categories = [];
foreach ($_SESSION['db']['tourist_sites'] as $site) {
    if (!in_array($site['category'], $categories)) {
        $categories[] = $site['category'];
    }
}
foreach ($additionalSites as $site) {
    if (!in_array($site['category'], $categories)) {
        $categories[] = $site['category'];
    }
}

include '../includes/header.php';
?>

<div class="booking-search-container">
    <div class="container-fluid py-4 bg-primary text-white">
        <div class="row">
            <div class="col-12">
                <h3 class="mb-3">Discover amazing attractions and activities</h3>
                <p>From iconic landmarks to hidden gems around the world</p>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <form action="" method="GET" class="booking-search-form bg-white p-3 rounded shadow">
                    <div class="row g-2">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-primary"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" id="filter" name="filter" 
                                       placeholder="What are you looking to do?" value="<?php echo htmlspecialchars($filter); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-map-marker-alt text-primary"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" id="location" name="location" 
                                       placeholder="Where?" value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-tag text-primary"></i>
                                </span>
                                <select class="form-select border-start-0" id="category" name="category">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat); ?>
                                        </option>
                                    <?php endforeach; ?>
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
    <div class="container-fluid py-3">
        <div class="row">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div>
                    <a href="dashboard.php" class="btn btn-link text-decoration-none">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
                <div class="text-muted small">
                    <?php echo count($sites); ?> attractions found
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">

    <!-- Tourist Site Listings -->
    <div class="row g-4">
        <?php if (empty($sites)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>No tourist sites found matching your criteria. Try adjusting your filters.
                </div>
            </div>
        <?php else: ?>
            <?php 
            // Calculate total pages and ensure current page is valid
            $totalSites = count($sites);
            $totalPages = ceil($totalSites / $itemsPerPage);
            if ($currentPage > $totalPages) $currentPage = $totalPages;
            
            // Get sites for current page
            $startIndex = ($currentPage - 1) * $itemsPerPage;
            $pageSites = array_slice($sites, $startIndex, $itemsPerPage);
            
            foreach ($pageSites as $site): 
            ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm site-card border-0 overflow-hidden">
                        <div class="position-relative">
                            <img src="<?php echo $site['image'] ?? APP_URL . '/assets/images/sites/default.jpg'; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($site['name']); ?>" style="height: 200px; object-fit: cover;">
                            <?php if (stripos($site['category'], 'UNESCO') !== false): ?>
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-danger py-2 px-3">UNESCO Site</span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($site['name']); ?></h5>
                                <span class="badge bg-info text-dark"><?php echo htmlspecialchars($site['category']); ?></span>
                            </div>
                            <p class="card-text small text-muted mb-1">
                                <i class="fas fa-map-marker-alt me-1 text-primary"></i><?php echo htmlspecialchars($site['address']); ?>
                            </p>
                            <div class="rating mb-2">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star-half-alt text-warning"></i>
                                <span class="small text-muted ms-1">(<?php echo rand(50, 300); ?> reviews)</span>
                            </div>
                            <p class="card-text small text-muted flex-grow-1"><?php echo htmlspecialchars(substr($site['description'], 0, 100)); ?>...</p>
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <div class="activity-info">
                                    <span class="badge bg-light text-dark me-1"><i class="far fa-clock me-1"></i>2-3 hours</span>
                                </div>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#siteModal<?php echo $site['id']; ?>">
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tourist Site Details Modal -->
                <div class="modal fade" id="siteModal<?php echo $site['id']; ?>" tabindex="-1" aria-labelledby="siteModalLabel<?php echo $site['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="siteModalLabel<?php echo $site['id']; ?>"><?php echo htmlspecialchars($site['name']); ?></h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">
                                <!-- Site Gallery -->
                                <div class="site-gallery bg-light p-3">
                                    <div class="row g-2">
                                        <div class="col-md-8">
                                            <div class="main-image rounded overflow-hidden" style="height: 350px;">
                                                <img src="<?php echo $site['image'] ?? APP_URL . '/assets/images/sites/default.jpg'; ?>" class="w-100 h-100" style="object-fit: cover;" alt="<?php echo htmlspecialchars($site['name']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="row g-2">
                                                <div class="col-6 col-md-12">
                                                    <div class="thumbnail rounded overflow-hidden" style="height: 170px;">
                                                        <img src="<?php echo APP_URL . '/assets/images/sites/statue-of-liberty.jpg'; ?>" class="w-100 h-100" style="object-fit: cover;" alt="Thumbnail">
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-12">
                                                    <div class="thumbnail rounded overflow-hidden" style="height: 170px;">
                                                        <img src="<?php echo APP_URL . '/assets/images/sites/great-wall.jpg'; ?>" class="w-100 h-100" style="object-fit: cover;" alt="Thumbnail">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="container py-4">
                                    <div class="row">
                                        <!-- Site Details -->
                                        <div class="col-md-8">
                                            <div class="site-header d-flex justify-content-between align-items-start mb-4">
                                                <div>
                                                    <h3 class="mb-1"><?php echo htmlspecialchars($site['name']); ?></h3>
                                                    <div class="d-flex align-items-center mb-2">
                                                        <div class="me-2">
                                                            <i class="fas fa-star text-warning"></i>
                                                            <i class="fas fa-star text-warning"></i>
                                                            <i class="fas fa-star text-warning"></i>
                                                            <i class="fas fa-star text-warning"></i>
                                                            <i class="fas fa-star-half-alt text-warning"></i>
                                                        </div>
                                                        <span class="badge bg-primary rounded-pill px-2 py-1 me-2">4.5</span>
                                                        <span class="text-muted">(<?php echo rand(50, 500); ?> reviews)</span>
                                                    </div>
                                                    <p class="mb-2">
                                                        <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                                        <?php echo htmlspecialchars($site['address']); ?>
                                                        <a href="#" class="ms-2 small">Show on map</a>
                                                    </p>
                                                    <span class="badge bg-info text-dark"><?php echo htmlspecialchars($site['category']); ?></span>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <h5 class="mb-3">About this attraction</h5>
                                                <p><?php echo htmlspecialchars($site['description']); ?></p>
                                                <p>This iconic destination attracts visitors from around the world. Plan your visit in advance to make the most of your experience.</p>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <h5 class="mb-3">Visitor information</h5>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <div class="card border-0 bg-light">
                                                            <div class="card-body">
                                                                <h6 class="card-title"><i class="far fa-clock text-primary me-2"></i>Opening hours</h6>
                                                                <p class="card-text mb-0">Monday - Friday: 9:00 AM - 5:00 PM</p>
                                                                <p class="card-text mb-0">Saturday - Sunday: 10:00 AM - 6:00 PM</p>
                                                                <p class="card-text small text-muted">Last entry 1 hour before closing</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="card border-0 bg-light">
                                                            <div class="card-body">
                                                                <h6 class="card-title"><i class="fas fa-ticket-alt text-primary me-2"></i>Admission</h6>
                                                                <p class="card-text mb-0">Adults: $25</p>
                                                                <p class="card-text mb-0">Children (6-12): $15</p>
                                                                <p class="card-text small text-muted">Children under 6: Free</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <div class="card border-0 bg-light">
                                                            <div class="card-body">
                                                                <h6 class="card-title"><i class="fas fa-bus text-primary me-2"></i>Transportation</h6>
                                                                <p class="card-text mb-0">Public transit available</p>
                                                                <p class="card-text mb-0">Parking available on-site</p>
                                                                <p class="card-text small text-muted">10-minute walk from city center</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="card border-0 bg-light">
                                                            <div class="card-body">
                                                                <h6 class="card-title"><i class="fas fa-info-circle text-primary me-2"></i>Facilities</h6>
                                                                <p class="card-text mb-0"><i class="fas fa-wheelchair me-2"></i>Partially accessible</p>
                                                                <p class="card-text mb-0"><i class="fas fa-utensils me-2"></i>Restaurant on-site</p>
                                                                <p class="card-text mb-0"><i class="fas fa-restroom me-2"></i>Public restrooms</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <h5 class="mb-3">Visitor tips</h5>
                                                <div class="alert alert-info">
                                                    <i class="fas fa-lightbulb me-2"></i>
                                                    <strong>Pro tip:</strong> Visit early in the morning or late afternoon to avoid crowds. The recommended visit duration is 2-3 hours.
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Booking Box -->
                                        <div class="col-md-4">
                                            <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                                                <div class="card-header bg-primary text-white py-3">
                                                    <h5 class="mb-0">Add to your trip</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-4">
                                                        <h6 class="mb-3">Why visit <?php echo htmlspecialchars($site['name']); ?>?</h6>
                                                        <ul class="list-unstyled">
                                                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Top-rated attraction</li>
                                                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Cultural significance</li>
                                                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Stunning photo opportunities</li>
                                                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Unique experience</li>
                                                        </ul>
                                                    </div>
                                                    
                                                    <div class="mb-4">
                                                        <h6 class="mb-3">Available experiences</h6>
                                                        <div class="list-group">
                                                            <div class="list-group-item border-0 bg-light mb-2 rounded">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <div>
                                                                        <h6 class="mb-1">Guided Tour</h6>
                                                                        <p class="mb-0 small">2 hours, English guide</p>
                                                                    </div>
                                                                    <span class="badge bg-primary">$35</span>
                                                                </div>
                                                            </div>
                                                            <div class="list-group-item border-0 bg-light mb-2 rounded">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <div>
                                                                        <h6 class="mb-1">Skip-the-line Ticket</h6>
                                                                        <p class="mb-0 small">Priority access</p>
                                                                    </div>
                                                                    <span class="badge bg-primary">$45</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="booking-options">
                                                        <?php if (!empty($userTrips)): ?>
                                                        <div class="dropdown mb-2 w-100">
                                                            <button class="btn btn-outline-primary dropdown-toggle w-100" type="button" id="addToTripDropdown<?php echo $site['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                                Add to Existing Trip
                                                            </button>
                                                            <ul class="dropdown-menu w-100" aria-labelledby="addToTripDropdown<?php echo $site['id']; ?>">
                                                                <?php foreach ($userTrips as $trip): ?>
                                                                    <li>
                                                                        <a class="dropdown-item" href="edit_trip.php?id=<?php echo $trip['id']; ?>&add_site=<?php echo $site['id']; ?>">
                                                                            <?php echo htmlspecialchars($trip['destination']); ?> (<?php echo date('M d, Y', strtotime($trip['start_date'])); ?>)
                                                                        </a>
                                                                    </li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        </div>
                                                        <?php endif; ?>
                                                        <a href="create_trip.php?site_id=<?php echo $site['id']; ?>" class="btn btn-primary w-100">
                                                            <i class="fas fa-calendar-plus me-2"></i>Create New Trip
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="card-footer bg-white text-center">
                                                    <small class="text-muted">Recommended visit: 2-3 hours</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- Pagination Navigation -->
            <?php if ($totalPages > 1): ?>
            <div class="col-12 mt-4">
                <nav aria-label="Tourist sites pagination">
                    <ul class="pagination justify-content-center">
                        <!-- Previous page link -->
                        <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $currentPage - 1; ?><?php echo !empty($filter) ? '&filter=' . urlencode($filter) : ''; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <!-- Page number links -->
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($currentPage == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($filter) ? '&filter=' . urlencode($filter) : ''; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Next page link -->
                        <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $currentPage + 1; ?><?php echo !empty($filter) ? '&filter=' . urlencode($filter) : ''; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <!-- Page Information -->
    <?php if (!empty($sites)): ?>
    <div class="row mt-3">
        <div class="col-12 text-center text-muted">
            <small>Showing <?php echo min($startIndex + 1, $totalSites); ?> to <?php echo min($startIndex + $itemsPerPage, $totalSites); ?> of <?php echo $totalSites; ?> tourist sites</small>
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

.site-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 8px;
}

.site-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

.site-card .card-img-top {
    height: 200px;
    object-fit: cover;
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

/* Site gallery styling */
.site-gallery .thumbnail:hover {
    opacity: 0.8;
    cursor: pointer;
}

/* Activity info styling */
.activity-info .badge {
    font-size: 0.75rem;
    font-weight: normal;
}
</style>

<?php include '../includes/footer.php'; ?>
