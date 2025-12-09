<?php
require_once 'security.php';
require_once 'config.php';

if (!isLoggedIn()) {
    redirectTo('login.php');
}

$stmt = mysqli_prepare($conn, "SELECT id, username, email, created_at, last_login FROM users ORDER BY created_at DESC");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$users = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - HYPE Distributor Portal</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo-nav">
                <h1>HYPE</h1>
            </div>
            <div class="nav-items">
                <button id="themeToggle" class="theme-toggle" title="Change theme" style="position: relative; top: 0; right: 0; margin: 0;">
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
                <span class="user-welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h2>Distributor Portal</h2>
            <p>Manage your HYPE distributor account and view all registered users</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h3><?php echo count($users); ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">✓</div>
                <div class="stat-info">
                    <h3>Active</h3>
                    <p>Account Status</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🔒</div>
                <div class="stat-info">
                    <h3>Secure</h3>
                    <p>Connection</p>
                </div>
            </div>
        </div>
        
        <div class="users-section">
            <h3>Registered Users</h3>
            
            <div class="table-wrapper">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Registered</th>
                            <th>Last Login</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                        <span class="badge">You</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php 
                                    if ($user['last_login']) {
                                        echo date('M d, Y H:i', strtotime($user['last_login']));
                                    } else {
                                        echo 'Never';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="js/theme-toggle.js"></script>
</body>
</html>