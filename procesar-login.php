<?php
session_start();

// Conexión a la base de datos
require_once __DIR__ . '/core/conexion.php'; 

// Asegurar que $db esté disponible (PDO o MySQLi)
$db = isset($pdo) ? $pdo : (isset($cnx) ? $cnx : null);

if (!$db) {
    die("Error de conexión a la base de datos.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email       = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $contrasenia = isset($_POST['contrasenia']) ? trim($_POST['contrasenia']) : '';
    $rol         = (!empty($_POST['rol'])) ? strtolower(trim($_POST['rol'])) : 'maestro';

    if (empty($email) || empty($contrasenia)) {
        echo "<script>
                alert('Por favor, ingresa tu correo y contraseña.');
                window.location.href = 'login.html';
              </script>";
        exit;
    }

    // Consulta según el rol seleccionado
    if ($rol === 'maestro' || $rol === 'maestro/a') {
        
        // Buscamos al maestro y traemos su sala_id
        $stmt = $db->prepare("SELECT id, nombre, sala_id FROM maestros WHERE email = :email AND contrasenia = :contrasenia LIMIT 1");
        $stmt->execute([
            ':email' => $email,
            ':contrasenia' => $contrasenia
        ]);
        
        $docente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($docente) {
            // Guardamos los datos de la seño en la SESIÓN
            $_SESSION['usuario_id']     = $docente['id'];
            $_SESSION['usuario_nombre'] = $docente['nombre'];
            $_SESSION['sala_id']        = $docente['sala_id']; // <--- Sala dinámica (1, 2 o 3)
            $_SESSION['rol']            = 'maestro';

            // Redirigimos al panel dinámico
            header("Location: panel_docente.php");
            exit;
        } else {
            echo "<script>
                    alert('Usuario o contraseña incorrectos.');
                    window.location.href = 'login.html';
                  </script>";
            exit;
        }

    } else if ($rol === 'director' || $rol === 'director/a') {
        // Lógica de directores...
        header("Location: roles/directores/panel-director.html");
        exit;
    } else {
        // Lógica de tutores...
        header("Location: roles/tutores/panel-tutor.html");
        exit;
    }
}
?>