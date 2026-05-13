/* ============================================================
   contratos.js  —  ISHUME
   Lógica dinámica: Servicio → Paquete → Proforma
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

    /* ── Elementos principales ── */
    const elServicio  = document.getElementById('idservicio');
    const elPaquete   = document.getElementById('idpaquete');
    const elProforma  = document.getElementById('idproforma');
    const elTotal     = document.getElementById('total');
    const elAdelanto  = document.getElementById('adelanto');
    const elSaldo     = document.getElementById('saldo');
    const elCantidad  = document.getElementById('cantidad');

    /* ── Bloques dinámicos ── */
    const bloqueAlumnos        = document.getElementById('bloqueAlumnos');
    const bloqueDetalleProforma = document.getElementById('bloqueDetalleProforma');
    const infoProforma         = document.getElementById('infoProforma');
    const bloqueSesionPre      = document.getElementById('bloqueSesionPre');
    const bloqueEvento         = document.getElementById('bloqueEvento');
    const bloqueEntregas       = document.getElementById('bloqueEntregas');
    const listaEntregas        = document.getElementById('listaEntregas');

    /* ── Entregas por proforma ── */
    // Clave: idproforma (según orden en BD)
    // Estructura: [{ tipo, cantidad }]
    const ENTREGAS_POR_PROFORMA = {
        // PROMOCIÓN
        'Promoción|Proforma 02':      [{ tipo: 'USB', cantidad: 1 }],
        'Promoción|Proforma 03':      [{ tipo: 'USB', cantidad: 1 }],
        'Promoción|Proforma General': [{ tipo: 'USB', cantidad: 1 }, { tipo: 'Fotos digitales', cantidad: 1 }],
        'Promoción|Golden':           [{ tipo: 'USB madera personalizado', cantidad: 1 }, { tipo: 'Foto con toga 10x15 por alumno', cantidad: 1 }, { tipo: 'Link descarga digital', cantidad: 1 }],

        // QUINCEAÑERA
        'Quinceañera|Proforma 02': [{ tipo: 'USB', cantidad: 1 }],
        'Quinceañera|Proforma 03': [{ tipo: 'USB', cantidad: 1 }],
        'Quinceañera|Golden':      [{ tipo: 'USB madera personalizado', cantidad: 1 }, { tipo: 'Fotos impresas 10x15', cantidad: 20 }, { tipo: 'Link descarga digital', cantidad: 1 }],

        // MATRIMONIO
        'Matrimonio|Proforma 02': [{ tipo: 'USB', cantidad: 1 }],
        'Matrimonio|Proforma 03': [{ tipo: 'USB', cantidad: 1 }],
        'Matrimonio|Golden':      [{ tipo: 'USB madera personalizado', cantidad: 1 }, { tipo: 'Fotos impresas 10x15', cantidad: 20 }, { tipo: 'Link descarga digital', cantidad: 1 }],

        // BODA CIVIL
        'Boda Civil|Proforma 02': [{ tipo: 'USB', cantidad: 1 }],
        'Boda Civil|Proforma 03': [{ tipo: 'USB', cantidad: 1 }],
        'Boda Civil|Golden':      [{ tipo: 'USB madera personalizado', cantidad: 1 }, { tipo: 'Fotos impresas 10x15', cantidad: 20 }, { tipo: 'Link descarga digital', cantidad: 1 }],

        // BAUTIZO
        'Bautizo|Proforma 02': [{ tipo: 'USB', cantidad: 1 }],
        'Bautizo|Proforma 03': [{ tipo: 'USB', cantidad: 1 }],
        'Bautizo|Golden':      [{ tipo: 'USB madera personalizado', cantidad: 1 }, { tipo: 'Fotos impresas 10x15', cantidad: 20 }, { tipo: 'Link descarga digital', cantidad: 1 }],

        // BABY SHOWER
        'Baby Shower|Proforma 02': [{ tipo: 'USB', cantidad: 1 }],
        'Baby Shower|Proforma 03': [{ tipo: 'USB', cantidad: 1 }],
        'Baby Shower|Golden':      [{ tipo: 'USB madera personalizado', cantidad: 1 }, { tipo: 'Fotos impresas 10x15', cantidad: 15 }, { tipo: 'Link descarga digital', cantidad: 1 }],

        // CUMPLEAÑOS ADULTO
        'Cumpleaños Adulto|Proforma 02': [{ tipo: 'USB', cantidad: 1 }],
        'Cumpleaños Adulto|Proforma 03': [{ tipo: 'USB', cantidad: 1 }],
        'Cumpleaños Adulto|Golden':      [{ tipo: 'USB madera personalizado', cantidad: 1 }, { tipo: 'Fotos impresas 10x15', cantidad: 20 }, { tipo: 'Link descarga digital', cantidad: 1 }],

        // INFANTIL
        'Infantil|Proforma 02': [{ tipo: 'USB', cantidad: 1 }],
        'Infantil|Proforma 03': [{ tipo: 'USB', cantidad: 1 }],
        'Infantil|Golden':      [{ tipo: 'USB madera personalizado', cantidad: 1 }, { tipo: 'Fotos impresas 10x15', cantidad: 20 }, { tipo: 'Link descarga digital', cantidad: 1 }],

        // FOTOGRAFÍA
        'Fotografía|Exterior / Estudio':        [{ tipo: 'USB con fotos', cantidad: 1 }, { tipo: 'Fotos impresas 10x15', cantidad: 5 }, { tipo: 'Link descarga digital', cantidad: 1 }],
        'Fotografía|Evento':                    [{ tipo: 'USB con fotos', cantidad: 1 }, { tipo: 'Fotos impresas 10x15', cantidad: 10 }, { tipo: 'Link descarga digital', cantidad: 1 }],
        'Fotografía|Exterior / Estudio Golden': [{ tipo: 'USB con fotos', cantidad: 1 }, { tipo: 'Fotos impresas 10x15', cantidad: 12 }, { tipo: 'Link descarga digital', cantidad: 1 }, { tipo: 'Video collage de fotos', cantidad: 1 }, { tipo: 'Mini Video reel', cantidad: 1 }],
        'Fotografía|Evento Golden':             [{ tipo: 'USB madera personalizado', cantidad: 1 }, { tipo: 'Fotos impresas 10x15', cantidad: 20 }, { tipo: 'Link descarga digital', cantidad: 1 }, { tipo: 'Video collage de fotos', cantidad: 1 }],
    };

    /* ── Helpers ── */
    const mostrar = el => { if (el) el.style.display = 'block'; };
    const ocultar = el => { if (el) el.style.display = 'none';  };

    /* ── Calcular saldo ── */
    function calcularSaldo() {
        const t = parseFloat(elTotal?.value)    || 0;
        const a = parseFloat(elAdelanto?.value) || 0;
        if (elSaldo) elSaldo.value = (t - a).toFixed(2);
    }

    /* ── Al cambiar servicio → poblar paquetes ── */
    elServicio.addEventListener('change', function () {

    const idservicio = this.value;

    console.log("SERVICIO CAMBIADO:", idservicio);

    // Reset
    elPaquete.innerHTML = '';
    elProforma.innerHTML = '<option value="">Seleccione proforma</option>';

    elPaquete.disabled = false;
    elProforma.disabled = true;

    ocultar(bloqueAlumnos);
    ocultar(bloqueDetalleProforma);
    ocultar(bloqueSesionPre);
    ocultar(bloqueEvento);
    ocultar(bloqueEntregas);

    if (!idservicio) {
        elPaquete.innerHTML = '<option value="">Seleccione servicio primero</option>';
        return;
    }

    // Opción por defecto
    let defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = 'Seleccione paquete';
    elPaquete.appendChild(defaultOption);

    // Obtener paquetes únicos
    const paquetesMap = new Map();

    PROFORMAS.forEach(p => {
        if (String(p.idservicio) === String(idservicio)) {
            paquetesMap.set(Number(p.idpaquete), p.nombre_paquete);
        }
    });

    console.log("PAQUETES ENCONTRADOS:", paquetesMap);

    paquetesMap.forEach((nombre, id) => {
        let option = document.createElement('option');
        option.value = id;
        option.textContent = nombre;
        elPaquete.appendChild(option);
    });

    });
    /* ── Al cambiar paquete → poblar proformas ── */
    elPaquete?.addEventListener('change', () => {
        const idservicio = elServicio.value;
        const idpaquete  = elPaquete.value;

        elProforma.innerHTML = '<option value="">Seleccione proforma</option>';
        elProforma.disabled  = true;

        ocultar(bloqueDetalleProforma);
        ocultar(bloqueSesionPre);
        ocultar(bloqueEvento);
        ocultar(bloqueEntregas);
        ocultar(bloqueAlumnos);

        if (!idpaquete) return;

        const proformasFiltradas = PROFORMAS.filter(
            p => String(p.idservicio) === String(idservicio) && 
                String(p.idpaquete)  === String(idpaquete)
        );

        proformasFiltradas.forEach(pf => {
            const opt = document.createElement('option');
            opt.value       = pf.idproforma;
            opt.textContent = `${pf.nombre} — S/.${parseFloat(pf.precio).toFixed(2)}`;
            opt.dataset.precio          = pf.precio;
            opt.dataset.porAlumno       = pf.precio_por_alumno;
            opt.dataset.nombreProforma  = pf.nombre;
            elProforma.appendChild(opt);
        });

        elProforma.disabled = false;
    });

    /* ── Al cambiar proforma → autocompletar todo ── */
    elProforma?.addEventListener('change', () => {
        const selected = elProforma.options[elProforma.selectedIndex];
        if (!selected || !selected.value) return;

        const precio      = parseFloat(selected.dataset.precio)   || 0;
        const porAlumno   = selected.dataset.porAlumno === '1';
        const nombrePf    = selected.dataset.nombreProforma;
        const nombreSvc   = elServicio.options[elServicio.selectedIndex]?.text || '';
        const nombrePaq   = elPaquete.options[elPaquete.selectedIndex]?.text   || '';
        const esGolden    = nombrePaq === 'Golden';

        // Autocomplete precio
        if (!porAlumno) {
            if (elTotal) elTotal.value = precio.toFixed(2);
            calcularSaldo();
            ocultar(bloqueAlumnos);
        } else {
            // Precio por alumno: mostrar bloque y calcular
            mostrar(bloqueAlumnos);
            calcularTotalAlumnos(precio);
        }

        // Mostrar info de proforma
        ocultar(bloqueDetalleProforma);

        // Mostrar bloques según paquete
        if (esGolden) {
            mostrar(bloqueSesionPre);
        } else {
            ocultar(bloqueSesionPre);
        }
        mostrar(bloqueEvento);

        // Cargar entregas predefinidas
        const claveEntrega = `${nombreSvc}|${nombrePf}`;
        const entregasPredefinidas = ENTREGAS_POR_PROFORMA[claveEntrega] || [];
        cargarEntregas(entregasPredefinidas);
    });

    /* ── Calcular total por alumnos ── */
    function calcularTotalAlumnos(precioPorAlumno) {
        const cant = parseInt(elCantidad?.value) || 20;
        const total = cant * precioPorAlumno;
        if (elTotal) elTotal.value = total.toFixed(2);
        const elTotalCalc = document.getElementById('totalCalculado');
        if (elTotalCalc) elTotalCalc.value = `S/. ${total.toFixed(2)}`;
        calcularSaldo();
    }

    elCantidad?.addEventListener('input', () => {
        const selected = elProforma.options[elProforma.selectedIndex];
        if (!selected || selected.dataset.porAlumno !== '1') return;
        calcularTotalAlumnos(parseFloat(selected.dataset.precio) || 0);
    });

    /* ── Cargar entregas predefinidas ── */
    function cargarEntregas(entregas) {
        listaEntregas.innerHTML = '';

        if (entregas.length === 0) {
            ocultar(bloqueEntregas);
            return;
        }

        mostrar(bloqueEntregas);

        entregas.forEach((e, i) => {
            const div = document.createElement('div');
            div.classList.add('fila', 'fila-2');
            div.style.marginBottom = '8px';
            div.innerHTML = `
                <div class="campo">
                    <label>Material ${i + 1}</label>
                    <input type="text" name="entrega_tipo[]" value="${e.tipo}">
                </div>
                <div class="campo">
                    <label>Cantidad</label>
                    <input type="number" name="entrega_cantidad[]" value="${e.cantidad}" min="1">
                </div>
            `;
            listaEntregas.appendChild(div);
        });

        // Botón para agregar entrega extra
        const btnAgregar = document.createElement('button');
        btnAgregar.type = 'button';
        btnAgregar.className = 'btn-agregar';
        btnAgregar.textContent = '+ Agregar material extra';
        btnAgregar.onclick = () => agregarEntregaExtra();
        listaEntregas.appendChild(btnAgregar);
    }

    /* ── Agregar entrega extra manualmente ── */
    window.agregarEntregaExtra = function () {
        const div = document.createElement('div');
        div.classList.add('fila', 'fila-3');
        div.style.marginBottom = '8px';

        div.innerHTML = `
            <div class="campo">
                <label>Material</label>
                <input type="text" name="entrega_tipo[]" placeholder="Ej: Álbum, Marco, etc.">
            </div>

            <div class="campo">
                <label>Cantidad</label>
                <input type="number" name="entrega_cantidad[]" value="1" min="1">
            </div>

            <div class="campo">
                <label>&nbsp;</label>
                <button type="button" class="btn-quitar"
                    onclick="this.closest('.fila').remove()">
                    ✕ Quitar
                </button>
            </div>
        `;

        // Insertar antes del botón agregar
        const btn = listaEntregas.querySelector('.btn-agregar');
        listaEntregas.insertBefore(div, btn);
    };

    /* ── Testigos ── */
    window.agregarTestigo = function () {
        const container = document.getElementById('testigosContainer');

        // Solo permitir 1 testigo
        if (container.children.length > 0) return;

        const div = document.createElement('div');
        div.classList.add('fila-testigo');

        div.innerHTML = `
            <div class="fila fila-3">
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
            </div>

            <div class="fila fila-3" style="margin-top:10px;">
                <div class="campo">
                    <label>Dirección</label>
                    <input type="text" name="testigo_direccion[]" placeholder="Dirección">
                </div>

                <div class="campo">
                    <label>Teléfono</label>
                    <input type="text" name="testigo_telefono[]" placeholder="987654321">
                </div>

                <div class="campo">
                    <label>&nbsp;</label>
                    <button type="button" class="btn-quitar"
                        onclick="eliminarTestigo()">
                        ✕ Quitar
                    </button>
                </div>
            </div>

            <hr class="sep">
        `;

        container.appendChild(div);

        // Oculta botón + Agregar
        document.getElementById('btnAgregarTestigo').style.display = 'none';
    };

    window.eliminarTestigo = function () {
        const testigo = document.querySelector('.fila-testigo');

        if (testigo) {
            testigo.remove();
        }

        // Muestra nuevamente el botón + Agregar
        document.getElementById('btnAgregarTestigo').style.display = 'inline-block';
    };

    /* ── Eventos de pago ── */
    elTotal?.addEventListener('input',    calcularSaldo);
    elAdelanto?.addEventListener('input', calcularSaldo);

    /* ── Init ── */
    calcularSaldo();
});