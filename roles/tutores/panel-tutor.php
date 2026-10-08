<?php
/**
 * Creciendo · Panel de Tutores (código base en un solo archivo)
 * PHP 7.4+. Para probarlo en local:  php -S localhost:8000 creciendo_panel_tutores.php
 */
declare(strict_types=1);
session_start();

const MODO_DEMO    = true;  // false = usa la base de datos (ver db())
const QR_INTERVALO = 30;    // segundos que dura cada token del QR

const TIPOS_AVISO = [
    'retira_hoy'        => 'Quién retira hoy',
    'retiro_anticipado' => 'Retiro anticipado',
    'ausencia'          => 'Aviso de ausencia',
    'otro'              => 'Otro mensaje',
];
const TIPOS_NOVEDAD = [ // [etiqueta, color Bootstrap, ícono]
    'aviso'       => ['Aviso', 'warning', 'bi-megaphone'],
    'reporte'     => ['Reporte diario', 'info', 'bi-journal-text'],
    'publicacion' => ['Publicación', 'success', 'bi-image'],
];

// El secreto del QR NO va en el código: variable de entorno CRECIENDO_QR_SECRET.
$secreto = getenv('CRECIENDO_QR_SECRET');
if (!$secreto) {
    if (!MODO_DEMO) { http_response_code(500); exit('Falta CRECIENDO_QR_SECRET'); }
    $secreto = 'solo-demo-no-usar-en-produccion';
}
define('QR_SECRETO', $secreto);

function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/* ---------- Sesión del tutor ----------
 * Integración: tu login.php debe dejar $_SESSION['tutor'] =
 * ['id'=>, 'nombre'=>, 'apellido'=>, 'dni'=>, 'vinculo'=>]. */
$tutor = $_SESSION['tutor'] ?? null;
if (!$tutor && MODO_DEMO) {
    $tutor = $_SESSION['tutor'] = ['id' => 88, 'nombre' => 'Laura', 'apellido' => 'Martínez', 'dni' => '28.456.789', 'vinculo' => 'Madre'];
}
if (!$tutor) { header('Location: login.php'); exit; }
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));

