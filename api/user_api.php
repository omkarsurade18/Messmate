<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($action === 'update_profile') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';

    $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
    if ($stmt->execute([$name, $phone, $address, $user_id])) {
        $_SESSION['user_name'] = $name;
        echo json_encode(['status' => 'success', 'message' => 'Profile updated!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update profile']);
    }

} elseif ($action === 'get_payments') {
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);

} elseif ($action === 'pause_meal') {
    $start_date = $_POST['pause_start'] ?? '';
    $end_date = $_POST['pause_end'] ?? '';

    if (empty($start_date) || empty($end_date)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing date range']);
        exit;
    }
    if (strtotime($start_date) <= strtotime('today')) {
        echo json_encode(['status' => 'error', 'message' => 'You can only pause meals for future dates']);
        exit;
    }

    $current = strtotime($start_date);
    $last = strtotime($end_date);
    $count = 0;
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO meal_pauses (user_id, pause_date, meal_type) VALUES (?, ?, 'lunch'), (?, ?, 'dinner')");
    
    while ($current <= $last) {
        $d = date('Y-m-d', $current);
        $stmt->execute([$user_id, $d, $user_id, $d]);
        $current = strtotime('+1 day', $current);
        $count++;
    }
    echo json_encode(['status' => 'success', 'message' => "Successfully paused meals for $count days."]);

} elseif ($action === 'submit_rating') {
    $rating = $_POST['rating'] ?? 5;
    $comment = $_POST['comment'] ?? '';
    $stmt = $pdo->prepare("INSERT INTO ratings (user_id, rating, comment) VALUES (?, ?, ?)");
    if($stmt->execute([$user_id, $rating, $comment])){
        echo json_encode(['status' => 'success', 'message' => 'Thanks for your feedback!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to submit rating.']);
    }
} elseif ($action === 'cancel_sub') {
    $stmt = $pdo->prepare("UPDATE subscriptions SET status = 'cancelled' WHERE user_id = ? AND status IN ('active', 'pending')");
    if ($stmt->execute([$user_id])) {
        echo json_encode(['status' => 'success', 'message' => 'Your subscription was cancelled.', 'redirect' => 'dashboard.php']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to cancel plan']);
    }

} elseif ($action === 'delete_pause') {
    $date = $_POST['pause_date'];
    $stmt = $pdo->prepare("DELETE FROM meal_pauses WHERE user_id = ? AND pause_date = ?");
    if ($stmt->execute([$user_id, $date])) {
        echo json_encode(['status' => 'success', 'message' => 'Pause date removed!']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>
