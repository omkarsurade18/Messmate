<?php 
session_start();
require 'api/db.php';
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
if($_SESSION['user_role'] === 'admin') { header("Location: admin.php"); exit; }

$user_id = $_SESSION['user_id'];
$day = date('l'); 

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?"); $stmt->execute([$user_id]); $user = $stmt->fetch();

$stmt = $pdo->prepare("SELECT s.*, p.name as plan_name FROM subscriptions s JOIN plans p ON s.plan_id = p.id WHERE s.user_id = ? AND s.status = 'active' AND s.end_date >= CURDATE()");
$stmt->execute([$user_id]);
$active_sub = $stmt->fetch();

$pending_sub = $pdo->prepare("SELECT id FROM subscriptions WHERE user_id=? AND status='pending'");
$pending_sub->execute([$user_id]);
$pending_sub = $pending_sub->fetch();

$poll = $pdo->query("SELECT * FROM polls WHERE active = 1 ORDER BY id DESC LIMIT 1")->fetch();

$today_menu = $pdo->query("SELECT * FROM menu WHERE day_of_week='$day'")->fetchAll();
$all_menu = $pdo->query("SELECT * FROM menu ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")->fetchAll();

// Fetch System Settings & Notices
$settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$notices = $pdo->query("SELECT * FROM notices ORDER BY created_at DESC")->fetchAll();
$active_count = $pdo->query("SELECT COUNT(*) FROM subscriptions WHERE status='active' AND end_date >= CURDATE()")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Smart Mess</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<body>
    <nav>
        <a href="index.php" class="logo">SMART MESS</a>
        <ul class="nav-links">
            <li><a href="#" id="logout-btn" class="btn btn-secondary">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        
        <div style="display:flex; gap:1.5rem; margin-bottom: 2rem; flex-wrap: wrap;">
            <div class="card" style="flex:1; min-width:200px; padding:1.5rem; border-top: 4px solid var(--primary);">
                <h4 style="color:var(--text-muted); font-size: 0.8rem;">CHAIRS AVAILABLE</h4>
                <p style="font-size: 1.8rem; font-weight:800;"><?= max(0, (int)$settings['max_seats'] - (int)$active_count) ?> / <?= $settings['max_seats'] ?></p>
            </div>
            <div class="card" style="flex:2; min-width:300px; padding:1.5rem; border-top: 4px solid var(--success);">
                <h4 style="color:var(--text-muted); font-size: 0.8rem;">MESS TIMINGS (SET BY ADMIN)</h4>
                <p style="font-size: 1.2rem; font-weight:600; margin-top:5px;"><?= htmlspecialchars($settings['mess_timing']) ?></p>
            </div>
        </div>

        <?php if(!empty($notices)): ?>
        <div class="card" style="margin-bottom: 2.5rem; background: var(--primary-light); border: 2px solid var(--primary);">
            <h3 style="color:var(--primary); margin-bottom:1rem;">📢 NOTICE BOARD</h3>
            <?php foreach($notices as $n): ?>
                <div style="padding: 1rem 0; border-bottom: 1px solid rgba(0,0,0,0.1);">
                    <h4 style="color:var(--text-dark);"><?= htmlspecialchars($n['title']) ?></h4>
                    <p style="color:var(--text-muted); font-size: 0.95rem;"><?= nl2br(htmlspecialchars($n['content'])) ?></p>
                    <small style="color:var(--text-muted); opacity: 0.7;"><?= $n['created_at'] ?></small>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if($today_menu): ?>
        <div class="today-special-banner">
            <div class="banner-content">
                <p class="banner-subtitle">FRESHLY PREPARED</p>
                <h1><?= strtoupper($day) ?>'S MENU</h1>
            </div>
            <div class="banner-items">
                <?php foreach($today_menu as $tm): ?>
                    <h2><span class="dot"></span> <?= ucfirst($tm['meal_type']) ?>: <span><?= htmlspecialchars($tm['items']) ?></span></h2>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if($pending_sub): ?>
            <div class="notice-banner">
                ⏳ You have a Pending Subscription. Please complete your payment physically to activate dining!
            </div>
        <?php endif; ?>

        <div class="tabs">
            <button class="tab-btn active" data-target="tab-plans">Food Pass</button>
            <button class="tab-btn" data-target="tab-menu">Full Menu</button>
            <?php if($active_sub): ?>
                <button class="tab-btn" data-target="tab-pause">Pause Meals</button>
                <button class="tab-btn" data-target="tab-chat">Community</button>
            <?php endif; ?>
            <button class="tab-btn" data-target="tab-feedback">Feedback</button>
        </div>

        <!-- TAB: PLANS -->
        <div class="tab-content active" id="tab-plans">
            <?php if($active_sub): ?>
                <div class="card active-sub-card">
                    <h2>✅ <?= htmlspecialchars($active_sub['plan_name']) ?> Active</h2>
                    <p>Valid until <strong><?= $active_sub['end_date'] ?></strong>. Just walk into the mess and present your ID.</p>
                    <form action="api/user_api.php" method="POST" class="ajax-form mt-auto">
                        <input type="hidden" name="action" value="cancel_sub">
                        <button type="submit" class="btn btn-danger">Cancel Plan</button>
                    </form>
                </div>
            <?php else: ?>
                <h2 class="section-title">Select a Food Pass</h2>
                <div class="grid">
                    <?php $plans = $pdo->query("SELECT * FROM plans")->fetchAll(); foreach($plans as $plan): ?>
                    <div class="card">
                        <h3 class="plan-name"><?= htmlspecialchars($plan['name']) ?></h3>
                        <div class="price-tag">₹<?= $plan['price'] ?></div>
                        <p class="plan-desc"><?= htmlspecialchars($plan['description']) ?></p>
                        <form action="api/plans.php" method="POST" class="ajax-form mt-auto">
                            <input type="hidden" name="action" value="subscribe">
                            <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                            <button type="submit" class="btn btn-primary" style="width:100%;">Buy This Pass</button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- TAB: MENU -->
        <div class="tab-content" id="tab-menu">
            <h2 class="section-title">Weekly Schedule</h2>
            <div class="hotel-menu-list">
                <?php foreach($all_menu as $m): ?>
                <div class="menu-row">
                    <img class="menu-img" src="<?= htmlspecialchars($m['image_url']) ?>" alt="Food">
                    <div class="menu-info">
                        <h4><?= $m['day_of_week'] ?> <span class="meal-badge"><?= ucfirst($m['meal_type']) ?></span></h4>
                        <p><?= htmlspecialchars($m['items']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- TAB: PAUSE -->
        <div class="tab-content" id="tab-pause">
            <div class="card" style="max-width:500px">
                <h2>Pause Dining</h2>
                <p class="text-muted" style="margin-bottom:1.5rem">Going home? Pause your meals for a specific date range so food isn't wasted.</p>
                <form action="api/user_api.php" method="POST" class="ajax-form">
                    <input type="hidden" name="action" value="pause_meal">
                    <div class="input-group">
                        <label>Start Date</label>
                        <input type="date" name="pause_start" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                    </div>
                    <div class="input-group">
                        <label>End Date</label>
                        <input type="date" name="pause_end" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%">Pause Meals</button>
                </form>
            </div>

            <div class="card" style="margin-top:2rem">
                <h3>Manage Your Pauses</h3>
                <p class="text-muted" style="margin-bottom:1.5rem">Click the cross to cancel a specific pause date.</p>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <?php 
                    $user_pauses = $pdo->prepare("SELECT DISTINCT pause_date FROM meal_pauses WHERE user_id = ? AND pause_date >= CURDATE() ORDER BY pause_date ASC");
                    $user_pauses->execute([$user_id]);
                    $upauses = $user_pauses->fetchAll(PDO::FETCH_COLUMN);
                    foreach($upauses as $up): ?>
                        <div style="background:var(--primary-light); color:var(--primary); padding:8px 15px; border-radius:12px; display:flex; align-items:center; gap:10px; font-weight:700;">
                            <?= $up ?>
                            <form action="api/user_api.php" method="POST" class="ajax-form" style="display:inline;">
                                <input type="hidden" name="action" value="delete_pause">
                                <input type="hidden" name="pause_date" value="<?= $up ?>">
                                <button type="submit" style="background:none; border:none; color:var(--danger); cursor:pointer; font-size:1.2rem; line-height:1">×</button>
                            </form>
                        </div>
                    <?php endforeach; if(empty($upauses)) echo "<p class='text-muted'>No upcoming pauses.</p>"; ?>
                </div>
            </div>
        </div>

        <!-- TAB: CHAT -->
        <div class="tab-content" id="tab-chat">
            <div class="chat-container">
                <div class="chat-header">
                    <h3>Community</h3>
                    <select id="chat_receiver" onchange="loadChat()">
                        <option value="">🌎 Global Room</option>
                        <option value="1">🔒 Direct to Admin</option>
                    </select>
                </div>
                <div class="chat-box" id="global-chatbox"></div>
                <div class="chat-input-area">
                    <input type="text" id="chat-msg-input" placeholder="Type a message...">
                    <button class="btn btn-primary" onclick="sendChat()">Send</button>
                </div>
            </div>
        </div>

        <!-- TAB: FEEDBACK -->
        <div class="tab-content" id="tab-feedback">
            <div class="grid">
                <div class="card">
                    <h3>Rate Your Experience</h3>
                    <p class="text-muted" style="margin-bottom: 1rem">Help us improve the food quality.</p>
                    <form action="api/user_api.php" method="POST" class="ajax-form">
                        <input type="hidden" name="action" value="submit_rating">
                        
                        <div class="rating-stars">
                            <input type="radio" id="star5" name="rating" value="5"><label for="star5">★</label>
                            <input type="radio" id="star4" name="rating" value="4"><label for="star4">★</label>
                            <input type="radio" id="star3" name="rating" value="3"><label for="star3">★</label>
                            <input type="radio" id="star2" name="rating" value="2"><label for="star2">★</label>
                            <input type="radio" id="star1" name="rating" value="1"><label for="star1">★</label>
                        </div>

                        <div class="input-group" style="margin-top:1rem">
                            <label>Additional Comments</label>
                            <textarea name="comment" rows="3" placeholder="Tell us more..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-secondary">Submit Feedback</button>
                    </form>
                </div>

                <?php if($poll): 
                    $pid = $poll['id'];
                    $v1 = $pdo->query("SELECT COUNT(*) FROM poll_votes WHERE poll_id=$pid AND choice=1")->fetchColumn();
                    $v2 = $pdo->query("SELECT COUNT(*) FROM poll_votes WHERE poll_id=$pid AND choice=2")->fetchColumn();
                    $uv = $pdo->prepare("SELECT id FROM poll_votes WHERE poll_id=? AND user_id=?"); $uv->execute([$pid, $user_id]); $hasVoted = $uv->fetch();
                ?>
                <div class="card active-poll-card">
                    <h3>Live Poll: <?= htmlspecialchars($poll['question']) ?></h3>
                    <?php if(!$hasVoted): ?>
                    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:20px">
                        <button class="btn btn-secondary" onclick="votePoll(<?= $poll['id'] ?>, 1)"><?= htmlspecialchars($poll['option_1']) ?></button>
                        <button class="btn btn-secondary" onclick="votePoll(<?= $poll['id'] ?>, 2)"><?= htmlspecialchars($poll['option_2']) ?></button>
                    </div>
                    <?php else: ?>
                        <div class="poll-results">
                            <div style="margin-bottom:10px; display:flex; justify-content:space-between;">
                                <span><?= htmlspecialchars($poll['option_1']) ?></span>
                                <strong><?= $v1 ?> votes</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between;">
                                <span><?= htmlspecialchars($poll['option_2']) ?></span>
                                <strong><?= $v2 ?> votes</strong>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <script src="assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            loadChat(); setInterval(loadChat, 4000);
        });

        async function votePoll(pid, choice) {
            let fd = new FormData(); fd.append('action', 'vote_poll'); fd.append('poll_id', pid); fd.append('choice', choice);
            let r = await fetch('api/chat_api.php', {method:'POST', body:fd});
            let j = await r.json(); showToast(j.message, j.status);
            setTimeout(()=>window.location.reload(), 1000);
        }

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
