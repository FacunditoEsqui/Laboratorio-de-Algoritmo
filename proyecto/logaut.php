<?php
session_start(); // Conectamos con la sesión actual
session_unset(); // Limpiamos todas las variables (nombre, id, etc.)
session_destroy(); // Destruimos la sesión en el servidor

// Te redirigimos al login inmediatamente
header("Location: login.php");
exit();
?>
