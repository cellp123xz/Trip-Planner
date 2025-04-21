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

function getPriceRangeAmount($priceRange) {
    // Convert price range symbols to actual price ranges
    switch ($priceRange) {
        case '$':
        case '₱':
            return rand(1000, 2500);
        case '$$':
        case '₱₱':
            return rand(2500, 5000);
        case '$$$':
        case '₱₱₱':
            return rand(5000, 10000);
        case '$$$$':
        case '₱₱₱₱':
            return rand(10000, 20000);
        case '$$$$$':
        case '₱₱₱₱₱':
            return rand(20000, 50000);
        default:
            return rand(3000, 8000); // Default price range
    }
}

function getDestinationsByCountry() {
    return [
        // Philippines
        'Philippines' => [
            'Manila', 'Cebu City', 'Boracay', 'Palawan', 'Bohol', 'Davao City', 
            'Baguio City', 'Siargao', 'Coron', 'El Nido'
        ],
        
        // Asia
        'Japan' => ['Tokyo', 'Kyoto', 'Osaka', 'Nara', 'Hiroshima', 'Sapporo', 'Fukuoka'],
        'South Korea' => ['Seoul', 'Busan', 'Jeju Island', 'Incheon', 'Gyeongju'],
        'Thailand' => ['Bangkok', 'Phuket', 'Chiang Mai', 'Pattaya', 'Krabi', 'Koh Samui'],
        'Singapore' => ['Singapore City'],
        'Indonesia' => ['Bali', 'Jakarta', 'Yogyakarta', 'Lombok', 'Bandung'],
        'China' => ['Shanghai', 'Beijing', 'Guangzhou', 'Shenzhen', 'Xi\'an', 'Chengdu'],
        'Hong Kong' => ['Hong Kong Island', 'Kowloon', 'New Territories'],
        'Vietnam' => ['Hanoi', 'Ho Chi Minh City', 'Da Nang', 'Hoi An', 'Ha Long Bay'],
        'Malaysia' => ['Kuala Lumpur', 'Penang', 'Langkawi', 'Johor Bahru', 'Melaka'],
        'Taiwan' => ['Taipei', 'Kaohsiung', 'Taichung', 'Tainan', 'Hualien'],
        'UAE' => ['Dubai', 'Abu Dhabi', 'Sharjah', 'Ajman', 'Ras Al Khaimah'],
        
        // Europe
        'France' => ['Paris', 'Nice', 'Lyon', 'Marseille', 'Bordeaux', 'Strasbourg'],
        'United Kingdom' => ['London', 'Edinburgh', 'Manchester', 'Liverpool', 'Glasgow', 'Bath'],
        'Italy' => ['Rome', 'Venice', 'Florence', 'Milan', 'Naples', 'Amalfi Coast'],
        'Spain' => ['Barcelona', 'Madrid', 'Seville', 'Valencia', 'Malaga', 'Granada'],
        'Netherlands' => ['Amsterdam', 'Rotterdam', 'The Hague', 'Utrecht', 'Eindhoven'],
        'Germany' => ['Berlin', 'Munich', 'Hamburg', 'Frankfurt', 'Cologne', 'Dresden'],
        'Czech Republic' => ['Prague', 'Brno', 'Karlovy Vary', 'Český Krumlov', 'Pilsen'],
        'Austria' => ['Vienna', 'Salzburg', 'Innsbruck', 'Graz', 'Hallstatt'],
        'Greece' => ['Athens', 'Santorini', 'Mykonos', 'Crete', 'Rhodes', 'Corfu'],
        'Switzerland' => ['Zurich', 'Geneva', 'Bern', 'Lucerne', 'Interlaken', 'Zermatt'],
        'Hungary' => ['Budapest', 'Debrecen', 'Szeged', 'Pécs', 'Eger'],
        'Turkey' => ['Istanbul', 'Antalya', 'Bodrum', 'Cappadocia', 'Izmir'],
        
        // Americas
        'USA' => [
            'New York City', 'Los Angeles', 'San Francisco', 'Las Vegas', 'Miami',
            'Chicago', 'Boston', 'Washington DC', 'Seattle', 'San Diego'
        ],
        'Canada' => ['Toronto', 'Vancouver', 'Montreal', 'Calgary', 'Quebec City', 'Ottawa'],
        'Mexico' => ['Mexico City', 'Cancun', 'Playa del Carmen', 'Puerto Vallarta', 'Cabo San Lucas'],
        'Brazil' => ['Rio de Janeiro', 'São Paulo', 'Salvador', 'Fortaleza', 'Brasília'],
        'Argentina' => ['Buenos Aires', 'Mendoza', 'Córdoba', 'Bariloche', 'Salta'],
        
        // Oceania
        'Australia' => ['Sydney', 'Melbourne', 'Brisbane', 'Perth', 'Adelaide', 'Gold Coast'],
        'New Zealand' => ['Auckland', 'Queenstown', 'Wellington', 'Christchurch', 'Rotorua'],
        'Fiji' => ['Nadi', 'Suva', 'Denarau Island', 'Coral Coast', 'Mamanuca Islands'],
        
        // Africa
        'Egypt' => ['Cairo', 'Luxor', 'Alexandria', 'Sharm El Sheikh', 'Hurghada'],
        'South Africa' => ['Cape Town', 'Johannesburg', 'Durban', 'Pretoria', 'Kruger National Park'],
        'Morocco' => ['Marrakech', 'Casablanca', 'Fes', 'Tangier', 'Essaouira'],
        'Kenya' => ['Nairobi', 'Mombasa', 'Kisumu', 'Malindi', 'Lamu']
    ];
}

