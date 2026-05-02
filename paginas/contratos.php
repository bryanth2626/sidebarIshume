<?php
/* ============================================================
   contratos_80.php  —  ISHUME
   ETAPA 3 / 4  (≈ 80 % de avance)
   · Inserción de cliente + contrato + detalle
   · Testigos (buscar o crear + relación)
   · Sesión fotográfica (Promoción)
   · Evento de video (Video)
   · Sesión especial (Sesión Especial)
   · Sin entregas ni lista de contratos aún
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
    $idtipo   = $_POST['idtipo']         ?? 1;
    $fecha    = $_POST['fecha_contrato'] ?? date('Y-m-d H:i:s');
    $total    = $_POST['total']          ?? 0;
    $adelanto = $_POST['adelanto']       ?? 0;
    $saldo    = $total - $adelanto;

    /* ── Servicio ── */
    $servicio = $_POST['servicio'] ?? '';
    $cantidad = $_POST['cantidad'] ?? 1;

    /* ── Sesión fotográfica ── */
    $fecha_sesion_foto = $_POST['fecha_sesion_foto'] ?? null;
    $hora_sesion_foto  = $_POST['hora_sesion_foto']  ?? null;
    $lugar_sesion_foto = $_POST['lugar_sesion_foto'] ?? null;

    /* ── Evento video ── */
    $fecha_evento     = $_POST['fecha_evento']     ?? null;
    $hora_evento      = $_POST['hora_evento']      ?? null;
    $local_evento     = $_POST['local_evento']     ?? null;
    $ubicacion_evento = $_POST['ubicacion_evento'] ?? null;

    /* ── Sesión especial ── */
    $tipo_sesion_esp  = $_POST['tipo_sesion_esp']  ?? null;
    $fecha_sesion_esp = $_POST['fecha_sesion_esp'] ?? null;
    $hora_sesion_esp  = $_POST['hora_sesion_esp']  ?? null;
    $lugar_sesion_esp = $_POST['lugar_sesion_esp'] ?? null;

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

    /* ── Relación principal ── */
    $conn->prepare("INSERT INTO contrato_clientes (idcontrato, idcliente, rol) VALUES (?, ?, 'principal')")
         ->execute([$idcontrato, $idcliente]);

    /* ── Detalle ── */
    $conn->prepare("INSERT INTO detalle_contratos (idcontrato, servicio, precio, cantidad)
                    VALUES (?,?,?,?)")
         ->execute([$idcontrato, $servicio, 0, $cantidad]);

    /* ── Testigos ── */
    if (!empty($_POST['testigo_dni'])) {
        foreach ($_POST['testigo_dni'] as $i => $dni_t) {
            if (empty(trim($dni_t))) continue;
            $nombre_t    = $_POST['testigo_nombre'][$i]    ?? '';
            $apellidos_t = $_POST['testigo_apellidos'][$i] ?? '';

            $b = $conn->prepare("SELECT idcliente FROM clientes WHERE dni = ?");
            $b->execute([trim($dni_t)]);

            if ($b->rowCount() > 0) {
                $idtestigo = $b->fetch()['idcliente'];
            } else {
                $conn->prepare("INSERT INTO clientes (dni, nombre_cliente, apellidos, direccion)
                                VALUES (?,?,?,'')")
                     ->execute([trim($dni_t), $nombre_t, $apellidos_t]);
                $idtestigo = $conn->lastInsertId();
            }

            $conn->prepare("INSERT INTO contrato_clientes (idcontrato, idcliente, rol)
                            VALUES (?, ?, 'testigo')")
                 ->execute([$idcontrato, $idtestigo]);
        }
    }

    /* ── Sesión fotográfica — Promoción ── */
    if ($idtipo == 1 && !empty($fecha_sesion_foto)) {
        $conn->prepare("INSERT INTO sesiones (idcontrato, tipo_sesion, fecha, hora, lugar)
                        VALUES (?,?,?,?,?)")
             ->execute([$idcontrato, 'Sesión Promoción', $fecha_sesion_foto, $hora_sesion_foto, $lugar_sesion_foto]);
    }

    /* ── Sesión especial ── */
    if ($idtipo == 3 && !empty($fecha_sesion_esp)) {
        $conn->prepare("INSERT INTO sesiones (idcontrato, tipo_sesion, fecha, hora, lugar)
                        VALUES (?,?,?,?,?)")
             ->execute([$idcontrato, $tipo_sesion_esp, $fecha_sesion_esp, $hora_sesion_esp, $lugar_sesion_esp]);
    }

    /* ── Evento video ── */
    if ($idtipo == 2 && !empty($fecha_evento)) {
        $conn->prepare("INSERT INTO eventos (idcontrato, fecha_evento, hora, `local`, ubicacion, direccion)
                        VALUES (?,?,?,?,?,?)")
             ->execute([$idcontrato, $fecha_evento, $hora_evento, $local_evento, $ubicacion_evento, $ubicacion_evento]);
    }

    echo "<div class='mensaje-exito'>✅ Contrato guardado correctamente</div>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contratos — Etapa 3</title>
<link rel="stylesheet" href="contratos_80.css">
</head>
<body>

<span class="badge-etapa">⚙ Etapa 3 — Testigos y programación</span>
<h2 class="titulo-modulo">Registrar Contrato</h2>

<div class="card">
<form method="POST">

    <!-- ── Cliente ── -->
    <p class="seccion-titulo">👤 Datos del Cliente</p>
    <div class="fila fila-3">
        <div class="campo"><label>DNI</label>
            <input type="text" name="dni" maxlength="20" placeholder="12345678" required></div>
        <div class="campo"><label>Nombre</label>
            <input type="text" name="nombre_cliente" placeholder="Ej: María" required></div>
        <div class="campo"><label>Apellidos</label>
            <input type="text" name="apellidos" placeholder="Ej: García López" required></div>
    </div>
    <div class="fila fila-2">
        <div class="campo"><label>Dirección</label>
            <input type="text" name="direccion" placeholder="Av. Principal 123"></div>
        <div class="campo"><label>Teléfono</label>
            <input type="text" name="telefono" placeholder="987 654 321"></div>
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
        <div class="campo bloque-dinamico" id="colCantidad">
            <label>N° Alumnos</label>
            <input type="number" name="cantidad" id="cantidad" value="1" min="1">
        </div>
    </div>

    <hr class="sep">

    <!-- ── Pago ── -->
    <p class="seccion-titulo">💰 Pago</p>
    <div class="fila fila-4">
        <div class="campo"><label>Fecha Contrato</label>
            <input type="datetime-local" name="fecha_contrato"></div>
        <div class="campo"><label>Total (S/)</label>
            <input type="number" id="total" name="total" step="0.01" min="0" placeholder="0.00"></div>
        <div class="campo"><label>Adelanto (S/)</label>
            <input type="number" id="adelanto" name="adelanto" step="0.01" min="0" placeholder="0.00"></div>
        <div class="campo"><label>Saldo (S/)</label>
            <input type="number" id="saldo" class="color-saldo" readonly placeholder="0.00"></div>
    </div>

    <hr class="sep">

    <!-- ── Sesión fotográfica — Promoción ── -->
    <div id="bloqueSesionFoto" class="bloque-dinamico">
        <p class="seccion-titulo">📸 Programación de Sesión Fotográfica</p>
        <div class="bloque-seccion">
            <div class="fila fila-3">
                <div class="campo"><label>Fecha de la Sesión</label>
                    <input type="date" name="fecha_sesion_foto"></div>
                <div class="campo"><label>Hora</label>
                    <input type="time" name="hora_sesion_foto"></div>
                <div class="campo"><label>Lugar</label>
                    <input type="text" name="lugar_sesion_foto" placeholder="Ej: Colegio, Parque"></div>
            </div>
        </div>
    </div>

    <!-- ── Evento video ── -->
    <div id="bloqueEventoVideo" class="bloque-dinamico">
        <p class="seccion-titulo">🎬 Datos del Evento de Video</p>
        <div class="bloque-seccion">
            <div class="fila fila-4">
                <div class="campo"><label>Fecha del Evento</label>
                    <input type="date" name="fecha_evento"></div>
                <div class="campo"><label>Hora</label>
                    <input type="time" name="hora_evento"></div>
                <div class="campo"><label>Nombre del Local</label>
                    <input type="text" name="local_evento" placeholder="Ej: Salón Los Jardines"></div>
                <div class="campo"><label>Ubicación / Dirección</label>
                    <input type="text" name="ubicacion_evento"></div>
            </div>
        </div>
    </div>

    <!-- ── Sesión especial ── -->
    <div id="bloqueSesionEspecial" class="bloque-dinamico">
        <p class="seccion-titulo">🌟 Datos de la Sesión</p>
        <div class="bloque-seccion">
            <div class="fila fila-3">
                <div class="campo"><label>Fecha</label>
                    <input type="date" name="fecha_sesion_esp"></div>
                <div class="campo"><label>Hora</label>
                    <input type="time" name="hora_sesion_esp"></div>
                <div class="campo"><label>Lugar</label>
                    <input type="text" name="lugar_sesion_esp" placeholder="Ej: Playa, Parque"></div>
            </div>
        </div>
    </div>

    <hr class="sep">

    <!-- ── Testigos ── -->
    <p class="seccion-titulo">👥 Testigos</p>
    <button type="button" class="btn-agregar" onclick="agregarTestigo()">+ Agregar testigo</button>
    <div id="testigosContainer" style="margin-top: 12px;"></div>

    <button type="submit" class="btn-guardar">Guardar Contrato</button>

</form>
</div>

<script src="contratos_80.js"></script>
</body>
</html>