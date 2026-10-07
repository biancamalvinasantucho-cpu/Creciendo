<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'directores') {
    header('Location: ../../index.html');
    exit;
}