function getPopularDestinations() {
    $destinations = [];
    $destinationsByCountry = getDestinationsByCountry();
    
    foreach ($destinationsByCountry as $country => $cities) {
        foreach ($cities as $city) {
            $destinations[] = $city . ', ' . $country;
        }
    }
    
    return $destinations;
}

function getCountries() {
    return array_keys(getDestinationsByCountry());
}

function getDestinationsForCountry($country) {
    $destinationsByCountry = getDestinationsByCountry();
    
    if (isset($destinationsByCountry[$country])) {
        $destinations = [];
        foreach ($destinationsByCountry[$country] as $city) {
            $destinations[] = $city . ', ' . $country;
        }
        return $destinations;
    }
    
    return [];
}

function getRecommendedHotels($destination, $limit = 3) {
    $recommendedHotels = [];
    
    if (!empty($destination)) {
        $allHotels = getAllHotels();
        $otherHotels = [];
        
        // First, look for exact matches in the hotel address
        foreach ($allHotels as $hotel) {
            if (stripos($hotel['address'], $destination) !== false) {
                $recommendedHotels[] = $hotel;
                if (count($recommendedHotels) >= $limit) {
                    break;
                }
            } else {
                $otherHotels[] = $hotel;
            }
        }
        
        // If we don't have enough, try to match just the city/location part
        if (count($recommendedHotels) < $limit) {
            // Split destination into parts (e.g., "Paris, France" -> ["Paris", "France"])
            $destinationParts = explode(',', $destination);
            $mainLocation = trim($destinationParts[0]);
            $country = isset($destinationParts[1]) ? trim($destinationParts[1]) : '';
            
            foreach ($otherHotels as $hotel) {
                if (stripos($hotel['address'], $mainLocation) !== false) {
                    $recommendedHotels[] = $hotel;
                    if (count($recommendedHotels) >= $limit) {
                        break;
                    }
                }
            }
        }
        
        // If still no hotels found, generate new ones for this destination
        if (empty($recommendedHotels)) {
            return generateDestinationHotels($destination);
        }
    }
    
    // If we still don't have enough, add top-rated hotels
    if (count($recommendedHotels) < $limit) {
        // Sort by rating (highest first)
        usort($otherHotels, function($a, $b) {
            return $b['rating'] <=> $a['rating'];
        });
        
        // Add top-rated hotels until we reach the limit
        foreach ($otherHotels as $hotel) {
            if (!in_array($hotel, $recommendedHotels)) {
                $recommendedHotels[] = $hotel;
                if (count($recommendedHotels) >= $limit) {
                    break;
                }
            }
        }
    }
    
    // Sort final results by rating
    usort($recommendedHotels, function($a, $b) {
        return $b['rating'] <=> $a['rating'];
    });
    
    return array_slice($recommendedHotels, 0, $limit);
}

