<?php
$host = getenv('DB_HOST') ?: 'db';
$usuario = getenv('DB_USER') ?: 'usuario';
$password = getenv('DB_PASS') ?: 'contraseña';
$nombre_bd = getenv('DB_NAME') ?: 'sistema_venta';

$conexion = mysqli_connect($host, $usuario, $password, $nombre_bd);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}



