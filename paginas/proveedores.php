<?php
include 'config/conexion.php';
/* =========================
   ELIMINAR
========================= */

if(isset($_GET['eliminar'])){

    $idcompra = $_GET['eliminar'];
    $idproveedor = $_GET['proveedor'];

    try{

        $conn->beginTransaction();

        /* 1. ELIMINAR DETALLE */
        $sqlDetalle = "DELETE FROM detalle_compras
                       WHERE idcompra = :idcompra";

        $stmt = $conn->prepare($sqlDetalle);

        $stmt->execute([
            'idcompra' => $idcompra
        ]);

        /* 2. ELIMINAR COMPRA */
        $sqlCompra = "DELETE FROM compras
                      WHERE idcompra = :idcompra";

        $stmt = $conn->prepare($sqlCompra);

        $stmt->execute([
            'idcompra' => $idcompra
        ]);

        /* 3. ELIMINAR PROVEEDOR */
        $sqlProveedor = "DELETE FROM proveedores
                         WHERE idproveedor = :idproveedor";

        $stmt = $conn->prepare($sqlProveedor);

        $stmt->execute([
            'idproveedor' => $idproveedor
        ]);

        $conn->commit();

        echo "<div class='alert alert-success'>
                Compra eliminada correctamente
              </div>";

    }catch(Exception $e){

        $conn->rollBack();

        echo "<div class='alert alert-danger'>
                Error: ".$e->getMessage()."
              </div>";
    }
}

/* =========================
   INSERTAR DATOS (3 TABLAS)
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['editar'])){

    $nombre   = $_POST['nombre_proveedor'];
    $producto = $_POST['producto'];
    $fecha    = $_POST['fecha_compra'];
    $cantidad = $_POST['cantidad'];
    $precio   = $_POST['precio'];
    $adelanto = $_POST['adelanto'];

    $total = $cantidad * $precio;
    $saldo = $total - $adelanto;

    try {

        // 🔥 IMPORTANTE (transacción)
        $conn->beginTransaction();

        /* 1. PROVEEDOR */
        $sqlProveedor = "INSERT INTO proveedores (nombre_proveedor) VALUES (:nombre)";
        $stmt = $conn->prepare($sqlProveedor);
        $stmt->execute(['nombre' => $nombre]);

        $idproveedor = $conn->lastInsertId();

        /* 2. COMPRA */
        $sqlCompra = "INSERT INTO compras (idproveedor, fecha_compra, total, adelanto, saldo)
                      VALUES (:idproveedor, :fecha, :total, :adelanto, :saldo)";
        $stmt = $conn->prepare($sqlCompra);
        $stmt->execute([
            'idproveedor' => $idproveedor,
            'fecha' => $fecha,
            'total' => $total,
            'adelanto' => $adelanto,
            'saldo' => $saldo
        ]);

        $idcompra = $conn->lastInsertId();

        /* 3. DETALLE */
        $sqlDetalle = "INSERT INTO detalle_compras (idcompra, producto, cantidad, precio)
                       VALUES (:idcompra, :producto, :cantidad, :precio)";
        $stmt = $conn->prepare($sqlDetalle);
        $stmt->execute([
            'idcompra' => $idcompra,
            'producto' => $producto,
            'cantidad' => $cantidad,
            'precio' => $precio
        ]);

        // ✅ TODO OK
        $conn->commit();

        echo "<div class='alert alert-success'>Guardado correctamente</div>";

    } catch (Exception $e) {

        // ❌ ERROR → revertir todo
        $conn->rollBack();

        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}
/* =========================
   EDITAR DATOS
========================= */

if(isset($_POST['editar'])){

    $idcompra  = $_POST['idcompra'];
    $idproveedor = $_POST['idproveedor'];

    $nombre   = $_POST['nombre_proveedor'];
    $producto = $_POST['producto'];
    $fecha    = $_POST['fecha_compra'];
    $cantidad = $_POST['cantidad'];
    $precio   = $_POST['precio'];
    $adelanto = $_POST['adelanto'];

    $total = $cantidad * $precio;
    $saldo = $total - $adelanto;

    try{

        $conn->beginTransaction();

        /* ACTUALIZAR PROVEEDOR */
        $sqlProveedor = "UPDATE proveedores 
                         SET nombre_proveedor = :nombre
                         WHERE idproveedor = :idproveedor";

        $stmt = $conn->prepare($sqlProveedor);

        $stmt->execute([
            'nombre' => $nombre,
            'idproveedor' => $idproveedor
        ]);

        /* ACTUALIZAR COMPRA */
        $sqlCompra = "UPDATE compras 
                      SET fecha_compra = :fecha,
                          total = :total,
                          adelanto = :adelanto,
                          saldo = :saldo
                      WHERE idcompra = :idcompra";

        $stmt = $conn->prepare($sqlCompra);

        $stmt->execute([
            'fecha' => $fecha,
            'total' => $total,
            'adelanto' => $adelanto,
            'saldo' => $saldo,
            'idcompra' => $idcompra
        ]);

        /* ACTUALIZAR DETALLE */
        $sqlDetalle = "UPDATE detalle_compras
                       SET producto = :producto,
                           cantidad = :cantidad,
                           precio = :precio
                       WHERE idcompra = :idcompra";

        $stmt = $conn->prepare($sqlDetalle);

        $stmt->execute([
            'producto' => $producto,
            'cantidad' => $cantidad,
            'precio' => $precio,
            'idcompra' => $idcompra
        ]);

        $conn->commit();

        echo "<div class='alert alert-success'>Compra editada correctamente</div>";

    }catch(Exception $e){

        $conn->rollBack();

        echo "<div class='alert alert-danger'>Error: ".$e->getMessage()."</div>";
    }
}
?>


