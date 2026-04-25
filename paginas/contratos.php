<?php
/* ============================================================
   contratos_33.php  —  ISHUME
   ETAPA 1 / 3  (≈ 33 % de avance)
   · Estructura HTML del formulario de registro
   · Estilos base (tarjetas, campos, botón)
   · Sin lógica PHP de inserción
   · Sin JS dinámico (selects estáticos, sin cálculo de saldo)
   ============================================================ */
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contratos — Etapa 1</title>

<link rel="stylesheet" href="contratos.css">
</head>
<body>

<h2 class="titulo-modulo">Registrar Contrato</h2>

<!-- ══════════════ FORMULARIO ══════════════ -->
<div class="card">
<form method="POST" action="#">

    <!-- ── Cliente ── -->
    <p class="seccion-titulo">👤 Datos del Cliente</p>

    <div class="fila fila-3">
        <div class="campo">
            <label>DNI</label>
            <input type="text" name="dni" maxlength="20" placeholder="12345678">
        </div>
        <div class="campo">
            <label>Nombre</label>
            <input type="text" name="nombre_cliente" placeholder="Ej: María">
        </div>
        <div class="campo">
            <label>Apellidos</label>
            <input type="text" name="apellidos" placeholder="Ej: García López">
        </div>
    </div>

    <div class="fila fila-2">
        <div class="campo">
            <label>Dirección</label>
            <input type="text" name="direccion" placeholder="Av. Principal 123">
        </div>
        <div class="campo">
            <label>Teléfono</label>
            <input type="text" name="telefono" placeholder="987 654 321">
        </div>
    </div>

    <hr class="sep">

    <!-- ── Tipo de contrato ── -->
    <p class="seccion-titulo">📋 Tipo de Contrato</p>

    <div class="fila fila-2">
        <div class="campo">
            <label>Tipo</label>
            <select name="idtipo">
                <option value="1">Promoción</option>
                <option value="2">Video</option>
                <option value="3">Sesión Especial</option>
            </select>
        </div>
        <div class="campo">
            <label>Servicio / Paquete</label>
            <select name="servicio">
                <option value="">Seleccione un servicio</option>
                <option value="Cuadros">Cuadros</option>
                <option value="Anuarios">Anuarios</option>
                <option value="Grabación">Grabación</option>
                <option value="Paquete Básico">Paquete Básico</option>
            </select>
        </div>
    </div>

    <hr class="sep">

    <!-- ── Pago ── -->
    <p class="seccion-titulo">💰 Pago</p>

    <div class="fila fila-4">
        <div class="campo">
            <label>Fecha Contrato</label>
            <input type="date" name="fecha_contrato">
        </div>
        <div class="campo">
            <label>Total (S/)</label>
            <input type="number" name="total" placeholder="0.00" step="0.01" min="0">
        </div>
        <div class="campo">
            <label>Adelanto (S/)</label>
            <input type="number" name="adelanto" placeholder="0.00" step="0.01" min="0">
        </div>
        <div class="campo">
            <label>Saldo (S/)</label>
            <input type="number" name="saldo" placeholder="0.00" readonly
                   style="background:#f8f9fa; color:#2563eb; font-weight:600;">
        </div>
    </div>

    <button type="submit" class="btn-guardar">Guardar Contrato</button>

</form>
</div>



</body>
</html>