<?php
/* ============================================================
   contratos_100.php  —  ISHUME
   ETAPA 4 / 4  (100 % — versión final)
   · Todo lo del 80% +
   · Entregas de material y video
   · Checkbox video en Promoción
   · Listado completo de contratos
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

    /* ── Video promo ── */
    $tiene_video_promo = isset($_POST['tiene_video_promo']) ? 1 : 0;

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

    /* ── Entregas ── */
    $entrega_tipo           = $_POST['entrega_tipo']           ?? null;
    $entrega_cantidad       = $_POST['entrega_cantidad']       ?? 0;
    $fecha_entrega_material = $_POST['fecha_entrega_material'] ?? null;

    $video_formato       = $_POST['video_formato']       ?? null;
    $video_cantidad      = $_POST['video_cantidad']      ?? 0;
    $fecha_entrega_video = $_POST['fecha_entrega_video'] ?? null;

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

    /* ── Contrato ── */
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
    if (($idtipo == 2 || ($idtipo == 1 && $tiene_video_promo)) && !empty($fecha_evento)) {
        $conn->prepare("INSERT INTO eventos (idcontrato, fecha_evento, hora, `local`, ubicacion, direccion)
                        VALUES (?,?,?,?,?,?)")
             ->execute([$idcontrato, $fecha_evento, $hora_evento, $local_evento, $ubicacion_evento, $ubicacion_evento]);
    }

    /* ── Entrega material ── */
    if ($idtipo == 1 && !empty($entrega_tipo) && $entrega_cantidad > 0) {
        $conn->prepare("INSERT INTO entregas (idcontrato, tipo, descripcion, cantidad, fecha_entrega)
                        VALUES (?,?,?,?,?)")
             ->execute([$idcontrato, 'material', $entrega_tipo, $entrega_cantidad, $fecha_entrega_material ?: null]);
    }

    /* ── Entrega video ── */
    if (!empty($video_formato) && $video_cantidad > 0) {
        $conn->prepare("INSERT INTO entregas (idcontrato, tipo, formato, cantidad, fecha_entrega)
                        VALUES (?,?,?,?,?)")
             ->execute([$idcontrato, 'video', $video_formato, $video_cantidad, $fecha_entrega_video ?: null]);
    }

    echo "<div class='mensaje-exito'>✅ Contrato guardado correctamente</div>";
}

/* ── Listado de contratos ── */
$sql = "
    SELECT
        co.idcontrato,
        c.dni,
        c.nombre_cliente,
        c.apellidos,
        c.telefono,
        te.nombre AS tipo_evento,
        dc.servicio,
        dc.cantidad,
        co.total,
        co.adelanto,
        co.saldo,
        ev.fecha_evento,
        ev.hora        AS hora_evento,
        ev.`local`     AS local_evento,
        ev.ubicacion   AS ubicacion_evento,
        s.tipo_sesion,
        s.fecha        AS fecha_sesion,
        s.hora         AS hora_sesion,
        s.lugar        AS lugar_sesion,
        GROUP_CONCAT(DISTINCT CASE WHEN e.tipo='material'
            THEN CONCAT(e.descripcion,' x',e.cantidad,
                 IF(e.fecha_entrega,CONCAT(' (entrega: ',DATE_FORMAT(e.fecha_entrega,'%d/%m/%Y'),')'),''))
            END ORDER BY e.identrega SEPARATOR ' | ') AS materiales,
        GROUP_CONCAT(DISTINCT CASE WHEN e.tipo='video'
            THEN CONCAT(e.formato,' x',e.cantidad,
                 IF(e.fecha_entrega,CONCAT(' (entrega: ',DATE_FORMAT(e.fecha_entrega,'%d/%m/%Y'),')'),''))
            END SEPARATOR ' | ') AS videos
    FROM contratos co
    JOIN clientes c           ON co.idcliente  = c.idcliente
    LEFT JOIN tipos_evento te ON co.idtipo     = te.idtipo
    JOIN detalle_contratos dc ON co.idcontrato = dc.idcontrato
    LEFT JOIN eventos ev      ON co.idcontrato = ev.idcontrato
    LEFT JOIN sesiones s      ON co.idcontrato = s.idcontrato
    LEFT JOIN entregas e      ON co.idcontrato = e.idcontrato
    GROUP BY co.idcontrato
    ORDER BY co.idcontrato DESC
";
$resultado = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contratos — Versión Final</title>
<link rel="stylesheet" href="contratos.css">
</head>
<body>

<span class="badge-etapa">✅ Versión final — 100%</span>
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
                <div class="campo"><label>Fecha</label>
                    <input type="date" name="fecha_sesion_foto"></div>
                <div class="campo"><label>Hora</label>
                    <input type="time" name="hora_sesion_foto"></div>
                <div class="campo"><label>Lugar</label>
                    <input type="text" name="lugar_sesion_foto" placeholder="Ej: Colegio, Parque"></div>
            </div>
        </div>
    </div>

    <!-- ── Checkbox video promo ── -->
    <div id="bloqueVideoPromoCheck" class="bloque-dinamico">
        <label class="check-label">
            <input type="checkbox" name="tiene_video_promo" id="tiene_video_promo">
            🎬 ¿Incluye grabación de video en local?
        </label>
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
                <div class="campo"><label>Ubicación</label>
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

    <!-- ── Entrega material ── -->
    <div id="bloqueEntregaMaterial" class="bloque-dinamico">
        <p class="seccion-titulo">📦 Entrega de Material</p>
        <div class="bloque-seccion">
            <div class="fila fila-3">
                <div class="campo"><label>Material</label>
                    <input type="text" id="entrega_tipo_label" name="entrega_tipo" readonly
                           style="background:#f8f9fa; font-weight:600;"></div>
                <div class="campo"><label>Cantidad</label>
                    <input type="number" name="entrega_cantidad" value="1" min="1"></div>
                <div class="campo"><label>Fecha de Entrega</label>
                    <input type="date" name="fecha_entrega_material"></div>
            </div>
        </div>
    </div>

    <!-- ── Entrega video ── -->
    <div id="bloqueEntregaVideo" class="bloque-dinamico">
        <p class="seccion-titulo">💿 Entrega de Video</p>
        <div class="bloque-seccion">
            <div class="fila fila-3">
                <div class="campo"><label>Formato</label>
                    <select name="video_formato">
                        <option value="USB Físico">USB Físico</option>
                        <option value="USB Digital (Drive)">USB Digital (Drive)</option>
                        <option value="Físico + Digital">Físico + Digital</option>
                    </select>
                </div>
                <div class="campo"><label>Cantidad</label>
                    <input type="number" name="video_cantidad" value="1" min="1"></div>
                <div class="campo"><label>Fecha de Entrega</label>
                    <input type="date" name="fecha_entrega_video"></div>
            </div>
        </div>
    </div>

    <hr class="sep">

    <!-- ── Testigos ── -->
    <p class="seccion-titulo">👥 Testigos</p>
    <button type="button" class="btn-agregar" onclick="agregarTestigo()">+ Agregar testigo</button>
    <div id="testigosContainer" style="margin-top:12px;"></div>

    <button type="submit" class="btn-guardar">Guardar Contrato</button>

</form>
</div>

<!-- ── Listado ── -->
<div class="card-tabla">
    <p class="seccion-titulo" style="margin-bottom:16px;">📄 Contratos Registrados</p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>DNI</th>
                <th>Cliente</th>
                <th>Teléfono</th>
                <th>Tipo</th>
                <th>Servicio</th>
                <th>Alumnos</th>
                <th>Total</th>
                <th>Adelanto</th>
                <th>Saldo</th>
                <th>Material</th>
                <th>Video</th>
                <th>Sesión / Evento</th>
                <th>Local / Lugar</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($fila = $resultado->fetch(PDO::FETCH_ASSOC)): ?>
        <tr>
            <td><?= $fila['idcontrato'] ?></td>
            <td><?= htmlspecialchars($fila['dni']) ?></td>
            <td><?= htmlspecialchars($fila['nombre_cliente'] . ' ' . $fila['apellidos']) ?></td>
            <td><?= htmlspecialchars($fila['telefono'] ?? '—') ?></td>
            <td>
                <?= htmlspecialchars($fila['tipo_evento']) ?>
                <?php if (!empty($fila['tipo_sesion'])): ?>
                    <br><small style="color:#666;">(<?= htmlspecialchars($fila['tipo_sesion']) ?>)</small>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($fila['servicio']) ?></td>
            <td><?= ($fila['cantidad'] > 1) ? $fila['cantidad'] : '—' ?></td>
            <td class="color-total">S/ <?= number_format($fila['total'], 2) ?></td>
            <td class="color-adelanto">S/ <?= number_format($fila['adelanto'], 2) ?></td>
            <td class="color-saldo">S/ <?= number_format($fila['saldo'], 2) ?></td>
            <td><?= $fila['materiales'] ? htmlspecialchars($fila['materiales']) : '<span style="color:#aaa">—</span>' ?></td>
            <td><?= $fila['videos']     ? htmlspecialchars($fila['videos'])     : '<span style="color:#aaa">—</span>' ?></td>
            <td>
                <?php if ($fila['fecha_evento']): ?>
                    🎬 <?= date('d/m/Y', strtotime($fila['fecha_evento'])) ?>
                    <br><small><?= $fila['hora_evento'] ?></small>
                <?php elseif ($fila['fecha_sesion']): ?>
                    📷 <?= date('d/m/Y', strtotime($fila['fecha_sesion'])) ?>
                    <br><small><?= $fila['hora_sesion'] ?></small>
                <?php else: ?>
                    <span style="color:#aaa">—</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($fila['local_evento']): ?>
                    <?= htmlspecialchars($fila['local_evento']) ?>
                    <br><small><?= htmlspecialchars($fila['ubicacion_evento'] ?? '') ?></small>
                <?php elseif ($fila['lugar_sesion']): ?>
                    <?= htmlspecialchars($fila['lugar_sesion']) ?>
                <?php else: ?>
                    <span style="color:#aaa">—</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script src="contratos.js"></script>
</body>
</html>