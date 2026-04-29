/* ============================================================
   contratos_66.js  —  ISHUME
   ETAPA 2 / 3  (≈ 66 % de avance)
   · Cálculo automático de saldo
   · Select de servicios dinámico según tipo
   · Mostrar/ocultar campos según tipo de contrato
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

    const elTipo     = document.getElementById('idtipo');
    const elServicio = document.getElementById('servicio');
    const elTotal    = document.getElementById('total');
    const elAdelanto = document.getElementById('adelanto');
    const elSaldo    = document.getElementById('saldo');

    const colCantidad   = document.getElementById('colCantidad');
    const colTipoSesion = document.getElementById('colTipoSesion');

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
       CALCULAR SALDO
    ================================================ */
    function calcularSaldo() {
        const t = parseFloat(elTotal?.value)    || 0;
        const a = parseFloat(elAdelanto?.value) || 0;
        if (elSaldo) elSaldo.value = (t - a).toFixed(2);
    }

    /* ================================================
       HELPERS MOSTRAR / OCULTAR
    ================================================ */
    const mostrar = el => { if (el) el.style.display = 'block'; };
    const ocultar = el => { if (el) el.style.display = 'none';  };

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

        // Ocultar columnas opcionales
        ocultar(colCantidad);
        ocultar(colTipoSesion);

        if (val === "1") mostrar(colCantidad);    // Promoción → N° Alumnos
        if (val === "3") mostrar(colTipoSesion);  // Sesión Especial → Tipo sesión
    }

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