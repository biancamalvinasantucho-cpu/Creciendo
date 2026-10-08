<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Validar que sea director (Agregado por seguridad)
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

// Procesar Formulario (Crear o Eliminar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion']) && $_POST['accion'] === 'crear') {
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $fecha = $_POST['fecha'] ?? date('Y-m-d');
        $sala_id = intval($_POST['sala_id'] ?? 1);

        if (!empty($titulo) && !empty($descripcion) && $db) {
            try {
                $stmt = $db->prepare("INSERT INTO actividades (titulo, descripcion, fecha, sala_id) VALUES (:titulo, :descripcion, :fecha, :sala_id)");
                $stmt->execute([
                    ':titulo' => $titulo,
                    ':descripcion' => $descripcion,
                    ':fecha' => $fecha,
                    ':sala_id' => $sala_id
                ]);
                $mensaje = "Actividad publicada con éxito.";
                $tipoMensaje = "exito";
            } catch (PDOException $e) {
                $mensaje = "Error de MySQL al intentar guardar: " . $e->getMessage();
                $tipoMensaje = "error";
            }
        } else {
            $mensaje = "Por favor completá el título y la descripción.";
            $tipoMensaje = "error";
        }
    } elseif (isset($_POST['accion']) && $_POST['accion'] === 'eliminar' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        if ($db) {
            try {
                $stmt = $db->prepare("DELETE FROM actividades WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $mensaje = "Actividad eliminada correctamente.";
                $tipoMensaje = "exito";
            } catch (PDOException $e) {
                $mensaje = "Error al eliminar: " . $e->getMessage();
                $tipoMensaje = "error";
            }
        }
    }
}

// Consultar actividades cargadas
$actividades = [];
if ($db) {
    try {
        $stmt = $db->query("SELECT * FROM actividades ORDER BY fecha DESC, id DESC");
        if ($stmt) {
            $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $mensaje = "Error al consultar actividades: " . $e->getMessage();
        $tipoMensaje = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Actividades | Dirección</title>
    <!-- Tu CSS enlazado correctamente[cite: 9, 11] -->
    <link rel="stylesheet" href="../../../css/panel-directores.css">
</head>
<body>

    <!-- Tu Navbar unificada[cite: 11] -->
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
                <li><a href="actividades.php" class="active">Actividades</a></li>
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

    <!-- Contenido Principal envuelto en tu clase[cite: 11] -->
    <main class="contenedor-principal">
        
        <?php if ($mensaje !== ''): ?>
            <div style="padding: 12px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; <?php echo ($tipoMensaje === 'exito') ? 'background: #d4edda; color: #155724;' : 'background: #f8d7da; color: #721c24;'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario para publicar una nueva actividad -->
        <section style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 25px;">
            <h3 style="margin-top: 0; color: #1565C0; margin-bottom: 15px;">Nueva Actividad / Indicación para Maestras</h3>
            
            <form method="POST" action="" style="display: flex; flex-direction: column; gap: 15px; max-width: 600px;">
                <input type="hidden" name="accion" value="crear">

                <div>
                    <label for="titulo" style="font-weight: bold; display: block; margin-bottom: 5px;">Título de la Actividad:</label>
                    <input type="text" id="titulo" name="titulo" required style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; box-sizing: border-box;">
                </div>

                <div style="display: flex; gap: 15px;">
                    <div style="flex: 1;">
                        <label for="fecha" style="font-weight: bold; display: block; margin-bottom: 5px;">Fecha:</label>
                        <input type="date" id="fecha" name="fecha" value="<?php echo date('Y-m-d'); ?>" required style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; box-sizing: border-box;">
                    </div>
                    <div style="flex: 1;">
                        <label for="sala_id" style="font-weight: bold; display: block; margin-bottom: 5px;">Sala Destino:</label>
                        <select name="sala_id" id="sala_id" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; box-sizing: border-box;">
                            <option value="1">Sala 1 (Lactantes)</option>
                            <option value="2">Sala 2 (1 a 2 años)</option>
                            <option value="3">Sala 3 (3 a 4 años)</option>
                            <!-- Borré la "Sala Verde id=4" porque no existía en tu SQL -->
                        </select>
                    </div>
                </div>

                <div>
                    <label for="descripcion" style="font-weight: bold; display: block; margin-bottom: 5px;">Descripción / Detalle:</label>
                    <textarea id="descripcion" name="descripcion" rows="4" required style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; box-sizing: border-box;"></textarea>
                </div>

                <button type="submit" style="padding: 12px; background: #1565C0; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">Publicar Actividad</button>
            </form>
        </section>

        <!-- Lista de Actividades Existentes -->
        <section style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; color: #333; margin-bottom: 15px;">Actividades Publicadas</h3>
            
            <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background-color: #f1f5f9;">
                        <th>Fecha</th>
                        <th>Sala</th>
                        <th>Título</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($actividades)): ?>
                        <?php foreach ($actividades as $act): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($act['fecha'] ?? ''); ?></td>
                                <td><strong>Sala <?php echo htmlspecialchars($act['sala_id'] ?? '1'); ?></strong></td>
                                <td><strong><?php echo htmlspecialchars($act['titulo'] ?? ''); ?></strong></td>
                                <td><?php echo htmlspecialchars($act['descripcion'] ?? ''); ?></td>
                                <td>
                                    <form method="POST" action="" style="margin: 0;" onsubmit="return confirm('¿Seguro que querés eliminar esta actividad?');">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id" value="<?php echo $act['id']; ?>">
                                        <button type="submit" style="background: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center; color: #777; padding: 20px;">No hay actividades registradas aún.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>