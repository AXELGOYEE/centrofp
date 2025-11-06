<?php
$servername = "localhost";
$username = "root";   // usuario de XAMPP por defecto
$password = "";       // contraseña vacía por defecto
$dbname = "centrofp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
