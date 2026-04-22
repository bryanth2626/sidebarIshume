<?php
include 'config/conexion.php';

/* ELIMINAR */
if(isset($_GET['eliminar'])){
    $id = $_GET['eliminar'];
    $sql = "DELETE FROM proveedores WHERE idproveedor=$id";
    $conn->query($sql);

    echo "<div class='alert alert-danger mensaje-eliminado'>
            Eliminado correctamente
          </div>";
}

/* INSERTAR*/
if ($_SERVER['REQUEST_METHOD'] == 'POST'){

    $nombre   = $_POST['nombre_proveedor'];
    $producto = $_POST['producto'];
    $fecha    = $_POST['fecha_compra'];
    $adelanto = $_POST['adelanto'];
    $total    = $_POST['total'];
    $saldo    = $_POST['saldo'];

    $sql = "INSERT INTO proveedores 
            (nombre_proveedor, producto, fecha_compra, adelanto, total, saldo)
            VALUES 
            ('$nombre', '$producto', '$fecha', '$adelanto', '$total', '$saldo')";

    if($conn->query($sql)) {
        echo "<div class='alert alert-success mensaje-exito'>
                Proveedor guardado correctamente
              </div>";
    } else {
        echo "<div class='alert alert-danger mensaje-error'>
                Error: " . $conn->error . "
              </div>";
    }
}
?>

<div class="contenedor-modulo">

    <!-- FORMULARIO-->

    <div class="contenedor-proveedores py-4">

        <h2 class="titulo-proveedores mb-4">
            <i class='bx bxs-truck me-2'></i> Registrar Proveedor
        </h2>

        <div class="tarjeta-proveedor">
            <div class="cuerpo-tarjeta">

                <form class="formulario-proveedor" action="index.php?pagina=proveedores" method="POST">

                    <div class="row g-3">

                        <div class="col-md-6 campo-formulario">
                            <label class="form-label fw-semibold">Nombre del Proveedor</label>
                            <input type="text" name="nombre_proveedor" class="form-control" placeholder="Nombre del proveedor" required>
                        </div>

                        <div class="col-md-6 campo-formulario">
                            <label class="form-label fw-semibold">Producto</label>
                            <input type="text" name="producto" class="form-control" placeholder="Nombre del producto" required>
                        </div>

                        <div class="col-md-6 campo-formulario">
                            <label class="form-label fw-semibold">Fecha de la Compra</label>
                            <input type="datetime-local" name="fecha_compra" class="form-control" required>
                        </div>

                        <div class="col-md-2 campo-formulario">
                            <label class="form-label fw-semibold">Total</label>
                            <input type="number" name="total" class="form-control" placeholder="0.00" required>
                        </div>

                        <div class="col-md-2 campo-formulario">
                            <label class="form-label fw-semibold">Adelanto</label>
                            <input type="number" name="adelanto" class="form-control" placeholder="0.00" required>
                        </div>

                        <div class="col-md-2 campo-formulario">
                            <label class="form-label fw-semibold">Saldo</label>
                            <input type="number" name="saldo" class="form-control" placeholder="0.00" required>
                        </div>

                        <div class="col-12 boton-guardar">
                            <button type="submit" class="btn btn-brand fw-semibold">
                                <i class='bx bxs-save me-1'></i> Guardar Proveedor
                            </button>
                        </div>

                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- TABLA-->

    <?php
    $sql = "SELECT * FROM proveedores";
    $resultado = $conn->query($sql);
    ?>

    <div class="tarjeta-tabla">

        <div class="header-tabla">
            <span class="icono">👥</span>
            <h3>Proveedores Registrados</h3>

            <input type="text" placeholder="Buscar proveedor..." class="buscador">
        </div>

        <table class="tabla-personalizada">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Producto</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Adelanto</th>
                    <th>Saldo</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
            <?php while ($fila = $resultado->fetch()): ?>
                <tr>
                    <td><?= $fila['idproveedor'] ?></td>
                    <td><?= $fila['nombre_proveedor'] ?></td>
                    <td><?= $fila['producto'] ?></td>
                    <td><?= $fila['fecha_compra'] ?></td>

                    <td class="color-total"><?= $fila['total'] ?></td>
                    <td class="color-adelanto"><?= $fila['adelanto'] ?></td>
                    <td class="color-saldo"><?= $fila['saldo'] ?></td>

                    <td>
                        <a href="index.php?pagina=proveedores&eliminar=<?= $fila['idproveedor'] ?>" 
                           class="btn-eliminar">
                            🗑 Eliminar
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>

        </table>
    </div>

</div>