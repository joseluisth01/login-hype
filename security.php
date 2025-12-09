<?php
// Definir constante de seguridad antes de incluir config
define('HYPE_SECURITY_CHECK', true);

// Iniciar sesión con configuración segura
if (session_status() === PHP_SESSION_NONE) {
    // Configuración adicional de sesión
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    session_start();
    
    // Verificar timeout de sesión
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['LAST_ACTIVITY'] = time();
    
    // Regenerar ID de sesión periódicamente (cada 30 minutos)
    if (!isset($_SESSION['CREATED'])) {
        $_SESSION['CREATED'] = time();
    } else if (time() - $_SESSION['CREATED'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['CREATED'] = time();
    }
}

/**
 * Sanitiza input del usuario
 * @param string $data - Dato a sanitizar
 * @return string - Dato sanitizado
 */
function sanitizeInput($data) {
    if ($data === null || $data === '') {
        return '';
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Valida dirección de email
 * @param string $email - Email a validar
 * @return bool - true si es válido
 */
function validateEmail($email) {
    if (empty($email) || strlen($email) > 100) {
        return false;
    }
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida fortaleza de contraseña
 * @param string $password - Contraseña a validar
 * @return bool - true si cumple requisitos
 */
function validatePassword($password) {
    if (strlen($password) < 8 || strlen($password) > 128) {
        return false;
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    return true;
}

/**
 * Valida username
 * @param string $username - Username a validar
 * @return bool - true si cumple requisitos
 */
function validateUsername($username) {
    if (strlen($username) < 3 || strlen($username) > 50) {
        return false;
    }
    return preg_match('/^[a-zA-Z0-9_]+$/', $username) === 1;
}

/**
 * Hash seguro de contraseña
 * @param string $password - Contraseña a hashear
 * @return string - Hash de la contraseña
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verifica contraseña contra hash
 * @param string $password - Contraseña en texto plano
 * @param string $hash - Hash almacenado
 * @return bool - true si coincide
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Genera token CSRF
 * @return string - Token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token']) || 
        !isset($_SESSION['csrf_token_time']) || 
        (time() - $_SESSION['csrf_token_time']) > 3600) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida token CSRF
 * @param string $token - Token a validar
 * @return bool - true si es válido
 */
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Verifica si el usuario está autenticado
 * @return bool - true si está logueado
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && 
           isset($_SESSION['username']) && 
           isset($_SESSION['login_time']);
}

/**
 * Redirecciona a una URL
 * @param string $url - URL destino
 */
function redirectTo($url) {
    // Validar que la URL sea relativa para prevenir open redirect
    if (preg_match('/^https?:\/\//', $url)) {
        error_log('Attempted redirect to external URL: ' . $url);
        $url = 'index.php';
    }
    header("Location: " . $url);
    exit();
}

/**
 * Registra intento de login
 * @param mysqli $conn - Conexión a BD
 * @param string $username - Username
 * @param bool $success - Si fue exitoso
 * @return bool - true si se registró correctamente
 */
function logLoginAttempt($conn, $username, $success) {
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $stmt = mysqli_prepare($conn, "INSERT INTO login_attempts (username, ip_address, success) VALUES (?, ?, ?)");
    
    if (!$stmt) {
        error_log('Error preparing login attempt statement: ' . mysqli_error($conn));
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "ssi", $username, $ipAddress, $success);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
}

/**
 * Verifica límite de intentos de login
 * @param mysqli $conn - Conexión a BD
 * @param string $username - Username
 * @return bool - true si excede el límite
 */
function checkLoginAttempts($conn, $username) {
    $stmt = mysqli_prepare($conn, 
        "SELECT COUNT(*) as attempts FROM login_attempts 
         WHERE username = ? 
         AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE) 
         AND success = FALSE");
    
    if (!$stmt) {
        error_log('Error preparing check attempts statement: ' . mysqli_error($conn));
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return ($row['attempts'] >= 5);
}

/**
 * Verifica límite de registros por IP
 * @param mysqli $conn - Conexión a BD
 * @return bool - true si excede el límite
 */
function checkSignupRateLimit($conn) {
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    
    $stmt = mysqli_prepare($conn, 
        "SELECT COUNT(*) as attempts FROM login_attempts 
         WHERE ip_address = ? 
         AND attempt_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    
    if (!$stmt) {
        error_log('Error preparing rate limit statement: ' . mysqli_error($conn));
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "s", $ipAddress);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return ($row['attempts'] >= 10);
}

/**
 * Limpia intentos de login antiguos (mantenimiento)
 * @param mysqli $conn - Conexión a BD
 */
function cleanupOldAttempts($conn) {
    // Ejecutar solo 5% de las veces para no sobrecargar
    if (rand(1, 100) <= 5) {
        mysqli_query($conn, "DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    }
}
?>