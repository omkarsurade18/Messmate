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

if ($action === 'get_plans') {
    $stmt = $pdo->query("SELECT * FROM plans");
    $plans = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => $plans]);

} elseif ($action === 'subscribe') {
    $plan_id = $_POST['plan_id'] ?? '';
    
    // In a real app we redirect to payment gateway right here
    // But for this project we'll directly create a payment record and subscription
    
    // Get plan details
    $stmt = $pdo->prepare("SELECT * FROM plans WHERE id = ?");
    $stmt->execute([$plan_id]);
    $plan = $stmt->fetch();
    
    if (!$plan) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid plan']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Check if user already has an active sub
        $stmt = $pdo->prepare("SELECT id FROM subscriptions WHERE user_id = ? AND status = 'active' AND end_date >= CURDATE()");
        $stmt->execute([$user_id]);
        if ($stmt->fetch()) {
            throw new Exception("You already have an active subscription.");
        }

        // Add payment (pending by default)
        $stmt = $pdo->prepare("INSERT INTO payments (user_id, amount, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$user_id, $plan['price']]);

        // Add Subscription (pending by default)
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime("+" . $plan['duration_days'] . " days"));
        
        $stmt = $pdo->prepare("INSERT INTO subscriptions (user_id, plan_id, start_date, end_date, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$user_id, $plan_id, $start_date, $end_date]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Subscription requested. Please complete payment with Admin.', 'redirect' => 'dashboard.php']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>
