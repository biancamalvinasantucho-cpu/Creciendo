<?php
session_start();

// Carga conexion.php directamente desde la raíz
require_once __DIR__ . '/core/conexion.php'; 

$db = isset($pdo) ? $pdo : (isset($cnx) ? $cnx : null);

if (!$db) {
    die("Error de conexión a la base de datos.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['usuario']);
    $password = trim($_POST['contrasenia']);

    // Consultamos al usuario por correo electrónico
    $query = $db->prepare("SELECT id, nombre, apellido, email, password FROM usuarios WHERE email = :email");
    $query->execute([':email' => $email]);
    $usuario = $query->fetch(PDO::FETCH_ASSOC);

    // Validamos la existencia del usuario y la contraseña (soporta hash y texto plano)
    if ($usuario && (password_verify($password, $usuario['password']) || $password === $usuario['password'])) {
        
        // Guardamos los datos clave en la sesión
        $_SESSION['usuario_id'] = $usuario['id_usuario'];
        $_SESSION['nombre']     = $usuario['nombre'];
        $_SESSION['apellido']   = $usuario['apellido'];
        $_SESSION['email']      = $usuario['email'];

        // Redirige al panel principal o vista interna
        header('Location: inicio.html');
        exit;

    } else {
        // En caso de credenciales incorrectas
        echo "<script>
                alert('Correo o contraseña incorrectos.');
                window.location.href = 'index.html';
              </script>";
        exit;
    }
} else {
    header('Location: index.html');
    exit;
}