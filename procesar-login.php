<?php
/**
 * Creciendo · Procesador de Login Seguro (Fusionado)
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
    $rolInput    = (!empty($_POST['rol'])) ? strtolower(trim($_POST['rol'])) : 'tutor';

    if ($email === '' || $contrasenia === '') {
        echo "<script>
                alert('Por favor, ingresa tu correo y contraseña.');
                window.location.href = 'login.html';
              </script>";
        exit;
    }

    $tablaEspecifica = '';
    $redireccion     = '';
    $rolLimpio       = '';

    // Normalizamos el rol y definimos la tabla donde se DEBE buscar
    switch ($rolInput) {
        case 'maestro':
        case 'maestro/a':
            $tablaEspecifica = 'maestros';
            $redireccion     = 'roles/maestros/panel-maestros.php';
            $rolLimpio       = 'maestro';
            break;

        case 'director':
        case 'directora':
        case 'directores':
        case 'director/a':
            $tablaEspecifica = 'directores';
            // Mantenemos tu ruta corregida que apunta al HTML (o PHP si luego lo cambias)
            $redireccion     = 'roles/directores/panel-directores.php';
            $rolLimpio       = 'director';
            break;

        case 'tutor':
        case 'tutor/a':
        default:
            $tablaEspecifica = 'tutores';
            $redireccion     = 'roles/tutores/panel-tutor.php';
            $rolLimpio       = 'tutor';
            break;
    }

    // Buscamos ÚNICAMENTE en la tabla del rol seleccionado.
    $query = $db->prepare("SELECT * FROM {$tablaEspecifica} WHERE email = :email LIMIT 1");
    $query->execute([':email' => $email]);
    $usuario = $query->fetch(PDO::FETCH_ASSOC);

    // CORRECCIÓN CRÍTICA: Cambiamos $usuario['contrasenia'] por $usuario['password']
    // para que coincida exactamente con las columnas de tu base de datos SQL.
    if ($usuario && ($contrasenia === $usuario['password'] || password_verify($contrasenia, $usuario['password']))) {
        
        // Variables de sesión estandarizadas
        $_SESSION['usuario_id']     = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre']; // Requerido por panel-maestros.php
        $_SESSION['nombre']         = $usuario['nombre'];
        $_SESSION['apellido']       = $usuario['apellido'] ?? ''; 
        $_SESSION['email']          = $usuario['email'];
        $_SESSION['rol']            = $rolLimpio; // Guarda siempre 'maestro', 'director' o 'tutor'

        // Si es maestro, le asignamos su sala (o 1 por defecto)
        if ($rolLimpio === 'maestro') {
            $_SESSION['sala_id'] = $usuario['sala_id'] ?? 1;
        }

        // Si es tutor, guardamos su estructura específica para paneles y QR
        if ($rolLimpio === 'tutor') {
            $_SESSION['tutor'] = [
                'id'       => $usuario['id'],
                'nombre'   => $usuario['nombre'],
                'apellido' => $usuario['apellido'] ?? '',
                'dni'      => $usuario['dni'] ?? '',
                'vinculo'  => 'Tutor/a'
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
?>