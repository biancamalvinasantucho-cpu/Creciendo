<?php
session_start();

require_once __DIR__ . '/core/conexion.php';

$db = isset($pdo) ? $pdo : (isset($cnx) ? $cnx : null);

if (!$db) {
    die("Error de conexión a la base de datos.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $nombre      = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $apellido    = isset($_POST['apellido']) ? trim($_POST['apellido']) : '';
    $email       = isset($_POST['email']) ? trim($_POST['email']) : '';
    $contrasenia = isset($_POST['contrasenia']) ? trim($_POST['contrasenia']) : '';
    $telefono    = isset($_POST['telefono']) ? trim($_POST['telefono']) : null;
    
    // Obtenemos el rol desde el formulario (limpiamos espacio y pasamos a minúsculas)
    $rol = (!empty($_POST['rol'])) ? strtolower(trim($_POST['rol'])) : 'tutor';

    // Validamos que no vengan vacíos los campos requeridos
    if (empty($nombre) || empty($apellido) || empty($email) || empty($contrasenia)) {
        echo "<script>
                alert('Por favor, completa todos los campos requeridos.');
                window.location.href = 'registrar-usuario.html';
              </script>";
        exit;
    }

    // Verificamos si el email ya existe en la base de datos
    $checkEmail = $db->prepare("SELECT id FROM usuarios WHERE email = :email");
    $checkEmail->execute([':email' => $email]);

    if ($checkEmail->fetch()) {
        echo "<script>
                alert('El correo electrónico ya se encuentra registrado.');
                window.location.href = 'registrar-usuario.html';
              </script>";
        exit;
    }

    // Se guarda directamente en texto plano
    $password = $contrasenia;

    // Guardamos en la base de datos
    $query = $db->prepare("
        INSERT INTO usuarios (nombre, apellido, email, password, rol, telefono) 
        VALUES (:nombre, :apellido, :email, :password, :rol, :telefono)
    ");

    $resultado = $query->execute([
        ':nombre'    => $nombre,
        ':apellido'  => $apellido,
        ':email'     => $email,
        ':password'  => $password,
        ':rol'       => $rol,
        ':telefono'  => $telefono
    ]);

    if ($resultado) {
        // Guardamos los datos en la sesión
        $_SESSION['usuario_id'] = $db->lastInsertId();
        $_SESSION['nombre']     = $nombre;
        $_SESSION['apellido']   = $apellido;
        $_SESSION['email']      = $email;
        $_SESSION['rol']        = $rol;

     header('Location: roles/auxiliares/registrar-usuario.html');   
} else {
    header('Location: registrar-usuario.html');
    exit;
}
}