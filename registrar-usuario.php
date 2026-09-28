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
    
    // Le asignamos 'tutor' si no viene ningún rol desde el formulario
    $rol         = (!empty($_POST['rol'])) ? trim($_POST['rol']) : 'tutor';

    if (empty($nombre) || empty($apellido) || empty($email) || empty($contrasenia)) {
        echo "<script>
                alert('Por favor, completa todos los campos requeridos.');
                window.location.href = 'registrar-usuario.html';
              </script>";
        exit;
    }

    $checkEmail = $db->prepare("SELECT id FROM usuarios WHERE email = :email");
    $checkEmail->execute([':email' => $email]);

    if ($checkEmail->fetch()) {
        echo "<script>
                alert('El correo electrónico ya se encuentra registrado.');
                window.location.href = 'registrar-usuario.html';
              </script>";
        exit;
    }

    // Se guarda directamente en texto plano sin encriptar
    $password = $contrasenia;

    // Mantenemos 'password' como tenés la columna en tu BD e incluimos 'rol'
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
        $_SESSION['usuario_id'] = $db->lastInsertId();
        $_SESSION['nombre']     = $nombre;
        $_SESSION['apellido']   = $apellido;
        $_SESSION['email']      = $email;
        $_SESSION['rol']        = $rol;

        header('Location: index.html');
        exit;
    } else {
        echo "<script>
                alert('Ocurrió un error al registrar el usuario.');
                window.location.href = 'registrar-usuario.html';
              </script>";
        exit;
    }

} else {
    header('Location: registrar-usuario.html');
    exit;
}