function generateDestinationHotels($destination) {
    // Extract main location and country
    $parts = explode(',', $destination);
    $mainLocation = trim($parts[0]);
    $country = isset($parts[1]) ? trim($parts[1]) : '';
    
    // If no country specified, try to determine it from the destination list
    if (empty($country)) {
        $allDestinations = getDestinationsByCountry();
        foreach ($allDestinations as $countryName => $destinations) {
            if (in_array($mainLocation, $destinations)) {
                $country = $countryName;
                break;
            }
        }
        
        // If still empty, use a default
        if (empty($country)) {
            $country = 'International';
        }
    }
    
    // Set currency symbol based on country/region
    $currencySymbol = '$';
    if (stripos($country, 'Philippines') !== false) {
        $currencySymbol = '₱';
    } elseif (stripos($country, 'Japan') !== false || 
              stripos($country, 'China') !== false || 
              stripos($country, 'Hong Kong') !== false || 
              stripos($country, 'Taiwan') !== false || 
              stripos($country, 'Korea') !== false) {
        $currencySymbol = '¥';
    } elseif (stripos($country, 'UK') !== false || 
              stripos($country, 'United Kingdom') !== false || 
              stripos($country, 'England') !== false) {
        $currencySymbol = '£';
    } elseif (stripos($country, 'Euro') !== false || 
              stripos($country, 'France') !== false || 
              stripos($country, 'Germany') !== false || 
              stripos($country, 'Italy') !== false || 
              stripos($country, 'Spain') !== false || 
              stripos($country, 'Netherlands') !== false || 
              stripos($country, 'Greece') !== false || 
              stripos($country, 'Portugal') !== false || 
              stripos($country, 'Austria') !== false || 
              stripos($country, 'Belgium') !== false || 
              stripos($country, 'Finland') !== false || 
              stripos($country, 'Ireland') !== false) {
        $currencySymbol = '€';
    } elseif (stripos($country, 'Thailand') !== false) {
        $currencySymbol = '฿'; // Thai Baht
    }
    
    // Generate unique IDs for the hotels
    $baseId = crc32($destination) % 1000 + 1000;
    
    // Get country-specific hotel names and features
    $hotelNames = getCountrySpecificHotelNames($country, $mainLocation);
    $hotelAmenities = getCountrySpecificAmenities($country);
    $hotelImages = getCountrySpecificImages($country);
    
    // Create hotels for this destination
    return [
        // Luxury hotel
        [
            'id' => $baseId,
            'name' => $hotelNames['luxury'],
            'address' => 'Downtown ' . $destination,
            'rating' => 4.7 + (rand(0, 20) / 100), // 4.7-4.9
            'price_range' => str_repeat($currencySymbol, 4), // Luxury
            'amenities' => $hotelAmenities['luxury'],
            'image' => $hotelImages['luxury']
        ],
        // Mid-range hotel
        [
            'id' => $baseId + 1,
            'name' => $hotelNames['midrange'],
            'address' => $mainLocation . ' City Center, ' . $country,
            'rating' => 4.2 + (rand(0, 30) / 100), // 4.2-4.5
            'price_range' => str_repeat($currencySymbol, 3), // Mid-range
            'amenities' => $hotelAmenities['midrange'],
            'image' => $hotelImages['midrange']
        ],
        // Budget hotel
        [
            'id' => $baseId + 2,
            'name' => $hotelNames['budget'],
            'address' => $mainLocation . ' Airport Area, ' . $country,
            'rating' => 3.5 + (rand(0, 40) / 100), // 3.5-3.9
            'price_range' => str_repeat($currencySymbol, 2), // Budget
            'amenities' => $hotelAmenities['budget'],
            'image' => $hotelImages['budget']
        ],
        // Boutique hotel
        [
            'id' => $baseId + 3,
            'name' => $hotelNames['boutique'],
            'address' => 'Historic District, ' . $destination,
            'rating' => 4.4 + (rand(0, 30) / 100), // 4.4-4.7
            'price_range' => str_repeat($currencySymbol, 3), // Mid-range to luxury
            'amenities' => $hotelAmenities['boutique'],
            'image' => $hotelImages['boutique']
        ],
        // Resort (if applicable)
        [
            'id' => $baseId + 4,
            'name' => $hotelNames['resort'],
            'address' => 'Beachfront, ' . $destination,
            'rating' => 4.6 + (rand(0, 30) / 100), // 4.6-4.9
            'price_range' => str_repeat($currencySymbol, 4), // Luxury
            'amenities' => $hotelAmenities['resort'],
            'image' => $hotelImages['resort']
        ]
    ];
}

