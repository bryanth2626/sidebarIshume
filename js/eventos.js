// ════════════════════════════════════════════════════════════════════════════════
// EVENTOS.JS - Funciones para manejar personal dinámico en el formulario
// ════════════════════════════════════════════════════════════════════════════════

/**
 * Agregar un nuevo campo de selección de personal
 */
function agregarPersonal() {
    const contenedor = document.getElementById('contenedor-personal');
    
    if (!contenedor) {
        console.error('No se encontró el contenedor de personal');
        return;
    }

    // Crear una nueva fila
    const nuevaFila = document.createElement('div');
    nuevaFila.className = 'fila-personal';

    // Obtener todo el HTML del select de la primera fila (para copiar las opciones)
    const primerSelect = contenedor.querySelector('select.select-personal');
    
    if (primerSelect) {
        // Clonar el select existente
        const nuevoSelect = primerSelect.cloneNode(true);
        nuevoSelect.value = ''; // Limpiar el valor seleccionado

        // Crear el botón de eliminar
        const btnEliminar = document.createElement('button');
        btnEliminar.type = 'button';
        btnEliminar.className = 'btn-eliminar';
        btnEliminar.textContent = '❌';
        btnEliminar.onclick = function(e) {
            e.preventDefault();
            eliminarCampo(this);
        };

        // Agregar select y botón a la nueva fila
        nuevaFila.appendChild(nuevoSelect);
        nuevaFila.appendChild(btnEliminar);

        // Agregar la nueva fila al contenedor
        contenedor.appendChild(nuevaFila);
    } else {
        console.error('No se encontró el select de personal base');
    }
}

/**
 * Eliminar un campo de personal
 * @param {HTMLElement} boton - Elemento botón que se hizo click
 */
function eliminarCampo(boton) {
    if (!boton || !boton.parentElement) {
        console.error('El botón no tiene padre');
        return;
    }
    
    const fila = boton.parentElement;
    fila.remove();
}

// ════════════════════════════════════════════════════════════════════════════════
// Validación opcional del formulario
// ════════════════════════════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', function() {
    const formulario = document.querySelector('.form-eventos form');
    
    if (formulario) {
        formulario.addEventListener('submit', function(e) {
            // Verificar que haya al menos un personal seleccionado
            const selectsPersonal = document.querySelectorAll('select[name="personal_ids[]"]');
            const hayPersonalSeleccionado = Array.from(selectsPersonal).some(select => select.value !== '');
            
            if (!hayPersonalSeleccionado) {
                e.preventDefault();
                alert('Por favor, selecciona al menos un personal para el evento');
                return false;
            }
        });
    }
});