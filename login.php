<?php 
session_start();
if(isset($_SESSION['user_id'])) header("Location: dashboard.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Mess</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="bg-circles">
        <li></li><li></li><li></li><li></li><li></li>
    </div>

    <nav>
        <a href="index.php" class="logo">🍽️ Smart Mess</a>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="register.php" class="btn btn-glass">Register</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="form-container glass-panel">
            <h2>Welcome Back</h2>
            <form action="api/auth.php" method="POST" class="ajax-form">
                <input type="hidden" name="action" value="login">
                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="example@email.com">
                </div>
                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            <div class="form-footer">
                <p>Don't have an account? <a href="register.php">Sign up here</a></p>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
