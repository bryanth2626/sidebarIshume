/* ============================================================
   contratos_80.js  —  ISHUME
   ETAPA 3 / 4  (≈ 80 % de avance)
   · Cálculo de saldo
   · Select dinámico por tipo
   · Mostrar/ocultar bloques: sesión foto, evento video, sesión especial
   · Agregar / quitar testigos dinámicamente
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

    const elTipo     = document.getElementById('idtipo');
    const elServicio = document.getElementById('servicio');
    const elTotal    = document.getElementById('total');
    const elAdelanto = document.getElementById('adelanto');
    const elSaldo    = document.getElementById('saldo');

    const colCantidad        = document.getElementById('colCantidad');
    const colTipoSesion      = document.getElementById('colTipoSesion');
    const bloqueSesionFoto   = document.getElementById('bloqueSesionFoto');
    const bloqueEventoVideo  = document.getElementById('bloqueEventoVideo');
    const bloqueSesionEsp    = document.getElementById('bloqueSesionEspecial');

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
        ocultar(bloqueEventoVideo);
        ocultar(bloqueSesionEsp);

        if (val === "1") {
            mostrar(colCantidad);
            mostrar(bloqueSesionFoto);
        }
        if (val === "2") {
            mostrar(bloqueEventoVideo);
        }
        if (val === "3") {
            mostrar(colTipoSesion);
            mostrar(bloqueSesionEsp);
        }
    }

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
                <input type="text" name="testigo_dni[]" placeholder="12345678" class="form-control">
            </div>
            <div class="campo">
                <label>Nombre</label>
                <input type="text" name="testigo_nombre[]" placeholder="Nombre" class="form-control">
            </div>
            <div class="campo">
                <label>Apellidos</label>
                <input type="text" name="testigo_apellidos[]" placeholder="Apellidos" class="form-control">
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