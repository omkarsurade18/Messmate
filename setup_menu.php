<?php
require 'api/db.php';

echo "<h3>Initializing Advanced Systems & Database...</h3>";

try {
    // 1. Notices Table
    $pdo->query("CREATE TABLE IF NOT EXISTS notices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Global Settings Table (Mess Time, Capacity)
    $pdo->query("CREATE TABLE IF NOT EXISTS settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value TEXT NOT NULL
    )");

    // Default Settings
    $pdo->query("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES 
        ('mess_timing', 'Lunch: 12 PM - 3 PM | Dinner: 7 PM - 10 PM'),
        ('max_seats', '800')
    ");

    // 3. Clear/Refresh Menu and Plans (as previously required)
    $pdo->query("TRUNCATE TABLE menu");
    $pdo->query("DELETE FROM plans");

    $pdo->query("INSERT INTO plans (name, price, duration_days, description) VALUES 
        ('Weekly Pass', 750.00, 7, '7 days of premium meals'),
        ('Fortnight Pass', 1500.00, 15, '15 days of hot & fresh meals'),
        ('Monthly Elite', 3000.00, 30, '30 days standard full access')
    ");

    $menus = [
        ['Monday', 'lunch', 'Dal Tadka & Steamed Rice', 'https://images.unsplash.com/photo-1546833999-b9f581a1996d'], 
        ['Monday', 'dinner', 'Paneer Masala & Roti', 'https://images.unsplash.com/photo-1631452180519-c014fe946bc0'], 
        ['Tuesday', 'lunch', 'Aloo Gobi & Roti', 'https://images.unsplash.com/photo-1625220194771-7ebdea0b70b9'], 
        ['Tuesday', 'dinner', 'Veg Biryani & Raita', 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8'], 
        ['Wednesday', 'lunch', 'Chole Bhature', 'https://images.unsplash.com/photo-1626779843666-4df4f61f774d'], 
        ['Wednesday', 'dinner', 'Rajma Chawal', 'https://images.unsplash.com/photo-1546833998-877b37c2e5c4'], 
        ['Thursday', 'lunch', 'Kadhi Chawal', 'https://images.unsplash.com/photo-1589301760014-d929f39ce9b1'], 
        ['Thursday', 'dinner', 'Mixed Veg & Roti', 'https://images.unsplash.com/photo-1512152272829-410aabe2ba33'], 
        ['Friday', 'lunch', 'Pav Bhaji', 'https://images.unsplash.com/photo-1606491956689-2ea866880c84'], 
        ['Friday', 'dinner', 'Special Veg Thali', 'https://images.unsplash.com/photo-1585553616435-2dc0a54e271d'], 
        ['Saturday', 'lunch', 'Poori Bhaji', 'https://images.unsplash.com/photo-1601050638911-c3239a6fb39e'], 
        ['Saturday', 'dinner', 'Masala Dosa', 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd'], 
        ['Sunday', 'lunch', 'Idli Sambar', 'https://images.unsplash.com/photo-1589301973394-82c16d2e5055'], 
        ['Sunday', 'dinner', 'Egg Curry & Rice', 'https://images.unsplash.com/photo-1599487488170-d11ec9c172f0']
    ];

    $stmt = $pdo->prepare("INSERT INTO menu (day_of_week, meal_type, items, image_url) VALUES (?, ?, ?, ?)");
    foreach($menus as $m) {
        $stmt->execute([$m[0], $m[1], $m[2], $m[3] . "?w=600&q=80"]);
    }
    
    echo "<h2 style='color:green'>Done! New Systems Initialized! <br><a href='index.php'>Go Home</a></h2>";
} catch(Exception $e) { echo "Error: " . $e->getMessage(); }
?>
