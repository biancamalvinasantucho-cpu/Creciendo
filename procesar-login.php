<?php
session_start();

require_once __DIR__ . '/core/conexion.php';

$db = isset($pdo) ? $pdo : (isset($cnx) ? $cnx : null);

if (!$db) {
    die("Error de conexión a la base de datos.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email       = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $contrasenia = isset($_POST['contrasenia']) ? trim($_POST['contrasenia']) : '';
    $rol         = (!empty($_POST['rol'])) ? strtolower(trim($_POST['rol'])) : 'tutor';

    if ($email === '' || $contrasenia === '') {
        echo "<script>
                alert('Por favor, ingresa tu correo y contraseña.');
                window.location.href = 'login.html';
              </script>";
        exit;
    }

    switch ($rol) {
        case 'maestro':
        case 'maestro/a':
            $tablaEspecifica = 'maestros';
            $redireccion     = 'roles/maestros/panel-maestros.html';
            break;

        case 'director':
        case 'directora':
        case 'directores':
        case 'director/a':
            $tablaEspecifica = 'directores';
            $redireccion     = 'roles/directores/panel-director.html';
            break;

        case 'tutor':
        case 'tutor/a':
        default:
            $tablaEspecifica = 'tutores';
            $redireccion     = 'roles/tutores/panel-tutor.html';
            break;
    }

    $query = $db->prepare("SELECT * FROM {$tablaEspecifica} WHERE email = :email");
    $query->execute([':email' => $email]);
    $usuario = $query->fetch(PDO::FETCH_ASSOC);

    if ($usuario && ($contrasenia === $usuario['password'] || password_verify($contrasenia, $usuario['password']))) {
        
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre']     = $usuario['nombre'];
        $_SESSION['apellido']   = $usuario['apellido'];
        $_SESSION['email']      = $usuario['email'];
        $_SESSION['rol']        = $rol;

        header("Location: {$redireccion}");
        exit;

    } else {
        echo "<script>
                alert('Correo, contraseña o rol incorrectos.');
                window.location.href = 'login.html';
              </script>";
        exit;
    }

} else {
    header('Location: login.html');
    exit;
}