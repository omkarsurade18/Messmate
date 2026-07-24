<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($action === 'add_menu') {
    $day = $_POST['day_of_week'];
    $type = $_POST['meal_type'];
    $items = $_POST['items'];
    
    // Fixed images from internet 
    $fixed_images = [
        'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=500&q=80',
        'https://images.unsplash.com/photo-1589302168068-964664d93dc0?w=500&q=80',
        'https://images.unsplash.com/photo-1528207776546-3221b2bb20d6?w=500&q=80',
        'https://images.unsplash.com/photo-1512152272829-410aabe2ba33?w=500&q=80',
        'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=500&q=80'
    ];
    $img = $fixed_images[array_rand($fixed_images)];

    $stmt = $pdo->prepare("INSERT INTO menu (day_of_week, meal_type, items, image_url) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$day, $type, $items, $img])) {
        echo json_encode(['status' => 'success', 'message' => 'Menu added!', 'redirect' => 'admin.php']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add menu']);
    }
} elseif ($action === 'delete_menu') {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM menu WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo json_encode(['status' => 'success', 'message' => 'Menu deleted!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete']);
    }
} elseif ($action === 'get_stats') {
    $stats = [];
    $stats['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['orders'] = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $stats['revenue'] = $pdo->query("SELECT SUM(amount) FROM payments")->fetchColumn() ?: 0;
    echo json_encode(['status' => 'success', 'data' => $stats]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>
