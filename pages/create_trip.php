<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';

requireLogin();

$errors = [];
$success = false;

$hotels = getAllHotels();
$tourist_sites = getAllTouristSites();

$preSelectedHotelId = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : null;
$preSelectedHotel = null;
$preSelectedDestination = '';

if ($preSelectedHotelId) {
    foreach ($hotels as $hotel) {
        if ($hotel['id'] == $preSelectedHotelId) {
            $preSelectedHotel = $hotel;
            $addressParts = explode(',', $hotel['address']);
            $preSelectedDestination = trim($addressParts[0] ?? '');
            break;
        }
    }
}

$recommendedHotels = [];
$preSelectedHotels = [];
if (!empty($_GET['destination'])) {
    $preSelectedDestination = trim($_GET['destination']);
    $recommendedHotels = getRecommendedHotels($preSelectedDestination, 3);
    
    if (empty($recommendedHotels)) {
        $preSelectedHotels = generateDestinationHotels($preSelectedDestination);
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
                            <div class="mb-3">
                                <label for="destination" class="form-label">Destination</label>
                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <select class="form-select" id="country" name="country">
                                            <option value="">-- Select a Country --</option>
                                            <?php foreach(getCountries() as $country): ?>
                                                <option value="<?php echo htmlspecialchars($country); ?>">
                                                    <?php echo htmlspecialchars($country); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i class="fas fa-map-marker-alt text-primary"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0" id="destination" name="destination" 
                                                   placeholder="Where are you going?" value="<?php echo htmlspecialchars($_POST['destination'] ?? $preSelectedDestination ?? ''); ?>" 
                                                   list="destination-list" autocomplete="off" required>
                                            <datalist id="destination-list">
                                                <?php foreach(getPopularDestinations() as $dest): ?>
                                                    <option value="<?php echo htmlspecialchars($dest); ?>">
                                                <?php endforeach; ?>
                                            </datalist>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-text">First select a country, then choose a destination to see recommended hotels</div>
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
                        <?php elseif ((!empty($recommendedHotels) || !empty($preSelectedHotels)) && !empty($destination)): ?>
                        <div class="mb-4">
                            <div class="alert alert-info">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>Recommended Hotels for <?php echo htmlspecialchars($destination); ?></strong>
                                <p class="mb-0 small">We've found the best hotels for your destination based on ratings and location.</p>
                            </div>
                            
                            <div class="row g-3 mb-3">
                                <?php 
                                // Use either recommended hotels or generated hotels
                                $displayHotels = !empty($recommendedHotels) ? $recommendedHotels : $preSelectedHotels;
                                foreach ($displayHotels as $index => $hotel): 
                                ?>
                                <div class="col-md-4">
                                    <div class="card h-100 <?php echo $index === 0 ? 'border-primary' : ''; ?>">
                                        <?php if ($index === 0): ?>
                                        <div class="card-header bg-primary text-white py-2">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-award me-2"></i>
                                                <span>Best Match</span>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="hotel_id" 
                                                       id="hotel_<?php echo $hotel['id']; ?>" value="<?php echo $hotel['id']; ?>"
                                                       <?php echo $index === 0 ? 'checked' : ''; ?>>
                                                <label class="form-check-label w-100" for="hotel_<?php echo $hotel['id']; ?>">
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($hotel['name']); ?></h6>
                                                    <p class="small text-muted mb-1">
                                                        <i class="fas fa-map-marker-alt me-1 text-primary"></i>
                                                        <?php echo htmlspecialchars($hotel['address']); ?>
                                                    </p>
                                                    <div class="mb-2">
                                                        <span class="badge bg-success"><?php echo $hotel['rating']; ?> ★</span>
                                                        <span class="badge bg-secondary"><?php echo $hotel['price_range']; ?></span>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="mb-3">
                                <button type="button" class="btn btn-link" id="showAllHotelsBtn">Show all available hotels</button>
                            </div>
                            
                            <div id="allHotelsSelect" style="display: none;">
                                <label for="hotel_id_all" class="form-label">All Available Hotels</label>
                                <select class="form-select" id="hotel_id_all" name="hotel_id_all">
                                    <option value="">-- Select a Hotel --</option>
                                    <?php foreach ($hotels as $hotel): ?>
                                        <option value="<?php echo $hotel['id']; ?>">
                                            <?php echo htmlspecialchars($hotel['name']); ?> - <?php echo htmlspecialchars($hotel['address']); ?> (<?php echo $hotel['price_range']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="mb-4">
                            <label for="hotel_id" class="form-label">Select a Hotel</label>
                            <select class="form-select" id="hotel_id" name="hotel_id" required>
                                <option value="">-- Select a Hotel --</option>
                                <?php foreach ($hotels as $hotel): ?>
                                    <option value="<?php echo $hotel['id']; ?>" 
                                            <?php echo ((isset($_POST['hotel_id']) && $_POST['hotel_id'] == $hotel['id'])) ? 'selected' : ''; ?>
                                            data-address="<?php echo htmlspecialchars($hotel['address']); ?>"
                                            data-rating="<?php echo $hotel['rating']; ?>">
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
    const hotelSelectAll = document.getElementById('hotel_id_all');
    const showAllHotelsBtn = document.getElementById('showAllHotelsBtn');
    const allHotelsSelect = document.getElementById('allHotelsSelect');
    const touristSiteCheckboxes = document.querySelectorAll('input[name="tourist_sites[]"]');
    const hotelRadioButtons = document.querySelectorAll('input[type="radio"][name="hotel_id"]');
    
    // Summary elements
    const summaryDestination = document.getElementById('summary-destination');
    const summaryDates = document.getElementById('summary-dates');
    const summaryDuration = document.getElementById('summary-duration');
    const summaryHotel = document.getElementById('summary-hotel');
    const summaryAttractions = document.getElementById('summary-attractions');
    
    // Show all hotels functionality
    if (showAllHotelsBtn && allHotelsSelect) {
        showAllHotelsBtn.addEventListener('click', function() {
            allHotelsSelect.style.display = 'block';
            this.style.display = 'none';
        });
        
        if (hotelSelectAll) {
            hotelSelectAll.addEventListener('change', function() {
                const selectedValue = this.value;
                if (selectedValue) {
                    // Uncheck all radio buttons
                    hotelRadioButtons.forEach(radio => {
                        radio.checked = false;
                    });
                    
                    // Create a hidden input with the selected hotel_id
                    let hiddenInput = document.getElementById('selected_hotel_id');
                    if (!hiddenInput) {
                        hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'hotel_id';
                        hiddenInput.id = 'selected_hotel_id';
                        this.parentNode.appendChild(hiddenInput);
                    }
                    hiddenInput.value = selectedValue;
                    
                    // Update summary
                    const selectedOption = this.options[this.selectedIndex];
                    if (summaryHotel) {
                        summaryHotel.textContent = selectedOption.text.split(' - ')[0] || 'Not selected';
                    }
                }
            });
        }
    }
    
    // Update destination and fetch hotel recommendations
    if (destinationInput) {
        // Initial update of summary
        if (summaryDestination && destinationInput.value) {
            summaryDestination.textContent = destinationInput.value;
        }
        
        destinationInput.addEventListener('input', function() {
            if (summaryDestination) {
                summaryDestination.textContent = this.value || 'Not selected';
            }
        });
        
        // Handle country selection to update destination options
        const countrySelect = document.getElementById('country');
        if (countrySelect) {
            countrySelect.addEventListener('change', function() {
                const country = this.value;
                if (country) {
                    // Clear current destination
                    if (destinationInput) {
                        destinationInput.value = '';
                    }
                    
                    // Update destination datalist with options from this country
                    updateDestinationOptions(country);
                }
            });
        }
        
        // Function to update destination options based on country
        function updateDestinationOptions(country) {
            // Make an AJAX request to get destinations for this country
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `get_destinations.php?country=${encodeURIComponent(country)}`, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const destinations = JSON.parse(xhr.responseText);
                        const datalist = document.getElementById('destination-list');
                        if (datalist) {
                            // Clear existing options
                            datalist.innerHTML = '';
                            
                            // Add new options
                            destinations.forEach(dest => {
                                const option = document.createElement('option');
                                option.value = dest;
                                datalist.appendChild(option);
                            });
                            
                            // Focus the destination input
                            if (destinationInput) {
                                destinationInput.focus();
                                destinationInput.placeholder = `Select a destination in ${country}...`;
                            }
                        }
                    } catch (e) {
                        console.error('Error parsing destinations:', e);
                    }
                }
            };
            xhr.send();
        }
        
        // Handle destination selection to filter hotels
        if (destinationInput) {
            destinationInput.addEventListener('change', function() {
                const destination = this.value.trim();
                if (destination.length > 2) {
                    filterHotelsByDestination(destination.toLowerCase());
                }
            });
        }
        
        // Filter hotels based on destination
        function filterHotelsByDestination(destination) {
            if (!hotelSelect) return;
            
            // Show a loading message
            const label = document.querySelector('label[for="hotel_id"]');
            if (label) {
                label.innerHTML = `Select a Hotel <span class="badge bg-info ms-2">Finding hotels in ${destination}...</span>`;
            }
            
            const options = hotelSelect.querySelectorAll('option');
            let matchCount = 0;
            let bestMatch = null;
            let bestRating = 0;
            
            // First pass: hide all options except the default one
            options.forEach((option, index) => {
                if (index === 0) return; // Skip the default "Select a Hotel" option
                
                const address = option.getAttribute('data-address')?.toLowerCase() || '';
                const rating = parseFloat(option.getAttribute('data-rating') || 0);
                
                // Check if this hotel is in the selected destination
                const isMatch = address.includes(destination);
                
                if (isMatch) {
                    option.style.display = '';
                    matchCount++;
                    
                    // Track the highest rated hotel as the best match
                    if (rating > bestRating) {
                        bestRating = rating;
                        bestMatch = option;
                    }
                } else {
                    option.style.display = 'none';
                }
            });
            
            // Use AJAX to get hotels for this destination without page refresh
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `get_hotels.php?destination=${encodeURIComponent(destination)}`, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const hotels = JSON.parse(xhr.responseText);
                        updateHotelOptions(hotels, destination);
                    } catch (e) {
                        console.error('Error parsing hotels:', e);
                        // If AJAX fails, fallback to redirect
                        window.location.href = 'create_trip.php?destination=' + encodeURIComponent(destination);
                    }
                } else {
                    // If AJAX fails, fallback to redirect
                    window.location.href = 'create_trip.php?destination=' + encodeURIComponent(destination);
                }
            };
            xhr.onerror = function() {
                // If AJAX fails, fallback to redirect
                window.location.href = 'create_trip.php?destination=' + encodeURIComponent(destination);
            };
            xhr.send();
        }
        
        // Function to update hotel options with AJAX results
        function updateHotelOptions(hotels, destination) {
            if (!hotelSelect) return;
            
            // Clear existing options except the first one
            while (hotelSelect.options.length > 1) {
                hotelSelect.remove(1);
            }
            
            // Add new options
            let matchCount = 0;
            let bestMatch = null;
            let bestRating = 0;
            
            hotels.forEach(hotel => {
                const option = document.createElement('option');
                option.value = hotel.id;
                option.text = `${hotel.name} - ${hotel.rating}★ - ${hotel.price_range}`;
                option.dataset.rating = hotel.rating;
                option.dataset.address = hotel.address;
                option.dataset.amenities = hotel.amenities;
                option.dataset.image = hotel.image;
                hotelSelect.appendChild(option);
                
                matchCount++;
                
                // Keep track of the best rated hotel
                const rating = parseFloat(hotel.rating);
                if (rating > bestRating) {
                    bestRating = rating;
                    bestMatch = option;
                }
            });
            
            // If we found matches, select the best one
            if (bestMatch) {
                bestMatch.selected = true;
                if (summaryHotel) {
                    summaryHotel.textContent = bestMatch.text.split(' - ')[0];
                }
            }
            
            // Update the label
            const label = document.querySelector('label[for="hotel_id"]');
            if (label) {
                if (matchCount > 0) {
                    label.innerHTML = `Select a Hotel <span class="badge bg-success ms-2">${matchCount} hotels found in ${destination}</span>`;
                } else {
                    label.innerHTML = `Select a Hotel <span class="badge bg-warning ms-2">No hotels found</span>`;
                }
            }
            
            // Show the hotel selection section
            const hotelSection = document.querySelector('#hotel-selection');
            if (hotelSection) {
                hotelSection.style.display = 'block';
            }
        }
        
        // Initial filter if destination is already set
        if (destinationInput && destinationInput.value.trim().length > 0) {
            // Don't auto-filter if we already have a destination from URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            if (!urlParams.has('destination')) {
                filterHotelsByDestination(destinationInput.value.trim());
            }
        }
    }
    
    // Update dates and duration
    function updateDates() {
        const start = startDateInput.value;
        const end = endDateInput.value;
        
        if (start && end && summaryDates && summaryDuration) {
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
    
    // Update hotel from dropdown
    if (hotelSelect && summaryHotel) {
        hotelSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            summaryHotel.textContent = selectedOption.text.split(' - ')[0] || 'Not selected';
        });
    }
    
    // Update hotel from radio buttons
    hotelRadioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked && summaryHotel) {
                const label = document.querySelector(`label[for="${this.id}"]`);
                if (label) {
                    const hotelName = label.querySelector('h6').textContent;
                    summaryHotel.textContent = hotelName;
                }
            }
        });
    });
    
    // Update attractions count
    function updateAttractions() {
        if (!summaryAttractions) return;
        
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
