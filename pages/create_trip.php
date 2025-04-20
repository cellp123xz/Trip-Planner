<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';

requireLogin();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $destination = trim($_POST['destination'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $activities = trim($_POST['activities'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

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
            $tripId = createTrip(
                $_SESSION['user_id'],
                $destination,
                $start_date,
                $end_date,
                $activities,
                $notes
            );

            $_SESSION['alert'] = [
                'title' => 'Success!',
                'message' => 'Your trip has been created successfully.',
                'type' => 'success'
            ];
            
            header('Location: dashboard.php');
            exit;
        } catch (Exception $e) {
            $errors[] = "Error creating trip: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="card-title mb-4">Plan a New Trip</h2>

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
                                   value="<?php echo htmlspecialchars($_POST['destination'] ?? ''); ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date"
                                       value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date"
                                       value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="activities" class="form-label">Planned Activities</label>
                            <textarea class="form-control" id="activities" name="activities" rows="3"
                                    placeholder="List your planned activities..."><?php echo htmlspecialchars($_POST['activities'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                    placeholder="Any additional notes..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Create Trip</button>
                            <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
