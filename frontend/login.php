<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login</title>
	<style>
		body {
			font-family: Arial, sans-serif;
			max-width: 460px;
			margin: 40px auto;
			padding: 0 16px;
		}
		.card {
			border: 1px solid #dddddd;
			border-radius: 8px;
			padding: 16px;
		}
		label {
			display: block;
			margin-top: 12px;
			font-weight: 600;
		}
		input {
			width: 100%;
			box-sizing: border-box;
			padding: 10px;
			margin-top: 6px;
		}
		button {
			width: 100%;
			margin-top: 16px;
			padding: 10px;
			cursor: pointer;
		}
		.msg {
			margin-top: 12px;
			border-radius: 6px;
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
			color: #666666;
			font-size: 14px;
			margin-top: 12px;
		}
		.api-key {
			display: inline-block;
			margin-top: 6px;
			padding: 4px 8px;
			background: #f4f4f4;
			border-radius: 6px;
			font-family: Consolas, monospace;
		}
	</style>
</head>
<body>
	<h2>Login</h2>

	<div class="card">
		<form id="loginForm" action="../backend/api/login.php" method="post">
			<label for="username">Username</label>
			<input id="username" name="username" type="text" required autocomplete="username">

			<label for="password">Password</label>
			<input id="password" name="password" type="password" required autocomplete="current-password">

			<button type="submit">Masuk</button>
		</form>

		<div id="message" class="msg"></div>

		<div class="muted">
			Belum punya akun? <a href="register.php">Register</a>
		</div>
	</div>

	<script>
		const form = document.getElementById('loginForm');
		const message = document.getElementById('message');

		function showMessage(type, text) {
			message.className = 'msg ' + type;
			message.innerHTML = text;
		}

		form.addEventListener('submit', async function (event) {
			event.preventDefault();
			message.className = 'msg';
			message.textContent = '';

			const formData = new FormData(form);

			try {
				const response = await fetch('../backend/api/login.php', {
					method: 'POST',
					body: formData,
				});

				const data = await response.json();
				if (response.ok && data.status === 'success') {
					if (data.api_key) {
						localStorage.setItem('api_key', data.api_key);
					}

					const text = (data.message || 'Login berhasil!') +
						(data.api_key ? '<br>API KEY: <span class="api-key">' + data.api_key + '</span>' : '') +
						'<br>Mengarahkan ke dashboard...';

					showMessage('success', text);
					form.reset();

					setTimeout(function () {
						window.location.href = 'dashboard.php';
					}, 1100);
				} else {
					showMessage('error', data.message || 'Login gagal.');
				}
			} catch (error) {
				showMessage('error', 'Tidak bisa menghubungi API. Pastikan Apache dan MySQL di Laragon sedang aktif.');
			}
		});
	</script>
</body>
</html>
