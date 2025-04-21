<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


define('APP_NAME', 'TripPlanner');
define('APP_URL', '/trip-planner'); 


define('APP_BASE_URL', 'http://localhost:8080');



define('USE_SESSION_STORAGE', true);


define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'trip_planner');


putenv('SMTP_HOST=smtp.gmail.com');
putenv('SMTP_PORT=587');
putenv('SMTP_USERNAME=ciervo.jenojohn@gmail.com');
putenv('SMTP_PASSWORD=fpys hwzm nifg ahrp');  


define('STORAGE_FILE', __DIR__ . '/../data/storage.json');


if (!isset($_SESSION['db'])) {
    
    if (file_exists(STORAGE_FILE)) {
        
        $data = json_decode(file_get_contents(STORAGE_FILE), true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            $_SESSION['db'] = $data;
        } else {
            
            initializeDefaultData();
        }
    } else {
        
        initializeDefaultData();
    }
}


function initializeDefaultData() {
    
    if (!file_exists(dirname(STORAGE_FILE))) {
        mkdir(dirname(STORAGE_FILE), 0777, true);
    }
    
    $_SESSION['db'] = [
        'users' => [
            [
                'id' => 1,
                'name' => 'Demo User',
                'email' => 'demo@example.com',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'verification_code' => bin2hex(random_bytes(16)),
                'reset_token' => null,
                'reset_token_expires' => null,
                'security_question' => 'What was your first pet\'s name?',
                'security_answer' => password_hash('fluffy', PASSWORD_DEFAULT), // Hashed for security
                'is_verified' => true, 
                'email_verified' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ],
        'trips' => [
            [
                'id' => 1,
                'user_id' => 1,
                'destination' => 'Paris, France',
                'start_date' => '2025-06-15',
                'end_date' => '2025-06-22',
                'activities' => 'Visit Eiffel Tower, Louvre Museum',
                'notes' => 'Remember to pack light!',
                'status' => 'planned',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'user_id' => 1,
                'destination' => 'Rome, Italy',
                'start_date' => '2025-08-10',
                'end_date' => '2025-08-17',
                'activities' => 'Colosseum, Vatican City',
                'notes' => 'Book tickets in advance for Vatican',
                'status' => 'planned',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ],
        'hotels' => [
            [
                'id' => 1,
                'name' => 'Grand Luxury Hotel',
                'address' => '123 Champs-Élysées, Paris, France',
                'rating' => 4.7,
                'price_range' => '$$$'
            ],
            [
                'id' => 2,
                'name' => 'Roman Resort Suites',
                'address' => '45 Via del Corso, Rome, Italy',
                'rating' => 4.5,
                'price_range' => '$$'
            ]
        ],
        'tourist_sites' => [
            [
                'id' => 1,
                'name' => 'Eiffel Tower',
                'description' => 'Iconic Parisian landmark',
                'address' => 'Champ de Mars, Paris, France',
                'category' => 'Monument'
            ],
            [
                'id' => 2,
                'name' => 'Colosseum',
                'description' => 'Ancient Roman amphitheatre',
                'address' => 'Piazza del Colosseo, Rome, Italy',
                'category' => 'Historical Site'
            ]
        ]
    ];
}


function getUserByEmail($email) {
    foreach ($_SESSION['db']['users'] as $user) {
        if ($user['email'] === $email) {
            return $user;
        }
    }
    return null;
}

function getUserById($id) {
    foreach ($_SESSION['db']['users'] as $user) {
        if ($user['id'] == $id) {
            return $user;
        }
    }
    return null;
}

function createUser($name, $email, $password, $verificationCode = null, $securityQuestion = null, $securityAnswer = null) {
    $userId = count($_SESSION['db']['users']) + 1;
    
    $user = [
        'id' => $userId,
        'name' => $name,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'verification_code' => $verificationCode ?: bin2hex(random_bytes(16)),
        'reset_token' => null,
        'reset_token_expires' => null,
        'security_question' => $securityQuestion,
        'security_answer' => $securityAnswer ? password_hash($securityAnswer, PASSWORD_DEFAULT) : null,
        'is_verified' => false, 
        'email_verified' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $_SESSION['db']['users'][] = $user;
    
    // Save changes to persistent storage
    saveSessionData();
    
    return $userId;
}

function getTripsByUserId($userId) {
    $trips = [];
    foreach ($_SESSION['db']['trips'] as $trip) {
        if ($trip['user_id'] == $userId) {
            $trips[] = $trip;
        }
    }
    return $trips;
}

function createTrip($userId, $destination, $startDate, $endDate, $activities = '', $notes = '', $hotel_id = null, $tourist_sites = []) {
    $tripId = count($_SESSION['db']['trips']) + 1;
    
    $trip = [
        'id' => $tripId,
        'user_id' => $userId,
        'destination' => $destination,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'activities' => $activities,
        'notes' => $notes,
        'hotel_id' => $hotel_id,
        'tourist_sites' => $tourist_sites,
        'status' => 'planned',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $_SESSION['db']['trips'][] = $trip;
    
    // Save changes to persistent storage
    saveSessionData();
    
    return $tripId;
}

function getAllHotels() {
    // Define additional hotels that should be available throughout the application
    $additionalHotels = [
        [
            'id' => 3,
            'name' => 'Shangri-La Boracay Resort & Spa',
            'address' => 'Barangay Yapak, Boracay Island, Malay, Aklan, Philippines',
            'rating' => 4.9,
            'price_range' => '₱₱₱₱',
            'amenities' => 'Private Beach, Infinity Pool, Spa, Multiple Restaurants, Water Sports',
            'image' => APP_URL . '/assets/images/hotels/beach-resort.jpg'
        ],
        [
            'id' => 4,
            'name' => 'El Nido Resorts Lagen Island',
            'address' => 'Lagen Island, El Nido, Palawan, Philippines',
            'rating' => 4.8,
            'price_range' => '₱₱₱',
            'amenities' => 'Eco-Sanctuary, Lagoon, Diving, Island Hopping, Spa',
            'image' => APP_URL . '/assets/images/hotels/mountain-lodge.jpg'
        ],
        [
            'id' => 5,
            'name' => 'The Farm at San Benito',
            'address' => 'Barangay Tipakan, Lipa City, Batangas, Philippines',
            'rating' => 4.7,
            'price_range' => '₱₱₱₱',
            'amenities' => 'Wellness Retreat, Vegan Restaurant, Holistic Spa, Yoga, Meditation',
            'image' => APP_URL . '/assets/images/hotels/urban-luxury.jpg'
        ],
        [
            'id' => 6,
            'name' => 'Henann Resort Alona Beach',
            'address' => 'Alona Beach, Panglao Island, Bohol, Philippines',
            'rating' => 4.6,
            'price_range' => '₱₱₱',
            'amenities' => 'Beachfront, Multiple Pools, Restaurants, Spa, Water Activities',
            'image' => APP_URL . '/assets/images/hotels/historic-hotel.jpg'
        ],
        [
            'id' => 7,
            'name' => 'Siargao Bleu Resort & Spa',
            'address' => 'Tourism Road, General Luna, Siargao Island, Philippines',
            'rating' => 4.5,
            'price_range' => '₱₱',
            'amenities' => 'Surfing Lessons, Pool, Restaurant, Airport Transfers, WiFi',
            'image' => APP_URL . '/assets/images/hotels/budget-inn.jpg'
        ],
        [
            'id' => 8,
            'name' => 'Marco Polo Davao',
            'address' => 'CM Recto Street, Davao City, Philippines',
            'rating' => 4.7,
            'price_range' => '₱₱₱',
            'amenities' => 'City Views, Pool, Multiple Restaurants, Spa, Business Center',
            'image' => APP_URL . '/assets/images/hotels/desert-resort.jpg'
        ],
        [
            'id' => 9,
            'name' => 'Seda Centrio',
            'address' => 'Cagayan de Oro City, Philippines',
            'rating' => 4.6,
            'price_range' => '₱₱',
            'amenities' => 'Mall Access, Business Center, Restaurant, Gym, Free WiFi',
            'image' => APP_URL . '/assets/images/hotels/urban-luxury.jpg'
        ],
        [
            'id' => 10,
            'name' => 'Marina Bay Sands',
            'address' => '10 Bayfront Avenue, Singapore',
            'rating' => 4.8,
            'price_range' => '$$$$$',
            'amenities' => 'Infinity Pool, Casino, Luxury Shopping, SkyPark Observation Deck',
            'image' => APP_URL . '/assets/images/hotels/historic-hotel.jpg'
        ],
        [
            'id' => 11,
            'name' => 'Burj Al Arab Jumeirah',
            'address' => 'Jumeirah Beach Road, Dubai, UAE',
            'rating' => 4.9,
            'price_range' => '$$$$$',
            'amenities' => 'Private Beach, Helipad, Underwater Restaurant, Butler Service',
            'image' => APP_URL . '/assets/images/hotels/budget-inn.jpg'
        ],
        [
            'id' => 12,
            'name' => 'The Ritz Paris',
            'address' => '15 Place Vendôme, 75001 Paris, France',
            'rating' => 4.9,
            'price_range' => '$$$$$',
            'amenities' => 'Michelin Star Restaurant, Luxury Spa, Historic Suites, Bar Hemingway',
            'image' => APP_URL . '/assets/images/hotels/desert-resort.jpg'
        ],
        [
            'id' => 13,
            'name' => 'Aman Tokyo',
            'address' => 'The Otemachi Tower, 1-5-6 Otemachi, Tokyo, Japan',
            'rating' => 4.8,
            'price_range' => '$$$$',
            'amenities' => 'Urban Sanctuary, Traditional Onsen, Panoramic Views, Spa',
            'image' => APP_URL . '/assets/images/hotels/urban-luxury.jpg'
        ],
        [
            'id' => 14,
            'name' => 'Jade Mountain Resort',
            'address' => 'Soufriere, St. Lucia, Caribbean',
            'rating' => 4.9,
            'price_range' => '$$$$$',
            'amenities' => 'Open-air Suites, Private Infinity Pools, Ocean Views, Organic Cuisine',
            'image' => APP_URL . '/assets/images/hotels/beach-resort.jpg'
        ],
        [
            'id' => 15,
            'name' => 'Four Seasons Resort Bora Bora',
            'address' => 'Motu Tehotu, Bora Bora, French Polynesia',
            'rating' => 4.9,
            'price_range' => '$$$$$',
            'amenities' => 'Overwater Bungalows, Lagoon Views, Snorkeling, Spa, Fine Dining',
            'image' => APP_URL . '/assets/images/hotels/mountain-lodge.jpg'
        ],
        [
            'id' => 16,
            'name' => 'The Savoy',
            'address' => 'Strand, London, United Kingdom',
            'rating' => 4.8,
            'price_range' => '$$$$',
            'amenities' => 'Historic Luxury, Thames Views, Gordon Ramsay Restaurant, Afternoon Tea',
            'image' => APP_URL . '/assets/images/hotels/historic-hotel.jpg'
        ],
        [
            'id' => 17,
            'name' => 'Atlantis The Palm',
            'address' => 'Crescent Road, The Palm, Dubai, UAE',
            'rating' => 4.7,
            'price_range' => '$$$$',
            'amenities' => 'Aquaventure Waterpark, Lost Chambers Aquarium, Underwater Suites',
            'image' => APP_URL . '/assets/images/hotels/budget-inn.jpg'
        ],
        [
            'id' => 18,
            'name' => 'Mandarin Oriental Hong Kong',
            'address' => '5 Connaught Road, Central, Hong Kong',
            'rating' => 4.8,
            'price_range' => '$$$$$',
            'amenities' => 'Rolls-Royce Fleet, Helicopter, Spa, Michelin-Starred Dining, Harbor Views',
            'image' => APP_URL . '/assets/images/hotels/historic-hotel.jpg'
        ]
    ];
    
    // Merge with session hotels and return
    return array_merge($_SESSION['db']['hotels'], $additionalHotels);
}

function getAllTouristSites() {
    return $_SESSION['db']['tourist_sites'];
}

function saveSessionData() {
    if (defined('STORAGE_FILE')) {
        
        if (!file_exists(dirname(STORAGE_FILE))) {
            mkdir(dirname(STORAGE_FILE), 0777, true);
        }
        
        
        file_put_contents(STORAGE_FILE, json_encode($_SESSION['db'], JSON_PRETTY_PRINT));
    }
}


class SessionPDO {
    public function prepare($query) {
        return new SessionPDOStatement($query);
    }
    
    public function exec($query) {
        
        return true;
    }
    
    public function setAttribute($attr, $value) {
        return true;
    }
    
    public function lastInsertId() {
        return count($_SESSION['db']['users']) + count($_SESSION['db']['trips']);
    }
}

class SessionPDOStatement {
    private $query;
    private $params = [];
    
    public function __construct($query) {
        $this->query = $query;
    }
    
    public function bindParam($param, &$value, $type = null) {
        $this->params[$param] = $value;
        return true;
    }
    
    public function bindValue($param, $value, $type = null) {
        $this->params[$param] = $value;
        return true;
    }
    
    public function execute($params = null) {
        if ($params) {
            $this->params = $params;
        }
        return true;
    }
    
    public function fetch($style = null) {
        if (strpos($this->query, 'SELECT * FROM users WHERE email') !== false ||
            strpos($this->query, 'SELECT id, name, email, password') !== false) {
            $email = $this->params[0] ?? '';
            return getUserByEmail($email);
        }
        return null;
    }
    
    public function fetchAll($style = null) {
        if (strpos($this->query, 'SELECT * FROM trips WHERE user_id') !== false) {
            $userId = $this->params[0] ?? 0;
            return getTripsByUserId($userId);
        }
        return [];
    }
    
    public function rowCount() {
        if (strpos($this->query, 'SELECT id FROM users WHERE email') !== false) {
            $email = $this->params[0] ?? '';
            return getUserByEmail($email) ? 1 : 0;
        }
        return 0;
    }
}


$pdo = new SessionPDO();
?>
