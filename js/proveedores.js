document.addEventListener("DOMContentLoaded", function(){

    const cantidad = document.getElementById("cantidad");
    const precio = document.getElementById("precio");
    const total = document.getElementById("total");
    const adelanto = document.getElementById("adelanto");
    const saldo = document.getElementById("saldo");

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

});