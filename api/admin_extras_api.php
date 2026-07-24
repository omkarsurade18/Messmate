<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { exit; }

if ($action === 'approve_sub') {
    $sub_id = $_POST['sub_id'];
    $stmt = $pdo->prepare("UPDATE subscriptions SET status='active' WHERE id=?");
    $stmt->execute([$sub_id]);
    // Also mark related payment completed
    $pdo->query("UPDATE payments p JOIN subscriptions s ON p.user_id = s.user_id SET p.status='completed' WHERE s.id = $sub_id");
    echo json_encode(['status' => 'success', 'message' => 'Payment received & Plan Activated!']);
} elseif ($action === 'create_poll') {
    $q = $_POST['question'];
    $o1 = $_POST['option_1'];
    $o2 = $_POST['option_2'];
    $pd = date('Y-m-d');
    
    // Deactivate old polls
    $pdo->query("UPDATE polls SET active=FALSE");
    
    $stmt = $pdo->prepare("INSERT INTO polls (question, option_1, option_2, poll_date) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$q, $o1, $o2, $pd])) {
        echo json_encode(['status' => 'success', 'message' => 'Poll published!']);
    }
} elseif ($action === 'delete_poll') {
    $poll_id = $_POST['poll_id'];
    $stmt = $pdo->prepare("DELETE FROM polls WHERE id = ?");
    $stmt->execute([$poll_id]);
    echo json_encode(['status' => 'success', 'message' => 'Poll terminated.']);
} elseif ($action === 'add_user') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    if ($stmt->execute([$name, $email, $password])) {
        echo json_encode(['status' => 'success', 'message' => 'Member added!', 'redirect' => 'admin.php']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Email already exists.']);
    }
} elseif ($action === 'delete_user') {
    $id = $_POST['id'];
    if ($id == 1) exit; // Prevent admin deletion
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo json_encode(['status' => 'success', 'message' => 'Member removed.', 'redirect' => 'admin.php']);
    }
} elseif ($action === 'add_notice') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $stmt = $pdo->prepare("INSERT INTO notices (title, content) VALUES (?, ?)");
    if ($stmt->execute([$title, $content])) {
        echo json_encode(['status' => 'success', 'message' => 'Notice published!']);
    }
} elseif ($action === 'delete_notice') {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM notices WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo json_encode(['status' => 'success', 'message' => 'Notice removed!']);
    }
} elseif ($action === 'approve_sub_manual') {
    $id = $_POST['sub_id'];
    $stmt = $pdo->prepare("UPDATE subscriptions SET status = 'active' WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo json_encode(['status' => 'success', 'message' => 'Subscription Activated!']);
    }
} elseif ($action === 'reject_sub') {
    $id = $_POST['sub_id'];
    $stmt = $pdo->prepare("UPDATE subscriptions SET status = 'cancelled' WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo json_encode(['status' => 'success', 'message' => 'Subscription Rejected.']);
    }
} elseif ($action === 'save_settings') {
    $timing = $_POST['mess_timing'];
    $cap = $_POST['max_seats'];
    $stmt1 = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'mess_timing'");
    $stmt2 = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'max_seats'");
    if ($stmt1->execute([$timing]) && $stmt2->execute([$cap])) {
        echo json_encode(['status' => 'success', 'message' => 'System settings updated!']);
    }
} elseif ($action === 'update_menu_item') {
    $id = $_POST['id'];
    $items = $_POST['items'];

    // Simple Standard Mess Dishes & High-Quality Verified Images
    $dish_images = [
        'Dal Tadka & Steamed Rice' => 'https://images.unsplash.com/photo-1546833999-b9f581a1996d',
        'Paneer Masala & Roti' => 'https://images.unsplash.com/photo-1631452180519-c014fe946bc0',
        'Aloo Gobi & Roti' => 'https://images.unsplash.com/photo-1625220194771-7ebdea0b70b9',
        'Veg Biryani & Raita' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8',
        'Chole Bhature' => 'https://images.unsplash.com/photo-1626779843666-4df4f61f774d',
        'Rajma Chawal' => 'https://images.unsplash.com/photo-1546833998-877b37c2e5c4',
        'Kadhi Chawal' => 'https://images.unsplash.com/photo-1589301760014-d929f39ce9b1',
        'Mixed Veg & Roti' => 'https://images.unsplash.com/photo-1512152272829-410aabe2ba33',
        'Pav Bhaji' => 'https://images.unsplash.com/photo-1606491956689-2ea866880c84',
        'Special Veg Thali' => 'https://images.unsplash.com/photo-1585553616435-2dc0a54e271d',
        'Poori Bhaji' => 'https://images.unsplash.com/photo-1601050638911-c3239a6fb39e',
        'Masala Dosa' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd',
        'Idli Sambar' => 'https://images.unsplash.com/photo-1589301973394-82c16d2e5055',
        'Egg Curry & Rice' => 'https://images.unsplash.com/photo-1599487488170-d11ec9c172f0'
    ];

    $image_url = $dish_images[$items] ?? 'https://images.unsplash.com/photo-1546833999-b9f581a1996d';
    $image_url .= "?w=600&q=80";

    $stmt = $pdo->prepare("UPDATE menu SET items = ?, image_url = ? WHERE id = ?");
    if ($stmt->execute([$items, $image_url, $id])) {
        echo json_encode(['status' => 'success', 'message' => 'Menu & Image updated!']);
    }
} elseif ($action === 'shuffle_menu') {
    // Re-use current simple dish mapping
    $dish_names = [
        'Dal Tadka & Steamed Rice', 'Paneer Masala & Roti', 'Aloo Gobi & Roti',
        'Veg Biryani & Raita', 'Chole Bhature', 'Rajma Chawal', 'Kadhi Chawal',
        'Mixed Veg & Roti', 'Pav Bhaji', 'Special Veg Thali'
    ];
    $dish_images = [
        'Dal Tadka & Steamed Rice' => 'https://images.unsplash.com/photo-1546833999-b9f581a1996d',
        'Paneer Masala & Roti' => 'https://images.unsplash.com/photo-1631452180519-c014fe946bc0',
        'Aloo Gobi & Roti' => 'https://images.unsplash.com/photo-1625220194771-7ebdea0b70b9',
        'Veg Biryani & Raita' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8',
        'Chole Bhature' => 'https://images.unsplash.com/photo-1626779843666-4df4f61f774d',
        'Rajma Chawal' => 'https://images.unsplash.com/photo-1546833998-877b37c2e5c4',
        'Kadhi Chawal' => 'https://images.unsplash.com/photo-1589301760014-d929f39ce9b1',
        'Mixed Veg & Roti' => 'https://images.unsplash.com/photo-1512152272829-410aabe2ba33',
        'Pav Bhaji' => 'https://images.unsplash.com/photo-1606491956689-2ea866880c84',
        'Special Veg Thali' => 'https://images.unsplash.com/photo-1585553616435-2dc0a54e271d'
    ];

    $slots = $pdo->query("SELECT id FROM menu")->fetchAll(PDO::FETCH_COLUMN);
    shuffle($dish_names);

    $stmt = $pdo->prepare("UPDATE menu SET items = ?, image_url = ? WHERE id = ?");
    $i = 0;
    foreach ($slots as $sid) {
        $dish = $dish_names[$i % count($dish_names)];
        $img = $dish_images[$dish] . "?w=600&q=80";
        $stmt->execute([$dish, $img, $sid]);
        $i++;
    }
    echo json_encode(['status' => 'success', 'message' => 'Weekly menu shuffled successfully!']);
}
?>
