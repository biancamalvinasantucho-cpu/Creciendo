<?php
session_start();
if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] !== 'director' && $_SESSION['rol'] !== 'directores')) {
    header('Location: ../../../login.html');
    exit;
}
require_once __DIR__ . '/../../../core/conexion.php';
$db = isset($pdo) ? $pdo : ($cnx ?? null);

$fecha = $_GET['fecha'] ?? date('Y-m-d');

// Consulta las asistencias relacionando la tabla asistencias con la tabla ninos
$stmt = $db->prepare("SELECT a.*, n.nombre, n.apellido 
                      FROM asistencias a 
                      JOIN ninos n ON a.nino_id = n.id 
                      WHERE a.fecha = :fecha");
$stmt->execute([':fecha' => $fecha]);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Asistencia | Dirección</title>
    <!-- Integración de tu CSS -->
    <link rel="stylesheet" href="../../../css/panel-directores.css">
</head>
<body>

    <!-- Navbar unificada -->
    <header class="navbar">
        <div class="navbar-brand">
            <div class="brand-logo-container">
                <a href="../panel-directores.php">
                    <img src="../../../img/logo.png" alt="Logo Creciendo" class="logo-navbar">
                </a>
            </div>
            <span class="role-badge">Director/a</span>
        </div>

        <nav class="navbar-menu">
            <ul>
                <li><a href="../panel-directores.php">Inicio</a></li>
                <li><a href="asistencia.php" class="active">Asistencia</a></li>
                <li><a href="actividades.php">Actividades</a></li>
                <li><a href="tutores.php">Tutores</a></li>
                <li><a href="observaciones.html">Observaciones</a></li>
                <li><a href="comunicaciones.html">Comunicaciones</a></li>
                <li><a href="configuracion.php">Configuración</a></li>
            </ul>
        </nav>

        <div class="navbar-user">
            <span>Hola, <strong><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Director/a'); ?></strong></span>
            <a href="../../../logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="contenedor-principal">
        
        <section style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="color: #333; margin-bottom: 20px;">Control General de Asistencia</h2>
            
            <form method="GET" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center;">
                <label for="fecha" style="font-weight: bold;">Seleccionar Fecha:</label>
                <input type="date" name="fecha" id="fecha" value="<?= htmlspecialchars($fecha) ?>" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                <button type="submit" style="padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">Ver Reporte</button>
            </form>

            <table border="1" cellpadding="12" style="border-collapse: collapse; width: 100%; text-align: left;">
                <tr style="background: #f1f5f9;">
                    <th>Alumno</th>
                    <th>Estado</th>
                    <th>Hora de Registro</th>
                </tr>
                <?php if (count($registros) > 0): ?>
                    <?php foreach ($registros as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['apellido'] . ' ' . $r['nombre']) ?></td>
                        <td>
                            <span style="padding: 4px 8px; border-radius: 4px; font-weight: bold; color: white; background: <?= strtolower($r['estado']) === 'presente' ? '#10b981' : '#ef4444' ?>;">
                                <?= htmlspecialchars($r['estado']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($r['hora']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3" style="text-align: center; color: #777; padding: 20px;">No hay registros de asistencia para esta fecha.</td></tr>
                <?php endif; ?>
            </table>
        </section>

    </main>

</body>
</html>