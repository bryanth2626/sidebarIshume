<?php
$host = "localhost";
$usuario = "root";
$password = "";
$bd = "ISHUME";

try {
    $conn = new PDO("mysql:host=$host;dbname=$bd;charset=utf8", $usuario, $password);

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>