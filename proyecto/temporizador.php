<?php
// CONTROL DE SESIÓN Y SEGURIDAD
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temporizador - Tuortox</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        /* Solo para esta página */
        .tempo-layout {
            display: grid;
            grid-template-columns: 1fr 1.6fr 1fr;
            grid-template-areas: "izq centro .";
            column-gap: 30px;
            align-items: start;
            width: 100%;
            padding-top: 60px;
        }
        .tempo-izquierda {
            grid-area: izq;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 20px;
        }
        .tempo-centro { grid-area: centro; }
        .tempo-display {
            font-size: clamp(48px, 9vw, 72px);
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            text-align: center;
            color: #ffffff;
            margin-bottom: 28px;
        }
        .tempo-fila {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        .tempo-botones {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        @media (max-width: 900px) {
            .tempo-layout {
                grid-template-columns: 1fr;
                grid-template-areas: "izq" "centro";
                padding-top: 0;
            }
        }
    </style>
</head>
<body>

    <div class="dashboard-wrapper">
        <div class="tempo-layout">

            <!-- Columna izquierda: título y volver -->
            <div class="tempo-izquierda">
                <h1 class="dashboard-title">Temporizador</h1>
                <a href="panel.php" style="color: #fff; text-decoration: none; background: #333; padding: 8px 15px; border-radius: 5px; display: inline-block;">← Volver al Panel</a>
            </div>

            <!-- Columna central: temporizador -->
            <div class="tempo-centro">
                <div class="form-card">
                    <div class="tempo-display" id="tempo-display">00:00:00</div>

                    <div id="aviso" hidden>
                        <h3 class="dashboard-title" id="aviso-texto">⏰ ¡Se acabó el tiempo!</h3>
                        <br>
                        <button class="btn-submit" onclick="reiniciar()">Detener</button>
                    </div>

                    <div id="controles">
                        <h3>Configurar tiempo</h3>
                        <div class="tempo-fila">
                            <div class="input-group">
                                <input type="number" id="tempo-horas" min="0" max="99" placeholder="Horas">
                            </div>
                            <div class="input-group">
                                <input type="number" id="tempo-minutos" min="0" max="59" placeholder="Minutos">
                            </div>
                            <div class="input-group">
                                <input type="number" id="tempo-segundos" min="0" max="59" placeholder="Segundos">
                            </div>
                        </div>

                        <div class="tempo-botones">
                            <button class="btn-submit" id="btn-iniciar" onclick="alternar()">Iniciar</button>
                            <button class="btn-submit" onclick="reiniciar()">Reiniciar</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        let restante = 0;        // milisegundos que faltan
        let fin = 0;             // momento exacto en que termina (timestamp)
        let intervalo = null;
        let corriendo = false;
        let audioCtx = null;
        let beepTimer = null;

        const $display = document.getElementById('tempo-display');
        const $btnIniciar = document.getElementById('btn-iniciar');
        const $inputs = ['tempo-horas', 'tempo-minutos', 'tempo-segundos']
            .map(id => document.getElementById(id));

        function dos(n) { return String(n).padStart(2, '0'); }

        function formatear(ms) {
            const total = Math.ceil(ms / 1000);
            const h = Math.floor(total / 3600);
            const m = Math.floor((total % 3600) / 60);
            const s = total % 60;
            return `${dos(h)}:${dos(m)}:${dos(s)}`;
        }

        function leerInputs() {
            const h = Math.max(0, parseInt($inputs[0].value) || 0);
            const m = Math.max(0, parseInt($inputs[1].value) || 0);
            const s = Math.max(0, parseInt($inputs[2].value) || 0);
            return ((h * 3600) + (m * 60) + s) * 1000;
        }

        function bloquearInputs(bloquear) {
            $inputs.forEach(i => i.disabled = bloquear);
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

        function detenerSonido() {
            clearInterval(beepTimer);
            beepTimer = null;
        }

        // ---------- Lógica del temporizador ----------
        function alternar() {
            if (corriendo) {
                pausar();
            } else {
                iniciar();
            }
        }

        function iniciar() {
            // Si no hay tiempo pendiente, toma lo que se escribió en los campos
            if (restante <= 0) {
                restante = leerInputs();
                if (restante <= 0) {
                    alert('Por favor, ingresa un tiempo mayor a cero.');
                    return;
                }
            }

            habilitarAudio(); // el navegador exige un clic antes de reproducir sonido

            fin = Date.now() + restante;
            corriendo = true;
            bloquearInputs(true);
            $btnIniciar.textContent = 'Pausar';
            $display.textContent = formatear(restante);

            intervalo = setInterval(actualizar, 200);
        }

        function pausar() {
            restante = fin - Date.now();
            clearInterval(intervalo);
            corriendo = false;
            $btnIniciar.textContent = 'Continuar';
        }

        function actualizar() {
            restante = fin - Date.now();

            if (restante <= 0) {
                terminar();
                return;
            }

            const texto = formatear(restante);
            $display.textContent = texto;
            document.title = texto + ' - Temporizador';
        }

        function terminar() {
            clearInterval(intervalo);
            corriendo = false;
            restante = 0;
            $display.textContent = '00:00:00';
            document.title = '⏰ ¡Tiempo! - Temporizador';

            document.getElementById('controles').hidden = true;
            document.getElementById('aviso').hidden = false;

            beep();
            beepTimer = setInterval(beep, 700);
        }

        function reiniciar() {
            clearInterval(intervalo);
            detenerSonido();
            corriendo = false;
            restante = 0;

            bloquearInputs(false);
            $btnIniciar.textContent = 'Iniciar';
            $display.textContent = '00:00:00';
            document.title = 'Temporizador - Tuortox';

            document.getElementById('aviso').hidden = true;
            document.getElementById('controles').hidden = false;
        }

        // Cualquier clic en la página habilita el audio
        document.addEventListener('click', habilitarAudio);
    </script>
</body>
</html>