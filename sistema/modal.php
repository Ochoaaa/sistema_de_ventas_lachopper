<?php
include("../conexion.php");
session_start();

// Función para enviar JSON y salir
function response($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Función para calcular totales e IVA
function calcularTotales($detalle, $iva) {
    $sub_total = 0;
    foreach ($detalle as &$item) {
        $item['precioTotal'] = round($item['cantidad'] * $item['precio_venta'], 2);
        $sub_total += $item['precioTotal'];
    }
    $impuesto = round($sub_total * ($iva / 100), 2);
    $total = round($sub_total + $impuesto, 2);

    return ['detalle' => $detalle, 'sub_total' => $sub_total, 'impuesto' => $impuesto, 'total' => $total];
}

if (!empty($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {

        // Obtener información de un producto
        case 'infoProducto':
            if (!empty($_POST['producto']) && is_numeric($_POST['producto'])) {
                $stmt = $conexion->prepare("SELECT codproducto, descripcion, precio, existencia FROM producto WHERE codproducto = ?");
                $stmt->bind_param("i", $_POST['producto']);
                $stmt->execute();
                $result = $stmt->get_result();
                response($result->num_rows > 0 ? $result->fetch_assoc() : 0);
            }
            response("error");
            break;

        // Buscar cliente por DNI
        case 'searchCliente':
            if (!empty($_POST['cliente'])) {
                $stmt = $conexion->prepare("SELECT * FROM cliente WHERE dni = ?");
                $stmt->bind_param("s", $_POST['cliente']);
                $stmt->execute();
                $result = $stmt->get_result();
                response($result->num_rows > 0 ? $result->fetch_assoc() : 0);
            }
            response("error");
            break;

        // Agregar nuevo cliente
        case 'addCliente':
            $dni = $_POST['dni_cliente'] ?? '';
            $nombre = $_POST['nom_cliente'] ?? '';
            $telefono = $_POST['tel_cliente'] ?? '';
            $direccion = $_POST['dir_cliente'] ?? '';
            $usuario_id = $_SESSION['idUser'] ?? 0;

            if ($dni && $nombre) {
                $stmt = $conexion->prepare("INSERT INTO cliente(dni, nombre, telefono, direccion, usuario_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssi", $dni, $nombre, $telefono, $direccion, $usuario_id);
                response($stmt->execute() ? $conexion->insert_id : "error");
            }
            response("error");
            break;

        // Agregar producto al detalle temporal
        case 'addProductoDetalle':
            $idProducto = $_POST['producto_id'] ?? 0;
            $cantidad = $_POST['cantidad'] ?? 0;
            $usuario = $_SESSION['idUser'] ?? 0;

            if ($idProducto > 0 && $cantidad > 0) {
                $stmt = $conexion->prepare("SELECT precio, existencia FROM producto WHERE codproducto = ?");
                $stmt->bind_param("i", $idProducto);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $producto = $result->fetch_assoc();
                    if ($producto['existencia'] >= $cantidad) {
                        // Insertar en detalle temporal
                        $stmt2 = $conexion->prepare("INSERT INTO detalle_temp(codproducto, cantidad, precio_venta, usuario_id) VALUES (?, ?, ?, ?)");
                        $stmt2->bind_param("iddi", $idProducto, $cantidad, $producto['precio'], $usuario);
                        response($stmt2->execute() ? 1 : 0);
                    } else {
                        response("stock"); // No hay suficiente stock
                    }
                } else {
                    response(0);
                }
            }
            response("error");
            break;

        // Eliminar producto del detalle temporal
        case 'delProductoDetalle':
            $idDetalle = $_POST['id_detalle'] ?? 0;
            if ($idDetalle > 0) {
                $stmt = $conexion->prepare("DELETE FROM detalle_temp WHERE id = ?");
                $stmt->bind_param("i", $idDetalle);
                response($stmt->execute() ? 1 : 0);
            }
            response("error");
            break;

        // Obtener detalle temporal
        case 'getDetalleTemp':
            $usuario = $_SESSION['idUser'] ?? 0;
            $stmt = $conexion->prepare("SELECT dt.id, p.descripcion, dt.cantidad, dt.precio_venta FROM detalle_temp dt INNER JOIN producto p ON dt.codproducto = p.codproducto WHERE dt.usuario_id = ?");
            $stmt->bind_param("i", $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            $detalle = $result->fetch_all(MYSQLI_ASSOC);

            $iva = 18; // Ajustar IVA según necesidad
            response(calcularTotales($detalle, $iva));
            break;

        // Procesar venta
        case 'procesarVenta':
            $usuario = $_SESSION['idUser'] ?? 0;
            $idCliente = $_POST['id_cliente'] ?? 0;
            $tipoComprobante = $_POST['tipo_comprobante'] ?? 'Boleta';
            $stmt = $conexion->prepare("SELECT * FROM detalle_temp WHERE usuario_id = ?");
            $stmt->bind_param("i", $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            $detalle = $result->fetch_all(MYSQLI_ASSOC);

            if ($detalle && $idCliente > 0) {
                $iva = 18;
                $totales = calcularTotales($detalle, $iva);
                $sub_total = $totales['sub_total'];
                $impuesto = $totales['impuesto'];
                $total = $totales['total'];

                $stmt2 = $conexion->prepare("INSERT INTO venta(cliente_id, usuario_id, tipo_comprobante, subtotal, impuesto, total) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt2->bind_param("iissdd", $idCliente, $usuario, $tipoComprobante, $sub_total, $impuesto, $total);
                if ($stmt2->execute()) {
                    $idVenta = $conexion->insert_id;
                    $stmt3 = $conexion->prepare("INSERT INTO detalle_venta(venta_id, codproducto, cantidad, precio_venta) VALUES (?, ?, ?, ?)");
                    foreach ($detalle as $item) {
                        $stmt3->bind_param("iiid", $idVenta, $item['codproducto'], $item['cantidad'], $item['precio_venta']);
                        $stmt3->execute();
                    }
                    $conexion->query("DELETE FROM detalle_temp WHERE usuario_id = $usuario");
                    response(['status' => 1, 'idVenta' => $idVenta]);
                } else {
                    response(['status' => 0]);
                }
            }
            response(['status' => 0]);
            break;

        // Cambiar contraseña
        case 'changePasword':
            $passActual = $_POST['passActual'] ?? '';
            $passNuevo = $_POST['passNuevo'] ?? '';
            $idUser = $_SESSION['idUser'] ?? 0;

            if ($passActual && $passNuevo && $idUser > 0) {
                $passActualMd5 = md5($passActual);
                $passNuevoMd5 = md5($passNuevo);
                $stmt = $conexion->prepare("SELECT * FROM usuario WHERE clave = ? AND idusuario = ?");
                $stmt->bind_param("si", $passActualMd5, $idUser);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $update = $conexion->prepare("UPDATE usuario SET clave = ? WHERE idusuario = ?");
                    $update->bind_param("si", $passNuevoMd5, $idUser);
                    response($update->execute() ? ['cod'=>'00','msg'=>"Contraseña actualizada"] : ['cod'=>'2','msg'=>"Error al actualizar"]);
                } else {
                    response(['cod'=>'1','msg'=>"Contraseña actual incorrecta"]);
                }
            }
            response("error");
            break;

        default:
            response("acción no válida");
    }
}

mysqli_close($conexion);
exit;
?>
