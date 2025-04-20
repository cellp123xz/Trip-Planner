<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/alerts.php';

// Require login to access dashboard
requireLogin();

// Get user data
$user = getUserById($_SESSION['user_id']);
if (!$user) {
    // If user not found, redirect to login
    logoutUser();
    header("Location: login.php");
    exit;
}

// Convert 'alert' format to 'sweet_alert' format for compatibility
if (isset($_SESSION['alert'])) {
    $_SESSION['sweet_alert'] = [
        'title' => $_SESSION['alert']['title'] ?? '',
        'message' => $_SESSION['alert']['message'] ?? '',
        'type' => $_SESSION['alert']['type'] ?? 'info',
        'options' => $_SESSION['alert']['options'] ?? []
    ];
    unset($_SESSION['alert']);
}

include '../includes/header.php';
?>

<!-- Custom Dashboard CSS -->
<style>
    .dashboard-hero {
        background: linear-gradient(135deg, #0d6efd 0%, #0099f7 100%);
        padding: 3rem 0;
        border-radius: 0 0 2rem 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }
    
    .dashboard-hero::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100%;
        height: 100%;
        background-image: url('<?php echo APP_URL; ?>/assets/images/pattern.svg');
        background-size: cover;
        opacity: 0.1;
    }
    
    .welcome-text {
        font-weight: 800;
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
    }
    
    .dashboard-card {
        border-radius: 1rem;
        border: none;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }
    
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .dashboard-card .card-header {
        background-color: #ffffff;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 1.25rem 1.5rem;
    }
    
    .dashboard-card .card-title {
        font-weight: 700;
        margin-bottom: 0;
        color: #333;
        display: flex;
        align-items: center;
    }
    
    .dashboard-card .card-title i {
        margin-right: 10px;
        background: #f8f9fa;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: #0d6efd;
    }
    
    .action-btn {
        border-radius: 50px;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .action-btn i {
        margin-right: 8px;
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
    }
    
    .stats-box {
        background: #f8f9fa;
        border-radius: 1rem;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .stats-box:hover {
        background: #ffffff;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    
    .stats-icon {
        width: 50px;
        height: 50px;
        background: rgba(13, 110, 253, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        color: #0d6efd;
        font-size: 1.5rem;
    }
    
    .stats-number {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: #333;
    }
    
    .stats-label {
        color: #6c757d;
        font-weight: 500;
    }
    
    .trip-table th {
        font-weight: 600;
        color: #495057;
    }
    
    .empty-state {
        padding: 3rem 1rem;
        text-align: center;
    }
    
    .empty-state img {
        max-width: 150px;
        margin-bottom: 1.5rem;
    }
    
    .empty-state h4 {
        font-weight: 600;
        margin-bottom: 1rem;
        color: #495057;
    }
    
    .empty-state p {
        color: #6c757d;
        margin-bottom: 1.5rem;
    }
</style>

<!-- Dashboard Hero Section -->
<section class="dashboard-hero text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="welcome-text">Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>
                <p class="lead mb-4">Ready to plan your next adventure? Your journey starts here.</p>
                <!-- Removed non-functional button -->
            </div>
            <div class="col-lg-5 d-none d-lg-block text-center">
                <img src="<?php echo APP_URL; ?>/assets/images/logo/logo.svg" alt="Trip Planner Logo" class="img-fluid" style="max-width: 200px;">
            </div>
        </div>
    </div>
</section>

<!-- Dashboard Content -->
<div class="container py-4">
    <!-- Stats Overview -->
    <div class="row mb-4">
        <div class="col-md-4 mb-4 mb-md-0">
            <div class="stats-box">
                <div class="stats-icon">
                    <i class="fas fa-suitcase"></i>
                </div>
                <div class="stats-number"><?php echo count(getTripsByUserId($_SESSION['user_id'])); ?></div>
                <div class="stats-label">Total Trips</div>
            </div>
        </div>
        <div class="col-md-4 mb-4 mb-md-0">
            <div class="stats-box">
                <div class="stats-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="stats-number">20+</div>
                <div class="stats-label">Destinations</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-box">
                <div class="stats-icon">
                    <i class="fas fa-hotel"></i>
                </div>
                <div class="stats-number">50+</div>
                <div class="stats-label">Hotels</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Quick Actions -->
        <div class="col-md-4">
            <div class="card dashboard-card h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h3>
                </div>
                <div class="card-body p-4">
                    <div class="d-grid gap-3">
                        <a href="create_trip.php" class="btn btn-primary action-btn">
                            <i class="fas fa-plus-circle"></i>Create New Trip
                        </a>
                        <a href="browse_hotels.php" class="btn btn-outline-primary action-btn">
                            <i class="fas fa-search"></i>Browse Hotels
                        </a>
                        <a href="explore_sites.php" class="btn btn-outline-primary action-btn">
                            <i class="fas fa-map-marked-alt"></i>Explore Sites
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Trips -->
        <div class="col-md-8">
            <div class="card dashboard-card h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-suitcase"></i> Recent Trips</h3>
                </div>
                <div class="card-body p-0">
                    <?php
                    // Fetch recent trips using our session-based function
                    $trips = getTripsByUserId($_SESSION['user_id']);

                    if (empty($trips)): ?>
                        <div class="empty-state">
                            <img src="<?php echo APP_URL; ?>/assets/images/empty-trips.svg" alt="No trips" class="img-fluid">
                            <h4>No trips planned yet</h4>
                            <p>Start planning your first adventure today!</p>
                            <a href="create_trip.php" class="btn btn-primary action-btn">
                                <i class="fas fa-plus-circle"></i>Plan Your First Trip
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table trip-table">
                                <thead>
                                    <tr>
                                        <th>Destination</th>
                                        <th>Dates</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($trips as $trip): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-light rounded-circle p-2 me-2">
                                                        <i class="fas fa-map-marker-alt text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($trip['destination']); ?></strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="far fa-calendar-alt text-muted me-2"></i>
                                                    <?php 
                                                    echo date('M d, Y', strtotime($trip['start_date'])) . ' - ' . 
                                                        date('M d, Y', strtotime($trip['end_date']));
                                                    ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php 
                                                $today = new DateTime();
                                                $start = new DateTime($trip['start_date']);
                                                $end = new DateTime($trip['end_date']);
                                                
                                                if ($today < $start) {
                                                    echo '<span class="badge bg-primary rounded-pill px-3 py-2">Upcoming</span>';
                                                } elseif ($today >= $start && $today <= $end) {
                                                    echo '<span class="badge bg-success rounded-pill px-3 py-2">Active</span>';
                                                } else {
                                                    echo '<span class="badge bg-secondary rounded-pill px-3 py-2">Completed</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="view_trip.php?id=<?php echo $trip['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill me-1">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit_trip.php?id=<?php echo $trip['id']; ?>" class="btn btn-sm btn-outline-secondary rounded-pill">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