<div class="contenedor-modulo">

    <!-- FORMULARIO -->

    <div class="contenedor-modulo">

    <div class="contenedor-proveedores py-4">

    <h2 class="titulo-proveedores mb-4">
        <i class='bx bxs-truck me-2'></i> Registrar Compra
    </h2>

    <div class="tarjeta-proveedor">
        <div class="cuerpo-tarjeta">

            <form class="formulario-proveedor" action="index.php?pagina=proveedores" method="POST">
                <input type="hidden" id="idcompra" name="idcompra">
                <input type="hidden" id="idproveedor" name="idproveedor">

                <div class="row g-3">

                    <!-- FILA 1 -->
                    <div class="col-md-6 campo-formulario">
                        <label class="form-label fw-semibold">Proveedor</label>
                        <input type="text" id="nombre_proveedor" name="nombre_proveedor" class="form-control" required>
                    </div>

                    <div class="col-md-6 campo-formulario">
                        <label class="form-label fw-semibold">Producto</label>
                        <input type="text" id="producto" name="producto" class="form-control" required>
                    </div>

                    <!-- FILA 2 -->
                    <div class="col-md-4 campo-formulario">
                        <label class="form-label fw-semibold">Cantidad</label>
                        <input type="number" id="cantidad" name="cantidad" class="form-control" required>
                    </div>

                    <div class="col-md-4 campo-formulario">
                        <label class="form-label fw-semibold">Precio</label>
                        <input type="number" id="precio" name="precio" class="form-control" required>
                    </div>

                    <div class="col-md-4 campo-formulario">
                        <label class="form-label fw-semibold">Fecha</label>
                        <input type="datetime-local" id="fecha_compra" name="fecha_compra" class="form-control" required>
                    </div>

                    <!-- FILA 3 -->
                    <div class="col-md-4 campo-formulario">
                        <label class="form-label fw-semibold">Total</label>
                        <input type="number" id="total" name="total" class="form-control" readonly>
                    </div>

                    <div class="col-md-4 campo-formulario">
                        <label class="form-label fw-semibold">Adelanto</label>
                        <input type="number" id="adelanto" name="adelanto" class="form-control" required>
                    </div>

                    <div class="col-md-4 campo-formulario">
                        <label class="form-label fw-semibold">Saldo</label>
                        <input type="number" id="saldo" name="saldo" class="form-control" readonly>
                    </div>

                    <!-- BOTÓN -->
                    <div class="col-12 text-end mt-2">
                       <button type="submit" id="btnGuardar" class="btn btn-brand fw-semibold">
                            <i class='bx bxs-save me-1'></i> Guardar Compra
                        </button>
                    </div>

                </div>

            </form>

        </div>
    </div>
</div>
</div>

    <!-- =========================
         TABLA
    ========================= -->

    <?php
    $sql = "
    SELECT 
        p.idproveedor,
        c.idcompra,
        p.nombre_proveedor,
        dc.producto,
        dc.cantidad,
        dc.precio,
        c.fecha_compra,
        c.total,
        c.adelanto,
        c.saldo
    FROM proveedores p
    JOIN compras c ON p.idproveedor = c.idproveedor
    JOIN detalle_compras dc ON c.idcompra = dc.idcompra
    ";

    $resultado = $conn->query($sql);
    ?>

    <div class="tarjeta-tabla">

        <div class="header-tabla">
            <h3>Compras Registradas</h3>
        </div>

        <table class="tabla-personalizada">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Proveedor</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Adelanto</th>
                    <th>Saldo</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
            <?php while ($fila = $resultado->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?= $fila['idproveedor'] ?></td>
                    <td><?= $fila['nombre_proveedor'] ?></td>
                    <td><?= $fila['producto'] ?></td>

                    <td><?= $fila['cantidad'] ?></td>
                    <td class="color-precio"><?= $fila['precio'] ?></td>

                    <td><?= $fila['fecha_compra'] ?></td>

                    <td class="color-total"><?= $fila['total'] ?></td>
                    <td class="color-adelanto"><?= $fila['adelanto'] ?></td>
                    <td class="color-saldo"><?= $fila['saldo'] ?></td>
                    <td>

                        <button 
                            type="button"
                            class="btn btn-warning btn-sm btn-editar"

                            data-idcompra="<?= $fila['idcompra'] ?>"
                            data-idproveedor="<?= $fila['idproveedor'] ?>"
                            data-proveedor="<?= $fila['nombre_proveedor'] ?>"
                            data-producto="<?= $fila['producto'] ?>"
                            data-cantidad="<?= $fila['cantidad'] ?>"
                            data-precio="<?= $fila['precio'] ?>"
                            data-fecha="<?= date('Y-m-d\TH:i', strtotime($fila['fecha_compra'])) ?>"
                            data-adelanto="<?= $fila['adelanto'] ?>"
                        >
                            Editar
                        </button>

                        <a 
                            href="index.php?pagina=proveedores&eliminar=<?= $fila['idcompra'] ?>&proveedor=<?= $fila['idproveedor'] ?>"
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('¿Eliminar compra?')"
                        >
                            Eliminar
                        </a>

                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>