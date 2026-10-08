<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Control de acceso: solo maestra
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'maestro') {
    // header("Location: ../../login.html");
    // exit;
}

// Conexión dinámica (sube 2 o 3 niveles según si el archivo está en pantallas/ o en la raíz del rol)
$pathConexion = file_exists(__DIR__ . '/../../core/conexion.php') 
    ? __DIR__ . '/../../core/conexion.php' 
    : __DIR__ . '/../../../core/conexion.php';

if (!file_exists($pathConexion)) {
    die("Error fatal: No se encontró el archivo de conexión en ninguna de las rutas esperadas.");
}
require_once $pathConexion;
$db = isset($pdo) ? $pdo : ($cnx ?? null);

// Ruta dinámica para el logout según la profundidad del archivo
$logoutPath = file_exists(__DIR__ . '/../../logout.php') ? '../../logout.php' : '../../../logout.php';

// Filtro de sala seleccionada
if (isset($_GET['sala_id'])) {
    $_SESSION['sala_id'] = intval($_GET['sala_id']);
}
$sala_id = $_SESSION['sala_id'] ?? 1;

// 1. Consultar actividades cargadas por Dirección
$actividades = [];
if ($db) {
    try {
        $stmt = $db->prepare("SELECT * FROM actividades WHERE sala_id = :sala_id ORDER BY fecha DESC, id DESC");
        $stmt->execute([':sala_id' => $sala_id]);
        $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $errorActividades = $e->getMessage();
    }
}

// 2. Consultar avisos privados enviados por los tutores
$avisosTutores = [];
if ($db) {
    try {
        $stmtAvisos = $db->prepare("
            SELECT a.*, t.nombre AS tutor_nombre, t.apellido AS tutor_apellido 
            FROM avisos_privados a 
            JOIN tutores t ON a.tutor_id = t.id 
            WHERE a.sala_id = :sala_id 
            ORDER BY a.fecha DESC
        ");
        $stmtAvisos->execute([':sala_id' => $sala_id]);
        $avisosTutores = $stmtAvisos->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $errorAvisos = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actividades y Avisos | Panel Maestros</title>
    <link rel="stylesheet" href="../../css/panel-maestros.css">
</head>
<body style="font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 0;">

    <header style="background: #1976D2; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
        <h2>Creciendo | Panel Maestros</h2>
        
        <div style="display: flex; align-items: center; gap: 15px;">
            <!-- Selector para cambiar de sala en vivo -->
            <form method="GET" action="" style="margin: 0;">
                <label for="sala_id" style="color: white; font-weight: bold; margin-right: 5px;">Sala:</label>
                <select name="sala_id" id="sala_id" onchange="this.form.submit()" style="padding: 6px 10px; border-radius: 4px; border: none; cursor: pointer; font-weight: bold;">
                    <option value="1" <?php echo ($sala_id == 1) ? 'selected' : ''; ?>>Sala 1 (Lactantes)</option>
                    <option value="2" <?php echo ($sala_id == 2) ? 'selected' : ''; ?>>Sala 2 (1 a 2 años)</option>
                    <option value="3" <?php echo ($sala_id == 3) ? 'selected' : ''; ?>>Sala 3 (3 a 4 años)</option>
                    <option value="4" <?php echo ($sala_id == 4) ? 'selected' : ''; ?>>Sala Verde (5 años)</option>
                </select>
            </form>

            <span>Hola, <strong><?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Docente'); ?></strong></span>
            <a href="<?php echo $logoutPath; ?>" style="color: #ffcdd2; text-decoration: none; font-weight: bold; background: rgba(0,0,0,0.2); padding: 5px 10px; border-radius: 4px;">Cerrar Sesión</a>
        </div>
    </header>

    <main style="padding: 20px; max-width: 1000px; margin: 0 auto;">
        
<nav style="margin-bottom: 20px;">
    <!-- Le sacamos el '../' porque panel-maestros.php está en la misma carpeta pantallas/ -->
    <a href="panel-maestros.php" style="padding: 10px 18px; background: #e0e0e0; color: #333; text-decoration: none; border-radius: 5px; font-weight: bold; margin-right: 10px;">← Volver al Panel</a>
    <a href="actividades.php" style="padding: 10px 18px; background: #e0e0e0; color: #333; text-decoration: none; border-radius: 5px; font-weight: bold; margin-right: 10px;">Actividades / Avisos</a>
    <a href="asistencia.php" style="padding: 10px 18px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Tomar Asistencia</a>
</nav>

        <!-- SECCIÓN 1: AVISOS PRIVADOS RECIBIDOS DE LOS TUTORES -->
        <section style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 25px;">
            <h3 style="margin-top: 0; color: #c62828;">📩 Avisos Privados Enviados por los Tutores (Sala <?php echo htmlspecialchars($sala_id); ?>)</h3>
            
            <?php if (isset($errorAvisos)): ?>
                <p style="color: red;">Error al consultar avisos: <?php echo htmlspecialchars($errorAvisos); ?></p>
            <?php endif; ?>

            <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; text-align: left; margin-top: 10px;">
                <thead>
                    <tr style="background-color: #ffebee;">
                        <th style="width: 160px;">Fecha / Hora</th>
                        <th style="width: 200px;">Tutor / Familia</th>
                        <th>Mensaje / Nota</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($avisosTutores)): ?>
                        <?php foreach ($avisosTutores as $aviso): ?>
                            <tr>
                                <td><small><?php echo htmlspecialchars($aviso['fecha']); ?></small></td>
                                <td><strong><?php echo htmlspecialchars($aviso['tutor_apellido'] . ', ' . $aviso['tutor_nombre']); ?></strong></td>
                                <td><?php echo nl2br(htmlspecialchars($aviso['mensaje'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align: center; color: #777; padding: 15px;">No hay avisos privados enviados por tutores para esta sala.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <!-- SECCIÓN 2: ACTIVIDADES CARGADAS POR DIRECCIÓN -->
        <section style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; color: #1976D2;">📌 Actividades Asignadas por Dirección (Sala <?php echo htmlspecialchars($sala_id); ?>)</h3>
            
            <?php if (isset($errorActividades)): ?>
                <p style="color: red;">Error en la base de datos: <?php echo htmlspecialchars($errorActividades); ?></p>
            <?php endif; ?>

            <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; text-align: left; margin-top: 15px;">
                <thead>
                    <tr style="background-color: #e3f2fd;">
                        <th>Fecha</th>
                        <th>Título</th>
                        <th>Descripción</th>
                        <th>Publicado Por</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($actividades)): ?>
                        <?php foreach ($actividades as $act): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($act['fecha']); ?></td>
                                <td><strong><?php echo htmlspecialchars($act['titulo']); ?></strong></td>
                                <td><?php echo htmlspecialchars($act['descripcion']); ?></td>
                                <td><?php echo htmlspecialchars($act['creado_por'] ?? 'Dirección'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align: center; color: #777;">No hay actividades registradas para esta sala.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

    </main>

</body>
</html>