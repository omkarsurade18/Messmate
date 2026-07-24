<?php 
session_start(); 
require 'api/db.php';

// 1. Dynamic Menu (For Subscribers) - Fetched from Database
$all_menu = $pdo->query("SELECT * FROM menu ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), meal_type ASC")->fetchAll();

// 2. Permanent Static Menu (For Public) - Hardcoded Fixed Sample
$static_menu = [
    ['day' => 'Monday', 'type' => 'Lunch', 'dish' => 'Dal Tadka & Steamed Rice', 'img' => 'https://images.unsplash.com/photo-1546833999-b9f581a1996d'],
    ['day' => 'Monday', 'type' => 'Dinner', 'dish' => 'Paneer Masala & Roti', 'img' => 'https://images.unsplash.com/photo-1631452180519-c014fe946bc0'],
    ['day' => 'Tuesday', 'type' => 'Lunch', 'dish' => 'Mixed Veg & Roti', 'img' => 'https://images.unsplash.com/photo-1512152272829-410aabe2ba33'],
    ['day' => 'Tuesday', 'type' => 'Dinner', 'dish' => 'Veg Biryani & Raita', 'img' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8'],
    ['day' => 'Wednesday', 'type' => 'Lunch', 'dish' => 'Chole Bhature', 'img' => 'https://images.unsplash.com/photo-1626779843666-4df4f61f774d'],
    ['day' => 'Wednesday', 'type' => 'Dinner', 'dish' => 'Rajma Chawal', 'img' => 'https://images.unsplash.com/photo-1546833998-877b37c2e5c4'],
    ['day' => 'Thursday', 'type' => 'Lunch', 'dish' => 'Aloo Gobi & Roti', 'img' => 'https://images.unsplash.com/photo-1625220194771-7ebdea0b70b9'],
    ['day' => 'Thursday', 'type' => 'Dinner', 'dish' => 'Kadhi Chawal', 'img' => 'https://images.unsplash.com/photo-1589301760014-d929f39ce9b1'],
    ['day' => 'Friday', 'type' => 'Lunch', 'dish' => 'Pav Bhaji', 'img' => 'https://images.unsplash.com/photo-1606491956689-2ea866880c84'],
    ['day' => 'Friday', 'type' => 'Dinner', 'dish' => 'Special Veg Thali', 'img' => 'https://images.unsplash.com/photo-1585553616435-2dc0a54e271d'],
    ['day' => 'Saturday', 'type' => 'Lunch', 'dish' => 'Poori Bhaji', 'img' => 'https://images.unsplash.com/photo-1601050638911-c3239a6fb39e'],
    ['day' => 'Saturday', 'type' => 'Dinner', 'dish' => 'Masala Dosa', 'img' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd'],
    ['day' => 'Sunday', 'type' => 'Lunch', 'dish' => 'Idli Sambar', 'img' => 'https://images.unsplash.com/photo-1589301973394-82c16d2e5055'],
    ['day' => 'Sunday', 'type' => 'Dinner', 'dish' => 'Egg Curry & Rice', 'img' => 'https://images.unsplash.com/photo-1599487488170-d11ec9c172f0']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Mess Subscription System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <style>
        .hero { display: flex; align-items: center; justify-content: space-between; padding: 4rem 5%; min-height: 80vh; gap: 4rem; flex-wrap: wrap; }
        .hero-text { flex: 1; min-width: 300px; animation: slideInX 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.2); }
        .hero-text h1 { font-size: 4.5rem; font-weight: 800; line-height: 1.1; margin-bottom: 1.5rem; letter-spacing: -2px; color: var(--text-dark); }
        .hero-text h1 span { color: var(--primary); }
        .hero-text p { font-size: 1.2rem; color: var(--text-muted); margin-bottom: 2.5rem; line-height: 1.6; max-width: 500px; }
        .hero-images { flex: 1; display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; animation: fadeInUp 1s ease-out; min-width: 300px; }
        .hero-img { width: 100%; height: 250px; object-fit: cover; border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); transition: 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.2); }
        .hero-img:hover { transform: scale(1.05) translateY(-10px); }
        .hero-img:nth-child(1) { transform: translateY(40px); }
        .hero-img:nth-child(1):hover { transform: translateY(20px) scale(1.05); }

        .features-section { padding: 5rem 5%; background: white; text-align: center; }
        .features-section h2 { font-size: 2.5rem; margin-bottom: 3rem; font-weight: 800; color: var(--text-dark); letter-spacing:-1px;}
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2.5rem; }
        .feature-card { padding: 2.5rem; background: var(--bg-color); border-radius: 24px; transition: 0.3s; border: 1px solid var(--card-border); }
        .feature-card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(79, 70, 229, 0.1); border-color: rgba(79, 70, 229, 0.3);}
        .feature-icon { font-size: 3rem; margin-bottom: 1.5rem; display: inline-block; padding: 1rem; background: var(--card-bg); border-radius: 20px; box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .feature-card h3 { font-size: 1.3rem; margin-bottom: 1rem; color: var(--text-dark); font-weight: 700; }
        .feature-card p { color: var(--text-muted); line-height: 1.6; }

        .public-menu-section { padding: 5rem 5%; background: var(--bg-color); }
        .public-menu-section h2 { font-size: 2.5rem; margin-bottom: 1rem; font-weight: 800; color: var(--text-dark); text-align: center; letter-spacing:-1px;}

        .footer { text-align: center; padding: 3rem 5%; background: var(--text-dark); color: white; margin-top: auto; }
        .footer p { color: rgba(255,255,255,0.6); }

        /* General Button Updates for landing */
        .btn-glass { background: rgba(79, 70, 229, 0.1); color: var(--primary); font-weight: 700; }
        .btn-glass:hover { background: rgba(79, 70, 229, 0.2); transform: translateY(-2px); }
    </style>
</head>
<body>
    <nav>
        <a href="index.php" class="logo">SMART MESS <span>BY HOSTEL</span></a>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="#weekly-menu">View Menu</a></li>
            <?php if(isset($_SESSION['user_id'])): ?>
                <li><a href="<?= $_SESSION['user_role'] === 'admin' ? 'admin.php' : 'dashboard.php' ?>" class="btn btn-primary" style="padding:0.6rem 1.5rem;">Dashboard</a></li>
            <?php else: ?>
                <li><a href="login.php" style="font-weight: 700;">Login</a></li>
                <li><a href="register.php" class="btn btn-glass" style="padding:0.6rem 1.5rem;">Join Now</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <main class="hero">
        <div class="hero-text">
            <h1 class="badge-text" style="font-size:1.2rem; color:var(--primary); margin-bottom:1rem; font-weight:700; letter-spacing:1px; text-transform:uppercase;">🔥 Reimagined Dining</h1>
            <h1>Premium Meals, <br><span>Zero Hassle.</span></h1>
            <p>Subscribe to our elite Mess System and enjoy freshly prepared, authentic Indian cuisine delivered straight from the kitchen. No cooking, no grocery shopping.</p>
            <div style="display:flex; gap:1rem; align-items:center;">
                <a href="<?= isset($_SESSION['user_id']) ? 'dashboard.php' : 'register.php' ?>" class="btn btn-primary" style="padding: 1.2rem 2.5rem; font-size:1.1rem; border-radius:100px; box-shadow: 0 10px 30px rgba(79, 70, 229, 0.3);">Start Dining Today</a>
            </div>
        </div>
        <div class="hero-images" style="display:flex; justify-content:center; align-items:center;">
             <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=700&q=90" alt="Delicious Meal Dish" class="hero-img" style="width:380px; height:460px; border-radius:40px; object-fit:cover;">
        </div>
    </main>

    <section class="features-section">
        <h2>Why You'll Love Smart Mess</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">✨</div>
                <h3>Top-Tier Hygiene</h3>
                <p>Our ingredients are rigorously sanitized and meals are prepared in a state-of-the-art kitchen ensuring safety.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📅</div>
                <h3>Flexible Subscriptions</h3>
                <p>Going home? You can instantly pause your subscription dates from your dashboard freely.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💬</div>
                <h3>Community Chat</h3>
                <p>Interact with the administration and other members privately or on the global community board.</p>
            </div>
        </div>
    </section>

    <!-- DUAL MENU SECTION -->
    <section class="public-menu-section" id="weekly-menu">
        <?php 
        $user_has_sub = false;
        if(isset($_SESSION['user_id'])) {
            $check_sub = $pdo->prepare("SELECT id FROM subscriptions WHERE user_id = ? AND status IN ('active', 'pending')");
            $check_sub->execute([$_SESSION['user_id']]);
            if($check_sub->fetch()) $user_has_sub = true;
        }
        ?>

        <?php if($user_has_sub): ?>
            <h2>Your Live Subscriber Menu</h2>
            <p style="text-align:center; color:var(--text-muted); margin-bottom:3rem; font-size:1.1rem;">This menu is updated live by the mess administration.</p>
            <div class="hotel-menu-list" style="max-width:1200px; margin:0 auto;">
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
        <?php else: ?>
            <h2>Our Weekly Special Menu</h2>
            <p style="text-align:center; color:var(--text-muted); margin-bottom:3rem; font-size:1.1rem;">A sample look at our high-quality weekly fixed offerings.</p>
            <div class="hotel-menu-list" style="max-width:1200px; margin:0 auto;">
                <?php foreach($static_menu as $sm): ?>
                <div class="menu-row">
                    <img class="menu-img" src="<?= $sm['img'] ?>?w=600&q=80" alt="Food">
                    <div class="menu-info">
                        <h4><?= $sm['day'] ?> <span class="meal-badge"><?= $sm['type'] ?></span></h4>
                        <p><?= $sm['dish'] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                <div style="text-align:center; margin-top:3rem;">
                    <a href="register.php" class="btn btn-primary" style="padding:1rem 3rem; border-radius:100px;">Subscribe to View Live Menu Updates</a>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <footer class="footer">
        <h2 style="margin-bottom:10px;">SMART MESS</h2>
        <p>&copy; <?= date('Y') ?> Advanced Mess Solutions. Built for seamless hostel management.</p>
    </footer  >

    <script src="assets/js/main.js"></script>
</body>
</html>
