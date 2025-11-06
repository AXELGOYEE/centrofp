<?php
session_start();
require 'conexion.php';

// Verificar sesión
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'user') {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$nombre = $_SESSION['nombre'];

// Obtener cursos abiertos para inscripción
$cursos = $conn->query("SELECT * FROM cursos WHERE estado='abierto'");

// Obtener preinscripciones del usuario con estado
$stmt = $conn->prepare("
    SELECT p.id, c.nombre AS curso, c.descripcion, p.estado
    FROM preinscripciones p
    INNER JOIN cursos c ON p.id_curso = c.id
    WHERE p.id_usuario = ?
");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado_preinscripciones = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Usuario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Bienvenido, <?php echo htmlspecialchars($nombre); ?></h3>
        <a href="logout.php" class="btn btn-danger">Cerrar sesión</a>
    </div>

    <!-- Cursos disponibles -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5>Cursos Disponibles</h5>
        </div>
        <div class="card-body">
            <?php if ($cursos->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Descripción</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($curso = $cursos->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($curso['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($curso['descripcion']); ?></td>
                        <td>
                            <a href="ficha.php?id_curso=<?php echo $curso['id']; ?>" class="btn btn-success btn-sm">Preinscribirse</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p class="text-muted">No hay cursos disponibles para inscripción en este momento.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Mis preinscripciones -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5>Mis Preinscripciones</h5>
        </div>
        <div class="card-body">
            <?php if ($resultado_preinscripciones->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Certificados</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($pre = $resultado_preinscripciones->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pre['curso']); ?></td>
                        <td><?php echo htmlspecialchars($pre['descripcion']); ?></td>
                        <td><?php echo ucfirst($pre['estado']); ?></td>
                        <td>
                            <?php if ($pre['estado'] == 'regular'): ?>
                                <a href="certificado_regular.php?id=<?php echo $pre['id']; ?>" class="btn btn-success btn-sm" target="_blank">📜 Certificado Regular</a>
                            <?php elseif ($pre['estado'] == 'finalizado'): ?>
                                <a href="certificado.php?id=<?php echo $pre['id']; ?>" class="btn btn-primary btn-sm" target="_blank">📜 Certificado Finalización</a>
                            <?php else: ?>
                                <span class="text-muted">No disponible</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p class="text-muted">No tenés preinscripciones realizadas.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
