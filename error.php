<?php
require_once 'security.php';

$errorMessage = isset($_GET['msg']) ? sanitizeInput($_GET['msg']) : 'An error occurred during login.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Error - HYPE Distributor Portal</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <div class="logo">
                <h1>HYPE</h1>
            </div>
            
            <div class="error-page">
                <div class="error-icon">⚠</div>
                <h2>Login Error</h2>
                
                <div class="error-message">
                    <p><?php echo htmlspecialchars($errorMessage); ?></p>
                </div>
                
                <div class="error-actions">
                    <a href="login.php" class="btn-primary">Try Again</a>
                    <a href="signup.php" class="btn-secondary">Create Account</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>