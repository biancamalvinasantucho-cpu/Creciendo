<?php
session_start();

// Validar que sea director
if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] !== 'director' && $_SESSION['rol'] !== 'directores')) {
    header('Location: ../../../login.html');
    exit;
}

// Conexión a la base de datos (3 niveles arriba desde pantallas/)
$pathConexion = __DIR__ . '/../../../core/conexion.php';
if (!file_exists($pathConexion)) {
    die("Error fatal: No se encontró el archivo de conexión en: " . $pathConexion);
}
require_once $pathConexion;
$db = isset($pdo) ? $pdo : ($cnx ?? null);

$mensaje = '';
$tutores = [];

if ($db) {
    try {
        $sql = "SELECT
                    t.id,
                    t.nombre,
                    t.apellido,
                    t.email,
                    t.parentesco,
                    t.es_autorizado_retiro,
                    t.nino_id,
                    n.nombre   AS nino_nombre,
                    n.apellido AS nino_apellido,
                    n.sala     AS nino_sala
                FROM tutores t
                LEFT JOIN ninos n ON t.nino_id = n.id
                ORDER BY t.apellido ASC, t.nombre ASC";
        $stmt = $db->query($sql);
        if ($stmt) {
            $tutores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $mensaje = "Error al consultar los tutores: " . $e->getMessage();
    }
} else {
    $mensaje = "No se pudo establecer la conexión con la base de datos.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutores | Dirección</title>
    <link rel="stylesheet" href="../../../css/panel-directores.css">
</head>
<body>

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
                <li><a href="asistencia.php">Asistencia</a></li>
                <li><a href="actividades.php">Actividades</a></li>
                <li><a href="tutores.php" class="active">Tutores</a></li>
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

    <main class="contenedor-principal">

        <?php if ($mensaje !== ''): ?>
            <div style="padding: 12px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; background: #f8d7da; color: #721c24;">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <section style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; color: #1565C0; margin-bottom: 15px;">Listado de Tutores</h3>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                    <thead>
                        <tr style="background-color: #f1f5f9; color: #475569;">
                            <th style="padding: 12px; border-bottom: 2px solid #e2e8f0;">Tutor</th>
                            <th style="padding: 12px; border-bottom: 2px solid #e2e8f0;">Email</th>
                            <th style="padding: 12px; border-bottom: 2px solid #e2e8f0;">Parentesco</th>
                            <th style="padding: 12px; border-bottom: 2px solid #e2e8f0;">Autorizado a retirar</th>
                            <th style="padding: 12px; border-bottom: 2px solid #e2e8f0;">Niño/a asignado/a</th>
                            <th style="padding: 12px; border-bottom: 2px solid #e2e8f0;">Sala</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($tutores)): ?>
                            <?php foreach ($tutores as $t): ?>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 12px;">
                                        <strong><?php echo htmlspecialchars(($t['apellido'] ?? '') . ', ' . ($t['nombre'] ?? '')); ?></strong>
                                    </td>
                                    <td style="padding: 12px;"><?php echo htmlspecialchars($t['email'] ?? ''); ?></td>
                                    <td style="padding: 12px;">
                                        <?php echo !empty($t['parentesco']) ? htmlspecialchars($t['parentesco']) : '<span style="color: #94a3b8;">—</span>'; ?>
                                    </td>
                                    <td style="padding: 12px;">
                                        <?php if (!empty($t['es_autorizado_retiro'])): ?>
                                            <span style="background: #d4edda; color: #155724; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold;">Sí</span>
                                        <?php else: ?>
                                            <span style="background: #f8d7da; color: #721c24; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold;">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if ($t['nino_id'] === null || $t['nino_nombre'] === null): ?>
                                        <td style="padding: 12px; color: #94a3b8; font-style: italic;">Sin asignar</td>
                                        <td style="padding: 12px; color: #94a3b8;">—</td>
                                    <?php else: ?>
                                        <td style="padding: 12px;">
                                            <?php echo htmlspecialchars($t['nino_apellido'] . ', ' . $t['nino_nombre']); ?>
                                        </td>
                                        <td style="padding: 12px;"><?php echo htmlspecialchars($t['nino_sala'] ?? ''); ?></td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: #777; padding: 20px;">No hay tutores registrados aún.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>