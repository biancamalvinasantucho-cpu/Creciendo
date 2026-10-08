<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Control de acceso
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'maestro') {
    // header("Location: ../../../login.html");
    // exit;
}

// Conexión dinámica a la base de datos
$pathConexion = file_exists(__DIR__ . '/../../../core/conexion.php') 
    ? __DIR__ . '/../../../core/conexion.php' 
    : __DIR__ . '/../../core/conexion.php';

if (!file_exists($pathConexion)) {
    die("Error fatal: No se encontró conexion.php.");
}
require_once $pathConexion;

$db = isset($pdo) ? $pdo : ($cnx ?? null);

$mensaje = '';
$tipoMensaje = '';
$logoutPath = file_exists(__DIR__ . '/../../../logout.php') ? '../../../logout.php' : '../../logout.php';

// Procesar el registro de asistencia de ALUMNOS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alumno_id'], $_POST['estado'])) {
    $alumno_id = intval($_POST['alumno_id']);
    $estado    = trim($_POST['estado']);
    $fecha     = date('Y-m-d');
    $hora      = date('H:i:s');

    if ($db) {
        try {
            $stmt = $db->prepare("INSERT INTO asistencias (alumno_id, fecha, estado, hora) VALUES (:alumno_id, :fecha, :estado, :hora)");
            $stmt->execute([
                ':alumno_id' => $alumno_id,
                ':fecha'     => $fecha,
                ':estado'    => $estado,
                ':hora'      => $hora
            ]);
            $mensaje = "Asistencia del alumno guardada correctamente.";
            $tipoMensaje = "exito";
        } catch (PDOException $e) {
            $mensaje = "Error en base de datos: " . $e->getMessage();
            $tipoMensaje = "error";
        }
    }
}

// Cargar la lista de NIÑOS / ALUMNOS
$alumnos = [];
if ($db) {
    try {
        $stmt = $db->query("SELECT id, nombre, apellido FROM alumnos ORDER BY apellido ASC");
        if ($stmt) $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // En caso de que la tabla se llame diferente en tu esquema
        $mensajeErrorAlumnos = $e->getMessage();
    }
}

// Cargar asistencias tomadas hoy a los alumnos
$asistenciasHoy = [];
if ($db) {
    $hoy = date('Y-m-d');
    try {
        $stmtHoy = $db->prepare("SELECT a.*, al.nombre AS alumno_nombre, al.apellido AS alumno_apellido 
                                 FROM asistencias a 
                                 JOIN alumnos al ON a.alumno_id = al.id 
                                 WHERE a.fecha = :fecha ORDER BY a.id DESC");
        $stmtHoy->execute([':fecha' => $hoy]);
        $asistenciasHoy = $stmtHoy->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $mensajeErrorAsistencias = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tomar Asistencia | Panel Maestros</title>
    <link rel="stylesheet" href="../../../css/panel-maestros.css">
</head>
<body style="font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 0;">

    <header style="background: #1976D2; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
        <h2>Creciendo | Asistencia Maestras</h2>
        <div>
            <span>Hola, <strong><?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Docente'); ?></strong></span>
            <a href="<?php echo $logoutPath; ?>" style="color: #ffcdd2; text-decoration: none; font-weight: bold; margin-left: 15px; background: rgba(0,0,0,0.2); padding: 5px 10px; border-radius: 4px;">Cerrar Sesión</a>
        </div>
    </header>

    <main style="padding: 20px; max-width: 1000px; margin: 0 auto;">
   <nav style="margin-bottom: 20px;">
    <!-- Le sacamos el '../' porque panel-maestros.php está en la misma carpeta pantallas/ -->
    <a href="panel-maestros.php" style="padding: 10px 18px; background: #e0e0e0; color: #333; text-decoration: none; border-radius: 5px; font-weight: bold; margin-right: 10px;">← Volver al Panel</a>
    <a href="actividades.php" style="padding: 10px 18px; background: #e0e0e0; color: #333; text-decoration: none; border-radius: 5px; font-weight: bold; margin-right: 10px;">Actividades / Avisos</a>
    <a href="asistencia.php" style="padding: 10px 18px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Tomar Asistencia</a>
</nav>
        <?php if (!empty($mensaje)): ?>
            <div style="padding: 12px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; <?php echo ($tipoMensaje === 'exito') ? 'background: #d4edda; color: #155724;' : 'background: #f8d7da; color: #721c24;'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <section style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 25px;">
            <h3 style="margin-top: 0;">Registrar Asistencia de Alumnos (<?php echo date('d/m/Y'); ?>)</h3>
            
            <form method="POST" action="" style="display: flex; flex-direction: column; gap: 15px; max-width: 500px;">
                <div>
                    <label style="font-weight: bold; display: block; margin-bottom: 5px;">Alumno / Niñx:</label>
                    <select name="alumno_id" required style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc;">
                        <option value="">-- Seleccionar Alumno --</option>
                        <?php foreach ($alumnos as $al): ?>
                            <option value="<?php echo $al['id']; ?>">
                                <?php echo htmlspecialchars($al['apellido'] . ', ' . $al['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label style="font-weight: bold; display: block; margin-bottom: 5px;">Estado:</label>
                    <select name="estado" required style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc;">
                        <option value="Presente">Presente</option>
                        <option value="Ausente">Ausente</option>
                        <option value="Llegada Tardía">Llegada Tardía</option>
                    </select>
                </div>

                <button type="submit" style="padding: 12px; background: #4CAF50; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">Guardar Asistencia</button>
            </form>
        </section>

        <section style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0;">Asistencias Registradas Hoy</h3>
            <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; text-align: left; margin-top: 10px;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th>Hora</th>
                        <th>Alumno</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($asistenciasHoy)): ?>
                        <?php foreach ($asistenciasHoy as $reg): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reg['hora']); ?></td>
                                <td><?php echo htmlspecialchars($reg['alumno_apellido'] . ', ' . $reg['alumno_nombre']); ?></td>
                                <td><strong><?php echo htmlspecialchars($reg['estado']); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align: center; color: #777;">No hay registros de alumnos cargados hoy.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

    </main>
</body>
</html>