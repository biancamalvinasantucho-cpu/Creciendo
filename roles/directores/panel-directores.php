<?php
session_start();

// 1. Verificamos que el usuario esté logueado y tenga el rol correcto
// (Cambiado a 'director' para coincidir con el procesar-login.php fusionado)
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'director') {
    // 2. Corregido: redirigir a login.html (index.html no existe)
    header('Location: ../../login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Directores | Creciendo</title>
    <link rel="stylesheet" href="../../css/panel-directores.css">
</head>
<body>

    <!-- Navbar Superior -->
    <header class="navbar">
        <div class="navbar-brand">
            <div class="brand-logo-container">
                <!-- Enlace actualizado al PHP -->
                <a href="panel-directores.php">
                    <img src="../../img/logo.png" alt="Logo Creciendo" class="logo-navbar">
                </a>
            </div>
            <span class="role-badge">Director/a</span>
        </div>

        <nav class="navbar-menu">
            <ul>
                <li><a href="panel-directores.php" class="active">Inicio</a></li>
                <!-- 3. Enlaces corregidos apuntando a los PHP de Bianca -->
                <li><a href="pantallas/asistencia.php">Asistencia</a></li>
                <li><a href="pantallas/actividades.php">Actividades</a></li>
                <li><a href="pantallas/tutores.php">Tutores</a></li>
                <li><a href="pantallas/observaciones.html">Observaciones</a></li>
                <li><a href="pantallas/comunicaciones.html">Comunicaciones</a></li>
                <li><a href="pantallas/configuracion.php">Configuración</a></li>
            </ul>
        </nav>

        <div class="navbar-user">
            <!-- 4. Nombre dinámico traído desde la base de datos -->
            <span>Hola, <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></span>
            <a href="../../logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="contenedor-principal">
        <h1 style="color: #1e293b; margin-bottom: 10px;">Bienvenido/a al Panel Institucional</h1>
        <p style="color: #475569; margin-bottom: 30px;">Seleccioná una opción del menú superior para administrar los módulos del jardín, o utiliza los accesos rápidos a continuación.</p>

        <!-- Bloque Nuevo: Botones de Acción Rápida (Feature de Guille Integrada) -->
        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <a href="registrar-nino.php" style="display: flex; flex-direction: column; align-items: center; justify-content: center; background-color: #3b82f6; color: white; text-decoration: none; padding: 25px; border-radius: 12px; width: 220px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.2s;">
                <span style="font-size: 24px; font-weight: bold;">+</span>
                <span style="font-size: 16px; font-weight: 600; margin-top: 5px;">Registrar Alumno</span>
            </a>

            <a href="registrar-tutores.php" style="display: flex; flex-direction: column; align-items: center; justify-content: center; background-color: #10b981; color: white; text-decoration: none; padding: 25px; border-radius: 12px; width: 220px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.2s;">
                <span style="font-size: 24px; font-weight: bold;">+</span>
                <span style="font-size: 16px; font-weight: 600; margin-top: 5px;">Asignar Tutor</span>
            </a>
        </div>
    </main>

</body>
</html>