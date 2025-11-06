<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// ========================
// ACCIONES
// ========================
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $accion = $_GET['accion'];

    switch($accion) {
        case 'aceptar':
            $stmt = $conn->prepare("UPDATE preinscripciones SET estado='aceptado' WHERE id=?");
            break;
        case 'rechazar':
            $stmt = $conn->prepare("UPDATE preinscripciones SET estado='rechazado' WHERE id=?");
            break;
        case 'regular':
            $stmt = $conn->prepare("UPDATE preinscripciones SET estado='regular' WHERE id=?");
            break;
        case 'finalizar':
            $stmt = $conn->prepare("UPDATE preinscripciones SET estado='finalizado' WHERE id=?");
            break;
        case 'borrar_preinscripcion':
            $stmt = $conn->prepare("DELETE FROM preinscripciones WHERE id=?");
            break;
        case 'cambiar_estado_curso':
            $nuevo_estado = $_GET['nuevo_estado'] ?? 'abierto';
            $stmt = $conn->prepare("UPDATE cursos SET estado=? WHERE id=?");
            $stmt->bind_param("si", $nuevo_estado, $id);
            $stmt->execute();
            $stmt->close();
            header("Location: admin.php");
            exit;
            
    }

    if (isset($stmt) && $accion != 'cambiar_estado_curso') {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: admin.php");
    exit;
}

// ========================
// CURSOS
// ========================
$cursos_result = $conn->query("SELECT * FROM cursos ORDER BY nombre ASC");

// ========================
// PREINSCRIPCIONES
// ========================
$query = "
SELECT p.id AS id_pre, p.estado, p.id_curso,
       u.id AS id_usuario, u.nombre, u.apellido, u.usuario, telefono,
       c.nombre AS curso_nombre, c.descripcion AS curso_desc, c.estado AS curso_estado,
       f.*
FROM preinscripciones p
INNER JOIN usuarios u ON p.id_usuario = u.id
INNER JOIN cursos c ON p.id_curso = c.id
LEFT JOIN ficha_preinscripcion f ON f.id_preinscripcion = p.id
ORDER BY c.nombre ASC, p.estado ASC, p.id DESC
";
$result = $conn->query($query);

// Agrupar por curso
$preinscripciones_por_curso = [];
while($row = $result->fetch_assoc()) {
    $preinscripciones_por_curso[$row['curso_nombre']][] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Administrador</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Panel de Administrador</h3>
        <a href="logout.php" class="btn btn-danger">Cerrar sesión</a>
    </div>

    <!-- ================== GESTIÓN DE CURSOS ================== -->
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h5>Gestión de Cursos</h5>
        </div>
        <div class="card-body">
            <form method="POST" class="d-flex mb-3">
                <input type="text" name="nuevo_curso" class="form-control me-2" placeholder="Nombre del curso">
                <input type="text" name="descripcion_curso" class="form-control me-2" placeholder="Descripción">
                <button type="submit" name="agregar_curso" class="btn btn-success">Agregar</button>
            </form>
            <select class="form-select mb-2" onchange="location = this.value;">
                <option value="">Seleccionar curso para habilitar/suspender</option>
                <?php while($curso = $cursos_result->fetch_assoc()): ?>
                    <option value="admin.php?accion=cambiar_estado_curso&id=<?php echo $curso['id']; ?>&nuevo_estado=<?php echo $curso['estado'] == 'abierto' ? 'cerrado' : 'abierto'; ?>">
                        <?php echo htmlspecialchars($curso['nombre']) . " (" . $curso['estado'] . ")"; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
    </div>

    <!-- ================== PREINSCRIPCIONES ================== -->
    <?php foreach($preinscripciones_por_curso as $curso_nombre => $inscripciones): ?>
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5><?php echo htmlspecialchars($curso_nombre); ?></h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Nombre y Apellido</th>
                            <th>Usuario</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($inscripciones as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nombre'].' '.$row['apellido']); ?></td>
                            <td><?php echo htmlspecialchars($row['usuario']); ?></td>
                            <td><?php echo htmlspecialchars($row['telefono']); ?></td>
                            <td><?php echo ucfirst($row['estado']); ?></td>
                            <td>
                                <a class="btn btn-primary btn-sm" data-bs-toggle="collapse" href="#detalle<?php echo $row['id_pre']; ?>">Ver detalles</a>
                                <a class="btn btn-danger btn-sm" href="admin.php?accion=borrar_preinscripcion&id=<?php echo $row['id_pre']; ?>">Borrar</a>

                                <?php if($row['estado'] == 'pendiente'): ?>
                                    <a href="admin.php?accion=aceptar&id=<?php echo $row['id_pre']; ?>" class="btn btn-success btn-sm">Aceptar</a>
                                    <a href="admin.php?accion=rechazar&id=<?php echo $row['id_pre']; ?>" class="btn btn-danger btn-sm">Rechazar</a>
                                <?php elseif($row['estado'] == 'aceptado'): ?>
                                    <a href="admin.php?accion=regular&id=<?php echo $row['id_pre']; ?>" class="btn btn-warning btn-sm">Marcar como Regular</a>
                                    <a href="admin.php?accion=finalizar&id=<?php echo $row['id_pre']; ?>" class="btn btn-secondary btn-sm">Marcar como Finalizado</a>
                                <?php elseif($row['estado'] == 'regular'): ?>
                                    <a href="certificado_regular.php?id=<?php echo $row['id_pre']; ?>" class="btn btn-success btn-sm" target="_blank">📜 Certificado Regular</a>
                                    <a href="admin.php?accion=finalizar&id=<?php echo $row['id_pre']; ?>" class="btn btn-secondary btn-sm">Marcar como Finalizado</a>
                                <?php elseif($row['estado'] == 'finalizado'): ?>
                                    <a href="certificado.php?id=<?php echo $row['id_pre']; ?>" class="btn btn-primary btn-sm" target="_blank">📜 Certificado Finalización</a>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- FICHA COMPLETA DEL ALUMNO -->
                        <tr class="collapse" id="detalle<?php echo $row['id_pre']; ?>">
                            <td colspan="5">
                                <div class="p-3 bg-light border">
                                    <p><strong>DNI:</strong> <?php echo htmlspecialchars($row['dni']); ?></p>
                                    <p><strong>Ocupación:</strong> <?php echo htmlspecialchars($row['ocupacion']); ?></p>
                                    <p><strong>Estudio cursado:</strong> <?php echo htmlspecialchars($row['estudio_cursado']); ?></p>
                                    <p><strong>Título:</strong> <?php echo htmlspecialchars($row['titulo_estudio']); ?></p>
                                    <p><strong>Contacto Emergencia:</strong> <?php echo htmlspecialchars($row['contacto_emergencia_nombre'].' | Tel: '.$row['contacto_emergencia_telefono'].' | Vinculo: '.$row['contacto_emergencia_vinculo']); ?></p>
                                    <p><strong>Enfermedad / Condición:</strong> <?php echo htmlspecialchars($row['padece_enfermedad']); ?></p>
                                </div>
                            </td>
                        </tr>

                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