/* ---------- Acceso a datos (demo o PDO) ---------- */
function db(): PDO {
    static $pdo = null;
    return $pdo ??= new PDO(getenv('CRECIENDO_DB_DSN'), getenv('CRECIENDO_DB_USER'), getenv('CRECIENDO_DB_PASS'),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
}

function ninos_del_tutor(int $tutorId): array {
    if (MODO_DEMO) {
        return [
            ['id' => 145, 'nombre' => 'Sofía', 'apellido' => 'Martínez', 'sala_id' => 2, 'sala' => 'Sala 2'],
            ['id' => 212, 'nombre' => 'Tomás', 'apellido' => 'Martínez', 'sala_id' => 1, 'sala' => 'Sala 1'],
        ];
    }
    $st = db()->prepare('SELECT n.id, n.nombre, n.apellido, s.id AS sala_id, s.nombre AS sala
        FROM nino_tutor nt JOIN ninos n ON n.id = nt.nino_id JOIN salas s ON s.id = n.sala_id
        WHERE nt.tutor_id = ? AND n.activo = 1 ORDER BY n.nombre');
    $st->execute([$tutorId]);
    return $st->fetchAll();
}

// Solo novedades de la institución (sala_id NULL) o de las salas de SUS hijos.
function obtener_novedades(array $salaIds): array {
    if (MODO_DEMO) {
        $demo = [
            ['tipo' => 'aviso', 'titulo' => 'Reunión de familias', 'contenido' => 'El jueves a las 17 hs en el SUM. ¡Los esperamos!', 'autor' => 'Dirección', 'sala_id' => null, 'fecha_hora' => date('Y-m-d H:i:s', strtotime('-1 hour'))],
            ['tipo' => 'reporte', 'titulo' => 'Reporte del día', 'contenido' => 'Hoy almorzaron bien, durmieron 1 hora y pintamos con las manos conociendo a Andy Warhol.', 'autor' => 'Seño Agustina', 'sala_id' => 2, 'fecha_hora' => date('Y-m-d H:i:s', strtotime('-3 hours'))],
            ['tipo' => 'publicacion', 'titulo' => 'Salimos al patio', 'contenido' => 'Jugamos libremente y repasamos las normas de convivencia.', 'autor' => 'Seño Angy', 'sala_id' => 2, 'fecha_hora' => date('Y-m-d H:i:s', strtotime('-5 hours'))],
            ['tipo' => 'reporte', 'titulo' => 'Momento de relajación', 'contenido' => 'Descanso de 1 hora después del almuerzo.', 'autor' => 'Seño Pato', 'sala_id' => 1, 'fecha_hora' => date('Y-m-d H:i:s', strtotime('-6 hours'))],
            ['tipo' => 'publicacion', 'titulo' => 'Taller de música', 'contenido' => 'Esta publicación es de Sala 3: este tutor NO la ve.', 'autor' => 'Seño Fiorela', 'sala_id' => 3, 'fecha_hora' => date('Y-m-d H:i:s', strtotime('-7 hours'))],
        ];
        return array_values(array_filter($demo, fn($n) => $n['sala_id'] === null || in_array($n['sala_id'], $salaIds, true)));
    }
    // Adaptá "novedades" a tu esquema (p. ej. UNION de avisos + publicaciones).
    $in = $salaIds ? implode(',', array_fill(0, count($salaIds), '?')) : 'NULL';
    $st = db()->prepare("SELECT tipo, titulo, contenido, autor, sala_id, fecha_hora FROM novedades
        WHERE sala_id IS NULL OR sala_id IN ($in) ORDER BY fecha_hora DESC LIMIT 50");
    $st->execute($salaIds);
    return $st->fetchAll();
}

// El tipo se antepone al texto para encajar con avisos_privados(nino_id, remitente_nombre, mensaje, fecha_envio, leido).
function guardar_aviso(array $tutor, int $ninoId, string $tipo, string $mensaje): void {
    if (MODO_DEMO) { return; }
    db()->prepare('INSERT INTO avisos_privados (nino_id, remitente_nombre, mensaje, fecha_envio, leido) VALUES (?, ?, ?, NOW(), 0)')
        ->execute([$ninoId, $tutor['nombre'] . ' ' . $tutor['apellido'], '[' . TIPOS_AVISO[$tipo] . '] ' . $mensaje]);
}

/* ---------- QR dinámico: token firmado que rota cada QR_INTERVALO s ---------- */
function firma_qr(int $tutorId, int $ventana): string {
    return substr(hash_hmac('sha256', $tutorId . '|' . $ventana, QR_SECRETO), 0, 32);
}
function generar_token_qr(int $tutorId, int $ventana): string {
    return 'CRE1.' . $tutorId . '.' . $ventana . '.' . firma_qr($tutorId, $ventana);
}
/**
 * Para el endpoint de la puerta: devuelve el tutor_id si el token es válido, o null.
 * Vale la ventana actual y la anterior (~30-60 s). Después hay que buscar sus hijos y
 * mostrar fotos para la confirmación visual de la docente. Para token de un solo uso,
 * guardá la ventana consumida y rechazá repetidos.
 */
function validar_token_qr(string $token): ?int {
    $p = explode('.', $token);
    if (count($p) !== 4 || $p[0] !== 'CRE1' || !ctype_digit($p[1]) || !ctype_digit($p[2])) { return null; }
    $actual = intdiv(time(), QR_INTERVALO);
    $ventana = (int)$p[2];
    if ($ventana > $actual || $ventana < $actual - 1) { return null; }
    return hash_equals(firma_qr((int)$p[1], $ventana), $p[3]) ? (int)$p[1] : null;
}

if (($_GET['accion'] ?? '') === 'token') {
    header('Content-Type: application/json');
    header('Cache-Control: no-store');
    $ahora = time();
    echo json_encode([
        'token'     => generar_token_qr((int)$tutor['id'], intdiv($ahora, QR_INTERVALO)),
        'intervalo' => QR_INTERVALO,
        'restante'  => QR_INTERVALO - ($ahora % QR_INTERVALO),
    ]);
    exit;
}

/* ---------- Pantalla y formulario de avisos ---------- */
$ninos   = ninos_del_tutor((int)$tutor['id']);
$salaIds = array_values(array_unique(array_column($ninos, 'sala_id')));
$tab     = in_array($_GET['tab'] ?? '', ['novedades', 'credencial', 'avisos'], true) ? $_GET['tab'] : 'novedades';
$ok      = isset($_GET['ok']);
$errores = [];
$v       = ['nino_id' => 0, 'tipo' => '', 'mensaje' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post = fn(string $k): string => is_string($_POST[$k] ?? null) ? trim($_POST[$k]) : '';
    if (!hash_equals($_SESSION['csrf'], $post('csrf'))) { http_response_code(400); exit('Solicitud inválida'); }
    $v = ['nino_id' => (int)$post('nino_id'), 'tipo' => $post('tipo'), 'mensaje' => $post('mensaje')];

    // El alumno elegido tiene que ser hijo/a de este tutor: nunca se confía en el id que llega del navegador.
    if (!in_array($v['nino_id'], array_column($ninos, 'id'), true)) { $errores[] = 'Elegí un alumno válido.'; }
    if (!isset(TIPOS_AVISO[$v['tipo']])) { $errores[] = 'Elegí el tipo de aviso.'; }
    $largo = mb_strlen($v['mensaje']);
    if ($largo < 5 || $largo > 500) { $errores[] = 'El mensaje debe tener entre 5 y 500 caracteres.'; }

    if (!$errores) {
        guardar_aviso($tutor, $v['nino_id'], $v['tipo'], $v['mensaje']);
        header('Location: ?tab=avisos&ok=1');
        exit;
    }
    $tab = 'avisos';
}

$novedades = obtener_novedades($salaIds);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Creciendo · Panel de Tutores</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;800&display=swap" rel="stylesheet">
<style>
  :root { --cre-teal:#0E7C74; --cre-deep:#163A4D; --cre-bg:#F2F6F5; --cre-ink:#16302C; }
  body { background:var(--cre-bg); color:var(--cre-ink); }
  h1,h2,h3,.navbar-brand,.marca { font-family:'Nunito',sans-serif; }
  .navbar-brand { font-weight:800; color:var(--cre-teal) !important; }
  .nav-pills .nav-link { color:var(--cre-ink); font-weight:600; border-radius:14px; }
  .nav-pills .nav-link.active { background:var(--cre-teal); }
  .btn-cre { background:var(--cre-teal); color:#fff; border-radius:14px; font-weight:600; }
  .btn-cre:hover { background:#0a5f59; color:#fff; }

  /* Credencial: bordes redondeados, ranura de cordón y perforaciones tipo ticket */
  .credencial { max-width:340px; margin:0 auto; background:#fff; border-radius:28px; position:relative; overflow:hidden; box-shadow:0 12px 32px rgba(22,48,44,.14); }
  .credencial .cabecera { background:linear-gradient(135deg,var(--cre-teal),var(--cre-deep)); color:#fff; padding:44px 24px 22px; text-align:center; }
  .credencial .ranura { position:absolute; top:14px; left:50%; transform:translateX(-50%); width:64px; height:12px; border-radius:12px; background:var(--cre-bg); box-shadow:inset 0 2px 3px rgba(0,0,0,.25); }
  .perforacion { position:relative; border-top:2px dashed #cfdad8; margin:0 20px; }
  .perforacion::before, .perforacion::after { content:""; position:absolute; top:-14px; width:26px; height:26px; border-radius:50%; background:var(--cre-bg); box-shadow:inset 0 2px 4px rgba(0,0,0,.12); }
  .perforacion::before { left:-33px; }
  .perforacion::after  { right:-33px; }
  #barra { transition:width 1s linear; background:var(--cre-teal); }
</style>
</head>
<body>

<nav class="navbar bg-white border-bottom">
  <div class="container" style="max-width:720px">
    <a class="navbar-brand" href="?"><i class="bi bi-flower1"></i> Creciendo</a>
    <div class="d-flex align-items-center gap-3">
      <span class="small text-secondary d-none d-sm-inline"><?= h($tutor['nombre'] . ' ' . $tutor['apellido']) ?></span>
      <a class="btn btn-sm btn-outline-secondary rounded-pill" href="logout.php">Salir</a>
    </div>
  </div>
</nav>

<main class="container py-4" style="max-width:720px">
  <h1 class="h4 fw-bold mb-3">Panel de Tutores de Creciendo</h1>

  <ul class="nav nav-pills nav-fill gap-1 mb-4" role="tablist">
    <?php foreach ([['novedades', 'bi-newspaper', 'Novedades'], ['credencial', 'bi-qr-code', 'Credencial'], ['avisos', 'bi-chat-dots', 'Avisos']] as [$id, $ico, $txt]): ?>
      <li class="nav-item" role="presentation">
        <button class="nav-link <?= $tab === $id ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#pane-<?= $id ?>" type="button" role="tab">
          <i class="bi <?= $ico ?>"></i> <?= $txt ?>
        </button>
      </li>
    <?php endforeach; ?>
  </ul>

  <div class="tab-content">

    <!-- 1. Muro de novedades -->
    <section class="tab-pane fade <?= $tab === 'novedades' ? 'show active' : '' ?>" id="pane-novedades" role="tabpanel">
      <?php foreach ($novedades as $n): [$etiqueta, $color, $icono] = TIPOS_NOVEDAD[$n['tipo']] ?? TIPOS_NOVEDAD['publicacion']; ?>
        <article class="card border-0 shadow-sm rounded-4 mb-3">
          <div class="card-body">
            <span class="badge text-bg-<?= $color ?> rounded-pill mb-2"><i class="bi <?= $icono ?>"></i> <?= h($etiqueta) ?></span>
            <h2 class="h6 fw-bold mb-1"><?= h($n['titulo']) ?></h2>
            <p class="mb-2"><?= h($n['contenido']) ?></p>
            <div class="small text-secondary"><?= h($n['autor']) ?> · <?= h(date('d/m H:i', strtotime($n['fecha_hora']))) ?></div>
          </div>
        </article>
      <?php endforeach; ?>
      <?php if (!$novedades): ?><p class="text-secondary">Todavía no hay novedades en Creciendo para tu sala.</p><?php endif; ?>
    </section>

    <!-- 2. Credencial de retiro con QR dinámico -->
    <section class="tab-pane fade <?= $tab === 'credencial' ? 'show active' : '' ?>" id="pane-credencial" role="tabpanel">
      <div class="credencial">
        <span class="ranura"></span>
        <div class="cabecera">
          <div class="small opacity-75">Credencial de retiro</div>
          <div class="marca fs-2 fw-bold">Creciendo</div>
        </div>
        <div class="p-4 text-center">
          <div class="fw-bold fs-5"><?= h($tutor['nombre'] . ' ' . $tutor['apellido']) ?></div>
          <span class="badge text-bg-light border rounded-pill my-1"><?= h($tutor['vinculo']) ?></span>
          <div class="small text-secondary">DNI <?= h($tutor['dni']) ?></div>
          <div class="small mt-3">Autorizado/a a retirar a:</div>
          <?php foreach ($ninos as $n): ?>
            <div class="fw-semibold"><?= h($n['nombre'] . ' ' . $n['apellido']) ?> <span class="text-secondary fw-normal small">(<?= h($n['sala']) ?>)</span></div>
          <?php endforeach; ?>
        </div>
        <div class="perforacion"></div>
        <div class="p-4 text-center">
          <div id="qr" class="d-inline-block p-2 bg-white rounded-3 border"></div>
          <div class="progress mt-3" style="height:6px"><div id="barra" class="progress-bar" style="width:100%"></div></div>
          <p class="small text-secondary mt-2 mb-0">Se renueva en <strong id="cuenta">--</strong> s. No compartas capturas: vence en segundos.</p>
        </div>
      </div>
    </section>

    <!-- 3. Aviso a la maestra -->
    <section class="tab-pane fade <?= $tab === 'avisos' ? 'show active' : '' ?>" id="pane-avisos" role="tabpanel">
      <?php if ($ok): ?><div class="alert alert-success rounded-4">Tu aviso llegó a la sala. ¡Gracias!</div><?php endif; ?>
      <?php if ($errores): ?>
        <div class="alert alert-danger rounded-4"><ul class="mb-0"><?php foreach ($errores as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul></div>
      <?php endif; ?>
      <form method="post" action="?tab=avisos" class="card border-0 shadow-sm rounded-4 p-4" novalidate>
        <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
        <div class="mb-3">
          <label class="form-label fw-semibold" for="nino_id">Alumno/a</label>
          <select class="form-select" id="nino_id" name="nino_id" required>
            <option value="">Elegí...</option>
            <?php foreach ($ninos as $n): ?>
              <option value="<?= h($n['id']) ?>" <?= $v['nino_id'] === (int)$n['id'] ? 'selected' : '' ?>><?= h($n['nombre'] . ' ' . $n['apellido'] . ' (' . $n['sala'] . ')') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold" for="tipo">Motivo</label>
          <select class="form-select" id="tipo" name="tipo" required>
            <option value="">Elegí...</option>
            <?php foreach (TIPOS_AVISO as $clave => $texto): ?>
              <option value="<?= h($clave) ?>" <?= $v['tipo'] === $clave ? 'selected' : '' ?>><?= h($texto) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold" for="mensaje">Mensaje para la seño</label>
          <textarea class="form-control" id="mensaje" name="mensaje" rows="4" maxlength="500" required
            placeholder="Ej: Hoy retira la abuela Marta (DNI 12.345.678) a las 15 hs."><?= h($v['mensaje']) ?></textarea>
        </div>
        <button class="btn btn-cre py-2" type="submit"><i class="bi bi-send"></i> Enviar aviso</button>
      </form>
    </section>

  </div>
</main>

<footer class="text-center text-secondary small pb-4">© <?= date('Y') ?> Creciendo. Panel de Tutores.</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
  // QR dinámico: el servidor firma un token nuevo cada ventana; acá solo se pide y se dibuja.
  let qr = null, timer = null, restante = 0, intervalo = 30, cargando = false;
  const $cuenta = document.getElementById('cuenta'), $barra = document.getElementById('barra');

  async function pedirToken() {
    if (cargando) return;
    cargando = true;
    try {
      const r = await fetch('?accion=token', { cache: 'no-store', headers: { 'Accept': 'application/json' } });
      if (r.status === 401 || r.redirected) { location.reload(); return; }
      const d = await r.json();
      restante = d.restante; intervalo = d.intervalo;
      if (qr) { qr.makeCode(d.token); }
      else { qr = new QRCode(document.getElementById('qr'), { text: d.token, width: 200, height: 200, correctLevel: QRCode.CorrectLevel.M }); }
    } catch (e) {
      $cuenta.textContent = 'sin conexión';
    } finally { cargando = false; pintar(); }
  }
  function pintar() {
    const s = Math.max(restante, 0);
    if (!$cuenta.textContent.includes('sin')) $cuenta.textContent = s;
    $barra.style.width = (s / intervalo * 100) + '%';
  }
  function tick() { restante--; if (restante <= 0) pedirToken(); pintar(); }
  function iniciar() { if (timer) return; pedirToken(); timer = setInterval(tick, 1000); }
  function detener() { clearInterval(timer); timer = null; }

  const tabCred = document.querySelector('[data-bs-target="#pane-credencial"]');
  document.querySelectorAll('[data-bs-toggle="pill"]').forEach(b => b.addEventListener('shown.bs.tab', e => {
    e.target === tabCred ? iniciar() : detener();
  }));
  // Si el celular se bloqueó, el token mostrado puede estar vencido: pedir uno nuevo al volver.
  document.addEventListener('visibilitychange', () => { if (!document.hidden && timer) pedirToken(); });
  if (tabCred.classList.contains('active')) iniciar();
</script>
</body>
</html>