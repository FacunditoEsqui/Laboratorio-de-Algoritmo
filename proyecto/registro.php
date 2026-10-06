<?php 
include("conexion.php"); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $email  = mysqli_real_escape_string($conexion, $_POST['email']);
    $passPlano = $_POST['pass'];
    $confirmPass = $_POST['confirm_pass'];

    if ($passPlano !== $confirmPass) {
        echo "<script>alert('Las contraseñas no coinciden.'); window.history.back();</script>";
        exit();
    }

    $password = password_hash($passPlano, PASSWORD_DEFAULT);

    $checkEmail = "SELECT email FROM usuario WHERE email = '$email'";
    $resultadoCheck = mysqli_query($conexion, $checkEmail);

    if (mysqli_num_rows($resultadoCheck) > 0) {
        echo "<script>alert('El usuario ya está guardado, por favor prueba con otro correo.'); window.history.back();</script>";
    } else {
        $sql = "INSERT INTO usuario (nombre, email, pass) VALUES ('$nombre', '$email', '$password')"; 
        if (mysqli_query($conexion, $sql)) {
            echo "<script>alert('¡Registro exitoso!'); window.location='login.php';</script>";
            exit();
        } else {
            echo "Error inesperado: " . mysqli_error($conexion);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crea tu cuenta</title>
    <link rel="stylesheet" href="estilos.css">
    <!-- Importamos una tipografía limpia y moderna -->
    <link href="https://googleapis.com" rel="stylesheet">
</head>
<body>

    <div class="card">
        <!-- Contenedor del avatar circular exterior de la imagen -->
        <div class="avatar-container">
            <div class="profile-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
        </div>

        <h1 class="title">Crea tu cuenta</h1>
        <p class="subtitle">Regístrate para comenzar</p>

        <form method="POST" action="">
            <div class="form-group">
                <input type="text" name="nombre" class="form-input" placeholder="Nombre completo" required>
            </div>

            <div class="form-group">
                <input type="email" name="email" class="form-input" placeholder="Email – you@example.com" required>
            </div>
            
            <div class="form-group">
                <input type="password" name="pass" class="form-input" placeholder="Contraseña – ••••••••" required>
            </div>
            
            <div class="form-group">
                <input type="password" name="confirm_pass" class="form-input" placeholder="Confirmar contraseña – ••••••••" required>
            </div>

            <button type="submit" class="submit-btn">Crear cuenta</button>
        </form>

        <p class="footer-text"><a href="login.php">Inicia sesión</a></p>
    </div>

</body>
</html>
