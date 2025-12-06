<?php
require_once 'config.php';
require_once 'security.php';

if (isLoggedIn()) {
    redirectTo('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            $stmt = mysqli_prepare($conn, "SELECT attempt_time FROM login_attempts WHERE username = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE) AND success = FALSE");
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            
            if (mysqli_stmt_num_rows($stmt) >= 5) {
                $error = 'Too many failed login attempts. Please try again in 15 minutes.';
                mysqli_stmt_close($stmt);
            } else {
                mysqli_stmt_close($stmt);
                
                $stmt = mysqli_prepare($conn, "SELECT id, username, password FROM users WHERE username = ?");
                mysqli_stmt_bind_param($stmt, "s", $username);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                if ($row = mysqli_fetch_assoc($result)) {
                    if (verifyPassword($password, $row['password'])) {
                        $stmt = mysqli_prepare($conn, "INSERT INTO login_attempts (username, ip_address, success) VALUES (?, ?, TRUE)");
                        mysqli_stmt_bind_param($stmt, "ss", $username, $ipAddress);
                        mysqli_stmt_execute($stmt);
                        
                        $stmt = mysqli_prepare($conn, "UPDATE users SET last_login = NOW() WHERE id = ?");
                        mysqli_stmt_bind_param($stmt, "i", $row['id']);
                        mysqli_stmt_execute($stmt);
                        
                        $_SESSION['user_id'] = $row['id'];
                        $_SESSION['username'] = $row['username'];
                        session_regenerate_id(true);
                        
                        redirectTo('dashboard.php');
                    } else {
                        $error = 'Invalid username or password.';
                        $stmt = mysqli_prepare($conn, "INSERT INTO login_attempts (username, ip_address, success) VALUES (?, ?, FALSE)");
                        mysqli_stmt_bind_param($stmt, "ss", $username, $ipAddress);
                        mysqli_stmt_execute($stmt);
                    }
                } else {
                    $error = 'Invalid username or password.';
                    $stmt = mysqli_prepare($conn, "INSERT INTO login_attempts (username, ip_address, success) VALUES (?, ?, FALSE)");
                    mysqli_stmt_bind_param($stmt, "ss", $username, $ipAddress);
                    mysqli_stmt_execute($stmt);
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HYPE Distributor Portal</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <div class="logo">
                <h1>HYPE</h1>
            </div>
            
            <h2>Distributor Login</h2>
            
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                           required maxlength="50" autocomplete="username">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" 
                           required autocomplete="current-password">
                </div>
                
                <button type="submit" class="btn-primary">Log In</button>
            </form>
            
            <p class="form-footer">
                Don't have an account? <a href="signup.php">Sign up here</a>
            </p>
        </div>
    </div>
</body>
</html>