<?php
/* ============================================================
   contratos_66.php  —  ISHUME
   ETAPA 2 / 3  (≈ 66 % de avance)
   · Inserción de cliente + contrato en BD
   · Select dinámico y cálculo de saldo via JS
   · Campos opcionales según tipo (N° Alumnos / Tipo Sesión)
   · Sin testigos, sesiones, eventos ni entregas aún
   ============================================================ */
include 'config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    /* ── Cliente ── */
    $dni       = trim($_POST['dni']            ?? '');
    $nombre    = trim($_POST['nombre_cliente'] ?? '');
    $apellidos = trim($_POST['apellidos']      ?? '');
    $direccion = trim($_POST['direccion']      ?? '');
    $telefono  = trim($_POST['telefono']       ?? '');

    /* ── Contrato ── */
    $idtipo   = $_POST['idtipo']          ?? 1;
    $fecha    = $_POST['fecha_contrato']  ?? date('Y-m-d H:i:s');
    $total    = $_POST['total']           ?? 0;
    $adelanto = $_POST['adelanto']        ?? 0;
    $saldo    = $total - $adelanto;

    /* ── Servicio ── */
    $servicio = $_POST['servicio'] ?? '';
    $cantidad = $_POST['cantidad'] ?? 1;

    /* ── Buscar o crear cliente ── */
    $buscar = $conn->prepare("SELECT idcliente FROM clientes WHERE dni = ?");
    $buscar->execute([$dni]);

    if ($buscar->rowCount() > 0) {
        $idcliente = $buscar->fetch(PDO::FETCH_ASSOC)['idcliente'];
    } else {
        $conn->prepare("INSERT INTO clientes (dni, nombre_cliente, apellidos, direccion, telefono)
                        VALUES (?,?,?,?,?)")
             ->execute([$dni, $nombre, $apellidos, $direccion, $telefono]);
        $idcliente = $conn->lastInsertId();
    }

    /* ── Insertar contrato ── */
    $conn->prepare("INSERT INTO contratos (idcliente, idtipo, fecha_contrato, total, adelanto, saldo)
                    VALUES (?,?,?,?,?,?)")
         ->execute([$idcliente, $idtipo, $fecha, $total, $adelanto, $saldo]);
    $idcontrato = $conn->lastInsertId();

    /* ── Detalle ── */
    $conn->prepare("INSERT INTO detalle_contratos (idcontrato, servicio, precio, cantidad)
                    VALUES (?,?,?,?)")
         ->execute([$idcontrato, $servicio, 0, $cantidad]);

    echo "<div class='mensaje-exito'>✅ Contrato guardado correctamente</div>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contratos — Etapa 2</title>
<link rel="stylesheet" href="contratos.css">
</head>
<body>

<span class="badge-etapa">⚙ Etapa 2 — Lógica base</span>
<h2 class="titulo-modulo">Registrar Contrato</h2>

<div class="card">
<form method="POST">
<div class="row g-3">

    <!-- ── Cliente ── -->
    <p class="seccion-titulo">👤 Datos del Cliente</p>

    <div class="fila fila-3">
        <div class="campo">
            <label>DNI</label>
            <input type="text" name="dni" maxlength="20" placeholder="12345678" required>
        </div>
        <div class="campo">
            <label>Nombre</label>
            <input type="text" name="nombre_cliente" placeholder="Ej: María" required>
        </div>
        <div class="campo">
            <label>Apellidos</label>
            <input type="text" name="apellidos" placeholder="Ej: García López" required>
        </div>
    </div>

    <div class="fila fila-2">
        <div class="campo">
            <label>Dirección</label>
            <input type="text" name="direccion" placeholder="Av. Principal 123">
        </div>
        <div class="campo">
            <label>Teléfono</label>
            <input type="text" name="telefono" placeholder="987 654 321">
        </div>
    </div>

    <hr class="sep">

    <!-- ── Tipo de contrato ── -->
    <p class="seccion-titulo">📋 Tipo de Contrato</p>

    <div class="fila" style="grid-template-columns: 1fr 1fr auto auto; align-items: end;">

        <div class="campo">
            <label>Tipo</label>
            <select name="idtipo" id="idtipo">
                <option value="1">Promoción</option>
                <option value="2">Video</option>
                <option value="3">Sesión Especial</option>
            </select>
        </div>

        <div class="campo">
            <label>Servicio / Paquete</label>
            <select name="servicio" id="servicio" required>
                <option value="">Seleccione servicio</option>
            </select>
        </div>

        <!-- Solo visible en Sesión Especial -->
        <div class="campo bloque-dinamico" id="colTipoSesion">
            <label>Tipo de Sesión</label>
            <select name="tipo_sesion_esp">
                <option value="">Seleccione</option>
                <option value="Pedida de mano">Pedida de mano</option>
                <option value="Cumpleaños">Cumpleaños</option>
                <option value="Romántica">Romántica</option>
                <option value="Familiar">Familiar</option>
                <option value="Otro">Otro</option>
            </select>
        </div>

        <!-- Solo visible en Promoción -->
        <div class="campo bloque-dinamico" id="colCantidad">
            <label>N° Alumnos</label>
            <input type="number" name="cantidad" id="cantidad" value="1" min="1">
        </div>

    </div>

    <hr class="sep">

    <!-- ── Pago ── -->
    <p class="seccion-titulo">💰 Pago</p>

    <div class="fila fila-4">
        <div class="campo">
            <label>Fecha Contrato</label>
            <input type="datetime-local" name="fecha_contrato">
        </div>
        <div class="campo">
            <label>Total (S/)</label>
            <input type="number" id="total" name="total" step="0.01" min="0" placeholder="0.00">
        </div>
        <div class="campo">
            <label>Adelanto (S/)</label>
            <input type="number" id="adelanto" name="adelanto" step="0.01" min="0" placeholder="0.00">
        </div>
        <div class="campo">
            <label>Saldo (S/)</label>
            <input type="number" id="saldo" class="color-saldo" readonly placeholder="0.00">
        </div>
    </div>

    <button type="submit" class="btn-guardar">Guardar Contrato</button>

</div>
</form>
</div>

<script src="contratos.js"></script>
</body>
</html>