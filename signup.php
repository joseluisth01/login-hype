<?php
require_once 'security.php';
require_once 'config.php';

if (isLoggedIn()) {
    redirectTo('dashboard.php');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        if (checkSignupRateLimit($conn)) {
            $errors[] = 'Too many registration attempts. Please try again later.';
        } else {
            $username = sanitizeInput($_POST['username'] ?? '');
            $email = sanitizeInput($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';
            
            if (empty($username)) {
                $errors[] = 'Username is required.';
            } elseif (!validateUsername($username)) {
                $errors[] = 'Username must be 3-50 characters with only letters, numbers, and underscores.';
            }
            
            if (empty($email)) {
                $errors[] = 'Email is required.';
            } elseif (!validateEmail($email)) {
                $errors[] = 'Please enter a valid email address.';
            }
            
            if (empty($password)) {
                $errors[] = 'Password is required.';
            } elseif (!validatePassword($password)) {
                $errors[] = 'Password must be at least 8 characters with uppercase, lowercase, and numbers.';
            }
            
            if ($password !== $passwordConfirm) {
                $errors[] = 'Passwords do not match.';
            }
            
            if (empty($errors)) {
                $checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
                
                if (!$checkStmt) {
                    $errors[] = 'Database error. Please try again later.';
                } else {
                    mysqli_stmt_bind_param($checkStmt, "ss", $username, $email);
                    mysqli_stmt_execute($checkStmt);
                    mysqli_stmt_store_result($checkStmt);
                    
                    if (mysqli_stmt_num_rows($checkStmt) > 0) {
                        $errors[] = 'Username or email already exists.';
                    } else {
                        // Usuario no existe, proceder con registro
                        $hashedPassword = hashPassword($password);
                        $insertStmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                        
                        if (!$insertStmt) {
                            $errors[] = 'Registration failed. Please try again.';
                        } else {
                            mysqli_stmt_bind_param($insertStmt, "sss", $username, $email, $hashedPassword);
                            
                            if (mysqli_stmt_execute($insertStmt)) {
                                $success = true;
                                logLoginAttempt($conn, $username, true);
                            } else {
                                $errors[] = 'Registration failed. Please try again.';
                            }
                            
                            mysqli_stmt_close($insertStmt);
                        }
                    }
                    
                    mysqli_stmt_close($checkStmt);
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
    <title>Sign Up - HYPE Distributor Portal</title>
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
                
                <h2>Create Account</h2>
                
                <?php if ($success): ?>
                    <div class="success-message">
                        <p><strong>Success!</strong> Your account has been created.</p>
                        <p>You can now <a href="login.php">log in</a> with your credentials.</p>
                    </div>
                <?php else: ?>
                    <?php if (!empty($errors)): ?>
                        <div class="error-message">
                            <?php foreach ($errors as $error): ?>
                                <p><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="signup.php" id="signupForm">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                        
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" 
                                   id="username" 
                                   name="username" 
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                   required 
                                   maxlength="50" 
                                   pattern="[a-zA-Z0-9_]+"
                                   title="Only letters, numbers, and underscores allowed">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                   required 
                                   maxlength="100">
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                            <small>At least 8 characters with uppercase, lowercase, and numbers</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="password_confirm">Confirm Password</label>
                            <input type="password" id="password_confirm" name="password_confirm" required>
                        </div>
                        
                        <button type="submit" class="btn-primary">Create Account</button>
                    </form>
                    
                    <p class="form-footer">
                        Already have an account? <a href="login.php">Log in here</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="js/validation.js"></script>
    <script src="js/theme-toggle.js"></script>
</body>
</html>