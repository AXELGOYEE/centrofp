<?php
require 'conexion.php'; // Archivo con la conexión a MySQL

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST["nombre"]);
    $apellido = trim($_POST["apellido"]);
    $dni = trim($_POST["dni"]);
    $usuario = trim($_POST["usuario"]);
    $contrasena = $_POST["contrasena"];

    // Validar que los campos no estén vacíos
    if (empty($nombre) || empty($apellido) || empty($dni) || empty($usuario) || empty($contrasena)) {
        $mensaje = "⚠️ Todos los campos son obligatorios.";
    } else {
        // Verificar si ya existe el usuario o el DNI
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ? OR dni = ?");
        $stmt->bind_param("ss", $usuario, $dni);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $mensaje = "⚠️ El usuario o DNI ya está registrado.";
        } else {
            // Insertar nuevo usuario
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO usuarios (usuario, contrasena, nombre, apellido, dni) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $usuario, $hash, $nombre, $apellido, $dni);

            if ($stmt->execute()) {
                $mensaje = "✅ Registro exitoso. Ya puedes iniciar sesión.";
            } else {
                $mensaje = "❌ Error al registrar el usuario.";
            }
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h4>Registro de Usuario</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($mensaje)) : ?>
                        <div class="alert alert-info"><?php echo $mensaje; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Apellido</label>
                            <input type="text" name="apellido" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">DNI</label>
                            <input type="text" name="dni" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Usuario (Email)</label>
                            <input type="email" name="usuario" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="contrasena" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Registrarse</button>
                        <a href="login.php" class="btn btn-link w-100 mt-2">Ya tengo cuenta</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
