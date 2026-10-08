<?php
session_start();

// Control de seguridad: Si no está logueada como docente, mandarla al login
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'maestro' \vert{}\vert{} !isset($_SESSION['sala_id'])) {
    header("Location: login.html");
    exit;
}

// Variables obtenidas de la sesión activa
$maestro_id     =$_SESSION['usuario_id'];
$maestro_nombre =$_SESSION['usuario_nombre'];
$sala_id        =$_SESSION['sala_id']; // <--- Lee dinámicamente si es 1, 2 o 3
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creciendo - Panel de Docentes</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome para Íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .estrella-btn {
            font-size: 1.5rem;
            color: #ccc;
            cursor: pointer;
            transition: color 0.2s ease, transform 0.2s ease;
        }
        .estrella-btn.logrado {
            color: #ffc107;
            transform: scale(1.2);
        }
        .card-aviso {
            border-left: 4px solid #0d6efd;
        }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Creciendo | Panel Docente</a>
            <span class="navbar-text text-white">
                <i class="fa-solid fa-chalkboard-user me-1"></i> 
                Seño: <strong><?php echo htmlspecialchars($maestro_nombre); ?></strong> 
                | Asignada a: <strong class="badge bg-warning text-dark fs-6">Sala <?php echo $sala_id; ?></strong>
            </span>
        </div>
    </nav>

    <div class="container">
        <!-- Pestañas del Panel -->
        <ul class="nav nav-pills nav-justified mb-4 bg-white p-2 rounded shadow-sm" id="panelTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="avisos-tab" data-bs-toggle="tab" data-bs-target="#avisos" type="button"><i class="fa-solid fa-envelope me-2"></i>Avisos de Padres</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="fotos-tab" data-bs-toggle="tab" data-bs-target="#fotos" type="button"><i class="fa-solid fa-camera me-2"></i>Subir Fotos</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="avances-tab" data-bs-toggle="tab" data-bs-target="#avances" type="button"><i class="fa-solid fa-star me-2"></i>Avances y Estrellitas</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="ia-tab" data-bs-toggle="tab" data-bs-target="#ia" type="button"><i class="fa-solid fa-robot me-2"></i>Asistente IA</button>
            </li>
        </ul>

        <div class="tab-content" id="panelTabsContent">
            
            <!-- 1. BANDEJA DE AVISOS -->
            <div class="tab-pane fade show active" id="avisos" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-header bg-white"><h5 class="mb-0">Bandeja de Avisos de Sala <?php echo $sala_id; ?></h5></div>
                    <div class="card-body" id="contenedor-avisos">
                        <p class="text-muted">Cargando avisos de los padres...</p>
                    </div>
                </div>
            </div>

            <!-- 2. SUBIR FOTOS -->
            <div class="tab-pane fade" id="fotos" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-header bg-white"><h5 class="mb-0">Subir Registro Fotográfico</h5></div>
                    <div class="card-body">
                        <form id="form-subir-foto">
                            <div class="mb-3">
                                <label class="form-label">Seleccionar Alumno</label>
                                <select class="form-select" id="select-nino-foto" required>
                                    <option value="">Cargando alumnos de Sala <?php echo $sala_id; ?>...</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Foto</label>
                                <input type="file" class="form-control" id="archivo-foto" accept="image/*" required>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-upload me-2"></i>Guardar Foto</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 3. AVANCES Y ESTRELLITAS CON IA -->
            <div class="tab-pane fade" id="avances" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0">Control de Hitos de Desarrollo (Sala <?php echo $sala_id; ?>)</h5>
                        <div class="d-flex gap-2 align-items-center">
                            <select class="form-select w-auto" id="select-nino-avances" onchange="cargarHitosNino(this.value)">
                                <option value="">Seleccionar alumno...</option>
                            </select>
                            <button class="btn btn-outline-primary" onclick="generarInformeAvancesIA()">
                                <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Redactar Informe con IA
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Resultado del informe de avances -->
                        <div class="card bg-light border-info mb-3 d-none" id="caja-informe-avances">
                            <div class="card-body">
                                <h6 class="fw-bold text-primary"><i class="fa-solid fa-robot me-2"></i>Informe de Progreso (Generado por IA):</h6>
                                <p id="texto-informe-avances" class="mb-0 text-dark" style="white-space: pre-line;"></p>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Eje Temático</th>
                                        <th>Tema / Hito</th>
                                        <th class="text-center">Logrado</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla-hitos">
                                    <tr><td colspan="3" class="text-center text-muted">Seleccioná un alumno para ver sus avances.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. ASISTENTE IA PARA NOTAS -->
            <div class="tab-pane fade" id="ia" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-header bg-white"><h5 class="mb-0">Redactor Pedagógico Asistido por IA</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Notas informales de la Seño:</label>
                            <textarea class="form-control" id="notas-borrador" rows="3" placeholder="Ej: Hoy comió toda la comida solo, pero en el patio le costó compartir los juguetes."></textarea>
                        </div>
                        <button class="btn btn-success mb-3" onclick="sintetizarConIA()">
                            <i class="fa-solid fa-wand-magic-sparkles me-2"></i>Sintetizar y Organizar con IA
                        </button>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-primary">Resultado Pulido para el Informe:</label>
                            <textarea class="form-control bg-light" id="notas-resultado-ia" rows="4" readonly></textarea>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Variables de sesión extraídas de PHP
        const SALA_DOCENTE_ID = <?php echo $sala_id; ?>;
        const MAESTRO_ID      = <?php echo $maestro_id; ?>;
        const API_KEY_GEMINI  = "AIzaSy_AQ.Ab8RN6IqKgPGBKhcoAAfMTyE0yuZIb2djXnIV_ASwJx9PKiVzg";

        console.log(`Sesión iniciada: Docente ID ${MAESTRO_ID} en Sala ${SALA_DOCENTE_ID}`);

        // Alternar estado de la estrella en BD
        async function toggleEstrella(elemento, ninoId, hitoId) {
            const esLogrado = elemento.classList.toggle('logrado');
            
            try {
                await fetch('guardar-logro.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        nino_id: ninoId,
                        hito_id: hitoId,
                        estado: esLogrado
                    })
                });
            } catch (error) {
                console.error('Error al guardar logro:', error);
            }
        }

        // Generar informe de avances por IA (estrellitas)
        async function generarInformeAvancesIA() {
            const selectNino = document.getElementById('select-nino-avances');
            const nombreNino = selectNino.options[selectNino.selectedIndex]?.text || "el alumno";

            const filas = document.querySelectorAll('#tabla-hitos tr');
            let logrados = [];
            let pendientes = [];

            filas.forEach(fila => {
                const celdas = fila.querySelectorAll('td');
                if (celdas.length >= 3) {
                    const eje = celdas[0].innerText.trim();
                    const tema = celdas[1].innerText.trim();
                    const tieneEstrella = celdas[2].querySelector('.estrella-btn')?.classList.contains('logrado');

                    if (tieneEstrella) {
                        logrados.push(`${eje}: ${tema}`);
                    } else {
                        pendientes.push(`${eje}: ${tema}`);
                    }
                }
            });

            if (logrados.length === 0 && pendientes.length === 0) {
                alert("Seleccioná un alumno con hitos cargados primero.");
                return;
            }

            const cajaResumen = document.getElementById('caja-informe-avances');
            const textoResumen = document.getElementById('texto-informe-avances');
            cajaResumen.classList.remove('d-none');
            textoResumen.innerText = "Analizando estrellas y generando informe pedagógico...";

            const prompt = `Sos un orientador pedagógico de un jardín maternal (Sala ${SALA_DOCENTE_ID}).
Analizá los siguientes datos de evaluación de ${nombreNino}:

Hitos ALCANZADOS (con estrella):
${logrados.length > 0 ? logrados.map(i => `- ${i}`).join('\n') : 'Ninguno registrado aún.'}

Hitos EN PROCESO (sin estrella):
${pendientes.length > 0 ? pendientes.map(i => `- ${i}`).join('\n') : 'Todos alcanzados.'}

Con base en estos datos exactos, redactá un informe pedagógico cualitativo de 2 párrafos para transmitir a la familia. Destacá los logros actuales y menciona con tacto los puntos donde se seguirá trabajando. Usá un tono profesional y empático.`;

            try {
                const response = await fetch('https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'x-goog-api-key': API_KEY_GEMINI 
                    },
                    body: JSON.stringify({
                        contents: [{ parts: [{ text: prompt }] }]
                    })
                });

                const data = await response.json();

                if (data.error) {
                    textoResumen.innerText = `Error de API: ${data.error.message}`;
                    return;
                }

                textoResumen.innerText = data.candidates[0].content.parts[0].text;

            } catch (error) {
                console.error("Error al conectar:", error);
                textoResumen.innerText = "Error al conectar con la IA. Verificá tu red o tu API Key.";
            }
        }

        // Sintetizar notas sueltas de la seño
        async function sintetizarConIA() {
            const borrador = document.getElementById('notas-borrador').value;
            const campoResultado = document.getElementById('notas-resultado-ia');

            if (!borrador.trim()) {
                alert('Escribí una nota borrador primero.');
                return;
            }

            campoResultado.value = "Sintetizando informe pedagógico...";

            try {
                const response = await fetch('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'x-goog-api-key': API_KEY_GEMINI 
                    },
                    body: JSON.stringify({
                        contents: [{
                            parts: [{
                                text: `Sos un asistente psicopedagógico de un jardín maternal. Toma estas notas escritas por la docente de Sala ${SALA_DOCENTE_ID}: "${borrador}". 
                                Reescribilas en un informe formal, amigable y estructurado para comunicar a los padres.`
                            }]
                        }]
                    })
                });

                const data = await response.json();

                if (data.error) {
                    campoResultado.value = `Error de API: ${data.error.message}`;
                    return;
                }

                campoResultado.value = data.candidates[0].content.parts[0].text;

            } catch (error) {
                console.error("Error al conectar:", error);
                campoResultado.value = "Error al conectar con la IA. Revisá la API Key o tu conexión.";
            }
        }
    </script>
</body>
</html>