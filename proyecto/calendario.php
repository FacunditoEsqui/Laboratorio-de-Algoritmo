<?php
// 1. CONTROL DE SESIÓN Y SEGURIDAD
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// Incluimos tu conexión que usa mysqli_connect y la variable $conexion
include 'conexion.php';
$usuarioId = $_SESSION['id_usuario'];

// 2. PROCESAMIENTO DE PETICIONES ASÍNCRONAS (API INTERNA)
if (isset($_GET['accion'])) {
    header('Content-Type: application/json');
    
    // Acción: Obtener los recordatorios del usuario conectado con su descripción
    if ($_GET['accion'] === 'obtener') {
        $sql = "SELECT idCalendario, año, mes, dia, descripcion FROM calendario WHERE usuarioId = $usuarioId";
        $resultado = mysqli_query($conexion, $sql);
        
        $eventos = [];
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $fechaString = sprintf("%04d-%02d-%02d", $fila['año'], $fila['mes'], $fila['dia']);
            $eventos[] = [
                "id" => $fila['idCalendario'],
                "fecha" => $fechaString,
                "texto" => $fila['descripcion'] ? $fila['descripcion'] : "Sin descripción"
            ];
        }
        echo json_encode($eventos);
        exit();
    }

    // Acción: Guardar la fecha Y la descripción en MySQL
    if ($_GET['accion'] === 'guardar') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['fecha']) && isset($data['texto'])) {
            $partes = explode('-', $data['fecha']);
            $año = intval($partes[0]);
            $mes = intval($partes[1]);
            $dia = intval($partes[2]);
            $descripcion = $data['texto'];

            // Agregamos la columna 'descripcion' (ssi i) -> string para descripción
            $stmt = mysqli_prepare($conexion, "INSERT INTO calendario (año, mes, dia, descripcion, usuarioId) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "iiisi", $año, $mes, $dia, $descripcion, $usuarioId);
            
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(["status" => "success"]);
            } else {
                echo json_encode(["status" => "error", "message" => mysqli_error($conexion)]);
            }
            mysqli_stmt_close($stmt);
        }
        exit();
    }

    // Acción: Eliminar un recordatorio por ID
    if ($_GET['accion'] === 'eliminar') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['id'])) {
            $idRecordatorio = intval($data['id']);

            $stmt = mysqli_prepare($conexion, "DELETE FROM calendario WHERE idCalendario = ? AND usuarioId = ?");
            mysqli_stmt_bind_param($stmt, "ii", $idRecordatorio, $usuarioId);
            
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(["status" => "success"]);
            } else {
                echo json_encode(["status" => "error", "message" => mysqli_error($conexion)]);
            }
            mysqli_stmt_close($stmt);
        }
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario & Recordatorios - Tuortox</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

    <div class="dashboard-wrapper">
        <h1 class="dashboard-title">Calendario & Recordatorios</h1>
        
        <!-- Enlace para regresar al panel principal de manera cómoda -->
        <div style="margin-bottom: 20px;">
            <a href="panel.php" style="color: #fff; text-decoration: none; background: #333; padding: 8px 15px; border-radius: 5px; display: inline-block;">← Volver al Panel</a>
        </div>

        <div class="main-container">
            <!-- Panel Izquierdo: Calendario -->
            <div class="calendar-card">
                <div class="calendar-header">
                    <h2 id="month-year">Junio 2026</h2>
                    <div class="calendar-nav">
                        <button onclick="cambiarMes(-1)">&lt;</button>
                        <button onclick="cambiarMes(1)">&gt;</button>
                    </div>
                </div>
                
                <div class="weekdays">
                    <div>DO</div><div>LU</div><div>MA</div><div>MI</div><div>JU</div><div>VI</div><div>SA</div>
                </div>
                
                <div class="days-grid" id="days-container"></div>
            </div>

            <!-- Panel Derecho: Formulario y Agenda -->
            <div class="sidebar">
                <div class="form-card">
                    <h3>Nuevo Recordatorio</h3>
                    <div class="input-group">
                        <input type="text" id="reminder-text" placeholder="¿Qué tienes que recordar?">
                    </div>
                    <div class="input-group">
                        <input type="date" id="reminder-date">
                    </div>
                    <button class="btn-submit" onclick="agregarRecordatorio()">Guardar Recordatorio</button>
                </div>

                <div class="agenda-section">
                    <h3>Lista de Agenda</h3>
                    <div class="agenda-list" id="agenda-container"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let fechaActual = new Date(2026, 5, 1); 
        let recordatorios = [];

        const meses = [
            "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
            "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
        ];

        // Trae los datos desde la BD (incluyendo las descripciones de la tabla)
        async function cargarRecordatorios() {
            try {
                const respuesta = await fetch('calendario.php?accion=obtener');
                recordatorios = await respuesta.json();
                renderizarCalendario();
                renderizarAgenda();
            } catch (error) {
                console.error("Error al cargar los datos desde la BD:", error);
            }
        }

        function renderizarCalendario() {
            const año = fechaActual.getFullYear();
            const mes = fechaActual.getMonth();

            document.getElementById('month-year').textContent = `${meses[mes]} ${año}`;

            const primerDiaIndex = new Date(año, mes, 1).getDay();
            const ultimoDia = new Date(año, mes + 1, 0).getDate();

            const container = document.getElementById('days-container');
            container.innerHTML = '';

            for (let i = 0; i < primerDiaIndex; i++) {
                const divVacio = document.createElement('div');
                divVacio.classList.add('day', 'empty');
                container.appendChild(divVacio);
            }

            for (let dia = 1; dia <= ultimoDia; dia++) {
                const divDia = document.createElement('div');
                divDia.classList.add('day');
                divDia.textContent = dia;

                const mesFormateado = String(mes + 1).padStart(2, '0');
                const diaFormateado = String(dia).padStart(2, '0');
                const fechaString = `${año}-${mesFormateado}-${diaFormateado}`;

                const tieneEvento = recordatorios.some(r => r.fecha === fechaString);
                if (tieneEvento) {
                    divDia.classList.add('has-event');
                }

                divDia.onclick = () => {
                    document.querySelectorAll('.day').forEach(d => d.classList.remove('active'));
                    divDia.classList.add('active');
                    document.getElementById('reminder-date').value = fechaString;
                };

                container.appendChild(divDia);
            }
        }

        function cambiarMes(direccion) {
            fechaActual.setMonth(fechaActual.getMonth() + direccion);
            renderizarCalendario();
        }

        function renderizarAgenda() {
            const container = document.getElementById('agenda-container');
            container.innerHTML = '';

            recordatorios.sort((a, b) => new Date(a.fecha) - new Date(b.fecha));

            recordatorios.forEach((rec) => {
                const item = document.createElement('div');
                item.classList.add('agenda-item');

                item.innerHTML = `
                    <div class="agenda-item-info">
                        <span class="agenda-date">${rec.fecha}</span>
                        <span class="agenda-text">${rec.texto}</span>
                    </div>
                    <button class="btn-delete" onclick="eliminarRecordatorio(${rec.id})">×</button>
                `;
                container.appendChild(item);
            });
        }

        // Envía tanto la fecha como el texto del input a procesar por el backend
        async function agregarRecordatorio() {
            const texto = document.getElementById('reminder-text').value.trim();
            const fecha = document.getElementById('reminder-date').value;

            if (!texto || !fecha) {
                alert('Por favor, ingresa una descripción y selecciona una fecha.');
                return;
            }

            try {
                const respuesta = await fetch('calendario.php?accion=guardar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ fecha: fecha, texto: texto })
                });

                const resultado = await respuesta.json();
                if (resultado.status === "success") {
                    document.getElementById('reminder-text').value = '';
                    await cargarRecordatorios();
                } else {
                    alert("Error en el servidor: " + resultado.message);
                }
            } catch (error) {
                console.error("Error en la solicitud:", error);
            }
        }

        async function eliminarRecordatorio(id) {
            if (!confirm('¿Estás seguro de eliminar este recordatorio?')) return;

            try {
                const respuesta = await fetch('calendario.php?accion=eliminar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });

                const resultado = await respuesta.json();
                if (resultado.status === "success") {
                    await cargarRecordatorios();
                } else {
                    alert("Error al eliminar: " + resultado.message);
                }
            } catch (error) {
                console.error("Error al procesar la baja:", error);
            }
        }

        // Inicializa la carga al abrir la página
        cargarRecordatorios();
    </script>
</body>
</html>
