<?php
/**
 * Mock Database for Trip Planner
 * This file provides in-memory data storage when MySQL is not available
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize session storage for mock database if not exists
if (!isset($_SESSION['mock_db'])) {
    $_SESSION['mock_db'] = [
        'users' => [],
        'trips' => []
    ];
}

/**
 * Register a new user
 */
function registerUser($name, $email, $password) {
    // Check if email already exists
    foreach ($_SESSION['mock_db']['users'] as $user) {
        if ($user['email'] === $email) {
            return [
                'success' => false,
                'message' => 'Email already registered'
            ];
        }
    }
    
    // Create new user
    $userId = count($_SESSION['mock_db']['users']) + 1;
    $verificationCode = md5(uniqid(rand(), true));
    
    $user = [
        'id' => $userId,
        'name' => $name,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'verification_code' => $verificationCode,
        'is_verified' => false,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $_SESSION['mock_db']['users'][] = $user;
    
    return [
        'success' => true,
        'user_id' => $userId,
        'verification_code' => $verificationCode
    ];
}

/**
 * Verify user email
 */
function verifyUser($code) {
    foreach ($_SESSION['mock_db']['users'] as &$user) {
        if ($user['verification_code'] === $code) {
            $user['is_verified'] = true;
            return true;
        }
    }
    return false;
}

/**
 * Login user
 */
function loginUser($email, $password) {
    foreach ($_SESSION['mock_db']['users'] as $user) {
        if ($user['email'] === $email) {
            if (password_verify($password, $user['password'])) {
                return [
                    'success' => true,
                    'user' => $user
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Invalid password'
                ];
            }
        }
    }
    
    return [
        'success' => false,
        'message' => 'Email not found'
    ];
}

/**
 * Create a new trip
 */
function createTrip($userId, $destination, $startDate, $endDate, $activities = '', $notes = '') {
    $tripId = count($_SESSION['mock_db']['trips']) + 1;
    
    $trip = [
        'id' => $tripId,
        'user_id' => $userId,
        'destination' => $destination,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'activities' => $activities,
        'notes' => $notes,
        'status' => 'planned',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $_SESSION['mock_db']['trips'][] = $trip;
    
    return [
        'success' => true,
        'trip_id' => $tripId
    ];
}

/**
 * Get user trips
 */
function getUserTrips($userId) {
    $trips = [];
    
    foreach ($_SESSION['mock_db']['trips'] as $trip) {
        if ($trip['user_id'] == $userId) {
            $trips[] = $trip;
        }
    }
    
    return $trips;
}

/**
 * Get user by ID
 */
function getUserById($userId) {
    foreach ($_SESSION['mock_db']['users'] as $user) {
        if ($user['id'] == $userId) {
            return $user;
        }
    }
    
    return null;
}

/**
 * Get trip by ID
 */
function getTripById($tripId) {
    foreach ($_SESSION['mock_db']['trips'] as $trip) {
        if ($trip['id'] == $tripId) {
            return $trip;
        }
    }
    
    return null;
}

/**
 * Update trip status
 */
function updateTripStatus($tripId, $status) {
    foreach ($_SESSION['mock_db']['trips'] as &$trip) {
        if ($trip['id'] == $tripId) {
            $trip['status'] = $status;
            $trip['updated_at'] = date('Y-m-d H:i:s');
            return true;
        }
    }
    
    return false;
}

// Add some sample data
if (count($_SESSION['mock_db']['users']) === 0) {
    // Add a demo user
    $_SESSION['mock_db']['users'][] = [
        'id' => 1,
        'name' => 'Demo User',
        'email' => 'demo@example.com',
        'password' => password_hash('password', PASSWORD_DEFAULT),
        'verification_code' => 'verified',
        'is_verified' => true,
        'email_verified' => 1,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Add some sample trips
    $_SESSION['mock_db']['trips'][] = [
        'id' => 1,
        'user_id' => 1,
        'destination' => 'Paris, France',
        'start_date' => '2025-06-15',
        'end_date' => '2025-06-22',
        'activities' => 'Visit Eiffel Tower, Louvre Museum, Notre Dame',
        'notes' => 'Remember to book hotel near city center',
        'status' => 'planned',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $_SESSION['mock_db']['trips'][] = [
        'id' => 2,
        'user_id' => 1,
        'destination' => 'Tokyo, Japan',
        'start_date' => '2025-08-10',
        'end_date' => '2025-08-20',
        'activities' => 'Visit Tokyo Tower, Shibuya Crossing, Akihabara',
        'notes' => 'Try authentic ramen and sushi',
        'status' => 'planned',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
}
?>