function getCountrySpecificHotelNames($country, $location) {
    // Default hotel names
    $names = [
        'luxury' => $location . ' Grand Luxury Hotel',
        'midrange' => $location . ' Plaza Hotel',
        'budget' => $location . ' Budget Inn',
        'boutique' => 'Boutique Hotel ' . $location,
        'resort' => $location . ' Beach Resort & Spa'
    ];
    
    // Country-specific hotel names
    if (stripos($country, 'Japan') !== false) {
        $names = [
            'luxury' => 'The ' . $location . ' Tokyo Palace',
            'midrange' => 'Hotel Sakura ' . $location,
            'budget' => $location . ' Business Hotel',
            'boutique' => 'Ryokan ' . $location,
            'resort' => 'Onsen Resort ' . $location
        ];
    } elseif (stripos($country, 'Thailand') !== false) {
        $names = [
            'luxury' => $location . ' Grand Palace Resort',
            'midrange' => $location . ' Orchid Hotel',
            'budget' => 'Sawadee ' . $location . ' Inn',
            'boutique' => $location . ' Boutique Retreat',
            'resort' => $location . ' Beach & Spa Resort'
        ];
    } elseif (stripos($country, 'France') !== false) {
        $names = [
            'luxury' => 'Grand Hôtel de ' . $location,
            'midrange' => 'Hôtel ' . $location . ' Paris',
            'budget' => 'Auberge de ' . $location,
            'boutique' => 'Le Petit ' . $location,
            'resort' => 'Château ' . $location . ' & Spa'
        ];
    } elseif (stripos($country, 'Italy') !== false) {
        $names = [
            'luxury' => 'Grand Hotel ' . $location . ' Palace',
            'midrange' => 'Hotel ' . $location . ' Roma',
            'budget' => 'Pensione ' . $location,
            'boutique' => 'Villa ' . $location,
            'resort' => $location . ' Resort & Terme'
        ];
    } elseif (stripos($country, 'Spain') !== false) {
        $names = [
            'luxury' => $location . ' Gran Hotel',
            'midrange' => 'Hotel ' . $location . ' Barcelona',
            'budget' => 'Hostal ' . $location,
            'boutique' => 'Casa ' . $location,
            'resort' => $location . ' Beach Resort & Spa'
        ];
    } elseif (stripos($country, 'United Kingdom') !== false || stripos($country, 'UK') !== false) {
        $names = [
            'luxury' => 'The ' . $location . ' Luxury Collection',
            'midrange' => $location . ' Park Hotel',
            'budget' => $location . ' Lodge',
            'boutique' => $location . ' Boutique Hotel',
            'resort' => $location . ' Manor & Spa'
        ];
    } elseif (stripos($country, 'China') !== false) {
        $names = [
            'luxury' => $location . ' Imperial Palace Hotel',
            'midrange' => $location . ' Garden Hotel',
            'budget' => $location . ' City Inn',
            'boutique' => $location . ' Courtyard Hotel',
            'resort' => $location . ' Hot Springs Resort'
        ];
    } elseif (stripos($country, 'Philippines') !== false) {
        $names = [
            'luxury' => $location . ' Luxury Resort & Casino',
            'midrange' => $location . ' City Hotel',
            'budget' => $location . ' Pension House',
            'boutique' => $location . ' Heritage Hotel',
            'resort' => $location . ' Island Resort & Spa'
        ];
    }
    
    return $names;
}

function getCountrySpecificAmenities($country) {
    // Default amenities
    $amenities = [
        'luxury' => 'Spa, Pool, Fine Dining, Concierge, Fitness Center, Business Center',
        'midrange' => 'Restaurant, Free WiFi, Pool, Parking, Room Service',
        'budget' => 'Free WiFi, Breakfast, Air Conditioning, 24-hour Front Desk',
        'boutique' => 'Unique Design, Local Experience, Personalized Service, Gourmet Breakfast',
        'resort' => 'Private Beach, Infinity Pool, Water Sports, Multiple Restaurants, Spa'
    ];
    
    // Country-specific amenities
    if (stripos($country, 'Japan') !== false) {
        $amenities['luxury'] .= ', Traditional Tea Ceremony, Kaiseki Dining';
        $amenities['midrange'] .= ', Japanese Bath, Tatami Rooms';
        $amenities['boutique'] = 'Tatami Rooms, Onsen Bath, Traditional Japanese Breakfast, Zen Garden';
        $amenities['resort'] = 'Hot Springs, Japanese Garden, Kaiseki Dining, Spa Treatments, Meditation Areas';
    } elseif (stripos($country, 'Thailand') !== false) {
        $amenities['luxury'] .= ', Thai Massage, Cooking Classes';
        $amenities['midrange'] .= ', Thai Restaurant, Massage Services';
        $amenities['boutique'] = 'Thai Decor, Massage Services, Local Tours, Cooking Classes';
        $amenities['resort'] = 'Beachfront, Thai Massage, Multiple Pools, Water Sports, Thai Cooking Classes';
    } elseif (stripos($country, 'Italy') !== false) {
        $amenities['luxury'] .= ', Wine Cellar, Italian Marble Bathrooms';
        $amenities['midrange'] .= ', Italian Restaurant, Wine Bar';
        $amenities['boutique'] = 'Historic Building, Wine Tastings, Italian Design, Artisanal Breakfast';
        $amenities['resort'] = 'Vineyard Views, Wine Tastings, Italian Cuisine, Spa Treatments, Pool';
    }
    
    return $amenities;
}

function getCountrySpecificImages($country) {
    // Default images
    $images = [
        'luxury' => APP_URL . '/assets/images/hotels/beach-resort.jpg',
        'midrange' => APP_URL . '/assets/images/hotels/urban-luxury.jpg',
        'budget' => APP_URL . '/assets/images/hotels/budget-inn.jpg',
        'boutique' => APP_URL . '/assets/images/hotels/historic-hotel.jpg',
        'resort' => APP_URL . '/assets/images/hotels/beach-resort.jpg'
    ];
    
    // We could customize images by country if we had more image options
    
    return $images;
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
