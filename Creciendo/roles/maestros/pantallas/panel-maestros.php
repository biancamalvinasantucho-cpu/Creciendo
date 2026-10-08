<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'maestro') {
    echo "<script>
            alert('Atención: Debes iniciar sesión como Maestra para ingresar.');
            window.location.href = '../../../login.html';
          </script>";
    exit;
}

require_once __DIR__ . '/../../../core/conexion.php';
$db = isset($pdo) ? $pdo : ($cnx ?? null);

if (isset($_GET['sala_id'])) {
    $_SESSION['sala_id'] = intval($_GET['sala_id']);
}

$sala_id = $_SESSION['sala_id'] ?? 1;
$maestro_nombre = $_SESSION['usuario_nombre'] ?? $_SESSION['nombre'] ?? 'Docente';
$mensaje_exito = "";
$mensaje_error = "";

// 1. PROCESAR SUBIDA DE FOTOS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subir_foto'])) {
    $descripcion_foto = trim($_POST['descripcion'] ?? 'Foto de la sala');
    
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $dirSubida = __DIR__ . '/../../../uploads/actividades/';
        
        if (!file_exists($dirSubida)) {
            mkdir($dirSubida, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($ext, $permitidas)) {
            $nombreArchivo = uniqid('foto_') . '.' . $ext;
            $rutaDestino = $dirSubida . $nombreArchivo;

            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
                if ($db) {
                    try {
                        // Guardamos en la tabla actividades o fotos_ninos según corresponda
                        $stmt = $db->prepare("INSERT INTO actividades (titulo, descripcion, sala_id, imagen, fecha, creado_por) VALUES (:titulo, :desc, :sala, :img, NOW(), :maestro)");
                        $stmt->execute([
                            ':titulo' => 'Foto de Sala',
                            ':desc' => $descripcion_foto,
                            ':sala' => $sala_id,
                            ':img' => $nombreArchivo,
                            ':maestro' => $maestro_nombre
                        ]);
                        $mensaje_exito = "¡La foto se subió y publicó correctamente para los padres!";
                    } catch (PDOException $e) {
                        $mensaje_error = "Error al registrar la foto en la base de datos: " . $e->getMessage();
                    }
                }
            } else {
                $mensaje_error = "Error al mover el archivo al servidor.";
            }
        } else {
            $mensaje_error = "Formato de imagen no permitido. Usá JPG, PNG o WEBP.";
        }
    } else {
        $mensaje_error = "Por favor seleccioná una imagen válida.";
    }
}

// GUARDAR / TOGGLE DE ESTRELLITA (LOGRO DEL NIÑO)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_hito'])) {
    $nino_id = intval($_POST['nino_id']);
    $hito_id = intval($_POST['hito_id']);
    $logrado = intval($_POST['logrado']);

    if ($db) {
        try {
            if ($logrado == 1) {
                $stmt = $db->prepare("INSERT INTO logros_ninos (nino_id, hito_id, fecha_logro) VALUES (:nino, :hito, NOW()) ON DUPLICATE KEY UPDATE fecha_logro = NOW()");
                $stmt->execute([':nino' => $nino_id, ':hito' => $hito_id]);
            } else {
                $stmt = $db->prepare("DELETE FROM logros_ninos WHERE nino_id = :nino AND hito_id = :hito");
                $stmt->execute([':nino' => $nino_id, ':hito' => $hito_id]);
            }
            $mensaje_exito = "Estado del hito actualizado.";
        } catch (PDOException $e) {
            $mensaje_error = "Error al actualizar hito: " . $e->getMessage();
        }
    }
}

