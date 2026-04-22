<!-- JS GLOBAL -->
<script src="js/main.js"></script>

<!-- JS POR MÓDULO -->
<?php
$pagina = isset($_GET['pagina']) ? $_GET['pagina'] : 'dashboard';
$jsModulo = "js/" . $pagina . ".js";

if(file_exists($jsModulo)){
    echo '<script src="'.$jsModulo.'"></script>';
}
?>

</body>
</html>