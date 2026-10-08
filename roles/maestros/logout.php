<?php
/**
 * Creciendo · Cierre de Sesión
 */
session_start();

// Destruimos todas las variables de sesión
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Redirigimos al login principal
// En vez de Location: login.html
header("Location: ../../login.html");
exit;
