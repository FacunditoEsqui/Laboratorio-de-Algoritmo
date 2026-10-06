<?php
// 1. CONTROL DE SESIÓN Y SEGURIDAD
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

include 'conexion.php';
$usuarioId = intval($_SESSION['id_usuario']);

// 2. PROCESAMIENTO DE PETICIONES ASÍNCRONAS (API INTERNA)
if (isset($_GET['accion'])) {
    header('Content-Type: application/json');

    // Acción: Obtener las alarmas del usuario conectado
    if ($_GET['accion'] === 'obtener') {
        $sql = "SELECT idAlarma, titulo, hora, minuto FROM alarma WHERE usuarioId = $usuarioId ORDER BY hora, minuto";
        $resultado = mysqli_query($conexion, $sql);

        $alarmas = [];
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $alarmas[] = [
                "id" => $fila['idAlarma'],
                "hora" => sprintf("%02d:%02d", $fila['hora'], $fila['minuto']), // HH:MM
                "etiqueta" => $fila['titulo'] ? $fila['titulo'] : "Alarma"
            ];
        }
        echo json_encode($alarmas);
        exit();
    }

    // Acción: Guardar una alarma nueva
    if ($_GET['accion'] === 'guardar') {
        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['hora']) && preg_match('/^\d{2}:\d{2}$/', $data['hora'])) {
            $partes = explode(':', $data['hora']);
            $hora = intval($partes[0]);
            $minuto = intval($partes[1]);
            $titulo = isset($data['etiqueta']) ? mb_substr(trim($data['etiqueta']), 0, 150) : "";
            $diaDeLaSemana = "todos"; // por ahora suena todos los días
            $sonido = "beep";

            $stmt = mysqli_prepare($conexion, "INSERT INTO alarma (titulo, hora, minuto, diaDeLaSemana, sonido, usuarioId) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "siissi", $titulo, $hora, $minuto, $diaDeLaSemana, $sonido, $usuarioId);

            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(["status" => "success"]);
            } else {
                echo json_encode(["status" => "error", "message" => mysqli_error($conexion)]);
            }
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(["status" => "error", "message" => "Hora inválida"]);
        }
        exit();
    }

    // Acción: Eliminar una alarma por ID
    if ($_GET['accion'] === 'eliminar') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['id'])) {
            $idAlarma = intval($data['id']);

            $stmt = mysqli_prepare($conexion, "DELETE FROM alarma WHERE idAlarma = ? AND usuarioId = ?");
            mysqli_stmt_bind_param($stmt, "ii", $idAlarma, $usuarioId);

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
    <title>Alarma - Tuortox</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        /* Solo para esta página: distribución en 3 columnas */
        .alarma-layout {
            display: grid;
            grid-template-columns: 1fr 1.6fr 1fr;
            grid-template-areas:
                "izq nueva ."
                "izq lista reloj";
            column-gap: 30px;
            row-gap: 24px;
            align-items: start;
            width: 100%;
            padding-top: 60px; /* sube/baja todo el bloque cambiando este valor */
        }
        .alarma-izquierda {
            grid-area: izq;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 20px;
        }
        .alarma-centro { display: contents; }
        .area-nueva { grid-area: nueva; }
        .area-lista { grid-area: lista; }
        .alarma-derecha { grid-area: reloj; }
        @media (max-width: 900px) {
            .alarma-layout {
                grid-template-columns: 1fr;
                grid-template-areas: "izq" "nueva" "lista" "reloj";
                padding-top: 0;
            }
        }
    </style>
</head>
<body>

    <div class="dashboard-wrapper">
        <div class="alarma-layout">

            <!-- Columna izquierda: título y volver -->
            <div class="alarma-izquierda">
                <h1 class="dashboard-title">Alarma</h1>
                <a href="panel.php" style="color: #fff; text-decoration: none; background: #333; padding: 8px 15px; border-radius: 5px; display: inline-block;">← Volver al Panel</a>
            </div>

            <!-- Columna central: nueva alarma y lista -->
            <div class="alarma-centro">
                <div class="form-card area-nueva">
                    <h3>Nueva Alarma</h3>
                    <div class="input-group">
                        <input type="time" id="alarma-hora">
                    </div>
                    <div class="input-group">
                        <input type="text" id="alarma-etiqueta" placeholder="Etiqueta (opcional)" maxlength="40">
                    </div>
                    <button class="btn-submit" onclick="agregarAlarma()">Guardar Alarma</button>
                </div>

                <div class="form-card agenda-section area-lista">
                    <h3>Mis Alarmas</h3>
                    <div class="agenda-list" id="agenda-container"></div>
                </div>
            </div>

            <!-- Columna derecha: hora actual y aviso -->
            <div class="alarma-derecha">
                <div class="calendar-card">
                    <div class="calendar-header">
                        <h2 id="hora-actual">--:--:--</h2>
                    </div>

                    <div id="aviso" hidden>
                        <h3 class="dashboard-title" id="aviso-texto">¡Alarma!</h3>
                        <br>
                        <button class="btn-submit" onclick="detenerAlarma()">Detener</button>
                    </div>

                    <p id="aviso-vacio" class="agenda-text">
                        Mantené esta pestaña abierta para que la alarma suene.
                    </p>
                </div>
            </div>

        </div>
    </div>

    <script>
        let alarmas = [];
        let disparadas = {};   // evita que suene más de una vez en el mismo minuto
        let audioCtx = null;
        let beepTimer = null;

        // Trae las alarmas desde la BD
        async function cargarAlarmas() {
            try {
                const respuesta = await fetch('alarma.php?accion=obtener');
                alarmas = await respuesta.json();
                renderizarAlarmas();
            } catch (error) {
                console.error("Error al cargar las alarmas:", error);
            }
        }

        function renderizarAlarmas() {
            const container = document.getElementById('agenda-container');
            container.innerHTML = '';

            if (alarmas.length === 0) {
                container.innerHTML = '<p class="agenda-text">No hay alarmas todavía.</p>';
                return;
            }

            alarmas.forEach((al) => {
                const item = document.createElement('div');
                item.classList.add('agenda-item');

                const info = document.createElement('div');
                info.classList.add('agenda-item-info');

                const hora = document.createElement('span');
                hora.classList.add('agenda-date');
                hora.textContent = al.hora;

                const texto = document.createElement('span');
                texto.classList.add('agenda-text');
                texto.textContent = al.etiqueta;

                info.appendChild(hora);
                info.appendChild(texto);

                const btn = document.createElement('button');
                btn.classList.add('btn-delete');
                btn.textContent = '×';
                btn.onclick = () => eliminarAlarma(al.id);

                item.appendChild(info);
                item.appendChild(btn);
                container.appendChild(item);
            });
        }

        async function agregarAlarma() {
            const hora = document.getElementById('alarma-hora').value;
            const etiqueta = document.getElementById('alarma-etiqueta').value.trim();

            if (!hora) {
                alert('Por favor, selecciona una hora.');
                return;
            }

            habilitarAudio(); // el navegador exige un clic del usuario antes de reproducir sonido

            try {
                const respuesta = await fetch('alarma.php?accion=guardar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ hora: hora, etiqueta: etiqueta })
                });

                const resultado = await respuesta.json();
                if (resultado.status === "success") {
                    document.getElementById('alarma-hora').value = '';
                    document.getElementById('alarma-etiqueta').value = '';
                    await cargarAlarmas();
                } else {
                    alert("Error en el servidor: " + resultado.message);
                }
            } catch (error) {
                console.error("Error en la solicitud:", error);
            }
        }

        async function eliminarAlarma(id) {

            try {
                const respuesta = await fetch('alarma.php?accion=eliminar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });

                const resultado = await respuesta.json();
                if (resultado.status === "success") {
                    await cargarAlarmas();
                } else {
                    alert("Error al eliminar: " + resultado.message);
                }
            } catch (error) {
                console.error("Error al eliminar:", error);
            }
        }

        // ---------- Sonido ----------
        function habilitarAudio() {
            if (!audioCtx) {
                const AC = window.AudioContext || window.webkitAudioContext;
                if (AC) audioCtx = new AC();
            }
            if (audioCtx && audioCtx.state === 'suspended') audioCtx.resume();
        }

        function beep() {
            if (!audioCtx) return;
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'square';
            osc.frequency.value = 880;
            gain.gain.value = 0.15;
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.3);
        }

        function sonarAlarma(al) {
            if (beepTimer) return; // ya está sonando
            document.getElementById('aviso-texto').textContent = '⏰ ' + al.hora + ' — ' + al.etiqueta;
            document.getElementById('aviso').hidden = false;
            document.getElementById('aviso-vacio').hidden = true;
            document.title = '⏰ ¡Alarma!';
            beep();
            beepTimer = setInterval(beep, 700);
        }

        function detenerAlarma() {
            clearInterval(beepTimer);
            beepTimer = null;
            document.getElementById('aviso').hidden = true;
            document.getElementById('aviso-vacio').hidden = false;
            document.title = 'Alarma - Tuortox';
        }

        // ---------- Reloj + chequeo cada segundo ----------
        function tick() {
            const ahora = new Date();
            const hh = String(ahora.getHours()).padStart(2, '0');
            const mm = String(ahora.getMinutes()).padStart(2, '0');
            const ss = String(ahora.getSeconds()).padStart(2, '0');

            document.getElementById('hora-actual').textContent = `${hh}:${mm}:${ss}`;

            const dia = ahora.toDateString();
            alarmas.forEach((al) => {
                const clave = `${al.id}|${dia}|${hh}:${mm}`;
                if (al.hora === `${hh}:${mm}` && !disparadas[clave]) {
                    disparadas[clave] = true;
                    sonarAlarma(al);
                }
            });
        }

        // Cualquier clic en la página habilita el audio (por si ya había alarmas guardadas)
        document.addEventListener('click', habilitarAudio);

        cargarAlarmas();
        tick();
        setInterval(tick, 1000);
    </script>
</body>
</html>