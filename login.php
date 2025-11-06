<?php
session_start();
require 'conexion.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST["usuario"]);
    $contrasena = $_POST["contrasena"];

    if (empty($usuario) || empty($contrasena)) {
        $mensaje = "⚠️ Completa todos los campos.";
    } else {
        $stmt = $conn->prepare("SELECT id, contrasena, rol, nombre FROM usuarios WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $hash, $rol, $nombre);

        if ($stmt->num_rows == 1) {
            $stmt->fetch();
            if (password_verify($contrasena, $hash)) {
                // Inicio de sesión exitoso
                $_SESSION['id_usuario'] = $id;
                $_SESSION['usuario'] = $usuario;
                $_SESSION['rol'] = $rol;
                $_SESSION['nombre'] = $nombre;

                // Redirigir según rol
                if ($rol === 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit;
            } else {
                $mensaje = "❌ Contraseña incorrecta.";
            }
        } else {
            $mensaje = "❌ Usuario no encontrado.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h4>Iniciar Sesión</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($mensaje)) : ?>
                        <div class="alert alert-info"><?php echo $mensaje; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Usuario (Email)</label>
                            <input type="email" name="usuario" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="contrasena" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Ingresar</button>
                        <a href="registro.php" class="btn btn-link w-100 mt-2">No tengo cuenta</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
