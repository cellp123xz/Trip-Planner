<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';

// Require login to edit trip
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
foreach ($_SESSION['db']['trips'] as $key => $t) {
    if ($t['id'] == $tripId && $t['user_id'] == $_SESSION['user_id']) {
        $trip = $t;
        $tripKey = $key;
        break;
    }
}

// If trip not found or doesn't belong to the user
if (!$trip) {
    $_SESSION['alert'] = [
        'title' => 'Error',
        'message' => 'Trip not found or you do not have permission to edit it',
        'type' => 'error'
    ];
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $destination = trim($_POST['destination'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $activities = trim($_POST['activities'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $status = trim($_POST['status'] ?? 'planned');

    if (empty($destination)) {
        $errors[] = "Destination is required";
    }
    if (empty($start_date)) {
        $errors[] = "Start date is required";
    }
    if (empty($end_date)) {
        $errors[] = "End date is required";
    }
    if ($start_date > $end_date) {
        $errors[] = "End date must be after start date";
    }

    if (empty($errors)) {
        try {
            // Update trip in session storage
            $_SESSION['db']['trips'][$tripKey] = [
                'id' => $trip['id'],
                'user_id' => $_SESSION['user_id'],
                'destination' => $destination,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'activities' => $activities,
                'notes' => $notes,
                'status' => $status,
                'created_at' => $trip['created_at'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Set success message and redirect
            $_SESSION['alert'] = [
                'title' => 'Success!',
                'message' => 'Your trip has been updated successfully.',
                'type' => 'success'
            ];
            
            header('Location: view_trip.php?id=' . $tripId);
            exit;
        } catch (Exception $e) {
            $errors[] = "Error updating trip: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="mb-0">Edit Trip</h1>
            <p class="text-muted">Update your trip details</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="view_trip.php?id=<?php echo $trip['id']; ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Trip
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="destination" class="form-label">Destination</label>
                            <input type="text" class="form-control" id="destination" name="destination" 
                                   value="<?php echo htmlspecialchars($trip['destination']); ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date"
                                       value="<?php echo htmlspecialchars($trip['start_date']); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date"
                                       value="<?php echo htmlspecialchars($trip['end_date']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Trip Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="planned" <?php echo $trip['status'] === 'planned' ? 'selected' : ''; ?>>Planned</option>
                                <option value="ongoing" <?php echo $trip['status'] === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                <option value="completed" <?php echo $trip['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $trip['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="activities" class="form-label">Planned Activities</label>
                            <textarea class="form-control" id="activities" name="activities" rows="4"
                                    placeholder="List your planned activities..."><?php echo htmlspecialchars($trip['activities']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4"
                                    placeholder="Any additional notes..."><?php echo htmlspecialchars($trip['notes']); ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">Update Trip</button>
                            <a href="#" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="fas fa-trash-alt me-2"></i>Delete Trip
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this trip? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="delete_trip.php?id=<?php echo $trip['id']; ?>" class="btn btn-danger">Delete Trip</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
