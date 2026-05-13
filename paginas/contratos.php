<?php
/* ============================================================
   contratos.php  —  ISHUME
   Nueva versión: Servicio + Paquete + Proforma dinámicos
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
    $idservicio = $_POST['idservicio']     ?? null;
    $idproforma = $_POST['idproforma']     ?? null;
    $fecha      = $_POST['fecha_contrato'] ?? date('Y-m-d H:i:s');
    $total    = floatval($_POST['total'] ?? 0);
    $adelanto = floatval($_POST['adelanto'] ?? 0);
    $saldo = max(0, $total - $adelanto);
    $cantidad = intval($_POST['cantidad'] ?? 1);

    /* ── Evento ── */
    $fecha_evento     = $_POST['fecha_evento']     ?? null;
    $hora_evento      = $_POST['hora_evento']      ?? null;
    $local_evento     = $_POST['local_evento']     ?? null;
    $ubicacion_evento = $_POST['ubicacion_evento'] ?? null;

    /* ── Sesión (pre-evento) ── */
    $fecha_sesion = $_POST['fecha_sesion'] ?? null;
    $hora_sesion  = $_POST['hora_sesion']  ?? null;
    $lugar_sesion = $_POST['lugar_sesion'] ?? null;

    /* ── Entregas ── */
    $entregas_tipos     = $_POST['entrega_tipo']     ?? [];
    $entregas_cantidades = $_POST['entrega_cantidad'] ?? [];
    $fecha_entrega      = $_POST['fecha_entrega']    ?? null;

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
    $conn->prepare("INSERT INTO contratos (idcliente, idservicio, idproforma, fecha_contrato, total, adelanto, saldo, cantidad)
                    VALUES (?,?,?,?,?,?,?,?)")
         ->execute([$idcliente, $idservicio, $idproforma, $fecha, $total, $adelanto, $saldo, $cantidad]);
    $idcontrato = $conn->lastInsertId();

    /* ── Relación principal ── */
    

    /* ── Testigos ── */
    /* ── Testigos ── */
    if (!empty($_POST['testigo_dni'])) {
        foreach ($_POST['testigo_dni'] as $i => $dni_t) {
            if (empty(trim($dni_t))) continue;

            $nombre_t    = $_POST['testigo_nombre'][$i]    ?? '';
            $apellidos_t = $_POST['testigo_apellidos'][$i] ?? '';
            $direccion_t = $_POST['testigo_direccion'][$i] ?? '';
            $telefono_t  = $_POST['testigo_telefono'][$i]  ?? '';


            // Buscar si ya existe ese testigo por DNI
            $b = $conn->prepare("SELECT idtestigo FROM testigos WHERE dni = ?");
            $b->execute([trim($dni_t)]);

            if ($b->rowCount() > 0) {
                $idtestigo = $b->fetch()['idtestigo'];
            } else {
                $conn->prepare("INSERT INTO testigos (dni, nombre, apellidos, direccion, telefono)
                                VALUES (?,?,?,?,?)")
                    ->execute([trim($dni_t), $nombre_t, $apellidos_t, $direccion_t, $telefono_t]);
                $idtestigo = $conn->lastInsertId();
            }

            // Asociar testigo al contrato
            $conn->prepare("INSERT INTO contrato_testigos (idcontrato, idtestigo)
                            VALUES (?, ?)")
                ->execute([$idcontrato, $idtestigo]);
        }
    }

    /* ── Sesión pre-evento (Golden) ── */
    if (!empty($fecha_sesion)) {
        $conn->prepare("INSERT INTO sesiones (idcontrato, tipo_sesion, fecha, hora, lugar)
                        VALUES (?,?,?,?,?)")
             ->execute([$idcontrato, 'Pre-evento', $fecha_sesion, $hora_sesion, $lugar_sesion]);
    }

    /* ── Evento ── */
    if (!empty($fecha_evento)) {
        $conn->prepare("INSERT INTO eventos (idcontrato, fecha_evento, hora, `local`, ubicacion)
                        VALUES (?,?,?,?,?)")
             ->execute([$idcontrato, $fecha_evento, $hora_evento, $local_evento, $ubicacion_evento]);
    }

    /* ── Entregas ── */
    if (!empty($entregas_tipos)) {
        foreach ($entregas_tipos as $i => $tipo) {
            if (empty(trim($tipo))) continue;
            $cant = $entregas_cantidades[$i] ?? 1;
            $conn->prepare("INSERT INTO entregas (idcontrato, tipo, cantidad, fecha_entrega)
                            VALUES (?,?,?,?)")
                 ->execute([$idcontrato, trim($tipo), $cant, $fecha_entrega ?: null]);
        }
    }

    header("Location: index.php?pagina=contratos&ok=1");
    exit;
}

/* ── Cargar servicios desde BD ── */
$servicios = $conn->query("SELECT idservicio, nombre FROM servicios ORDER BY idservicio")->fetchAll(PDO::FETCH_ASSOC);

/* ── Cargar proformas desde BD (para el JS) ── */
$proformas_raw = $conn->query("
    SELECT p.idproforma, p.idservicio, p.idpaquete, p.nombre, p.precio, p.precio_por_alumno,
           pq.nombre AS nombre_paquete
    FROM proformas p
    JOIN paquetes pq ON p.idpaquete = pq.idpaquete
    ORDER BY p.idservicio, p.idpaquete, p.idproforma
")->fetchAll(PDO::FETCH_ASSOC);

/* ── Listado de contratos ── */
$sql = "
    SELECT
        co.idcontrato,
        c.dni,
        c.nombre_cliente,
        c.apellidos,
        c.telefono,
        s.nombre       AS servicio,
        pf.nombre      AS proforma,
        pq.nombre      AS paquete,
        co.cantidad,
        co.total,
        co.adelanto,
        co.saldo,
        ev.fecha_evento,
        ev.hora        AS hora_evento,
        ev.`local`     AS local_evento,
        ses.fecha      AS fecha_sesion,
        ses.hora       AS hora_sesion,
        GROUP_CONCAT(DISTINCT CONCAT(e.tipo,' x',e.cantidad)
            ORDER BY e.identrega SEPARATOR ' | ') AS entregas,
        GROUP_CONCAT(DISTINCT CONCAT(t.nombre,' ',t.apellidos,' (',t.dni,')')
            ORDER BY t.idtestigo SEPARATOR ' | ') AS testigos
    FROM contratos co
    JOIN clientes c        ON co.idcliente  = c.idcliente
    JOIN servicios s       ON co.idservicio = s.idservicio
    JOIN proformas pf      ON co.idproforma = pf.idproforma
    JOIN paquetes pq       ON pf.idpaquete  = pq.idpaquete
    LEFT JOIN eventos ev   ON co.idcontrato = ev.idcontrato
    LEFT JOIN sesiones ses ON co.idcontrato = ses.idcontrato AND ses.tipo_sesion = 'Pre-evento'
    LEFT JOIN entregas e   ON co.idcontrato = e.idcontrato
    LEFT JOIN contrato_testigos ct ON co.idcontrato = ct.idcontrato
    LEFT JOIN testigos t           ON ct.idtestigo  = t.idtestigo
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
<title>Contratos — ISHUME</title>
<link rel="stylesheet" href="css/contratos.css">
</head>
<body>

<h2 class="titulo-modulo">Registrar Contrato</h2>

<div class="card">
<form method="POST">

    <!-- ── Cliente ── -->
    <p class="seccion-titulo">👤 Datos del Cliente</p>
    <div class="fila fila-3">
        <div class="campo"><label>Nombre</label>
            <input type="text" name="nombre_cliente" placeholder="Ej: María" required></div>
        <div class="campo"><label>Apellidos</label>
            <input type="text" name="apellidos" placeholder="Ej: García López" required></div>    
        <div class="campo"><label>DNI</label>
            <input type="text" name="dni" maxlength="20" placeholder="12345678" required></div>
    
        
    </div>
    <div class="fila fila-2">
        <div class="campo"><label>Dirección</label>
            <input type="text" name="direccion" placeholder="Av. Principal 123"></div>
        <div class="campo"><label>Teléfono</label>
            <input type="text" name="telefono" placeholder="987 654 321"></div>
    </div>
    <!-- ── Testigos ── -->
    <p class="seccion-titulo">👥 Testigos / Personas Relacionadas</p>
    <button type="button" class="btn-agregar" onclick="agregarTestigo()">+ Agregar</button>
    <div id="testigosContainer" style="margin-top:12px;"></div>

    <hr class="sep">

    <!-- ── Servicio ── -->
    <p class="seccion-titulo">📋 Servicio</p>
    <div class="fila fila-3">
        <div class="campo">
            <label>Servicio</label>
            <select name="idservicio" id="idservicio" required>
                <option value="">Seleccione servicio</option>
                <?php foreach ($servicios as $s): ?>
                <option value="<?= $s['idservicio'] ?>"><?= htmlspecialchars($s['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="campo">
            <label>Paquete</label>
            <select name="idpaquete" id="idpaquete" disabled>
                <option value="">— Seleccione servicio primero —</option>
            </select>
        </div>
        <div class="campo">
            <label>Proforma</label>
            <select name="idproforma" id="idproforma" disabled required>
                <option value="">— Seleccione paquete primero —</option>
            </select>
        </div>
    </div>

    <!-- ── N° Alumnos (solo Promoción) ── -->
    <div id="bloqueAlumnos" style="display:none;">
        <div class="fila fila-2">
            <div class="campo">
                <label>N° de Alumnos <small>(mín. 20)</small></label>
                <input type="number" name="cantidad" id="cantidad" value="20" min="20">
            </div>
            <div class="campo">
                <label>Total calculado</label>
                <input type="text" id="totalCalculado" readonly style="background:#f0f7ff; font-weight:600;">
            </div>
        </div>
    </div>

    <!-- ── Detalle proforma ── -->
    <div id="bloqueDetalleProforma" style="display:none;">
        <div class="bloque-proforma-info" id="infoProforma"></div>
    </div>

    <hr class="sep">

    <!-- ── Pago ── -->
    <p class="seccion-titulo">Detalles del Contrato</p>
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

    <!-- ── Sesión pre-evento (Golden) ── -->
    <div id="bloqueSesionPre" style="display:none;">
        <p class="seccion-titulo">📸 Pre-Evento (Sesión Fotográfica)</p>
        <div class="bloque-seccion">
            <div class="fila fila-3">
                <div class="campo"><label>Fecha</label>
                    <input type="date" name="fecha_sesion"></div>
                <div class="campo"><label>Hora</label>
                    <input type="time" name="hora_sesion"></div>
                <div class="campo"><label>Lugar</label>
                    <input type="text" name="lugar_sesion" placeholder="Ej: Parque, Playa"></div>
            </div>
        </div>
    </div>

    <!-- ── Evento ── -->
    <div id="bloqueEvento" style="display:none;">
        <p class="seccion-titulo">🎬 Datos del Evento</p>
        <div class="bloque-seccion">
            <div class="fila fila-4">
                <div class="campo"><label>Fecha del Evento</label>
                    <input type="date" name="fecha_evento"></div>
                <div class="campo"><label>Hora</label>
                    <input type="time" name="hora_evento"></div>
                <div class="campo"><label>Nombre del Local</label>
                    <input type="text" name="local_evento" placeholder="Ej: Salón Los Jardines"></div>
                <div class="campo"><label>Ubicación / Dirección</label>
                    <input type="text" name="ubicacion_evento" placeholder="Ej: Av. Los Héroes 123"></div>
            </div>
        </div>
    </div>

    <!-- ── Entregas ── -->
    <div id="bloqueEntregas" style="display:none;">
        <p class="seccion-titulo">📦 Entregas de Material</p>
        <div class="bloque-seccion">
            <div id="listaEntregas"></div>
            <div class="fila fila-2" style="margin-top:12px;">
                <div class="campo"><label>Fecha de Entrega</label>
                    <input type="date" name="fecha_entrega"></div>
            </div>
        </div>
    </div>

    <hr class="sep">

    

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
                <th>Testigos</th>
                <th>Servicio</th>
                <th>Paquete</th>
                <th>Proforma</th>
                <th>Cant.</th>
                <th>Total</th>
                <th>Adelanto</th>
                <th>Saldo</th>
                <th>Evento</th>
                <th>Pre-Evento</th>
                <th>Entregas</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($fila = $resultado->fetch(PDO::FETCH_ASSOC)): ?>
        <tr>
            <td><?= $fila['idcontrato'] ?></td>
            <td><?= htmlspecialchars($fila['dni']) ?></td>
            <td><?= htmlspecialchars($fila['nombre_cliente'] . ' ' . $fila['apellidos']) ?></td>
            <td><?= htmlspecialchars($fila['telefono'] ?? '—') ?></td>
            <td><?= $fila['testigos'] ? htmlspecialchars($fila['testigos']) : '<span style="color:#aaa">—</span>' ?></td>
            <td><?= htmlspecialchars($fila['servicio']) ?></td>
            <td><?= htmlspecialchars($fila['paquete']) ?></td>
            <td><?= htmlspecialchars($fila['proforma']) ?></td>
            <td><?= $fila['cantidad'] > 1 ? $fila['cantidad'] . ' alumnos' : '—' ?></td>
            
            <td class="color-total">S/ <?= number_format($fila['total'], 2) ?></td>
            <td class="color-adelanto">S/ <?= number_format($fila['adelanto'], 2) ?></td>
            <td class="color-saldo">S/ <?= number_format($fila['saldo'], 2) ?></td>
            
            <td>
                <?php if ($fila['fecha_evento']): ?>
                    🎬 <?= date('d/m/Y', strtotime($fila['fecha_evento'])) ?>
                    <br><small><?= $fila['hora_evento'] ?></small>
                    <?php if ($fila['local_evento']): ?>
                        <br><small><?= htmlspecialchars($fila['local_evento']) ?></small>
                    <?php endif; ?>
                <?php else: ?>
                    <span style="color:#aaa">—</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($fila['fecha_sesion']): ?>
                    📸 <?= date('d/m/Y', strtotime($fila['fecha_sesion'])) ?>
                    <br><small><?= $fila['hora_sesion'] ?></small>
                <?php else: ?>
                    <span style="color:#aaa">—</span>
                <?php endif; ?>
            </td>
            <td><?= $fila['entregas'] ? htmlspecialchars($fila['entregas']) : '<span style="color:#aaa">—</span>' ?></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>



<!-- Pasar proformas desde PHP -->
<script>
const PROFORMAS = <?= json_encode($proformas_raw, JSON_UNESCAPED_UNICODE) ?>;
</script>

<!-- Cargar JS después de que TODO el HTML exista -->
<script src="js/contratos.js"></script>

</body>
</html>