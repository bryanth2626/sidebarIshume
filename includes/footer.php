<!-- JS GLOBAL -->
<script src="js/proveedores.js"></script>
<script>
    
    const eventosDB = <?php echo json_encode($eventos_json, JSON_UNESCAPED_UNICODE); ?>;
</script>
<script src="js/calendar.js"></script>
<script src="js/eventos.js"></script>

<?php
$pagina = isset($_GET['pagina']) ? $_GET['pagina'] : 'dashboard';
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php
$jsModulo = "js/" . $pagina . ".js";

if(file_exists($jsModulo)){
    echo '<script src="'.$jsModulo.'"></script>';
}
?>
</body>
</html>