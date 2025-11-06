<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'user') {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$mensaje = "";

if (!isset($_GET['id_curso'])) {
    header("Location: dashboard.php");
    exit;
}

$id_curso = $_GET['id_curso'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $dni = $_POST['dni'];
    $cuil = $_POST['cuil'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $contacto_nombre = $_POST['contacto_emergencia_nombre'];
    $contacto_telefono = $_POST['contacto_emergencia_telefono'];
    $contacto_vinculo = $_POST['contacto_emergencia_vinculo'];
    $estudio_cursado = $_POST['estudio_cursado'];
    $titulo_estudio = $_POST['titulo_estudio'];
    $padece_enfermedad = $_POST['padece_enfermedad'];
    $ocupacion = $_POST['ocupacion'];

    // Validar que campos numéricos contengan solo números
    if (!ctype_digit($dni) || !ctype_digit($cuil) || ($telefono && !ctype_digit($telefono)) || ($contacto_telefono && !ctype_digit($contacto_telefono))) {
        $mensaje = "❌ DNI, CUIL y números de teléfono deben contener solo números.";
    } else {
        // Crear preinscripción
        $stmt = $conn->prepare("INSERT INTO preinscripciones (id_usuario, id_curso) VALUES (?, ?)");
        $stmt->bind_param("ii", $id_usuario, $id_curso);

        if ($stmt->execute()) {
            $id_preinscripcion = $stmt->insert_id;

            $stmt2 = $conn->prepare("INSERT INTO ficha_preinscripcion 
                (id_preinscripcion, nombre, apellido, dni, cuil, telefono, email, contacto_emergencia_nombre, contacto_emergencia_telefono, contacto_emergencia_vinculo, estudio_cursado, titulo_estudio, padece_enfermedad, ocupacion)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param("isssssssssssss", $id_preinscripcion, $nombre, $apellido, $dni, $cuil, $telefono, $email, $contacto_nombre, $contacto_telefono, $contacto_vinculo, $estudio_cursado, $titulo_estudio, $padece_enfermedad, $ocupacion);

            if ($stmt2->execute()) {
                $mensaje = "✅ Preinscripción enviada correctamente.";
            } else {
                $mensaje = "❌ Error al guardar la ficha extendida.";
            }

            $stmt2->close();
        } else {
            $mensaje = "❌ Error al crear la preinscripción.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha de Preinscripción</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script>
        // Función para permitir solo números
        function soloNumeros(e) {
            let tecla = e.key;
            if (!/[0-9]/.test(tecla)) {
                e.preventDefault();
            }
        }
    </script>
</head>
<body class="bg-light">

<div class="container mt-4">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <h4>Ficha de Preinscripción</h4>
        </div>
        <div class="card-body">
            <?php if (!empty($mensaje)) : ?>
                <div class="alert alert-info"><?php echo $mensaje; ?></div>
                <a href="dashboard.php" class="btn btn-success">Volver al Dashboard</a>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Nombre</label><input type="text" name="nombre" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Apellido</label><input type="text" name="apellido" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>DNI</label><input type="text" name="dni" class="form-control" maxlength="10" onkeypress="soloNumeros(event)" required></div>
                        <div class="col-md-6 mb-3"><label>CUIL</label><input type="text" name="cuil" class="form-control" maxlength="11" onkeypress="soloNumeros(event)" required></div>
                        <div class="col-md-6 mb-3"><label>Teléfono</label><input type="text" name="telefono" class="form-control" maxlength="15" onkeypress="soloNumeros(event)"></div>
                        <div class="col-md-6 mb-3"><label>Email</label><input type="email" name="email" class="form-control"></div>
                        <div class="col-md-4 mb-3"><label>Contacto Emergencia - Nombre</label><input type="text" name="contacto_emergencia_nombre" class="form-control"></div>
                        <div class="col-md-4 mb-3"><label>Teléfono</label><input type="text" name="contacto_emergencia_telefono" class="form-control" maxlength="15" onkeypress="soloNumeros(event)"></div>
                        <div class="col-md-4 mb-3"><label>Vínculo</label><input type="text" name="contacto_emergencia_vinculo" class="form-control"></div>
                        <div class="col-md-4 mb-3"><label>Estudio cursado</label>
                            <select name="estudio_cursado" class="form-select">
                                <option value="Primario">Primario</option>
                                <option value="Secundario">Secundario</option>
                                <option value="Terciario">Terciario</option>
                            </select>
                        </div>
                        <div class="col-md-8 mb-3"><label>Título de estudio</label><input type="text" name="titulo_estudio" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Enfermedad / Condición</label><input type="text" name="padece_enfermedad" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Ocupación</label><input type="text" name="ocupacion" class="form-control"></div>
                    </div>
                    <button type="submit" class="btn btn-success">Enviar Preinscripción</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
