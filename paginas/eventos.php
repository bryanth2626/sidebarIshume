<?php
include 'config/conexion.php';

// ─── GUARDAR NUEVO EVENTO ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar'])) {

    $fecha_evento    = $_POST['fecha_evento'];
    $lugar           = $_POST['lugar'];
    $hora            = $_POST['hora'];
    $equipamiento    = $_POST['equipamiento'];
    $idcontrato      = $_POST['idcontrato'];
    $personal_ids    = $_POST['personal_ids'] ?? []; // Array de IDs de personal

    // Obtener idcliente desde el contrato
    $buscar = $conn->prepare("SELECT idcliente FROM contratos WHERE idcontrato = ?");
    $buscar->execute([$idcontrato]);
    $row       = $buscar->fetch(PDO::FETCH_ASSOC);
    $idcliente = $row['idcliente'];

    // Insertar evento (sin idcliente en eventos si no existe la columna aún)
    $sql1 = $conn->prepare(
        "INSERT INTO eventos (fecha_evento, lugar, hora, equipamiento, idcontrato)
         VALUES (?, ?, ?, ?, ?)"
    );
    $sql1->execute([$fecha_evento, $lugar, $hora, $equipamiento, $idcontrato]);
    $idevento = $conn->lastInsertId();

    // Insertar personal del evento (usando idpersonal)
    $sql2 = $conn->prepare(
        "INSERT INTO personal_evento (idpersonal, idevento) VALUES (?, ?)"
    );

    foreach($personal_ids as $idpersonal) {
        if(!empty($idpersonal)) {
            $sql2->execute([$idpersonal, $idevento]);
        }
    }

    echo "<script>alert('Evento guardado correctamente'); window.location.href='index.php?pagina=eventos';</script>";
    exit;
}

// ─── CARGAR EVENTOS PARA EL CALENDARIO ──────────────────────────────────────
$sql_eventos = "
    SELECT DISTINCT
        e.idevento,
        e.fecha_evento,
        e.hora,
        e.lugar,
        e.equipamiento,
        dc.servicio,
        c.nombre_cliente,
        c.apellidos,
        p.nombre_personal
    FROM eventos e
    INNER JOIN contratos co ON e.idcontrato = co.idcontrato
    INNER JOIN clientes c ON co.idcliente = c.idcliente
    LEFT JOIN detalle_contratos dc ON co.idcontrato = dc.idcontrato
    LEFT JOIN personal_evento pe ON e.idevento = pe.idevento
    LEFT JOIN personal p ON pe.idpersonal = p.idpersonal
    ORDER BY e.fecha_evento, e.hora
";

$result_eventos = $conn->query($sql_eventos);
$eventos_json   = [];

if ($result_eventos) {
    while ($row = $result_eventos->fetch(PDO::FETCH_ASSOC)) {
        $fecha = new DateTime($row['fecha_evento']);
        $eventos_json[] = [
            'id'       => $row['idevento'],
            'day'      => (int) $fecha->format('d'),
            'month'    => (int) $fecha->format('m'),
            'year'     => (int) $fecha->format('Y'),
            'title'    => $row['equipamiento'],
            'time'     => date('g:i A', strtotime($row['hora'])),
            'lugar'    => $row['lugar'],
            'servicio' => $row['servicio'] ?? '',
            'cliente'  => $row['nombre_cliente'] . ' ' . $row['apellidos'],
            'personal' => $row['nombre_personal'] ?? ''
        ];
    }
}

// ─── CARGAR CONTRATOS PARA EL SELECT ────────────────────────────────────────
$contratos = $conn->query(
    "SELECT DISTINCT co.idcontrato, dc.servicio, c.nombre_cliente, c.apellidos
     FROM contratos co
     INNER JOIN clientes c ON co.idcliente = c.idcliente
     LEFT JOIN detalle_contratos dc ON co.idcontrato = dc.idcontrato
     ORDER BY c.nombre_cliente"
);

// ─── CARGAR PERSONAL REGISTRADO ──────────────────────────────────────────────
$personal_list = $conn->query("SELECT idpersonal, nombre_personal FROM personal ORDER BY nombre_personal");
$personal_array = [];
if ($personal_list) {
    while ($p = $personal_list->fetch(PDO::FETCH_ASSOC)) {
        $personal_array[] = $p;
    }
}
?>

