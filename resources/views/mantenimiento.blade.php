<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#f2f0e8">
	<link rel="icon" type="image/png" href="{{ asset('img/Sanwicheria Gabriel icon.png') }}">
	<title>Mantenimiento | RestoGest</title>
	<style>
		:root {
			color-scheme: light;
			--paper: #f2f0e8;
			--ink: #202a25;
			--muted: #69736d;
			--orange: #e76543;
			--green: #328b78;
			--line: rgba(32, 42, 37, 0.15);
			font-family: "Trebuchet MS", "Segoe UI", sans-serif;
		}

		* { box-sizing: border-box; }

		body {
			min-width: 320px;
			min-height: 100vh;
			margin: 0;
			color: var(--ink);
			background-color: var(--paper);
			background-image: radial-gradient(rgba(32, 42, 37, 0.1) 0.7px, transparent 0.7px);
			background-size: 18px 18px;
		}

		.page {
			display: flex;
			min-height: 100vh;
			flex-direction: column;
			padding: 28px clamp(22px, 6vw, 88px) 22px;
		}

		.topbar, .footer {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 20px;
		}

		.brand { display: flex; align-items: center; gap: 11px; font-size: 16px; font-weight: 800; }

		.brand-mark {
			display: grid;
			width: 35px;
			aspect-ratio: 1;
			place-items: center;
			border-radius: 9px;
			color: white;
			background: var(--ink);
			font-size: 18px;
		}

		.live-state {
			display: flex;
			align-items: center;
			gap: 9px;
			color: var(--muted);
			font-size: 12px;
			font-weight: 700;
			letter-spacing: 0.08em;
			text-transform: uppercase;
		}

		.live-dot {
			width: 9px;
			aspect-ratio: 1;
			border-radius: 50%;
			background: var(--orange);
			box-shadow: 0 0 0 4px rgba(231, 101, 67, 0.16);
			animation: pulse 1.8s ease-in-out infinite;
		}

		main {
			display: grid;
			width: min(1120px, 100%);
			flex: 1;
			grid-template-columns: minmax(0, 1.05fr) minmax(270px, 0.95fr);
			align-items: center;
			gap: clamp(34px, 8vw, 100px);
			margin: 0 auto;
			padding: 52px 0;
		}

		.copy { animation: enter 650ms both; }

		.eyebrow {
			display: flex;
			align-items: center;
			gap: 11px;
			margin: 0 0 22px;
			color: var(--green);
			font-size: 12px;
			font-weight: 800;
			letter-spacing: 0.13em;
			text-transform: uppercase;
		}

		.eyebrow::before { width: 28px; height: 2px; content: ""; background: currentColor; }

		h1 {
			max-width: 620px;
			margin: 0;
			font-size: clamp(42px, 6vw, 76px);
			line-height: 0.99;
		}

		h1 span { display: block; color: var(--orange); }

		.description {
			max-width: 470px;
			margin: 25px 0 0;
			color: var(--muted);
			font-size: 17px;
			line-height: 1.7;
		}

		.timer {
			display: flex;
			align-items: center;
			gap: 12px;
			margin-top: 28px;
			color: var(--muted);
			font-size: 13px;
		}

		.timer time { color: var(--ink); font-variant-numeric: tabular-nums; font-weight: 800; }

		.actions {
			display: flex;
			flex-wrap: wrap;
			gap: 12px;
			margin-top: 27px;
		}

		.refresh {
			display: inline-flex;
			min-height: 46px;
			align-items: center;
			gap: 10px;
			margin-top: 0;
			padding: 0 18px;
			border: 0;
			border-radius: 5px;
			color: white;
			background: var(--ink);
			cursor: pointer;
			font: inherit;
			font-size: 14px;
			font-weight: 700;
			transition: background 180ms ease, transform 180ms ease;
		}

		.refresh:hover { transform: translateY(-2px); background: #35463e; }
		.refresh:focus-visible { outline: 3px solid var(--green); outline-offset: 3px; }
		.refresh:disabled { cursor: wait; }
		.refresh-symbol { display: inline-block; font-size: 19px; }
		.refresh.is-loading .refresh-symbol { animation: rotate 700ms linear infinite; }

		.visual {
			position: relative;
			display: grid;
			width: min(100%, 420px);
			aspect-ratio: 1;
			place-items: center;
			justify-self: center;
			animation: enter 750ms 100ms both;
		}

		.orbit, .orbit-inner { position: absolute; aspect-ratio: 1; border-radius: 50%; }
		.orbit { width: 88%; border: 1px solid var(--line); animation: rotate 24s linear infinite; }
		.orbit-inner { width: 67%; border: 1px dashed rgba(32, 42, 37, 0.25); animation: rotate 32s linear infinite reverse; }

		.orbit::before, .orbit::after {
			position: absolute;
			width: 11px;
			aspect-ratio: 1;
			border: 3px solid var(--paper);
			border-radius: 50%;
			content: "";
			background: var(--green);
			box-shadow: 0 0 0 1px var(--green);
		}

		.orbit::before { top: 15%; left: 9%; }
		.orbit::after { right: 1%; bottom: 29%; width: 8px; background: var(--orange); box-shadow: 0 0 0 1px var(--orange); }

		.gear {
			display: grid;
			width: 49%;
			aspect-ratio: 1;
			place-items: center;
			border: 13px dotted var(--orange);
			border-radius: 50%;
			color: var(--ink);
			background: rgba(231, 101, 67, 0.1);
			font-size: clamp(76px, 11vw, 124px);
			line-height: 1;
			animation: rotate-back 22s linear infinite;
		}

		.gear::after {
			position: absolute;
			width: 15%;
			aspect-ratio: 1;
			border: 1px solid rgba(32, 42, 37, 0.2);
			border-radius: 50%;
			content: "";
		}

		.tool {
			position: absolute;
			right: 8%;
			bottom: 17%;
			display: grid;
			width: 70px;
			aspect-ratio: 1;
			place-items: center;
			border: 1px solid var(--line);
			border-radius: 16px;
			color: white;
			background: var(--green);
			box-shadow: 8px 8px 0 rgba(50, 139, 120, 0.16);
			font-size: 32px;
			transform: rotate(9deg);
			animation: bob 3.5s ease-in-out infinite;
		}

		.footer { color: var(--muted); font-size: 12px; }
		.footer strong { color: var(--ink); }

		@keyframes rotate { to { transform: rotate(360deg); } }
		@keyframes rotate-back { to { transform: rotate(-360deg); } }
		@keyframes pulse { 50% { box-shadow: 0 0 0 8px rgba(231, 101, 67, 0.05); opacity: 0.65; } }
		@keyframes bob { 50% { translate: 0 -9px; } }
		@keyframes enter { from { opacity: 0; translate: 0 16px; } to { opacity: 1; translate: 0 0; } }

		@media (max-width: 720px) {
			.page { padding-top: 20px; }
			main { grid-template-columns: 1fr; gap: 8px; padding: 38px 0; }
			.visual { order: 0; width: min(66vw, 280px); }
			.copy { order: 1; }
			.description { font-size: 15px; }
			.footer { align-items: flex-start; flex-direction: column; gap: 7px; }
		}

		@media (prefers-reduced-motion: reduce) {
			*, *::before, *::after {
				animation-duration: 0.01ms !important;
				animation-iteration-count: 1 !important;
				scroll-behavior: auto !important;
			}
		}
	</style>
</head>
<body>
	<div class="page">
		<header class="topbar">
			<div class="brand"><span class="brand-mark" aria-hidden="true">R</span>RestoGest</div>
			<div class="live-state"><span class="live-dot" aria-hidden="true"></span>Mantenimiento en curso</div>
		</header>

		<main>
			<section class="copy" aria-labelledby="maintenance-title">
				<p class="eyebrow">Volvemos pronto</p>
				<h1 id="maintenance-title">Estamos mejorando <span>RestoGest.</span></h1>
				<p class="description">Estamos realizando tareas de mantenimiento para que todo funcione mejor. Gracias por tu paciencia; el servicio estará disponible nuevamente en breve.</p>
				<div class="timer">Tiempo transcurrido <time id="elapsed-time">00:00</time></div>
				<div class="actions">
					<button class="refresh" id="refresh-button" type="button">
						<span class="refresh-symbol" aria-hidden="true">↻</span>
						<span id="refresh-label">Actualizar pantalla</span>
					</button>
					<button class="refresh" id="back-button" data-home="{{ route('home') }}" type="button">
						<span class="refresh-symbol" aria-hidden="true">←</span>
						<span>Volver</span>
					</button>
				</div>
			</section>

			<div class="visual" role="img" aria-label="Engranajes animados durante el mantenimiento">
				<div class="orbit"></div>
				<div class="orbit-inner"></div>
				<div class="gear" aria-hidden="true">⚙</div>
				<div class="tool" aria-hidden="true">🔧</div>
			</div>
		</main>

		<footer class="footer">
			<span><strong>RestoGest</strong> · Sistema de gestión</span>
			<span id="live-message" aria-live="polite">Estamos trabajando para volver pronto</span>
		</footer>
	</div>

	<script>
		const startedAt = Date.now();
		const elapsedTime = document.getElementById('elapsed-time');
		const refreshButton = document.getElementById('refresh-button');
		const refreshLabel = document.getElementById('refresh-label');
		const backButton = document.getElementById('back-button');
		const liveMessage = document.getElementById('live-message');

		function updateTimer() {
			const secondsTotal = Math.floor((Date.now() - startedAt) / 1000);
			const minutes = String(Math.floor(secondsTotal / 60)).padStart(2, '0');
			const seconds = String(secondsTotal % 60).padStart(2, '0');
			elapsedTime.textContent = `${minutes}:${seconds}`;
		}

		window.setInterval(updateTimer, 1000);

		backButton.addEventListener('click', () => {
			window.location.href = backButton.dataset.home;
		});

		refreshButton.addEventListener('click', () => {
			refreshButton.classList.add('is-loading');
			refreshButton.disabled = true;
			refreshLabel.textContent = 'Comprobando...';
			liveMessage.textContent = 'Actualizando la pantalla';
			window.setTimeout(() => window.location.reload(), 700);
		});
	</script>
</body>
</html>
