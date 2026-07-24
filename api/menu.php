<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'get';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($action === 'get') {
    $stmt = $pdo->query("SELECT * FROM menu ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')");
    $menu = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => $menu]);
} elseif ($action === 'update' && $_SESSION['user_role'] === 'admin') {
    // Admin updates menu (simplified implementation)
    $id = $_POST['id'];
    $items = $_POST['items'];
    $stmt = $pdo->prepare("UPDATE menu SET items = ? WHERE id = ?");
    if ($stmt->execute([$items, $id])) {
        echo json_encode(['status' => 'success', 'message' => 'Menu updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update menu']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>
