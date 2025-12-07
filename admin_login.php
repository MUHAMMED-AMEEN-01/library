<?php
session_start();
include 'db_connect.php';

$error = "";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check database
    $sql = "SELECT * FROM admins WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name'] = $username;
        header("Location: admin_entry.php"); // Redirect to dashboard
        exit();
    } else {
        $error = "Invalid Username or Password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login | Rook Library</title>
    <link rel="stylesheet" href="splash-style.css">
    <style>
        body { background-color: #f3f3f3; display: flex; justify-content: center; align-items: center; height: 100vh; font-family: 'Segoe UI', sans-serif; }
        .login-card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #2c3e50; color: white; border: none; padding: 12px; width: 100%; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        button:hover { background-color: #34495e; }
        .error { color: #e74c3c; margin-bottom: 15px; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2 style="color:#2c3e50;">Staff Login</h2>
        <?php if($error) echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
        <p style="margin-top:20px;"><a href="index.html" style="color:#3498db; text-decoration:none;">&larr; Back to Home</a></p>
    </div>
</body>
</html>