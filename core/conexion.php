<?php
$host = "localhost";
$usuario = "root";
$contrasenia = ""; // En XAMPP por defecto va vacío
$base_datos = "creciendo"; // Verifica que coincide con tu base en phpMyAdmin

try {
    $pdo = new PDO("mysql:host=$host;dbname=$base_datos;charset=utf8", $usuario, $contrasenia);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Asignamos también $cnx por si la usas en otros archivos
    $cnx = $pdo; 
} catch (PDOException $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}