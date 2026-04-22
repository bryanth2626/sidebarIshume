<?php
$pagina = isset($_GET['pagina']) ? $_GET['pagina'] : 'dashboard';

function activo($item, $pagina){
    return $item == $pagina ? 'active' : '';
}
?>

<div class="sidebar">

    <!-- LOGO -->
    <div class="logo-container">
        <img src="img/ishume.png" class="logo">
    </div>

    <!-- MENÚ -->
    <div class="menu">

        <a href="index.php?pagina=dashboard" class="menu-item <?= activo('dashboard',$pagina) ?>">
            <span class="icon">⬛</span> Dashboard
        </a>

        <a href="index.php?pagina=proveedores" class="menu-item <?= activo('proveedores',$pagina) ?>">
            🚚 Proveedores
        </a>

        <a href="index.php?pagina=contratos" class="menu-item <?= activo('contratos',$pagina) ?>">
            📄 Contratos
        </a>

        <a href="index.php?pagina=eventos" class="menu-item <?= activo('eventos',$pagina) ?>">
            📅 Eventos
        </a>

    </div>

</div>