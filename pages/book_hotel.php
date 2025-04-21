<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';

requireLogin();

$hotelId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$hotel = null;
$userTrips = getTripsByUserId($_SESSION['user_id']);
$error = '';
$success = false;

$allHotels = array_merge($_SESSION['db']['hotels'] ?? [], $additionalHotels ?? []);
foreach ($allHotels as $h) {
    if ($h['id'] == $hotelId) {
        $hotel = $h;
        break;
    }
}

if (!$hotel) {
    $_SESSION['alert'] = [
        'title' => 'Error',
        'message' => 'Hotel not found',
        'type' => 'error'
    ];
    header('Location: browse_hotels.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book_hotel') {
    $tripOption = isset($_POST['trip_option']) ? $_POST['trip_option'] : '';
    $tripId = isset($_POST['trip_id']) ? (int)$_POST['trip_id'] : 0;
    
    $destination = isset($_POST['destination']) ? trim($_POST['destination']) : '';
    $startDate = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
    $endDate = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';
    
    if ($tripOption === 'new') {
        if (empty($destination) || empty($startDate) || empty($endDate)) {
            $error = 'Please fill in all required fields for the new trip';
        } elseif ($startDate > $endDate) {
            $error = 'End date must be after start date';
        } else {
            $tripId = createTrip(
                $_SESSION['user_id'],
                $destination,
                $startDate,
                $endDate,
                '',
                '',
                $hotelId,
                []
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

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="mb-0">Book Hotel</h1>
            <p class="text-muted">Add <?php echo htmlspecialchars($hotel['name']); ?> to your trip</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="browse_hotels.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Hotels
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Hotel Details</h5>
                </div>
                <img src="<?php echo $hotel['image'] ?? APP_URL . '/assets/images/hotels/default.jpg'; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($hotel['name']); ?>">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($hotel['name']); ?></h5>
                    <p class="card-text">
                        <i class="fas fa-map-marker-alt me-2 text-primary"></i><?php echo htmlspecialchars($hotel['address']); ?>
                    </p>
                    <div class="mb-3">
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
                            <span class="fw-bold"><?php echo $hotel['rating']; ?></span>
                        </div>
                        <span class="badge bg-info"><?php echo $hotel['price_range']; ?></span>
                    </div>
                    <?php if (!empty($hotel['amenities'])): ?>
                        <p class="card-text">
                            <i class="fas fa-concierge-bell me-2 text-primary"></i><strong>Amenities:</strong><br>
                            <?php echo htmlspecialchars($hotel['amenities']); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Booking Options</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="action" value="book_hotel">
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Add to Trip</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="trip_option" id="newTrip" value="new" checked>
                                <label class="form-check-label" for="newTrip">
                                    Create a new trip
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="trip_option" id="existingTrip" value="existing" <?php echo empty($userTrips) ? 'disabled' : ''; ?>>
                                <label class="form-check-label" for="existingTrip">
                                    Add to existing trip <?php echo empty($userTrips) ? '(No trips available)' : ''; ?>
                                </label>
                            </div>
                        </div>
                        

                        <div id="newTripFields" class="mb-4">
                            <h6 class="mb-3">New Trip Details</h6>
                            <div class="mb-3">
                                <label for="destination" class="form-label">Destination</label>
                                <input type="text" class="form-control" id="destination" name="destination" value="<?php echo htmlspecialchars(explode(',', $hotel['address'])[0] ?? ''); ?>">
                                <div class="form-text">Enter the main destination for your trip</div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>">
                                </div>
                            </div>
                        </div>
                        

                        <div id="existingTripFields" class="mb-4" style="display: none;">
                            <h6 class="mb-3">Select Existing Trip</h6>
                            <div class="mb-3">
                                <label for="trip_id" class="form-label">Choose Trip</label>
                                <select class="form-select" id="trip_id" name="trip_id">
                                    <?php foreach ($userTrips as $trip): ?>
                                        <option value="<?php echo $trip['id']; ?>">
                                            <?php echo htmlspecialchars($trip['destination']); ?> (<?php echo date('M d, Y', strtotime($trip['start_date'])); ?> - <?php echo date('M d, Y', strtotime($trip['end_date'])); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Select which trip you want to add this hotel to</div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check-circle me-2"></i>Confirm Booking
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const newTripRadio = document.getElementById('newTrip');
    const existingTripRadio = document.getElementById('existingTrip');
    const newTripFields = document.getElementById('newTripFields');
    const existingTripFields = document.getElementById('existingTripFields');
    
    if (newTripRadio && existingTripRadio) {
        newTripRadio.addEventListener('change', function() {
            if (this.checked) {
                newTripFields.style.display = 'block';
                existingTripFields.style.display = 'none';
            }
        });
        
        existingTripRadio.addEventListener('change', function() {
            if (this.checked) {
                newTripFields.style.display = 'none';
                existingTripFields.style.display = 'block';
            }
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>
