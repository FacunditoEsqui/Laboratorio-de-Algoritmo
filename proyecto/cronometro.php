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
    <title>Cronómetro - Tuortox</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        /* Solo para esta página */
        .crono-layout {
            display: grid;
            grid-template-columns: 1fr 1.6fr 1fr;
            grid-template-areas:
                "izq centro ."
                "vueltas vueltas vueltas";
            column-gap: 30px;
            row-gap: 24px;
            align-items: start;
            width: 100%;
            padding-top: 60px;
        }
        .crono-izquierda {
            grid-area: izq;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 20px;
        }
        .crono-centro { grid-area: centro; }
        .crono-vueltas { grid-area: vueltas; }
        /* Vueltas en horizontal, bajan de fila solo cuando se llena */
        #vueltas-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 12px;
        }
        #vueltas-container > p { grid-column: 1 / -1; }
        .crono-display {
            font-size: clamp(40px, 8vw, 64px);
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            text-align: center;
            color: #ffffff;
            margin-bottom: 28px;
        }
        .crono-botones {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }
        .btn-submit:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            transform: none;
        }
        @media (max-width: 900px) {
            .crono-layout {
                grid-template-columns: 1fr;
                grid-template-areas: "izq" "centro" "vueltas";
                padding-top: 0;
            }
        }
    </style>
</head>
<body>

    <div class="dashboard-wrapper">
        <div class="crono-layout">

            <!-- Columna izquierda: título y volver -->
            <div class="crono-izquierda">
                <h1 class="dashboard-title">Cronómetro</h1>
                <a href="panel.php" style="color: #fff; text-decoration: none; background: #333; padding: 8px 15px; border-radius: 5px; display: inline-block;">← Volver al Panel</a>
            </div>

            <!-- Columna central: cronómetro y vueltas -->
            <div class="crono-centro">
                <div class="form-card">
                    <div class="crono-display" id="crono-display">00:00:00.00</div>

                    <div class="crono-botones">
                        <button class="btn-submit" id="btn-iniciar" onclick="alternar()">Iniciar</button>
                        <button class="btn-submit" id="btn-vuelta" onclick="registrarVuelta()" disabled>Vuelta</button>
                        <button class="btn-submit" onclick="reiniciar()">Reiniciar</button>
                    </div>
                </div>
            </div>

            <!-- Vueltas: ancho completo, en horizontal -->
            <div class="form-card agenda-section crono-vueltas">
                <h3>Vueltas</h3>
                <div class="agenda-list" id="vueltas-container">
                    <p class="agenda-text">Todavía no hay vueltas.</p>
                </div>
            </div>

        </div>
    </div>

    <script>
        let acumulado = 0;      // ms acumulados antes de la última pausa
        let inicio = 0;         // timestamp del último "Iniciar"
        let intervalo = null;
        let corriendo = false;
        let vueltas = [];       // tiempos totales de cada vuelta (ms)

        const $display = document.getElementById('crono-display');
        const $btnIniciar = document.getElementById('btn-iniciar');
        const $btnVuelta = document.getElementById('btn-vuelta');
        const $vueltas = document.getElementById('vueltas-container');

        function dos(n) { return String(n).padStart(2, '0'); }

        function formatear(ms) {
            const centesimas = Math.floor((ms % 1000) / 10);
            const totalSeg = Math.floor(ms / 1000);
            const h = Math.floor(totalSeg / 3600);
            const m = Math.floor((totalSeg % 3600) / 60);
            const s = totalSeg % 60;
            return `${dos(h)}:${dos(m)}:${dos(s)}.${dos(centesimas)}`;
        }

        function tiempoActual() {
            return corriendo ? acumulado + (Date.now() - inicio) : acumulado;
        }

        function actualizar() {
            $display.textContent = formatear(tiempoActual());
        }

        function alternar() {
            if (corriendo) {
                // Pausar
                acumulado += Date.now() - inicio;
                clearInterval(intervalo);
                corriendo = false;
                $btnIniciar.textContent = 'Continuar';
                $btnVuelta.disabled = true;
                actualizar();
            } else {
                // Iniciar / continuar
                inicio = Date.now();
                corriendo = true;
                intervalo = setInterval(actualizar, 30);
                $btnIniciar.textContent = 'Pausar';
                $btnVuelta.disabled = false;
            }
        }

        function registrarVuelta() {
            if (!corriendo) return;
            vueltas.push(tiempoActual());
            renderizarVueltas();
        }

        function renderizarVueltas() {
            $vueltas.innerHTML = '';

            // La más reciente arriba
            for (let i = vueltas.length - 1; i >= 0; i--) {
                const total = vueltas[i];
                const parcial = total - (i > 0 ? vueltas[i - 1] : 0);

                const item = document.createElement('div');
                item.classList.add('agenda-item');

                const info = document.createElement('div');
                info.classList.add('agenda-item-info');

                const titulo = document.createElement('span');
                titulo.classList.add('agenda-date');
                titulo.textContent = 'Vuelta ' + (i + 1);

                const texto = document.createElement('span');
                texto.classList.add('agenda-text');
                texto.textContent = '+' + formatear(parcial);

                info.appendChild(titulo);
                info.appendChild(texto);

                const totalSpan = document.createElement('span');
                totalSpan.classList.add('agenda-text');
                totalSpan.textContent = formatear(total);

                item.appendChild(info);
                item.appendChild(totalSpan);
                $vueltas.appendChild(item);
            }
        }

        function reiniciar() {
            clearInterval(intervalo);
            corriendo = false;
            acumulado = 0;
            vueltas = [];

            $btnIniciar.textContent = 'Iniciar';
            $btnVuelta.disabled = true;
            $display.textContent = '00:00:00.00';
            $vueltas.innerHTML = '<p class="agenda-text">Todavía no hay vueltas.</p>';
        }
    </script>
</body>
</html>