<div class="eventos-container">

    <!-- ── Formulario nuevo evento ──────────────────────────── -->
    <div class="form-eventos">
        <h2><i class='bx bxs-calendar-plus'></i> Registrar Nuevo Evento</h2>

        <form method="POST" action="index.php?pagina=eventos">
            <div class="form-grid">

                <div class="form-group">
                    <label for="idcontrato">
                        <i class='bx bxs-file'></i> Contrato / Cliente *
                    </label>
                    <select name="idcontrato" id="idcontrato" required>
                        <option value="">-- Selecciona un contrato --</option>
                        <?php
                        if ($contratos) {
                            while ($c = $contratos->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . htmlspecialchars($c['idcontrato']) . '">'
                                   . htmlspecialchars($c['nombre_cliente'] . ' ' . $c['apellidos'])
                                   . ' — '
                                   . htmlspecialchars($c['servicio'] ?? 'Sin servicio')
                                   . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fecha_evento">
                        <i class='bx bxs-calendar'></i> Fecha del Evento *
                    </label>
                    <input type="date" name="fecha_evento" id="fecha_evento" required>
                </div>

                <div class="form-group">
                    <label for="hora">
                        <i class='bx bxs-time'></i> Hora *
                    </label>
                    <input type="time" name="hora" id="hora" required>
                </div>

                <div class="form-group">
                    <label for="lugar">
                        <i class='bx bxs-map'></i> Lugar *
                    </label>
                    <input type="text" name="lugar" id="lugar"
                           placeholder="Ej: Salón Los Pinos" required>
                </div>

                <div class="form-group">
                    <label>
                        <i class='bx bxs-user-detail'></i> Personal Asignado *
                    </label>
                    <div id="contenedor-personal">
                        <div class="fila-personal">
                            <select name="personal_ids[]" class="select-personal" required>
                                <option value="">-- Selecciona personal --</option>
                                <?php
                                foreach($personal_array as $persona) {
                                    echo '<option value="' . htmlspecialchars($persona['idpersonal']) . '">'
                                       . htmlspecialchars($persona['nombre_personal'])
                                       . '</option>';
                                }
                                ?>
                            </select>
                            <button class="btn-eliminar" type="button" onclick="eliminarCampo(this)">❌</button>
                        </div>
                    </div>
                    <button class="btn-agregar" type="button" onclick="agregarPersonal()"> ➕ Agregar personal</button>
                </div>

                <div class="form-group">
                    <label for="equipamiento">
                        <i class='bx bxs-detail'></i> Equipamiento / Descripción *
                    </label>
                    <input type="text" name="equipamiento" id="equipamiento"
                           placeholder="Ej: Cámara 4K, Drone, etc." required>
                </div>

            </div>

            <button type="submit" name="guardar" class="btn-guardar">
                <i class='bx bxs-save'></i> Guardar Evento
            </button>
        </form>
    </div>

    <!-- ── Calendario ───────────────────────────────────────── -->
    <div class="calendar-wrapper">
        <div class="calendar-header">
            <h2><i class='bx bxs-calendar-event'></i> Calendario de Eventos</h2>
        </div>

        <div class="cal-container">

            <!-- Izquierda: grid del mes -->
            <div class="cal-left">
                <div class="calendar">

                    <div class="month">
                        <i class="fas fa-angle-left cal-prev"></i>
                        <div class="cal-date"></div>
                        <i class="fas fa-angle-right cal-next"></i>
                    </div>

                    <div class="weekdays">
                        <div>Dom</div>
                        <div>Lun</div>
                        <div>Mar</div>
                        <div>Mié</div>
                        <div>Jue</div>
                        <div>Vie</div>
                        <div>Sáb</div>
                    </div>

                    <!-- Días generados por calendar.js -->
                    <div class="cal-days"></div>

                    <div class="goto-today">
                        <div class="goto"></div>
                        <button class="cal-today-btn">Hoy</button>
                    </div>

                </div>
            </div>

            <!-- Derecha: detalle del día seleccionado -->
            <div class="cal-right">
                <div class="today-date">
                    <div class="event-day"></div>
                    <div class="event-date"></div>
                </div>
                <div class="cal-events"></div>
            </div>

        </div><!-- /cal-container -->
    </div><!-- /calendar-wrapper -->

</div><!-- /eventos-container -->

<!-- Datos de PHP → JS para el calendario -->
<script>
    const eventosDB = <?php echo json_encode($eventos_json, JSON_UNESCAPED_UNICODE); ?>;
</script>
<script src="js/calendar.js"></script>
<script src="js/eventos.js"></script>