document.addEventListener("DOMContentLoaded", function(){

    const cantidad = document.getElementById("cantidad");
    const precio = document.getElementById("precio");
    const total = document.getElementById("total");
    const adelanto = document.getElementById("adelanto");
    const saldo = document.getElementById("saldo");

    /* =========================
       CALCULAR
    ========================= */

    function calcular() {

        let c = parseFloat(cantidad.value) || 0;
        let p = parseFloat(precio.value) || 0;
        let a = parseFloat(adelanto.value) || 0;

        let t = c * p;
        let s = t - a;

        total.value = t.toFixed(2);
        saldo.value = s.toFixed(2);
    }

    cantidad.addEventListener("input", calcular);
    precio.addEventListener("input", calcular);
    adelanto.addEventListener("input", calcular);




    /* =========================
       EDITAR
    ========================= */

    const botonesEditar = document.querySelectorAll(".btn-editar");

    botonesEditar.forEach(boton => {

        boton.addEventListener("click", () => {

            document.getElementById("idcompra").value =
                boton.dataset.idcompra;

            document.getElementById("idproveedor").value =
                boton.dataset.idproveedor;

            document.getElementById("nombre_proveedor").value =
                boton.dataset.proveedor;

            document.getElementById("producto").value =
                boton.dataset.producto;

            document.getElementById("cantidad").value =
                boton.dataset.cantidad;

            document.getElementById("precio").value =
                boton.dataset.precio;

            document.getElementById("fecha_compra").value =
                boton.dataset.fecha;

            document.getElementById("adelanto").value =
                boton.dataset.adelanto;

            calcular();
        });

    });

});