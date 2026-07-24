<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($action === 'get_user_orders') {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY date DESC LIMIT 10");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => $orders]);

} elseif ($action === 'place_order') {
    $meal_type = $_POST['meal_type'] ?? '';
    
    if(!in_array($meal_type, ['lunch', 'dinner'])){
        echo json_encode(['status' => 'error', 'message' => 'Invalid meal type']);
        exit;
    }

    // Check if active subscription exists
    $stmt = $pdo->prepare("SELECT id FROM subscriptions WHERE user_id = ? AND status = 'active' AND end_date >= CURDATE()");
    $stmt->execute([$user_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'You do not have an active subscription']);
        exit;
    }

    $date = date('Y-m-d');
    
    // Check if already ordered today for this meal
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE user_id = ? AND date = ? AND meal_type = ?");
    $stmt->execute([$user_id, $date, $meal_type]);
    if ($stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'You have already placed an order for ' . $meal_type . ' today']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO orders (user_id, date, meal_type, status) VALUES (?, ?, ?, 'placed')");
    if ($stmt->execute([$user_id, $date, $meal_type])) {
        echo json_encode(['status' => 'success', 'message' => ucfirst($meal_type) . ' booked successfully for today!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to place order']);
    }
} elseif ($action === 'get_all_orders' && $_SESSION['user_role'] === 'admin') {
    $stmt = $pdo->query("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.date DESC");
    $orders = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => $orders]);
} elseif ($action === 'update_status' && $_SESSION['user_role'] === 'admin') {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if ($stmt->execute([$status, $order_id])) {
         echo json_encode(['status' => 'success', 'message' => 'Order updated']);
    } else {
         echo json_encode(['status' => 'error', 'message' => 'DB error']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>
