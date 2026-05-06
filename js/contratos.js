/* ============================================================
   contratos_100.js  —  ISHUME
   ETAPA 4 / 4  (100 % — versión final)
   · Cálculo de saldo
   · Select dinámico por tipo
   · Mostrar/ocultar todos los bloques
   · Autocomplete material según servicio
   · Checkbox video en Promoción
   · Agregar / quitar testigos
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

    const elTipo     = document.getElementById('idtipo');
    const elServicio = document.getElementById('servicio');
    const elTotal    = document.getElementById('total');
    const elAdelanto = document.getElementById('adelanto');
    const elSaldo    = document.getElementById('saldo');

    const colCantidad           = document.getElementById('colCantidad');
    const colTipoSesion         = document.getElementById('colTipoSesion');
    const bloqueSesionFoto      = document.getElementById('bloqueSesionFoto');
    const bloqueVideoPromoCheck = document.getElementById('bloqueVideoPromoCheck');
    const bloqueEventoVideo     = document.getElementById('bloqueEventoVideo');
    const bloqueSesionEsp       = document.getElementById('bloqueSesionEspecial');
    const bloqueEntregaMaterial = document.getElementById('bloqueEntregaMaterial');
    const bloqueEntregaVideo    = document.getElementById('bloqueEntregaVideo');
    const entregaTipoLabel      = document.getElementById('entrega_tipo_label');

    /* ================================================
       SERVICIOS POR TIPO
    ================================================ */
    const serviciosPorTipo = {
        "1": [
            { value: "Cuadros",            label: "Cuadros" },
            { value: "Anuarios",           label: "Anuarios" },
            { value: "Cuadros y Anuarios", label: "Cuadros y Anuarios" }
        ],
        "2": [
            { value: "Grabación",           label: "Grabación" },
            { value: "Grabación + Edición", label: "Grabación + Edición" },
            { value: "Drone",               label: "Drone" },
            { value: "Grabación + Drone",   label: "Grabación + Drone" }
        ],
        "3": [
            { value: "Paquete Básico",     label: "Paquete Básico — Fotos y video" },
            { value: "Paquete Intermedio", label: "Paquete Intermedio — + Champagne y fuegos" },
            { value: "Paquete Premium",    label: "Paquete Premium — + Drone" }
        ]
    };

    /* ================================================
       HELPERS
    ================================================ */
    const mostrar = el => { if (el) el.style.display = 'block'; };
    const ocultar = el => { if (el) el.style.display = 'none';  };

    /* ================================================
       CALCULAR SALDO
    ================================================ */
    function calcularSaldo() {
        const t = parseFloat(elTotal?.value)    || 0;
        const a = parseFloat(elAdelanto?.value) || 0;
        if (elSaldo) elSaldo.value = (t - a).toFixed(2);
    }

    /* ================================================
       AUTOCOMPLETE MATERIAL
    ================================================ */
    function actualizarEntregaMaterial(servicio) {
        if (entregaTipoLabel) entregaTipoLabel.value = servicio || '';
        const hayMaterial = ['Cuadros', 'Anuarios', 'Cuadros y Anuarios'].includes(servicio);
        hayMaterial ? mostrar(bloqueEntregaMaterial) : ocultar(bloqueEntregaMaterial);
    }

    /* ================================================
       ACTUALIZAR FORMULARIO SEGÚN TIPO
    ================================================ */
    function actualizarFormulario() {
        const val = elTipo?.value;

        // Poblar servicios
        elServicio.innerHTML = '<option value="">Seleccione servicio</option>';
        (serviciosPorTipo[val] || []).forEach(op => {
            const opt = document.createElement('option');
            opt.value       = op.value;
            opt.textContent = op.label;
            elServicio.appendChild(opt);
        });

        // Ocultar todo
        ocultar(colCantidad);
        ocultar(colTipoSesion);
        ocultar(bloqueSesionFoto);
        ocultar(bloqueVideoPromoCheck);
        ocultar(bloqueEventoVideo);
        ocultar(bloqueSesionEsp);
        ocultar(bloqueEntregaMaterial);
        ocultar(bloqueEntregaVideo);

        // Reset checkbox
        const chkVideo = document.getElementById('tiene_video_promo');
        if (chkVideo) chkVideo.checked = false;

        if (val === "1") {
            mostrar(colCantidad);
            mostrar(bloqueSesionFoto);
            mostrar(bloqueVideoPromoCheck);
        }
        if (val === "2") {
            mostrar(bloqueEventoVideo);
            mostrar(bloqueEntregaVideo);
        }
        if (val === "3") {
            mostrar(colTipoSesion);
            mostrar(bloqueSesionEsp);
            mostrar(bloqueEntregaVideo);
        }
    }

    /* ================================================
       CHECKBOX VIDEO EN PROMOCIÓN
    ================================================ */
    document.getElementById('tiene_video_promo')?.addEventListener('change', function () {
        if (this.checked) {
            mostrar(bloqueEventoVideo);
            mostrar(bloqueEntregaVideo);
        } else {
            ocultar(bloqueEventoVideo);
            ocultar(bloqueEntregaVideo);
        }
    });

    /* ================================================
       CAMBIO DE SERVICIO → autocomplete material
    ================================================ */
    elServicio?.addEventListener('change', () => {
        if (elTipo?.value === "1") actualizarEntregaMaterial(elServicio.value);
    });

    /* ================================================
       TESTIGOS — agregar / quitar
    ================================================ */
    window.agregarTestigo = function () {
        const container = document.getElementById('testigosContainer');
        const div = document.createElement('div');
        div.classList.add('fila-testigo');
        div.innerHTML = `
            <div class="campo">
                <label>DNI</label>
                <input type="text" name="testigo_dni[]" placeholder="12345678">
            </div>
            <div class="campo">
                <label>Nombre</label>
                <input type="text" name="testigo_nombre[]" placeholder="Nombre">
            </div>
            <div class="campo">
                <label>Apellidos</label>
                <input type="text" name="testigo_apellidos[]" placeholder="Apellidos">
            </div>
            <div class="campo" style="padding-top:20px;">
                <button type="button" class="btn-quitar"
                        onclick="this.closest('.fila-testigo').remove()">✕ Quitar</button>
            </div>
        `;
        container.appendChild(div);
    };

    /* ================================================
       EVENTOS
    ================================================ */
    elTotal?.addEventListener('input',    calcularSaldo);
    elAdelanto?.addEventListener('input', calcularSaldo);
    elTipo?.addEventListener('change',    actualizarFormulario);

    /* ── Init ── */
    calcularSaldo();
    actualizarFormulario();
});