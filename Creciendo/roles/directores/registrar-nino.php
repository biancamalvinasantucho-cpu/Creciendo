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
    // Recepción y limpieza
    $nombre                 = trim($_POST['nombre'] ?? '');
    $apellido               = trim($_POST['apellido'] ?? '');
    $fecha_nacimiento       = trim($_POST['fecha_nacimiento'] ?? '');
    $sala_id                = intval($_POST['sala_id'] ?? 0); // ¡Corregido a ID de sala!
    $alergias_observaciones = trim($_POST['alergias_observaciones'] ?? '');
    
    // Datos opcionales
    $tutor_id   = !empty($_POST['tutor_id']) ? intval($_POST['tutor_id']) : null;
    $parentesco = trim($_POST['parentesco'] ?? '');

    if ($nombre === '' || $apellido === '' || $fecha_nacimiento === '' || $sala_id === 0) {
        $mensaje = 'Por favor completa todos los campos obligatorios del niño (incluyendo la sala).';
        $tipoMensaje = 'error';
    } else {
        try {
            // Iniciamos transacción (Punto #8 corregido)
            $db->beginTransaction();

            // Mantenemos retrocompatibilidad con la columna "sala" varchar por las dudas
            $nombre_sala = "Sala " . $sala_id; 

            // Insertamos al niño usando sala_id como Foreign Key (Punto #7 corregido)
            $sqlNino = "INSERT INTO ninos (nombre, apellido, fecha_nacimiento, sala, sala_id, alergias_observaciones) 
                        VALUES (:nombre, :apellido, :fecha_nacimiento, :sala, :sala_id, :alergias_observaciones)";
            
            $stmtNino = $db->prepare($sqlNino);
            $stmtNino->execute([
                ':nombre'                 => $nombre,
                ':apellido'               => $apellido,
                ':fecha_nacimiento'       => $fecha_nacimiento,
                ':sala'                   => $nombre_sala, 
                ':sala_id'                => $sala_id,
                ':alergias_observaciones' => $alergias_observaciones
            ]);

            $nino_id = $db->lastInsertId();

            // Si envió tutor, lo verificamos y vinculamos
            if ($tutor_id && $nino_id) {
                $checkTutor = $db->prepare("SELECT id FROM tutores WHERE id = :id");
                $checkTutor->execute([':id' => $tutor_id]);
                
                if ($checkTutor->rowCount() > 0) {
                    $sqlTutor = "UPDATE tutores SET nino_id = :nino_id, parentesco = :parentesco WHERE id = :tutor_id";
                    $stmtTutor = $db->prepare($sqlTutor);
                    $stmtTutor->execute([
                        ':nino_id'    => $nino_id,
                        ':parentesco' => $parentesco,
                        ':tutor_id'   => $tutor_id
                    ]);
                } else {
                    $mensaje = "Niño registrado, pero el ID de Tutor ($tutor_id) no existe en la base de datos.";
                    $tipoMensaje = "warning";
                }
            }

            $db->commit();
            
            if (empty($mensaje)) {
                $mensaje = '¡Alumno registrado y vinculado exitosamente!';
                $tipoMensaje = 'exito';
            }

        } catch (PDOException $e) {
            $db->rollBack();
            // Punto #9 corregido: no revelamos datos del servidor
            $mensaje = 'Ocurrió un error en la base de datos. Verificá que los datos sean correctos.';
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
    <title>Registrar Niño | Dirección</title>
    <!-- Cargamos ambos estilos para mantener el diseño de Guille en el form y el tuyo en la navbar -->
    <link rel="stylesheet" href="../../css/registrar-nino.css">
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
            <h2 style="margin-top: 0; color: #0f172a; margin-bottom: 5px; font-size: 24px;">Registrar Nuevo Niño</h2>
            <h3 style="color: #64748b; font-weight: normal; margin-bottom: 25px;">Completá los datos del alumno y vinculá su tutor.</h3>

            <?php if ($mensaje !== ''): ?>
                <div style="padding: 12px; margin-bottom: 20px; border-radius: 8px; font-weight: bold; <?php echo ($tipoMensaje === 'error') ? 'background: #fee2e2; color: #991b1b;' : (($tipoMensaje === 'warning') ? 'background: #fef08a; color: #854d0e;' : 'background: #d1fae5; color: #065f46;'); ?>">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>

            <form action="registrar-nino.php" method="POST">
                
                <div class="form-group">
                    <label for="nombre">Nombre del Niño/a</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Nombre" required>
                </div>

                <div class="form-group">
                    <label for="apellido">Apellido del Niño/a</label>
                    <input type="text" id="apellido" name="apellido" placeholder="Apellido" required>
                </div>

                <div class="form-group">
                    <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>
                </div>

                <div class="form-group">
                    <label for="sala_id">Sala Asignada</label>
                    <select id="sala_id" name="sala_id" required style="width: 100%; padding: 12px 16px; border: 1px solid transparent; border-radius: 12px; background-color: #f1f5f9; font-size: 14px; color: #0f172a; outline: none; cursor: pointer;">
                        <option value="" disabled selected>Seleccioná una sala...</option>
                        <option value="1">Sala 1 (Lactantes)</option>
                        <option value="2">Sala 2 (1 a 2 años)</option>
                        <option value="3">Sala 3 (3 a 4 años)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="alergias_observaciones">Alergias / Observaciones Médicas</label>
                    <textarea id="alergias_observaciones" name="alergias_observaciones" placeholder="Indicar si tiene alergias, medicación o notas médicas..."></textarea>
                </div>

                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #e2e8f0;">

                <h3 style="font-size: 15px; color: #334155; margin-bottom: 15px; font-weight: 600;">Vinculación con Tutor (Opcional)</h3>

                <div class="form-group">
                    <label for="tutor_id">ID del Tutor Responsable</label>
                    <input type="number" id="tutor_id" name="tutor_id" placeholder="ID del tutor en la base de datos">
                </div>

                <div class="form-group">
                    <label for="parentesco">Parentesco</label>
                    <input type="text" id="parentesco" name="parentesco" placeholder="Ej: Madre, Padre, Tío/a">
                </div>

                <button type="submit" class="btn login-btn">Registrar Niño</button>
            </form>
        </section>
    </main>

</body>
</html>