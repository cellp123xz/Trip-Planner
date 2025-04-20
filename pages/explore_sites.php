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

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="mb-0">Explore Tourist Sites</h1>
            <p class="text-muted">Discover amazing places to visit on your trip</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="dashboard.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-6">
                    <label for="filter" class="form-label">Search Sites</label>
                    <input type="text" class="form-control" id="filter" name="filter" 
                           placeholder="Site name, description, or location" value="<?php echo htmlspecialchars($filter); ?>">
                </div>
                <div class="col-md-4">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
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
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm site-card">
                        <div class="card-img-top site-image" style="background-color: #198754; color: white; display: flex; align-items: center; justify-content: center; height: 200px;">
                            <h3><?php echo htmlspecialchars($site['name']); ?></h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($site['name']); ?></h5>
                                <span class="badge bg-info text-dark"><?php echo htmlspecialchars($site['category']); ?></span>
                            </div>
                            <p class="card-text small text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($site['address']); ?>
                            </p>
                            <p class="card-text">
                                <?php echo htmlspecialchars($site['description']); ?>
                            </p>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <button class="btn btn-sm btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#siteModal<?php echo $site['id']; ?>">
                                <i class="fas fa-info-circle me-1"></i>View Details
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tourist Site Details Modal -->
                <div class="modal fade" id="siteModal<?php echo $site['id']; ?>" tabindex="-1" aria-labelledby="siteModalLabel<?php echo $site['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="siteModalLabel<?php echo $site['id']; ?>"><?php echo htmlspecialchars($site['name']); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="rounded mb-3" style="background-color: #198754; color: white; display: flex; align-items: center; justify-content: center; height: 200px;">
                                            <h3><?php echo htmlspecialchars($site['name']); ?></h3>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Site Information</h5>
                                        <p><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($site['address']); ?></p>
                                        <p><i class="fas fa-tag me-2"></i>Category: <?php echo htmlspecialchars($site['category']); ?></p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <h5>About this Site</h5>
                                    <p><?php echo htmlspecialchars($site['description']); ?></p>
                                    <p>This is a sample extended description for <?php echo htmlspecialchars($site['name']); ?>. In a real application, this would contain detailed information about the site, its history, cultural significance, and visitor information.</p>
                                </div>
                                <div class="mt-3">
                                    <h5>Visitor Information</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item"><i class="fas fa-clock me-2"></i>Opening Hours: 9:00 AM - 5:00 PM</li>
                                                <li class="list-group-item"><i class="fas fa-ticket-alt me-2"></i>Admission: $10-25 (varies)</li>
                                                <li class="list-group-item"><i class="fas fa-calendar-alt me-2"></i>Best Time to Visit: Spring/Fall</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item"><i class="fas fa-bus me-2"></i>Transportation: Public transit available</li>
                                                <li class="list-group-item"><i class="fas fa-clock me-2"></i>Recommended Visit Duration: 2-3 hours</li>
                                                <li class="list-group-item"><i class="fas fa-wheelchair me-2"></i>Accessibility: Partially accessible</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary">Add to Trip</button>
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
.site-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.site-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}
.site-card .card-img-top {
    height: 200px;
    object-fit: cover;
}
</style>

<?php include '../includes/footer.php'; ?>
