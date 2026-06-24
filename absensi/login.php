<?php
include 'config.php';

if (isset($_SESSION['login']) && $_SESSION['login'] == true) {
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $_SESSION['login'] = true;
        $_SESSION['username'] = $username;
        header("Location: index.php");
        exit();
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Absensi Event</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container login-box">
    <div class="card">
        <h2 style="text-align: center;">🔐 Login Panitia</h2>
        
        <?php if ($error != "") { ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php } ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        
        <p style="text-align: center; margin-top: 15px; font-size: 12px; color: #666;">
            Default: username = admin, password = admin123
        </p>
    </div>
</div>
</body>
</html>