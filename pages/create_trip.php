<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';

requireLogin();

$errors = [];
$success = false;

// Get all hotels and tourist sites for selection
$hotels = getAllHotels();
$tourist_sites = getAllTouristSites();

// Check if hotel_id is provided in URL (coming from browse_hotels.php)
$preSelectedHotelId = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : null;
$preSelectedHotel = null;
$preSelectedDestination = '';

// If hotel_id is provided, find the hotel and pre-fill destination
if ($preSelectedHotelId) {
    foreach ($hotels as $hotel) {
        if ($hotel['id'] == $preSelectedHotelId) {
            $preSelectedHotel = $hotel;
            // Extract city/location from address for destination
            $addressParts = explode(',', $hotel['address']);
            $preSelectedDestination = trim($addressParts[0] ?? '');
            break;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destination = trim($_POST['destination'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $activities = trim($_POST['activities'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $hotel_id = !empty($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : null;
    $tourist_sites_ids = $_POST['tourist_sites'] ?? [];
    
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
                $notes,
                $hotel_id,
                $tourist_sites_ids
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

<div class="container-fluid py-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h3 class="card-title mb-0">Plan Your Trip</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Complete your trip details below. Your hotel and tourist site selections will be automatically booked when you create your trip.
                    </div>

                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="mb-3">Trip Details</h4>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="destination" class="form-label">Destination</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class="fas fa-map-marker-alt text-primary"></i>
                                    </span>
                                    <input type="text" class="form-control" id="destination" name="destination" 
                                           value="<?php echo htmlspecialchars($_POST['destination'] ?? $preSelectedDestination ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Check-in Date</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class="fas fa-calendar-alt text-primary"></i>
                                    </span>
                                    <input type="date" class="form-control" id="start_date" name="start_date"
                                           value="<?php echo htmlspecialchars($_POST['start_date'] ?? date('Y-m-d')); ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">Check-out Date</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class="fas fa-calendar-alt text-primary"></i>
                                    </span>
                                    <input type="date" class="form-control" id="end_date" name="end_date"
                                           value="<?php echo htmlspecialchars($_POST['end_date'] ?? date('Y-m-d', strtotime('+7 days'))); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h4 class="mb-3">Hotel Booking</h4>
                            </div>
                        </div>
                        
                        <?php if ($preSelectedHotel): ?>
                        <div class="card mb-4 border-primary">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <img src="<?php echo $preSelectedHotel['image'] ?? APP_URL . '/assets/images/hotels/default.jpg'; ?>" 
                                             class="img-fluid rounded" alt="<?php echo htmlspecialchars($preSelectedHotel['name']); ?>">
                                    </div>
                                    <div class="col-md-8">
                                        <h5 class="card-title"><?php echo htmlspecialchars($preSelectedHotel['name']); ?></h5>
                                        <p class="card-text">
                                            <i class="fas fa-map-marker-alt me-2 text-primary"></i><?php echo htmlspecialchars($preSelectedHotel['address']); ?>
                                        </p>
                                        <div class="mb-2">
                                            <span class="badge bg-success"><?php echo $preSelectedHotel['rating']; ?> ★</span>
                                            <span class="badge bg-secondary"><?php echo $preSelectedHotel['price_range']; ?></span>
                                        </div>
                                        <p class="card-text small">
                                            <strong>Amenities:</strong> <?php echo htmlspecialchars($preSelectedHotel['amenities'] ?? 'Free WiFi, Breakfast, Pool, Parking'); ?>
                                        </p>
                                        <input type="hidden" name="hotel_id" value="<?php echo $preSelectedHotel['id']; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="mb-4">
                            <label for="hotel_id" class="form-label">Select a Hotel</label>
                            <select class="form-select" id="hotel_id" name="hotel_id" required>
                                <option value="">-- Select a Hotel --</option>
                                <?php foreach ($hotels as $hotel): ?>
                                    <option value="<?php echo $hotel['id']; ?>" <?php echo ((isset($_POST['hotel_id']) && $_POST['hotel_id'] == $hotel['id'])) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($hotel['name']); ?> - <?php echo htmlspecialchars($hotel['address']); ?> (<?php echo $hotel['price_range']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Your hotel will be automatically booked when you create your trip</div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h4 class="mb-3">Attractions & Activities</h4>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Select Tourist Sites to Visit</label>
                            <div class="form-text mb-2">Selected sites will be booked and added to your itinerary</div>
                            <div class="row g-3">
                                <?php foreach ($tourist_sites as $site): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body p-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="tourist_sites[]" 
                                                       value="<?php echo $site['id']; ?>" id="site_<?php echo $site['id']; ?>"
                                                       <?php echo (isset($_POST['tourist_sites']) && in_array($site['id'], $_POST['tourist_sites'])) ? 'checked' : ''; ?>>
                                                <label class="form-check-label w-100" for="site_<?php echo $site['id']; ?>">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <strong><?php echo htmlspecialchars($site['name']); ?></strong>
                                                        <span class="badge bg-info text-dark"><?php echo htmlspecialchars($site['category']); ?></span>
                                                    </div>
                                                    <div class="small text-muted mt-1">
                                                        <i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($site['address']); ?>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h4 class="mb-3">Additional Information</h4>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="activities" class="form-label">Planned Activities</label>
                            <textarea class="form-control" id="activities" name="activities" rows="3"
                                    placeholder="List your planned activities..."><?php echo htmlspecialchars($_POST['activities'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                    placeholder="Any additional notes..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-calendar-check me-2"></i>Book Trip
                            </button>
                            <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Booking Summary -->
        <div class="col-md-4">
            <div class="card shadow sticky-top" style="top: 20px;">
                <div class="card-header bg-primary text-white py-3">
                    <h3 class="card-title mb-0">Booking Summary</h3>
                </div>
                <div class="card-body">
                    <div class="summary-item d-flex justify-content-between mb-3">
                        <span>Destination:</span>
                        <span id="summary-destination" class="fw-bold"><?php echo htmlspecialchars($_POST['destination'] ?? $preSelectedDestination ?? 'Not selected'); ?></span>
                    </div>
                    
                    <div class="summary-item d-flex justify-content-between mb-3">
                        <span>Dates:</span>
                        <span id="summary-dates" class="fw-bold">
                            <?php 
                            $start = $_POST['start_date'] ?? date('Y-m-d');
                            $end = $_POST['end_date'] ?? date('Y-m-d', strtotime('+7 days'));
                            echo date('M d', strtotime($start)) . ' - ' . date('M d, Y', strtotime($end));
                            ?>
                        </span>
                    </div>
                    
                    <div class="summary-item d-flex justify-content-between mb-3">
                        <span>Duration:</span>
                        <span id="summary-duration" class="fw-bold">
                            <?php 
                            $start_date = new DateTime($_POST['start_date'] ?? date('Y-m-d'));
                            $end_date = new DateTime($_POST['end_date'] ?? date('Y-m-d', strtotime('+7 days')));
                            $interval = $start_date->diff($end_date);
                            echo $interval->days + 1 . ' days';
                            ?>
                        </span>
                    </div>
                    
                    <hr>
                    
                    <div class="summary-item d-flex justify-content-between mb-3">
                        <span>Hotel:</span>
                        <span id="summary-hotel" class="fw-bold">
                            <?php 
                            if ($preSelectedHotel) {
                                echo htmlspecialchars($preSelectedHotel['name']);
                            } else {
                                echo 'Not selected';
                            }
                            ?>
                        </span>
                    </div>
                    
                    <div class="summary-item d-flex justify-content-between mb-3">
                        <span>Attractions:</span>
                        <span id="summary-attractions" class="fw-bold">
                            <?php 
                            if (isset($_POST['tourist_sites']) && is_array($_POST['tourist_sites'])) {
                                echo count($_POST['tourist_sites']) . ' selected';
                            } else {
                                echo '0 selected';
                            }
                            ?>
                        </span>
                    </div>
                    
                    <hr>
                    
                    <div class="price-summary mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Hotel (<?php echo $interval->days + 1; ?> nights):</span>
                            <span>
                                <?php 
                                if ($preSelectedHotel) {
                                    echo str_repeat(substr($preSelectedHotel['price_range'], 0, 1), $interval->days + 1);
                                } else {
                                    echo '₱₱';
                                }
                                ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Attractions:</span>
                            <span>₱</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Taxes and fees:</span>
                            <span>₱</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total:</span>
                            <span class="text-primary">
                                <?php 
                                if ($preSelectedHotel) {
                                    echo str_repeat(substr($preSelectedHotel['price_range'], 0, 1), $interval->days + 3);
                                } else {
                                    echo '₱₱₱';
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Free cancellation</strong> available up to 24 hours before check-in
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Update booking summary when form fields change
document.addEventListener('DOMContentLoaded', function() {
    const destinationInput = document.getElementById('destination');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const hotelSelect = document.getElementById('hotel_id');
    const touristSiteCheckboxes = document.querySelectorAll('input[name="tourist_sites[]"]');
    
    // Summary elements
    const summaryDestination = document.getElementById('summary-destination');
    const summaryDates = document.getElementById('summary-dates');
    const summaryDuration = document.getElementById('summary-duration');
    const summaryHotel = document.getElementById('summary-hotel');
    const summaryAttractions = document.getElementById('summary-attractions');
    
    // Update destination
    if (destinationInput) {
        destinationInput.addEventListener('input', function() {
            summaryDestination.textContent = this.value || 'Not selected';
        });
    }
    
    // Update dates and duration
    function updateDates() {
        const start = startDateInput.value;
        const end = endDateInput.value;
        
        if (start && end) {
            const startDate = new Date(start);
            const endDate = new Date(end);
            
            // Format dates
            const options = { month: 'short', day: 'numeric' };
            const endOptions = { month: 'short', day: 'numeric', year: 'numeric' };
            
            summaryDates.textContent = startDate.toLocaleDateString('en-US', options) + ' - ' + 
                                      endDate.toLocaleDateString('en-US', endOptions);
            
            // Calculate duration
            const diffTime = Math.abs(endDate - startDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            summaryDuration.textContent = diffDays + ' days';
        }
    }
    
    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', updateDates);
        endDateInput.addEventListener('change', updateDates);
    }
    
    // Update hotel
    if (hotelSelect) {
        hotelSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            summaryHotel.textContent = selectedOption.text.split(' - ')[0] || 'Not selected';
        });
    }
    
    // Update attractions count
    function updateAttractions() {
        let count = 0;
        touristSiteCheckboxes.forEach(checkbox => {
            if (checkbox.checked) count++;
        });
        
        summaryAttractions.textContent = count + ' selected';
    }
    
    touristSiteCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateAttractions);
    });
});
</script>

<?php include '../includes/footer.php'; ?>
