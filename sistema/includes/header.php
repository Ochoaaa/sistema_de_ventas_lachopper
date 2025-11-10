<?php
// Inicia buffer de salida primero
ob_start();

// Inicia sesión si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica si la sesión está activa
if (empty($_SESSION['active'])) {
    header('Location: ../');
    exit;
}

// Incluimos funciones y conexión
include "includes/functions.php";
include "../conexion.php";

// Datos de la empresa
$dni = $nombre_empresa = $razonSocial = $emailEmpresa = $telEmpresa = $dirEmpresa = $igv = '';

$query_empresa = mysqli_query($conexion, "SELECT * FROM configuracion");
if ($query_empresa && mysqli_num_rows($query_empresa) > 0) {
    $infoEmpresa = mysqli_fetch_assoc($query_empresa);
    $dni = $infoEmpresa['dni'];
    $nombre_empresa = $infoEmpresa['nombre'];
    $razonSocial = $infoEmpresa['razon_social'];
    $telEmpresa = $infoEmpresa['telefono'];
    $emailEmpresa = $infoEmpresa['email'];
    $dirEmpresa = $infoEmpresa['direccion'];
    $igv = $infoEmpresa['igv'];
}

// Procedimiento almacenado
$query_data = mysqli_query($conexion, "CALL data();");
if ($query_data && mysqli_num_rows($query_data) > 0) {
    $data = mysqli_fetch_assoc($query_data);
}

// Liberar resultados pendientes para evitar "commands out of sync"
while (mysqli_more_results($conexion) && mysqli_next_result($conexion)) {
    if ($res = mysqli_store_result($conexion)) {
        mysqli_free_result($res);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Punto de Venta</title>
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/dataTables.bootstrap4.min.css">
</head>
<body id="page-top">
<div id="wrapper">
<?php include_once "includes/menu.php"; ?>
<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-primary text-white topbar mb-4 static-top shadow">
            <div class="input-group">
                <h6>Sistema de Venta</h6>
                <p class="ml-auto"><strong>México, </strong><?php echo fechaMexico(); ?></p>
            </div>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="mr-2 d-none d-lg-inline small text-white"><?php echo $_SESSION['nombre']; ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                            <?php echo $_SESSION['email']; ?>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="salir.php">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                            Salir
                        </a>
                    </div>
                </li>
            </ul>
        </nav>
