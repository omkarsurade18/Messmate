<?php 
session_start();
if(isset($_SESSION['user_id'])) header("Location: dashboard.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Smart Mess</title>
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
            <li><a href="login.php" class="btn btn-glass">Login</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="form-container glass-panel">
            <h2>Create Account</h2>
            <form action="api/auth.php" method="POST" class="ajax-form">
                <input type="hidden" name="action" value="register">
                <div class="input-group">
                    <label>Full Name</label>
                    <input type="text" name="name" required placeholder="John Doe">
                </div>
                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="example@email.com">
                </div>
                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-primary">Register</button>
            </form>
            <div class="form-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
