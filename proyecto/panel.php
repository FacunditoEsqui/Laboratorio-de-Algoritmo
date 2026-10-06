<?php
session_start();

// SEGURIDAD: Si no hay sesión iniciada, mandamos al usuario al login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$nombreUsuario = $_SESSION['nombre'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Principal - Tuortox</title>
    <link rel="stylesheet" href="estilos.css?v=3">
  
</head>
<body class="panel-page">

    <main class="panel-dashboard">
        <section class="panel-time" aria-live="polite">
            <div id="reloj">00:00:00</div>
            <div id="fecha">Cargando fecha...</div>
        </section>

        <section class="panel-welcome">
            <p class="panel-eyebrow">Panel principal</p>
            <h1>Hola, <?php echo htmlspecialchars($nombreUsuario); ?></h1>
        </section>

        <nav class="panel-tools" aria-label="Herramientas">
            <a href="calendario.php" class="panel-tool panel-tool-primary">Calendario</a>
            <a href="alarma.php" class="panel-tool">Alarma</a>
            <a href="cronometro.php" class="panel-tool">Cronómetro</a>
            <a href="temporizador.php" class="panel-tool">Temporizador</a>
        </nav>

        <a href="logaut.php" class="btn-cerrar">Cerrar sesión</a>
    </main>

    <script>
        function actualizarReloj() {
            const ahora = new Date();
            
            // Formatear la hora
            const horas = String(ahora.getHours()).padStart(2, '0');
            const minutos = String(ahora.getMinutes()).padStart(2, '0');
            const segundos = String(ahora.getSeconds()).padStart(2, '0');
            
            document.getElementById('reloj').textContent = `${horas}:${minutos}:${segundos}`;

            // Formatear la fecha
            const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('fecha').textContent = ahora.toLocaleDateString('es-ES', opciones);
        }

        // Ejecutar cada segundo
        setInterval(actualizarReloj, 1000);
        
        // Ejecutar inmediatamente al cargar
        actualizarReloj();
    </script>


</body>
</html>
