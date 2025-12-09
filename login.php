<?php
require_once 'security.php';
require_once 'config.php';

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
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } elseif (strlen($username) > 50 || strlen($password) > 128) {
            $error = 'Invalid credentials.';
        } else {
            if (checkLoginAttempts($conn, $username)) {
                $error = 'Too many failed login attempts. Please try again in 15 minutes.';
            } else {
                $stmt = mysqli_prepare($conn, "SELECT id, username, password FROM users WHERE username = ? LIMIT 1");
                
                if (!$stmt) {
                    $error = 'An error occurred. Please try again later.';
                } else {
                    mysqli_stmt_bind_param($stmt, "s", $username);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    
                    if ($row = mysqli_fetch_assoc($result)) {
                        if (verifyPassword($password, $row['password'])) {
                            logLoginAttempt($conn, $username, true);
                            
                            $updateStmt = mysqli_prepare($conn, "UPDATE users SET last_login = NOW() WHERE id = ?");
                            mysqli_stmt_bind_param($updateStmt, "i", $row['id']);
                            mysqli_stmt_execute($updateStmt);
                            mysqli_stmt_close($updateStmt);
                            
                            session_regenerate_id(true);
                            
                            $_SESSION['user_id'] = $row['id'];
                            $_SESSION['username'] = $row['username'];
                            $_SESSION['login_time'] = time();
                            
                            cleanupOldAttempts($conn);
                            
                            redirectTo('dashboard.php');
                        } else {
                            $error = 'Invalid username or password.';
                            logLoginAttempt($conn, $username, false);
                            usleep(rand(100000, 500000));
                        }
                    } else {
                        $error = 'Invalid username or password.';
                        logLoginAttempt($conn, $username, false);
                        usleep(rand(100000, 500000));
                    }
                    
                    mysqli_stmt_close($stmt);
                }
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
    <div class="login-container">
        <div class="login-image-section">
            <img src="images/lata.webp" alt="HYPE Energy Drink" class="can-image">
        </div>
        
        <div class="login-form-section">
            <button id="themeToggle" class="theme-toggle" title="Change theme">
                <svg class="theme-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="5"></circle>
                    <line x1="12" y1="1" x2="12" y2="3"></line>
                    <line x1="12" y1="21" x2="12" y2="23"></line>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                    <line x1="1" y1="12" x2="3" y2="12"></line>
                    <line x1="21" y1="12" x2="23" y2="12"></line>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                </svg>
            </button>
            
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
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                               required 
                               maxlength="50">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required 
                               maxlength="128">
                    </div>
                    
                    <button type="submit" class="btn-primary">Log In</button>
                </form>
                
                <p class="form-footer">
                    Don't have an account? <a href="signup.php">Sign up here</a>
                </p>
            </div>
        </div>
    </div>
    
    <script src="js/theme-toggle.js"></script>
</body>
</html>