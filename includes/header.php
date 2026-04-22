<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Sistema Ishume</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- CSS GLOBAL -->
<link rel="stylesheet" href="css/main.css">

<!-- CSS POR MÓDULO -->
<?php
$pagina = isset($_GET['pagina']) ? $_GET['pagina'] : 'dashboard';
$cssModulo = "css/" . $pagina . ".css";

if(file_exists($cssModulo)){
    echo '<link rel="stylesheet" href="'.$cssModulo.'">';
}
?>
<link rel="stylesheet" href="css/proveedores.css">
</head>
<body>