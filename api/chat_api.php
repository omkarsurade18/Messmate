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
$is_admin = $_SESSION['user_role'] === 'admin';

if ($action === 'send_message') {
    $msg = $_POST['message'] ?? '';
    if (empty($msg)) exit;

    $recv = (isset($_POST['receiver_id']) && $_POST['receiver_id'] !== '') ? $_POST['receiver_id'] : NULL;

    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    if ($stmt->execute([$user_id, $recv, $msg])) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
} elseif ($action === 'get_messages') {
    $recv_filter = $_GET['receiver_id'] ?? '';
    
    if ($recv_filter === '') {
        // Global Chat
        $stmt = $pdo->query("SELECT m.*, u.name, u.role FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.receiver_id IS NULL ORDER BY m.created_at ASC");
        $msgs = $stmt->fetchAll();
    } else {
        // Private Chat
        $stmt = $pdo->prepare("SELECT m.*, u.name, u.role FROM messages m JOIN users u ON m.sender_id = u.id WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?) ORDER BY m.created_at ASC");
        $stmt->execute([$user_id, $recv_filter, $recv_filter, $user_id]);
        $msgs = $stmt->fetchAll();
    }
    
    $html = "";
    foreach ($msgs as $m) {
        $class = ($m['sender_id'] == $user_id) ? 'msg-mine' : 'msg-theirs';
        $sender = ($m['sender_id'] == $user_id) ? 'You' : htmlspecialchars($m['name']);
        $badge = ($m['role'] == 'admin') ? '<span style="background:var(--danger); color:white; padding:2px 5px; border-radius:4px; font-size:0.7rem; margin-right:5px">ADMIN</span>' : '';
        
        $html .= "<div class='msg $class'>";
        $html .= "<div style='font-size:0.8rem; margin-bottom:5px; opacity:0.7; font-weight:bold'>$badge$sender</div>";
        $html .= htmlspecialchars($m['message']);
        $html .= "</div>";
    }
    echo json_encode(['status' => 'success', 'html' => $html]);

} elseif ($action === 'vote_poll') {
    $poll_id = $_POST['poll_id'];
    $choice = $_POST['choice']; // 1 or 2
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO poll_votes (user_id, poll_id, choice) VALUES (?, ?, ?)");
    if ($stmt->execute([$user_id, $poll_id, $choice])) {
         echo json_encode(['status' => 'success', 'message' => 'Vote casted!']);
    } else {
         echo json_encode(['status' => 'error', 'message' => 'You already voted!']);
    }
}
?>
