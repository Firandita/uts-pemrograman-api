<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Register</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
	<style>
		:root {
			--bg-1: #f8fafc;
			--bg-2: #dbeafe;
			--ink: #0f172a;
			--muted: #475569;
			--card: rgba(255, 255, 255, 0.9);
			--stroke: rgba(15, 23, 42, 0.12);
			--accent: #1d4ed8;
			--accent-2: #0ea5e9;
			--shadow: 0 22px 50px rgba(15, 23, 42, 0.14);
		}

		* { box-sizing: border-box; }

		body {
			margin: 0;
			min-height: 100vh;
			display: grid;
			place-items: center;
			padding: 20px 16px;
			color: var(--ink);
			font-family: 'Space Grotesk', sans-serif;
			background:
				radial-gradient(1200px 500px at 20% -20%, rgba(14, 165, 233, 0.35), transparent 65%),
				radial-gradient(900px 480px at 95% 0%, rgba(29, 78, 216, 0.28), transparent 55%),
				linear-gradient(180deg, var(--bg-1), var(--bg-2));
		}

		.auth-shell {
			width: min(520px, 100%);
			animation: fade-up .5s ease-out;
		}

		@keyframes fade-up {
			from { opacity: 0; transform: translateY(14px); }
			to { opacity: 1; transform: translateY(0); }
		}

		.auth-card {
			background: var(--card);
			border: 1px solid var(--stroke);
			border-radius: 20px;
			padding: 24px;
			box-shadow: var(--shadow);
			backdrop-filter: blur(5px);
		}

		.eyebrow {
			margin: 0;
			font-size: 12px;
			font-weight: 700;
			letter-spacing: 0.12em;
			text-transform: uppercase;
			color: #1e3a8a;
		}

		h1 {
			margin: 8px 0 2px;
			font-size: clamp(30px, 6vw, 38px);
			line-height: 1.1;
			letter-spacing: -0.03em;
		}

		.sub {
			margin: 0 0 18px;
			font-size: 14px;
			color: var(--muted);
		}

		.form-grid {
			display: grid;
			gap: 12px;
		}

		label {
			display: block;
			font-weight: 600;
			font-size: 14px;
			margin-bottom: 6px;
		}

		input {
			width: 100%;
			padding: 11px 12px;
			border: 1px solid var(--stroke);
			border-radius: 10px;
			font: inherit;
			color: var(--ink);
			background: #ffffff;
			outline: none;
		}

		input:focus {
			border-color: rgba(37, 99, 235, 0.7);
			box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18);
		}

		button {
			width: 100%;
			margin-top: 6px;
			padding: 11px 14px;
			border: 0;
			border-radius: 10px;
			font-family: inherit;
			font-weight: 700;
			color: #ffffff;
			background: linear-gradient(120deg, var(--accent), var(--accent-2));
			cursor: pointer;
		}

		button:hover { filter: brightness(1.04); }

		.msg {
			margin-top: 12px;
			border-radius: 10px;
			padding: 10px;
			font-size: 14px;
			display: none;
			white-space: pre-wrap;
		}

		.msg.error {
			display: block;
			background: #ffecec;
			border: 1px solid #f5a5a5;
		}

		.msg.success {
			display: block;
			background: #eaffea;
			border: 1px solid #9fe29f;
		}

		.muted {
			color: var(--muted);
			font-size: 14px;
			margin-top: 14px;
		}

		a {
			color: #1d4ed8;
			font-weight: 600;
			text-decoration: none;
		}

		a:hover { text-decoration: underline; }

		@media (max-width: 560px) {
			.auth-card {
				padding: 20px;
				border-radius: 16px;
			}
		}
	</style>
</head>
<body>
	<main class="auth-shell">
		<section class="auth-card">
			<p class="eyebrow">API Hub</p>
			<h1>Register</h1>
			<p class="sub">Buat akun baru untuk mendapatkan akses ke dashboard.</p>

			<form id="registerForm" class="form-grid" action="../backend/api/register.php" method="post">
				<div>
					<label for="username">Username</label>
					<input id="username" name="username" type="text" required autocomplete="username">
				</div>

				<div>
					<label for="email">Email</label>
					<input id="email" name="email" type="email" required autocomplete="email">
				</div>

				<div>
					<label for="password">Password</label>
					<input id="password" name="password" type="password" required autocomplete="new-password">
				</div>

				<button type="submit">Daftar</button>
			</form>

			<div id="message" class="msg"></div>

			<div class="muted">
				Sudah punya akun? <a href="login.php">Login</a>
			</div>
		</section>
	</main>

	<script>
		const form = document.getElementById('registerForm');
		const message = document.getElementById('message');

		function showMessage(type, text) {
			message.className = 'msg ' + type;
			message.textContent = text;
		}

		form.addEventListener('submit', async function (event) {
			event.preventDefault();
			message.className = 'msg';
			message.textContent = '';

			const formData = new FormData(form);

			try {
				const response = await fetch('../backend/api/register.php', {
					method: 'POST',
					body: formData,
				});

				const data = await response.json();
				if (response.ok && data.status === 'success') {
					showMessage('success', data.message || 'Registrasi berhasil. Silakan login.');
					form.reset();
				} else {
					showMessage('error', data.message || 'Registrasi gagal.');
				}
			} catch (error) {
				showMessage('error', 'Tidak bisa menghubungi API. Pastikan Apache dan MySQL di Laragon sedang aktif.');
			}
		});
	</script>
</body>
</html>
