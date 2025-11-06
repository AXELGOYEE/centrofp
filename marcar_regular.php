<?php
require 'conexion.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "UPDATE preinscripciones SET estado='regular' WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: dashboard.php?msg=Alumno marcado como Regular");
        exit;
    } else {
        echo "Error al actualizar estado.";
    }
}
?>
