<?php
session_start(); // Iniciamos sesión para recordar al usuario
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conexion, $_POST['email']);
    $password = mysqli_real_escape_string($conexion, $_POST['pass']);

    // 1. Buscamos al usuario solo por su email
    $sql = "SELECT * FROM usuario WHERE email = '$email'";
    $resultado = mysqli_query($conexion, $sql);

    if (mysqli_num_rows($resultado) == 1) {
        $usuario = mysqli_fetch_assoc($resultado);

        // 2. COMPARACIÓN: ¿La contraseña coincide con la de la base de datos?
        if (password_verify($password, $usuario['pass'])) {
            // LOGIN EXITOSO
            $_SESSION['id_usuario'] = $usuario['idUsuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            
            header("Location: panel.php"); // Redirige a tu página principal
            exit();
        } else {
            // CONTRASEÑA INCORRECTA
            echo "<script>
                    alert('La contraseña es incorrecta. Intenta de nuevo.');
                    window.history.back();
                  </script>";
        }
    } else {
        // EL CORREO NO EXISTE
        echo "<script>
                alert('Este correo no está registrado.');
                window.history.back();
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tuortox</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body class="auth-container login-page">
    <div class="auth-card">
        <!-- Reemplazamos la 'T' por el icono SVG de la personita -->
        <div class="auth-logo" aria-label="Logo de Tuortox">
            <svg viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 60%; height: 60%;">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
        </div>
        <h1>Bienvenido de nuevo</h1>
        <p class="auth-subtitle">Inicia sesión para continuar</p>

        <form method="POST">
            <div class="input-wrapper">
                <input id="email" type="email" name="email" placeholder="Email – you@example.com" required>
            </div>

            <div class="input-wrapper password-wrapper">
                <input id="pass" type="password" name="pass" placeholder="••••••••" required>
            </div>

            <button type="submit">ENTRAR</button>
        </form>
        
        <p class="auth-footer"><a href="registro.php">Regístrate</a></p>
    </div>
</body>
</html>
