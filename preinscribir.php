<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'user') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_curso'])) {
    $id_usuario = $_SESSION['id_usuario'];
    $id_curso = $_POST['id_curso'];

    // Verificar si ya se preinscribió
    $stmt = $conn->prepare("SELECT id FROM preinscripciones WHERE id_usuario=? AND id_curso=?");
    $stmt->bind_param("ii", $id_usuario, $id_curso);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        // Insertar preinscripción
        $stmt = $conn->prepare("INSERT INTO preinscripciones (id_usuario, id_curso) VALUES (?, ?)");
        $stmt->bind_param("ii", $id_usuario, $id_curso);
        $stmt->execute();
    }

    $stmt->close();
}

header("Location: dashboard.php");
exit;
