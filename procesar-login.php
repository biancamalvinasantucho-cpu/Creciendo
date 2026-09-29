<?php
session_start();

require_once __DIR__ . '/core/conexion.php';

$db = isset($pdo) ? $pdo : (isset($cnx) ? $cnx : null);

if (!$db) {
    die("Error de conexión a la base de datos.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recibimos solo los datos del login
    $email       = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $contrasenia = isset($_POST['contrasenia']) ? trim($_POST['contrasenia']) : '';
    $rolEnviado  = isset($_POST['rol']) ? strtolower(trim($_POST['rol'])) : '';

    if (empty($email) || empty($contrasenia)) {
        echo "<script>
                alert('Por favor, completa el correo y la contraseña.');
                window.location.href = 'index.html';
              </script>";
        exit;
    }

    // Buscamos al usuario por su correo
    $query = $db->prepare("SELECT id, nombre, apellido, email, password, rol FROM usuarios WHERE email = :email");
    $query->execute([':email' => $email]);
    $usuario = $query->fetch(PDO::FETCH_ASSOC);

    // Verificamos si existe el usuario y coincide la contraseña
    if ($usuario && $usuario['password'] === $contrasenia) {

        // Normalizamos el rol de la base de datos
        $rolBD = strtolower($usuario['rol']);

        // Verificamos si seleccionó un rol en el formulario y si coincide con su rol en la BD
        if (!empty($rolEnviado) && $rolBD !== $rolEnviado) {
            echo "<script>
                    alert('El rol seleccionado no coincide con el registrado para esta cuenta.');
                    window.location.href = 'index.html';
                  </script>";
            exit;
        }

        // Guardamos los datos en la sesión
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre']     = $usuario['nombre'];
        $_SESSION['apellido']   = $usuario['apellido'];
        $_SESSION['email']      = $usuario['email'];
        $_SESSION['rol']        = $rolBD;

        // Redirección según el rol correspondiente
        switch ($rolBD) {
            case 'auxiliar':
                header('Location: roles/auxiliares/panel-auxiliar.html');
                exit;

            case 'maestro':
            case 'maestro/a':
                header('Location: roles/maestros/panel-maestros.html');
                exit;

            case 'tutor':
            case 'tutor/a':
            default:
                header('Location: roles/tutores/panel-tutor.html');
                exit;
        }

    } else {
        echo "<script>
                alert('Correo electrónico o contraseña incorrectos.');
                window.location.href = 'index.html';
              </script>";
        exit;
    }

} else {
    header('Location: index.html');
    exit;
}