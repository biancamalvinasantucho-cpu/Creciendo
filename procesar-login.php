<?php
/**
 * Creciendo · Procesador de Login Seguro
 */
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

    $tablaEspecifica = '';
    $redireccion = '';

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
            $redireccion = 'roles/directores/panel-directores.html';
            break;

        case 'tutor':
        case 'tutor/a':
        default:
            $tablaEspecifica = 'tutores';
            $redireccion = 'roles/tutores/panel-tutor.php';
            break;
    }

    // Consultamos los datos del usuario en la tabla correspondiente
    $query = $db->prepare("SELECT * FROM {$tablaEspecifica} WHERE email = :email LIMIT 1");
    $query->execute([':email' => $email]);
    $usuario = $query->fetch(PDO::FETCH_ASSOC);

    // Verificamos contraseña (compatible con texto plano o password_hash)
    if ($usuario && ($contrasenia === $usuario['password'] || password_verify($contrasenia, $usuario['password']))) {
        
        // Guardamos las variables de sesión generales y seguras
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre']     = $usuario['nombre'];
        $_SESSION['apellido']   = $usuario['apellido'] ?? ''; 
        $_SESSION['email']      = $usuario['email'];
        $_SESSION['rol']        = $rol;

        // Si es tutor, guardamos su estructura específica para que el panel cargue sus datos y QR
        if ($rol === 'tutor' || $rol === 'tutor/a') {
            $_SESSION['tutor'] = [
                'id'      => $usuario['id'],
                'nombre'  => $usuario['nombre'],
                'apellido'=> $usuario['apellido'] ?? '',
                'dni'     => $usuario['dni'] ?? '',
                'vinculo' => 'Tutor/a'
            ];
        }

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