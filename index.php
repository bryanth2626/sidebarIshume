<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="content">
<?php
$pagina = isset($_GET['pagina']) ? $_GET['pagina'] : 'dashboard';
$archivo = "paginas/" . $pagina . ".php";

if(file_exists($archivo)){
    include $archivo;
}else{
    echo "<h4>Página no encontrada</h4>";
}
?>
</div>

<?php include 'includes/footer.php'; ?>