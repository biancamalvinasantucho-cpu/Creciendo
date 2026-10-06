<?php
session_start();

require_once __DIR__ . '/core/conexion.php';

$db = isset($pdo) ? $pdo : (isset($cnx) ? $cnx : null);

if (!$db) {
    die("Error de conexión a la base de datos.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre   = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $apellido = isset($_POST['apellido']) ? trim($_POST['apellido']) : '';
    $email    = isset($_POST['email']) ? trim($_POST['email']) : '';

    $password = '';
    if (!empty($_POST['contrasenia'])) {
        $password = trim($_POST['contrasenia']);
    } elseif (!empty($_POST['password'])) {
        $password = trim($_POST['password']);
    }

    $rol = (!empty($_POST['rol'])) ? strtolower(trim($_POST['rol'])) : 'tutor';

    if ($nombre === '' || $apellido === '' || $email === '' || $password === '') {
        echo "<script>
                alert('Por favor, completa todos los campos requeridos.');
                window.location.href = 'registrar-usuario.html';
              </script>";
        exit;
    }

    switch ($rol) {
        case 'maestro':
        case 'maestro/a':
            $tablaTarget = 'maestros';
            break;

        case 'director':
        case 'directora':
        case 'directores':
        case 'director/a':
            $tablaTarget = 'directores';
            break;

        case 'tutor':
        case 'tutor/a':
        default:
            $tablaTarget = 'tutores';
            break;
    }

    $checkEmail = $db->prepare("SELECT id FROM {$tablaTarget} WHERE email = :email");
    $checkEmail->execute([':email' => $email]);

    if ($checkEmail->fetch()) {
        echo "<script>
                alert('El correo electrónico ya se encuentra registrado.');
                window.location.href = 'registrar-usuario.html';
              </script>";
        exit;
    }

    $queryRol = $db->prepare("
        INSERT INTO {$tablaTarget} (nombre, apellido, email, password, rol) 
        VALUES (:nombre, :apellido, :email, :password, :rol)
    ");

    $resultado = $queryRol->execute([
        ':nombre'   => $nombre,
        ':apellido' => $apellido,
        ':email'    => $email,
        ':password' => $password,
        ':rol'      => $rol,
    ]);

    if ($resultado) {
        echo "<script>
                alert('¡Usuario registrado con éxito!');
                window.location.href = 'registrar-usuario.html';
              </script>";
        exit;

    } else {
        echo "<script>
                alert('Ocurrió un error al registrar la cuenta.');
                window.location.href = 'registrar-usuario.html';
              </script>";
        exit;
    }

} else {
    header('Location: registrar-usuario.html');
    exit;
}