// CONSULTA DE ALUMNOS FILTRADOS POR SALA
$alumnos = [];
if ($db) {
    try {
        $stmt = $db->prepare("SELECT id, nombre, apellido FROM ninos WHERE sala_id = :sala ORDER BY apellido, nombre");
        $stmt->execute([':sala' => $sala_id]);
        $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $stmt = $db->query("SELECT id, nombre, apellido FROM ninos ORDER BY apellido, nombre");
        $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$alumno_seleccionado = $_GET['alumno_id'] ?? ($alumnos[0]['id'] ?? 1);

// CONSULTA DE HITOS FILTRADOS POR LA SALA ACTUAL
$hitos = [];
$logros_ids = [];
if ($db) {
    try {
        $stmtHitos = $db->prepare("SELECT * FROM hitos_desarrollo WHERE sala_id = :sala OR sala_id IS NULL ORDER BY eje_tematico, id");
        $stmtHitos->execute([':sala' => $sala_id]);
        $hitos = $stmtHitos->fetchAll(PDO::FETCH_ASSOC);

        if (empty($hitos)) {
            $stmtHitos = $db->prepare("SELECT * FROM hitos_desarrollo WHERE id_sala = :sala ORDER BY eje_tematico, id");
            $stmtHitos->execute([':sala' => $sala_id]);
            $hitos = $stmtHitos->fetchAll(PDO::FETCH_ASSOC);
        }

        if (!empty($alumno_seleccionado)) {
            $stmtLogros = $db->prepare("SELECT hito_id FROM logros_ninos WHERE nino_id = :nino");
            $stmtLogros->execute([':nino' => $alumno_seleccionado]);
            $logros_ids = $stmtLogros->fetchAll(PDO::FETCH_COLUMN);
        }
    } catch (PDOException $e) {
        $mensaje_error = "Error al consultar hitos de la base de datos: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Maestros | Creciendo</title>
    <style>
        body { margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f9; color: #333; }
        header { background: #1877f2; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        header h2 { margin: 0; font-size: 22px; font-weight: 700; }
        .header-info { display: flex; align-items: center; gap: 15px; font-size: 14px; }
        
        main { max-width: 1200px; margin: 25px auto; padding: 0 20px; }
        
        .tabs-container { background: white; border-radius: 12px; padding: 10px; display: flex; justify-content: space-between; gap: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .tab-btn { flex: 1; padding: 12px 15px; border: none; background: transparent; border-radius: 8px; font-size: 14px; font-weight: 600; color: #555; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s ease; }
        .tab-btn:hover { background: #f0f2f5; color: #1877f2; }
        .tab-btn.active { background: #1877f2; color: white; box-shadow: 0 2px 8px rgba(24, 119, 242, 0.3); }

        .tab-content { display: none; background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .tab-content.active { display: block; }

        .panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .panel-header h3 { margin: 0; font-size: 18px; color: #222; }
        .select-alumno { padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px; font-weight: bold; }

        .hitos-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .hitos-table th { background: #f8f9fa; color: #444; padding: 12px 15px; text-align: left; font-size: 14px; border-bottom: 2px solid #e9ecef; }
        .hitos-table td { padding: 14px 15px; border-bottom: 1px solid #f1f3f5; font-size: 14px; }
        .hitos-table tr:nth-child(even) { background-color: #fafafa; }

        .star-btn { background: none; border: none; font-size: 24px; cursor: pointer; transition: transform 0.1s ease; line-height: 1; }
        .star-btn:hover { transform: scale(1.2); }
        .star-active { color: #ffc107; }
        .star-inactive { color: #ccc; }

        .btn-submit { background: #1877f2; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>

    <header>
        <h2>Creciendo | Panel Maestros</h2>
        <div class="header-info">
            <form method="GET" action="panel-maestros.php" style="margin: 0;">
                <label for="sala_id" style="color: white; font-weight: bold; margin-right: 5px;">Asignado a:</label>
                <select name="sala_id" id="sala_id" onchange="this.form.submit()" style="padding: 6px 10px; border-radius: 4px; border: none; cursor: pointer; font-weight: bold; color: #1877f2; background: white;">
                    <option value="1" <?php echo ($sala_id == 1) ? 'selected' : ''; ?>>Sala 1 (Lactantes)</option>
                    <option value="2" <?php echo ($sala_id == 2) ? 'selected' : ''; ?>>Sala 2 (1 a 2 años)</option>
                    <option value="3" <?php echo ($sala_id == 3) ? 'selected' : ''; ?>>Sala 3 (3 a 4 años)</option>
                </select>
            </form>

            <span>Hola, <strong><?php echo htmlspecialchars($maestro_nombre); ?></strong></span>
            <a href="../../../logout.php" style="color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 6px 12px; border-radius: 4px;">Cerrar Sesión</a>
        </div>
    </header>

    <main>

        <div class="tabs-container">
            <button class="tab-btn" onclick="openTab('avisos', this)">✉ Avisos de Padres</button>
            <button class="tab-btn" onclick="openTab('fotos', this)">📷 Subir Fotos</button>
            <button class="tab-btn active" onclick="openTab('avances', this)">⭐ Avances y Estrellitas</button>
            <button class="tab-btn" onclick="openTab('ia', this)">🤖 Asistente IA</button>
        </div>

        <?php if (!empty($mensaje_exito)): ?>
            <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: bold;">
                <?php echo htmlspecialchars($mensaje_exito); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($mensaje_error)): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: bold;">
                <?php echo htmlspecialchars($mensaje_error); ?>
            </div>
        <?php endif; ?>

        <div id="avisos" class="tab-content">
            <h3>✉ Novedades y Avisos de los Padres</h3>
            <p style="color: #666;">Comunicados enviados por las familias de la Sala <?php echo $sala_id; ?>.</p>
        </div>

        <!-- 2. PESTAÑA SUBIR FOTOS -->
        <div id="fotos" class="tab-content">
            <h3>📷 Galería y Subida de Fotos - Sala <?php echo $sala_id; ?></h3>
            <p style="color: #666;">Subí imágenes de las actividades diarias para que los padres puedan visualizarlas en su panel.</p>
            
            <form action="panel-maestros.php?sala_id=<?php echo $sala_id; ?>" method="POST" enctype="multipart/form-data" style="margin-top: 20px; background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0;">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Descripción o título de la foto:</label>
                    <input type="text" name="descripcion" placeholder="Ej: Jugando en el rincón blando..." required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Seleccionar archivo de imagen:</label>
                    <input type="file" name="imagen" accept="image/*" required style="padding: 5px;">
                </div>

                <button type="submit" name="subir_foto" class="btn-submit">📤 Subir y Publicar Foto</button>
            </form>
        </div>

        <div id="avances" class="tab-content active">
            <div class="panel-header">
                <h3>Control de Hitos de Desarrollo</h3>
                
                <form method="GET" action="panel-maestros.php" id="formAlumno">
                    <input type="hidden" name="sala_id" value="<?php echo $sala_id; ?>">
                    <label for="alumno_id" style="font-size: 14px; font-weight: bold; margin-right: 5px;">Alumno:</label>
                    <select name="alumno_id" id="alumno_id" class="select-alumno" onchange="document.getElementById('formAlumno').submit()">
                        <?php if (!empty($alumnos)): ?>
                            <?php foreach ($alumnos as $al): ?>
                                <option value="<?php echo $al['id']; ?>" <?php echo ($alumno_seleccionado == $al['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($al['nombre'] . ' ' . $al['apellido']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">No hay alumnos registrados en esta sala</option>
                        <?php endif; ?>
                    </select>
                </form>
            </div>

            <table class="hitos-table">
                <thead>
                    <tr>
                        <th style="width: 30%;">Eje Temático</th>
                        <th style="width: 55%;">Tema / Hito</th>
                        <th style="width: 15%; text-align: center;">Logrado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($hitos)): ?>
                        <?php foreach ($hitos as $hito): ?>
                            <?php $es_logrado = in_array($hito['id'], $logros_ids); ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($hito['eje_tematico'] ?? $hito['area'] ?? 'General'); ?></strong></td>
                                <td><?php echo htmlspecialchars($hito['titulo'] ?? $hito['descripcion'] ?? $hito['nombre'] ?? 'Hito'); ?></td>
                                <td style="text-align: center;">
                                    <form method="POST" action="panel-maestros.php?sala_id=<?php echo $sala_id; ?>" style="margin: 0;">
                                        <input type="hidden" name="nino_id" value="<?php echo $alumno_seleccionado; ?>">
                                        <input type="hidden" name="hito_id" value="<?php echo $hito['id']; ?>">
                                        <input type="hidden" name="logrado" value="<?php echo $es_logrado ? '0' : '1'; ?>">
                                        <button type="submit" name="guardar_hito" class="star-btn <?php echo $es_logrado ? 'star-active' : 'star-inactive'; ?>" title="Marcar/Desmarcar Hito">
                                            ★
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center; color: #888;">No hay hitos de desarrollo registrados en la base de datos para esta sala.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div id="ia" class="tab-content">
            <h3>🤖 Generador de Resumen de Desarrollo Infantil</h3>
            <p style="color: #666;">Seleccioná un alumno para procesar con IA su informe de desarrollo basado en los hitos registrados.</p>

            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #e0e0e0; margin-bottom: 20px; display: flex; align-items: center; gap: 15px;">
                <div>
                    <label for="alumno_ia_id" style="font-weight: bold; font-size: 14px; display: block; margin-bottom: 4px;">Alumno:</label>
                    <select id="alumno_ia_id" class="select-alumno" style="min-width: 220px;">
                        <?php foreach ($alumnos as $al): ?>
                            <option value="<?php echo $al['id']; ?>" <?php echo ($alumno_seleccionado == $al['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($al['nombre'] . ' ' . $al['apellido']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="margin-top: 20px;">
                    <button type="button" onclick="generarResumenIA()" class="btn-submit" style="background: #673ab7; cursor: pointer;">
                        ✨ Generar Resumen con IA
                    </button>
                </div>
            </div>

            <div id="loadingIA" style="display: none; padding: 15px; color: #512da8; font-weight: bold;">
                ⏳ Analizando desarrollo e hitos logrados... Aguarde un instante.
            </div>

            <div id="resultadoResumenIA" style="display: none; background: #faf5ff; border: 1px solid #d1c4e9; border-radius: 10px; padding: 20px; margin-top: 15px;">
            </div>
        </div>

    </main>

    <script>
        function openTab(tabName, btnElement) {
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => content.classList.remove('active'));

            const buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(btn => btn.classList.remove('active'));

            document.getElementById(tabName).classList.add('active');
            btnElement.classList.add('active');
        }

        function generarResumenIA() {
            const ninoId = document.getElementById('alumno_ia_id').value;
            const loading = document.getElementById('loadingIA');
            const resultado = document.getElementById('resultadoResumenIA');

            if (!ninoId) {
                alert('Por favor seleccioná un alumno.');
                return;
            }

            loading.style.display = 'block';
            resultado.style.display = 'none';

            fetch('procesar-ia.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'nino_id=' + encodeURIComponent(ninoId) + '&sala_id=' + <?php echo $sala_id; ?>
            })
            .then(response => response.json())
            .then(data => {
                loading.style.display = 'none';
                resultado.style.display = 'block';

                if (data.status === 'success') {
                    resultado.innerHTML = data.html;
                } else {
                    resultado.innerHTML = '<p style="color:red;"><b>Error:</b> ' + data.message + '</p>';
                }
            })
            .catch(error => {
                loading.style.display = 'none';
                resultado.style.display = 'block';
                resultado.innerHTML = '<p style="color:red;">Error al conectarse con procesar-ia.php.</p>';
            });
        }
    </script>

</body>
</html>