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
$tipoMensaje = '';

// TODO: acá se procesará el POST del formulario de cambio de contraseña (pendiente).

// Consultar personal (directores + maestros)
$personal = [];
if ($db) {
    try {
        $sql = "SELECT id, nombre, apellido, email, 'Director' AS rol_label FROM directores
                UNION ALL
                SELECT id, nombre, apellido, email, 'Maestro' AS rol_label FROM maestros
                ORDER BY rol_label ASC, apellido ASC, nombre ASC";
        $stmt = $db->query($sql);
        if ($stmt) {
            $personal = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $mensaje = "Error al consultar el personal: " . $e->getMessage();
        $tipoMensaje = "error";
    }
} else {
    $mensaje = "No se pudo establecer la conexión con la base de datos.";
    $tipoMensaje = "error";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración | Dirección</title>
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
                <li><a href="tutores.php">Tutores</a></li>
                <li><a href="observaciones.html">Observaciones</a></li>
                <li><a href="comunicaciones.html">Comunicaciones</a></li>
                <li><a href="configuracion.php" class="active">Configuración</a></li>
            </ul>
        </nav>

        <div class="navbar-user">
            <span>Hola, <strong><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Director/a'); ?></strong></span>
            <a href="../../../logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </header>

    <main class="contenedor-principal">

        <?php if ($mensaje !== ''): ?>
            <div style="padding: 12px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; <?php echo ($tipoMensaje === 'exito') ? 'background: #d4edda; color: #155724;' : 'background: #f8d7da; color: #721c24;'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <div style="display: flex; flex-wrap: wrap; gap: 25px; align-items: flex-start;">

            <!-- Tarjeta 1: Gestión de Personal -->
            <section style="flex: 2 1 480px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0; color: #1565C0; margin-bottom: 15px;">Gestión de Personal</h3>

                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                        <thead>
                            <tr style="background-color: #f1f5f9; color: #475569;">
                                <th style="padding: 12px; border-bottom: 2px solid #e2e8f0;">Nombre</th>
                                <th style="padding: 12px; border-bottom: 2px solid #e2e8f0;">Apellido</th>
                                <th style="padding: 12px; border-bottom: 2px solid #e2e8f0;">Email</th>
                                <th style="padding: 12px; border-bottom: 2px solid #e2e8f0;">Rol</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($personal)): ?>
                                <?php foreach ($personal as $p): ?>
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td style="padding: 12px;"><?php echo htmlspecialchars($p['nombre']); ?></td>
                                        <td style="padding: 12px;"><?php echo htmlspecialchars($p['apellido']); ?></td>
                                        <td style="padding: 12px;"><?php echo htmlspecialchars($p['email']); ?></td>
                                        <td style="padding: 12px;">
                                            <?php if ($p['rol_label'] === 'Director'): ?>
                                                <span style="background: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold;">Director</span>
                                            <?php else: ?>
                                                <span style="background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold;">Maestro</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #777; padding: 20px;">No hay personal registrado aún.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Tarjeta 2: Seguridad / Contraseñas -->
            <section style="flex: 1 1 320px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0; color: #1565C0; margin-bottom: 15px;">Seguridad / Contraseñas</h3>

                <form method="POST" action="" style="display: flex; flex-direction: column; gap: 15px;">
                    <input type="hidden" name="accion" value="cambiar_password">

                    <div>
                        <label for="rol" style="font-weight: bold; display: block; margin-bottom: 5px;">Rol del usuario:</label>
                        <select name="rol" id="rol" required style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; box-sizing: border-box;">
                            <option value="director">Director</option>
                            <option value="maestro">Maestro</option>
                            <option value="tutor">Tutor</option>
                        </select>
                    </div>

                    <div>
                        <label for="email" style="font-weight: bold; display: block; margin-bottom: 5px;">Email del usuario:</label>
                        <input type="email" id="email" name="email" required placeholder="usuario@correo.com" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; box-sizing: border-box;">
                    </div>

                    <div>
                        <label for="nueva_password" style="font-weight: bold; display: block; margin-bottom: 5px;">Nueva contraseña:</label>
                        <input type="password" id="nueva_password" name="nueva_password" required minlength="6" autocomplete="new-password" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; box-sizing: border-box;">
                    </div>

                    <button type="submit" style="padding: 12px; background: #1565C0; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">Cambiar contraseña</button>
                </form>
            </section>

        </div>
    </main>
</body>
</html>