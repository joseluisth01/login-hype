<?php
require_once 'security.php';

// Destruir toda la sesión
$_SESSION = array();

// Eliminar cookie de sesión
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destruir la sesión
session_destroy();

// Redirigir al login
header("Location: login.php");
exit();
?>