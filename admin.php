<?php 
session_start();
require 'api/db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { header("Location: index.php"); exit; }

// Fetch System Config
$settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$notices = $pdo->query("SELECT * FROM notices ORDER BY created_at DESC")->fetchAll();
$active_count = $pdo->query("SELECT COUNT(*) FROM subscriptions WHERE status='active' AND end_date >= CURDATE()")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Panel - Smart Mess</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<body>
    <nav>
        <a href="index.php" class="logo">SMART MESS <span>ADMIN</span></a>
        <ul class="nav-links">
            <li><a href="#" id="logout-btn" class="btn btn-secondary">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="tabs">
            <button class="tab-btn active" data-target="tab-aprv">Approvals</button>
            <button class="tab-btn" data-target="tab-subscribers">Master Directory</button>
            <button class="tab-btn" data-target="tab-notices">Notice Board</button>
            <button class="tab-btn" data-target="tab-menu">Menu Editor</button>
            <button class="tab-btn" data-target="tab-config">System Config</button>
            <button class="tab-btn" data-target="tab-history">Subscription History</button>
            <button class="tab-btn" data-target="tab-chat">Community</button>
            <button class="tab-btn" data-target="tab-feedback">Ratings</button>
        </div>

        <!-- TAB: APPROVALS -->
        <div class="tab-content active" id="tab-aprv">
            <div class="card">
                <h3>Pending Subscriptions</h3>
                <div class="table-container">
                    <table>
                        <thead><tr><th>User Info</th><th>Plan</th><th>Date Requested</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php
                            $subs = $pdo->query("SELECT s.id, u.name, u.phone, p.name as plan, s.start_date FROM subscriptions s JOIN users u ON s.user_id=u.id JOIN plans p ON s.plan_id=p.id WHERE s.status='pending'")->fetchAll();
                            foreach($subs as $s): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($s['name']) ?></strong><br><small><?= htmlspecialchars($s['phone']) ?></small></td>
                                <td><?= htmlspecialchars($s['plan']) ?></td>
                                <td><?= htmlspecialchars($s['start_date']) ?></td>
                                <td style="display:flex; gap:10px">
                                    <button class="btn btn-primary" onclick="approveSub(<?= $s['id'] ?>)" style="padding:0.6rem 1rem; font-size:0.8rem">Confirm</button>
                                    <button class="btn btn-danger" onclick="rejectSub(<?= $s['id'] ?>)" style="padding:0.6rem 1rem; font-size:0.8rem">Reject</button>
                                </td>
                            </tr>
                            <?php endforeach;
                            if(empty($subs)) echo "<tr><td colspan='4'>No pending approvals.</td></tr>";
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-content" id="tab-subscribers">
            <div class="card">
                <h3>Active Members & Subscription History</h3>
                <div class="table-container">
                    <table>
                        <thead><tr><th>User</th><th>Active Plan</th><th>Start Date</th><th>End Date</th><th>Pauses</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php
                            $users_list = $pdo->query("SELECT u.*, s.status as sub_status, s.start_date, s.end_date, p.name as plan_name 
                                                      FROM users u 
                                                      LEFT JOIN subscriptions s ON u.id = s.user_id AND s.status IN ('active', 'pending')
                                                      LEFT JOIN plans p ON s.plan_id = p.id
                                                      ORDER BY u.role ASC, s.status DESC")->fetchAll();
                            foreach($users_list as $u): 
                                $pauses = $pdo->prepare("SELECT DISTINCT pause_date FROM meal_pauses WHERE user_id = ? ORDER BY pause_date ASC");
                                $pauses->execute([$u['id']]);
                                $pause_list = $pauses->fetchAll(PDO::FETCH_COLUMN);
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($u['name']) ?></strong><br><small><?= htmlspecialchars($u['email']) ?></small></td>
                                <td><?= $u['plan_name'] ? '<span class="meal-badge">'.strtoupper($u['sub_status']).'</span>' : 'None' ?></td>
                                <td><?= $u['start_date'] ?? '-' ?></td>
                                <td><?= $u['end_date'] ?? '-' ?></td>
                                <td style="max-width:200px; font-size:0.8rem;"><?= !empty($pause_list) ? implode(", ", $pause_list) : 'No pauses' ?></td>
                                <td>
                                    <?php if($u['id'] !== 1): ?>
                                    <form action="api/admin_extras_api.php" method="POST" class="ajax-form">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="btn btn-danger" style="padding: 0.4rem 1rem; font-size:0.8rem">Delete</button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB: MENU EDITOR -->
        <div class="tab-content" id="tab-menu">
            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem">
                    <div>
                        <h3>Weekly Menu Editor</h3>
                        <p class="text-muted">Update dishes manually or shuffle them all at once.</p>
                    </div>
                    <button class="btn btn-primary" onclick="shuffleMenu()" style="background:linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);">🎲 Shuffle All Dishes</button>
                </div>
                <div class="table-container">
                    <table>
                        <thead><tr><th>Day</th><th>Meal Type</th><th>Items / Dishes</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php 
                            $menu_items = $pdo->query("SELECT * FROM menu ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), meal_type ASC")->fetchAll();
                            foreach($menu_items as $mi): ?>
                            <tr>
                                <td><strong><?= $mi['day_of_week'] ?></strong></td>
                                <td><span class="meal-badge"><?= strtoupper($mi['meal_type']) ?></span></td>
                                <td colspan="2">
                                    <form action="api/admin_extras_api.php" method="POST" class="ajax-form" style="display:flex; align-items:center; gap:15px; margin:0; width:100%">
                                        <img src="<?= htmlspecialchars($mi['image_url']) ?>" style="width:50px; height:50px; border-radius:10px; object-fit:cover; border:1px solid #ddd; flex-shrink:0;">
                                        <input type="hidden" name="action" value="update_menu_item">
                                        <input type="hidden" name="id" value="<?= $mi['id'] ?>">
                                        <select name="items" style="flex:1; padding:0.6rem; border:1px solid #ddd; border-radius:12px; background:var(--bg-color); color:var(--text-dark);">
                                            <?php 
                                            $dishes = [
                                                'Dal Tadka & Steamed Rice', 'Paneer Masala & Roti', 'Aloo Gobi & Roti',
                                                'Veg Biryani & Raita', 'Chole Bhature', 'Rajma Chawal', 'Kadhi Chawal',
                                                'Mixed Veg & Roti', 'Pav Bhaji', 'Special Veg Thali', 'Poori Bhaji',
                                                'Masala Dosa', 'Idli Sambar', 'Egg Curry & Rice'
                                            ];
                                            foreach($dishes as $d): ?>
                                                <option value="<?= $d ?>" <?= $mi['items'] == $d ? 'selected' : '' ?>><?= $d ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-success" style="padding:0.6rem 1.2rem; font-size:0.85rem; border-radius:10px; flex-shrink:0;">Update</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB: NOTICE BOARD -->
        <div class="tab-content" id="tab-notices">
            <div class="grid">
                <div class="card">
                    <h3>Publish New Notice</h3>
                    <form action="api/admin_extras_api.php" method="POST" class="ajax-form">
                        <input type="hidden" name="action" value="add_notice">
                        <div class="input-group">
                            <label>Notice Title</label>
                            <input type="text" name="title" required placeholder="e.g. Mess Closed Tomorrow">
                        </div>
                        <div class="input-group">
                            <label>Content</label>
                            <textarea name="content" rows="4" required placeholder="Details..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width:100%">Post Notice</button>
                    </form>
                </div>
                <div class="card">
                    <h3>Current Notices</h3>
                    <div class="table-container">
                        <table>
                            <thead><tr><th>Notice</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php foreach($notices as $n): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($n['title']) ?></strong><br><small><?= substr($n['content'], 0, 50) ?>...</small></td>
                                    <td>
                                        <form action="api/admin_extras_api.php" method="POST" class="ajax-form">
                                            <input type="hidden" name="action" value="delete_notice">
                                            <input type="hidden" name="id" value="<?= $n['id'] ?>">
                                            <button type="submit" class="btn btn-danger" style="padding:0.4rem">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: SYSTEM CONFIG -->
        <div class="tab-content" id="tab-config">
            <div class="card" style="max-width: 600px;">
                <h3>System Global Settings</h3>
                <p class="text-muted" style="margin-bottom: 2rem;">Configure core mess parameters visible to all users.</p>
                <form action="api/admin_extras_api.php" method="POST" class="ajax-form">
                    <input type="hidden" name="action" value="save_settings">
                    <div class="input-group">
                        <label>Mess Timings (Visible to Users)</label>
                        <input type="text" name="mess_timing" value="<?= htmlspecialchars($settings['mess_timing']) ?>" required>
                    </div>
                    <div class="input-group">
                        <label>Total Seat Capacity</label>
                        <input type="number" name="max_seats" value="<?= $settings['max_seats'] ?>" required max="800">
                        <small>Note: Maximum allowed is 800 seats.</small>
                    </div>
                    <button type="submit" class="btn btn-success" style="width:100%">Save Configuration</button>
                </form>
            </div>
        </div>

            </div>
        </div>

        <!-- TAB: SUBSCRIPTION HISTORY -->
        <div class="tab-content" id="tab-history">
            <div class="card">
                <h3>Global Subscription Audit Log</h3>
                <p class="text-muted">History of every subscription record created in the system.</p>
                <div class="table-container" style="margin-top:1.5rem">
                    <table>
                        <thead><tr><th>User</th><th>Plan Name</th><th>Start Date</th><th>End Date</th><th>Final Status</th></tr></thead>
                        <tbody>
                            <?php
                            $history = $pdo->query("SELECT s.*, u.name, u.email, p.name as plan_name 
                                                   FROM subscriptions s 
                                                   JOIN users u ON s.user_id = u.id 
                                                   JOIN plans p ON s.plan_id = p.id 
                                                   ORDER BY s.id DESC")->fetchAll();
                            foreach($history as $h): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($h['name']) ?></strong><br><small><?= htmlspecialchars($h['email']) ?></small></td>
                                <td><?= htmlspecialchars($h['plan_name']) ?></td>
                                <td><?= $h['start_date'] ?></td>
                                <td><?= $h['end_date'] ?></td>
                                <td><span class="meal-badge" style="<?= $h['status']=='cancelled'?'background:#fee2e2;color:#ef4444':'' ?>"><?= strtoupper($h['status']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB: CHAT & POLLS -->
        <div class="tab-content" id="tab-chat">
            <div class="grid">
                
                <div class="chat-container">
                    <div class="chat-header">
                        <h3>Mess Comms</h3>
                        <select id="chat_receiver" onchange="loadChat()">
                            <option value="">🌎 Global Room</option>
                            <?php foreach($users as $u): if($u['id']!=1): ?>
                                <option value="<?= $u['id'] ?>">🔒 <?= htmlspecialchars($u['name']) ?></option>
                            <?php endif; endforeach; ?>
                        </select>
                    </div>
                    <div class="chat-box" id="global-chatbox"></div>
                    <div class="chat-input-area">
                        <input type="text" id="chat-msg-input" placeholder="Broadcast a message...">
                        <button class="btn btn-primary" onclick="sendChat()">Send</button>
                    </div>
                </div>

                <div>
                    <div class="card" style="margin-bottom:1.5rem">
                        <h3>Quick System Poll</h3>
                        <form action="api/admin_extras_api.php" method="POST" class="ajax-form">
                            <input type="hidden" name="action" value="create_poll">
                            <input type="hidden" name="question" value="Do you want a Fixed Menu or Customized Menu?">
                            <input type="hidden" name="option_1" value="Fixed Menu">
                            <input type="hidden" name="option_2" value="Customize Menu">
                            <button type="submit" class="btn btn-primary" style="width:100%; margin-bottom:1rem">🚀 Push: Fixed vs Customize</button>
                        </form>
                        <h4 style="margin-bottom:10px">Custom Poll:</h4>
                        <form action="api/admin_extras_api.php" method="POST" class="ajax-form">
                            <input type="hidden" name="action" value="create_poll">
                            <div class="input-group"><input type="text" name="question" required placeholder="Question block..."></div>
                            <div class="input-group"><input type="text" name="option_1" required placeholder="Option 1"></div>
                            <div class="input-group"><input type="text" name="option_2" required placeholder="Option 2"></div>
                            <button type="submit" class="btn btn-secondary" style="width:100%">Broadcast Custom Poll</button>
                        </form>
                    </div>

                    <?php 
                    $poll = $pdo->query("SELECT * FROM polls WHERE active=1 ORDER BY id DESC LIMIT 1")->fetch();
                    if($poll): 
                        $pid = $poll['id'];
                        $v1 = $pdo->query("SELECT COUNT(*) FROM poll_votes WHERE poll_id=$pid AND choice=1")->fetchColumn();
                        $v2 = $pdo->query("SELECT COUNT(*) FROM poll_votes WHERE poll_id=$pid AND choice=2")->fetchColumn();
                    ?>
                        <div class="card active-poll-card">
                            <h4>LIVE: <?= htmlspecialchars($poll['question']) ?></h4>
                            <p><span><?= htmlspecialchars($poll['option_1']) ?></span> <strong><?= $v1 ?> votes</strong></p>
                            <p><span><?= htmlspecialchars($poll['option_2']) ?></span> <strong><?= $v2 ?> votes</strong></p>
                            <form action="api/admin_extras_api.php" method="POST" class="ajax-form" style="margin-top: 1.5rem;">
                                <input type="hidden" name="action" value="delete_poll">
                                <input type="hidden" name="poll_id" value="<?= $pid ?>">
                                <button type="submit" class="btn btn-danger" style="width:100%">Terminate Poll</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- TAB: FEEDBACK -->
        <div class="tab-content" id="tab-feedback">
            <div class="card">
                <h3>Customer Feedback & Ratings</h3>
                <div class="table-container">
                    <table>
                        <thead><tr><th>User</th><th>Rating</th><th>Comment</th><th>Date</th></tr></thead>
                        <tbody>
                            <?php
                            try {
                                $ratings = $pdo->query("SELECT r.*, u.name FROM ratings r JOIN users u ON r.user_id=u.id ORDER BY r.created_at DESC")->fetchAll();
                                foreach($ratings as $r): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($r['name']) ?></strong></td>
                                    <td class="star-display"><?= str_repeat("★", $r['rating']) ?><span style="color:#ddd"><?= str_repeat("★", 5-$r['rating']) ?></span></td>
                                    <td><?= htmlspecialchars($r['comment']) ?></td>
                                    <td class="text-muted"><?= $r['created_at'] ?></td>
                                </tr>
                                <?php endforeach; 
                                if(empty($ratings)) echo "<tr><td colspan='4'>No feedback received yet.</td></tr>";
                            } catch(Exception $e) {
                                echo "<tr><td colspan='4'>Please run setup_menu.php to initialize ratings table.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script src="assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            loadChat(); setInterval(loadChat, 4000);
        });

        async function approveSub(id) {
            let fd = new FormData(); fd.append('action', 'approve_sub'); fd.append('sub_id', id);
            let r = await fetch('api/admin_extras_api.php', {method:'POST', body:fd});
            let j = await r.json(); showToast(j.message, j.status);
            setTimeout(()=>window.location.reload(), 1000);
        }

        async function rejectSub(id) {
            if(!confirm("Are you sure you want to REJECT this subscription?")) return;
            let fd = new FormData(); fd.append('action', 'reject_sub'); fd.append('sub_id', id);
            let r = await fetch('api/admin_extras_api.php', {method:'POST', body:fd});
            let j = await r.json(); showToast(j.message, j.status);
            setTimeout(()=>window.location.reload(), 1000);
        }

        async function shuffleMenu() {
            if(!confirm("Are you sure you want to SHUFFLE the entire weekly menu randomly?")) return;
            let fd = new FormData(); fd.append('action', 'shuffle_menu');
            let r = await fetch('api/admin_extras_api.php', {method:'POST', body:fd});
            let j = await r.json(); showToast(j.message, j.status);
            setTimeout(()=>window.location.reload(), 1000);
        }

        /* Chat logic */
        function loadChat() {
            if(!document.getElementById('tab-chat').classList.contains('active')) return;
            let recv = document.getElementById('chat_receiver').value;
            fetchData(`api/chat_api.php?action=get_messages&receiver_id=${recv}`, res => {
                if(res.status==='success') {
                    let box = document.getElementById('global-chatbox');
                    box.innerHTML = res.html; box.scrollTop = box.scrollHeight;
                }
            });
        }
        async function sendChat() {
            let ipt = document.getElementById('chat-msg-input');
            let recv = document.getElementById('chat_receiver').value;
            if(!ipt.value) return;
            let fd = new FormData(); fd.append('action', 'send_message'); fd.append('message', ipt.value); fd.append('receiver_id', recv);
            ipt.value = ''; await fetch('api/chat_api.php', {method:'POST', body:fd}); loadChat();
        }
    </script>
</body>
</html>
