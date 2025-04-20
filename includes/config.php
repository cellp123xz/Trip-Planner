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

function createTrip($userId, $destination, $startDate, $endDate, $activities = '', $notes = '') {
    $tripId = count($_SESSION['db']['trips']) + 1;
    
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
    
    $_SESSION['db']['trips'][] = $trip;
    
    // Save changes to persistent storage
    saveSessionData();
    
    return $tripId;
}

function getAllHotels() {
    return $_SESSION['db']['hotels'];
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
