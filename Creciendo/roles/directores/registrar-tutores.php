<?php
session_start();

// 1. Candado de seguridad: Solo Directores
if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] !== 'director' && $_SESSION['rol'] !== 'directores')) {
    header('Location: ../../login.html');
    exit;
}

require_once __DIR__ . '/../../core/conexion.php';
$db = isset($pdo) ? $pdo : ($cnx ?? null);

if (!$db) {
    die("Error de conexión a la base de datos.");
}

$mensaje = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recepción y limpieza de datos
    $nombre   = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? ''); // Se guarda como lo mandó Guille (el login ya soporta texto plano o hash)

    $nino_id                 = !empty($_POST['nino_id']) ? intval($_POST['nino_id']) : null;
    $parentesco              = trim($_POST['parentesco'] ?? '');
    $es_autorizado_retiro    = isset($_POST['es_autorizado_retiro']) ? intval($_POST['es_autorizado_retiro']) : 0;
    $foto_identificacion_url = trim($_POST['foto_identificacion_url'] ?? '');
    $rol                     = 'tutor';

    if ($nombre === '' || $apellido === '' || $email === '' || $password === '') {
        $mensaje = 'Por favor completa todos los campos obligatorios del tutor.';
        $tipoMensaje = 'error';
    } else {
        try {
            // CORRECCIÓN #9: Verificamos si el email ya existe ANTES de insertar
            $checkEmail = $db->prepare("SELECT id FROM tutores WHERE email = :email");
            $checkEmail->execute([':email' => $email]);

            if ($checkEmail->rowCount() > 0) {
                $mensaje = 'Ese correo electrónico ya está registrado para otro usuario.';
                $tipoMensaje = 'warning';
            } else {
                // Insertamos el tutor
                $sql = "INSERT INTO tutores 
                        (nino_id, parentesco, es_autorizado_retiro, foto_identificacion_url, nombre, apellido, email, password, rol) 
                        VALUES 
                        (:nino_id, :parentesco, :es_autorizado_retiro, :foto_identificacion_url, :nombre, :apellido, :email, :password, :rol)";

                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':nino_id'                 => $nino_id,
                    ':parentesco'              => $parentesco,
                    ':es_autorizado_retiro'    => $es_autorizado_retiro,
                    ':foto_identificacion_url' => $foto_identificacion_url,
                    ':nombre'                  => $nombre,
                    ':apellido'                => $apellido,
                    ':email'                   => $email,
                    ':password'                => $password,
                    ':rol'                     => $rol
                ]);

                $mensaje = '¡Tutor registrado y habilitado para iniciar sesión exitosamente!';
                $tipoMensaje = 'exito';
            }
        } catch (PDOException $e) {
            // Ocultamos el error crudo de SQL al usuario
            $mensaje = 'Ocurrió un error en la base de datos al intentar registrar. Revisá los datos.';
            $tipoMensaje = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Tutor | Dirección</title>
    <!-- Cargamos ambos CSS para mantener el diseño del form y la navbar -->
    <link rel="stylesheet" href="../../css/registrar-tutores.css">
    <link rel="stylesheet" href="../../css/panel-directores.css">
</head>
<body style="padding-top: 0;">

    <!-- Navbar Superior Integrada -->
    <header class="navbar" style="margin-bottom: 40px; width: 100%;">
        <div class="navbar-brand">
            <div class="brand-logo-container">
                <a href="panel-directores.php">
                    <img src="../../img/logo.png" alt="Logo Creciendo" class="logo-navbar" style="height: 60px; width: auto;">
                </a>
            </div>
            <span class="role-badge">Director/a</span>
        </div>

        <nav class="navbar-menu">
            <ul>
                <li><a href="panel-directores.php">Inicio</a></li>
                <li><a href="pantallas/asistencia.php">Asistencia</a></li>
                <li><a href="pantallas/actividades.php">Actividades</a></li>
                <li><a href="pantallas/tutores.php">Tutores</a></li>
                <li><a href="pantallas/observaciones.html">Observaciones</a></li>
                <li><a href="pantallas/comunicaciones.html">Comunicaciones</a></li>
                <li><a href="pantallas/configuracion.php">Configuración</a></li>
            </ul>
        </nav>

        <div class="navbar-user">
            <span>Hola, <strong><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Director/a'); ?></strong></span>
            <a href="../../logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </header>

    <main class="contenedor-principal" style="display: flex; flex-direction: column; align-items: center; padding-top: 0;">
        
        <div style="width: 100%; max-width: 480px; margin-bottom: 20px;">
            <a href="panel-directores.php" class="btn-volver" style="text-decoration: none; color: #3b82f6; font-weight: bold;">← Volver al Panel</a>
        </div>

        <section class="contenedor-login">
            <h2 style="margin-top: 0; color: #0f172a; margin-bottom: 5px; font-size: 24px;">Registrar Nuevo Tutor/a</h2>
            <h3 style="color: #64748b; font-weight: normal; margin-bottom: 25px;">Generá el acceso para que el tutor ingrese al sistema.</h3>

            <?php if ($mensaje !== ''): ?>
                <div style="padding: 12px; margin-bottom: 20px; border-radius: 8px; font-weight: bold; <?php echo ($tipoMensaje === 'error') ? 'background: #fee2e2; color: #991b1b;' : (($tipoMensaje === 'warning') ? 'background: #fef08a; color: #854d0e;' : 'background: #d1fae5; color: #065f46;'); ?>">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>

            <form action="registrar-tutores.php" method="POST">
                
                <!-- Datos Personales del Tutor -->
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Nombre del tutor" required>
                </div>

                <div class="form-group">
                    <label for="apellido">Apellido</label>
                    <input type="text" id="apellido" name="apellido" placeholder="Apellido del tutor" required>
                </div>

                <div class="form-group">
                    <label for="email">Correo Electrónico (Será su usuario)</label>
                    <input type="email" id="email" name="email" placeholder="ejemplo@correo.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña de acceso</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #e2e8f0;">

                <h3 style="font-size: 15px; color: #334155; margin-bottom: 15px; font-weight: 600;">Vinculación con el Niño/a</h3>

                <div class="form-group">
                    <label for="nino_id">ID del Niño/a (Opcional)</label>
                    <input type="number" id="nino_id" name="nino_id" placeholder="Ej: 1 (Dejar vacío si se vinculará después)">
                </div>

                <div class="form-group">
                    <label for="parentesco">Parentesco</label>
                    <input type="text" id="parentesco" name="parentesco" placeholder="Ej: Padre, Madre, Tío/a, Abuelo/a">
                </div>

                <div class="form-group">
                    <label style="font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px;">¿Está autorizado a retirar al niño?</label>
                    <div style="display: flex; gap: 20px; margin-top: 5px;">
                        <label style="font-weight: normal; cursor: pointer; display: flex; align-items: center;">
                            <input type="radio" name="es_autorizado_retiro" value="1" checked style="margin-right: 5px;"> Sí
                        </label>
                        <label style="font-weight: normal; cursor: pointer; display: flex; align-items: center;">
                            <input type="radio" name="es_autorizado_retiro" value="0" style="margin-right: 5px;"> No
                        </label>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label for="foto_identificacion_url">URL / Ruta de Foto de Identificación</label>
                    <input type="text" id="foto_identificacion_url" name="foto_identificacion_url" placeholder="Ej: uploads/dni_tutor_1.jpg">
                </div>

                <button type="submit" class="btn login-btn">Guardar Tutor</button>
            </form>
        </section>
    </main>

</body>
</html>