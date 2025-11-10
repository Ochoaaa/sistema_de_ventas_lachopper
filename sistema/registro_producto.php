<?php
// Incluimos primero el header para iniciar sesión y cargar configuración
include_once "includes/header.php";
include "../conexion.php";

// Inicializamos la alerta
$alert = "";

// Procesamos el formulario
if (!empty($_POST)) {

    if (empty($_POST['proveedor']) || empty($_POST['producto']) || empty($_POST['precio']) || $_POST['precio'] < 0 || empty($_POST['cantidad']) || $_POST['cantidad'] < 0) {
        $alert = '<div class="alert alert-danger" role="alert">
                    Todos los campos son obligatorios y deben ser válidos.
                  </div>';
    } else {
        $proveedor = mysqli_real_escape_string($conexion, $_POST['proveedor']);
        $producto = mysqli_real_escape_string($conexion, $_POST['producto']);
        $precio = floatval($_POST['precio']);
        $cantidad = intval($_POST['cantidad']);
        $usuario_id = $_SESSION['idUser'];

        $query_insert = mysqli_query($conexion, "INSERT INTO producto(proveedor, descripcion, precio, existencia, usuario_id) VALUES ('$proveedor', '$producto', '$precio', '$cantidad', '$usuario_id')");

        if ($query_insert) {
            $alert = '<div class="alert alert-primary" role="alert">
                        Producto registrado correctamente.
                      </div>';
        } else {
            $alert = '<div class="alert alert-danger" role="alert">
                        Error al registrar el producto.
                      </div>';
        }
    }
}
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Panel de Administración</h1>
        <a href="lista_productos.php" class="btn btn-primary">Regresar</a>
    </div>

    <!-- Content Row -->
    <div class="row">
        <div class="col-lg-6 m-auto">
            <form action="" method="post" autocomplete="off">
                <?php echo $alert; ?>
                <div class="form-group">
                    <label>Proveedor</label>
                    <?php
                    $query_proveedor = mysqli_query($conexion, "SELECT codproveedor, proveedor FROM proveedor ORDER BY proveedor ASC");
                    ?>
                    <select id="proveedor" name="proveedor" class="form-control">
                        <?php
                        while ($row = mysqli_fetch_assoc($query_proveedor)) {
                            echo "<option value='{$row['codproveedor']}'>{$row['proveedor']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="producto">Producto</label>
                    <input type="text" placeholder="Ingrese nombre del producto" name="producto" id="producto" class="form-control">
                </div>
                <div class="form-group">
                    <label for="precio">Precio</label>
                    <input type="number" step="0.01" placeholder="Ingrese precio" class="form-control" name="precio" id="precio">
                </div>
                <div class="form-group">
                    <label for="cantidad">Cantidad</label>
                    <input type="number" placeholder="Ingrese cantidad" class="form-control" name="cantidad" id="cantidad">
                </div>
                <input type="submit" value="Guardar Producto" class="btn btn-primary">
            </form>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

<?php include_once "includes/footer.php"; ?>
