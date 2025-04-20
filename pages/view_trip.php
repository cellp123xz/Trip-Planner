<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';

// Require login to view trip
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
$trip = null;
foreach ($_SESSION['db']['trips'] as $t) {
    if ($t['id'] == $tripId && $t['user_id'] == $_SESSION['user_id']) {
        $trip = $t;
        break;
    }
}

// If trip not found or doesn't belong to the user
if (!$trip) {
    $_SESSION['alert'] = [
        'title' => 'Error',
        'message' => 'Trip not found or you do not have permission to view it',
        'type' => 'error'
    ];
    header('Location: dashboard.php');
    exit;
}

// Get hotels and tourist sites for recommendations
$hotels = $_SESSION['db']['hotels'];
$touristSites = $_SESSION['db']['tourist_sites'];

// Filter recommendations based on destination
$recommendedHotels = [];
$recommendedSites = [];

foreach ($hotels as $hotel) {
    if (stripos($hotel['address'], $trip['destination']) !== false) {
        $recommendedHotels[] = $hotel;
    }
}

foreach ($touristSites as $site) {
    if (stripos($site['address'], $trip['destination']) !== false) {
        $recommendedSites[] = $site;
    }
}

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="mb-0"><?php echo htmlspecialchars($trip['destination']); ?></h1>
            <p class="text-muted">
                <?php 
                echo date('M d, Y', strtotime($trip['start_date'])) . ' - ' . 
                     date('M d, Y', strtotime($trip['end_date']));
                ?>
                <span class="badge bg-<?php 
                    echo match($trip['status']) {
                        'planned' => 'primary',
                        'ongoing' => 'success',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                        default => 'secondary'
                    };
                ?>">
                    <?php echo ucfirst($trip['status']); ?>
                </span>
            </p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="edit_trip.php?id=<?php echo $trip['id']; ?>" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>Edit Trip
            </a>
            <a href="dashboard.php" class="btn btn-outline-secondary ms-2">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Trip Details -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Trip Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Destination:</div>
                        <div class="col-md-8"><?php echo htmlspecialchars($trip['destination']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Start Date:</div>
                        <div class="col-md-8"><?php echo date('F j, Y', strtotime($trip['start_date'])); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">End Date:</div>
                        <div class="col-md-8"><?php echo date('F j, Y', strtotime($trip['end_date'])); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Duration:</div>
                        <div class="col-md-8">
                            <?php 
                            $start = new DateTime($trip['start_date']);
                            $end = new DateTime($trip['end_date']);
                            $interval = $start->diff($end);
                            echo $interval->days + 1 . ' days';
                            ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Status:</div>
                        <div class="col-md-8">
                            <span class="badge bg-<?php 
                                echo match($trip['status']) {
                                    'planned' => 'primary',
                                    'ongoing' => 'success',
                                    'completed' => 'info',
                                    'cancelled' => 'danger',
                                    default => 'secondary'
                                };
                            ?>">
                                <?php echo ucfirst($trip['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Activities</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($trip['activities'])): ?>
                        <p><?php echo nl2br(htmlspecialchars($trip['activities'])); ?></p>
                    <?php else: ?>
                        <p class="text-muted">No activities planned yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Notes</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($trip['notes'])): ?>
                        <p><?php echo nl2br(htmlspecialchars($trip['notes'])); ?></p>
                    <?php else: ?>
                        <p class="text-muted">No notes added yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recommendations -->
        <div class="col-lg-4">
            <!-- Hotels -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Recommended Hotels</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($recommendedHotels)): ?>
                        <div class="list-group">
                            <?php foreach ($recommendedHotels as $hotel): ?>
                                <div class="list-group-item">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($hotel['name']); ?></h6>
                                    <p class="mb-1 small"><?php echo htmlspecialchars($hotel['address']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-success"><?php echo $hotel['rating']; ?> ★</span>
                                            <span class="badge bg-secondary"><?php echo $hotel['price_range']; ?></span>
                                        </div>
                                        <a href="#" class="btn btn-sm btn-outline-primary">Details</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No hotel recommendations available for this destination.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tourist Sites -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Places to Visit</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($recommendedSites)): ?>
                        <div class="list-group">
                            <?php foreach ($recommendedSites as $site): ?>
                                <div class="list-group-item">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($site['name']); ?></h6>
                                    <p class="mb-1 small text-muted"><?php echo htmlspecialchars($site['description']); ?></p>
                                    <p class="mb-1 small"><?php echo htmlspecialchars($site['address']); ?></p>
                                    <span class="badge bg-info text-dark"><?php echo $site['category']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No site recommendations available for this destination.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
