<?php
require_once 'config.php';
require_once 'security.php';

if (isLoggedIn()) {
    redirectTo('dashboard.php');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        
        if (empty($username) || strlen($username) < 3 || strlen($username) > 50) {
            $errors[] = 'Username must be between 3 and 50 characters.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, and underscores.';
        }
        
        if (empty($email) || !validateEmail($email)) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        if (empty($password) || !validatePassword($password)) {
            $errors[] = 'Password must be at least 8 characters and contain uppercase, lowercase, and numbers.';
        }
        
        if ($password !== $passwordConfirm) {
            $errors[] = 'Passwords do not match.';
        }
        
        if (empty($errors)) {
            $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? OR email = ?");
            mysqli_stmt_bind_param($stmt, "ss", $username, $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            
            if (mysqli_stmt_num_rows($stmt) > 0) {
                $errors[] = 'Username or email already exists.';
            } else {
                $hashedPassword = hashPassword($password);
                $stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "sss", $username, $email, $hashedPassword);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success = true;
                } else {
                    $errors[] = 'Registration failed. Please try again.';
                }
            }
            mysqli_stmt_close($stmt);
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
    <div class="container">
        <div class="form-wrapper">
            <div class="logo">
                <h1>HYPE</h1>
            </div>
            
            <h2>Create Distributor Account</h2>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <p>Account created successfully! You can now <a href="login.php">log in</a>.</p>
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
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                               required maxlength="50" pattern="[a-zA-Z0-9_]+"
                               title="Only letters, numbers, and underscores allowed">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               required maxlength="100">
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
    
    <script src="js/validation.js"></script>
</body>
</html>