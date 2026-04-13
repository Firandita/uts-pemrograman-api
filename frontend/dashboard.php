<?php
declare(strict_types=1);

session_start();

if (empty($_SESSION['auth_logged_in'])) {
	header('Location: login.php');
	exit;
}

$sessionApiKey = isset($_SESSION['api_key']) ? (string)$_SESSION['api_key'] : '';
$sessionUsername = isset($_SESSION['username']) ? (string)$_SESSION['username'] : 'User';

function base_url(): string
{
	$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
	$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
	$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/frontend/dashboard.php'));
	$root = preg_replace('#/frontend$#', '', rtrim($scriptDir, '/'));
	return $scheme . '://' . $host . $root;
}

function method_color(string $method): string
{
	$map = [
		'GET' => '#16a34a',
		'POST' => '#2563eb',
		'PUT' => '#ca8a04',
		'PATCH' => '#7c3aed',
		'DELETE' => '#dc2626',
	];
	return $map[$method] ?? '#475569';
}

function infer_methods(string $content): array
{
	$methods = [];

	if (preg_match("/REQUEST_METHOD'\\]\\s*!==\\s*'([A-Z]+)'/", $content, $m)) {
		$methods[] = $m[1];
	}

	if (preg_match_all("/case\\s+'([A-Z]+)'\\s*:/", $content, $m2)) {
		foreach ($m2[1] as $found) {
			$methods[] = strtoupper($found);
		}
	}

	$methods = array_values(array_unique(array_filter($methods)));
	if (count($methods) === 0) {
		$methods[] = 'GET';
	}

	sort($methods);
	return $methods;
}

function endpoint_description(string $fileName, string $method): string
{
	$name = strtolower($fileName);
	if ($name === 'login.php') {
		return 'Autentikasi pengguna dan generate API KEY baru saat login berhasil.';
	}
	if ($name === 'register.php') {
		return 'Mendaftarkan akun baru dengan username, email, dan password.';
	}
	if ($name === 'api.php' && $method === 'GET') {
		return 'Mengambil daftar menu_items (butuh header X-API-KEY yang valid).';
	}
	if ($name === 'api.php' && $method === 'POST') {
		return 'Menambahkan menu_items baru via body JSON (butuh header X-API-KEY).';
	}
	return 'Endpoint dinamis terdeteksi dari file backend API.';
}

$apiDir = realpath(__DIR__ . '/../backend/api');
$baseUrl = base_url();
$serverEndpoints = [];

if ($apiDir !== false) {
	$files = glob($apiDir . '/*.php') ?: [];
	sort($files);

	foreach ($files as $filePath) {
		$fileName = basename($filePath);
		$content = (string)file_get_contents($filePath);
		$methods = infer_methods($content);
		$needsApiKey = stripos($content, 'X-API-KEY') !== false || stripos($content, 'api_key_input') !== false;
		$url = $baseUrl . '/backend/api/' . $fileName;

		foreach ($methods as $method) {
			$serverEndpoints[] = [
				'id' => strtolower(pathinfo($fileName, PATHINFO_FILENAME) . '-' . $method),
				'source' => 'server',
				'name' => strtoupper(pathinfo($fileName, PATHINFO_FILENAME)) . ' ' . $method,
				'method' => $method,
				'url' => $url,
				'description' => endpoint_description($fileName, $method),
				'requiresApiKey' => $needsApiKey,
				'color' => method_color($method),
			];
		}
	}
}
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>API Hub Dashboard</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
	<style>
		:root {
			--bg-1: #f8fafc;
			--bg-2: #dbeafe;
			--ink: #0f172a;
			--muted: #475569;
			--card: rgba(255, 255, 255, 0.86);
			--stroke: rgba(15, 23, 42, 0.1);
			--accent: #1d4ed8;
			--accent-2: #0ea5e9;
			--radius: 18px;
			--shadow: 0 22px 50px rgba(15, 23, 42, 0.12);
		}

		* { box-sizing: border-box; }

		body {
			margin: 0;
			color: var(--ink);
			font-family: 'Space Grotesk', sans-serif;
			background:
				radial-gradient(1200px 500px at 20% -20%, rgba(14, 165, 233, 0.35), transparent 65%),
				radial-gradient(900px 480px at 95% 0%, rgba(29, 78, 216, 0.28), transparent 55%),
				linear-gradient(180deg, var(--bg-1), var(--bg-2));
			min-height: 100vh;
		}

		.wrap {
			width: min(1180px, calc(100% - 36px));
			margin: 26px auto 34px;
			animation: fade-up .55s ease-out;
		}

		@keyframes fade-up {
			from { opacity: 0; transform: translateY(14px); }
			to { opacity: 1; transform: translateY(0); }
		}

		.top {
			text-align: center;
			margin-bottom: 22px;
		}

		.top-row {
			display: flex;
			align-items: center;
			justify-content: flex-end;
			margin-bottom: 10px;
		}

		.user-chip {
			display: inline-flex;
			align-items: center;
			gap: 10px;
			padding: 8px 12px;
			border-radius: 999px;
			border: 1px solid var(--stroke);
			background: rgba(255, 255, 255, 0.9);
			box-shadow: 0 10px 26px rgba(15, 23, 42, 0.1);
			font-size: 13px;
		}

		.logout-link {
			text-decoration: none;
			font-weight: 700;
			color: #b91c1c;
			padding: 5px 9px;
			border-radius: 8px;
			background: #fee2e2;
		}

		.logout-link:hover {
			background: #fecaca;
		}

		.title {
			margin: 0;
			font-size: clamp(30px, 5vw, 44px);
			letter-spacing: -0.03em;
			font-weight: 700;
		}

		.sub {
			margin: 8px 0 0;
			color: var(--muted);
			font-size: 15px;
		}

		.stats {
			display: grid;
			grid-template-columns: repeat(4, minmax(0, 1fr));
			gap: 12px;
			margin-bottom: 16px;
		}

		.stat {
			background: var(--card);
			border: 1px solid var(--stroke);
			border-radius: 16px;
			padding: 14px;
			box-shadow: var(--shadow);
			backdrop-filter: blur(5px);
			text-align: center;
		}

		.stat-num {
			display: block;
			font-size: 28px;
			font-weight: 700;
			color: var(--accent);
			line-height: 1;
		}

		.stat-label {
			display: block;
			margin-top: 6px;
			color: var(--muted);
			font-size: 13px;
		}

		.panel {
			background: var(--card);
			border: 1px solid var(--stroke);
			border-radius: var(--radius);
			padding: 18px;
			box-shadow: var(--shadow);
			backdrop-filter: blur(5px);
			margin-top: 12px;
		}

		.panel-head {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 12px;
			margin-bottom: 14px;
		}

		.panel-title {
			margin: 0;
			font-size: 30px;
			letter-spacing: -0.02em;
		}

		.btn {
			border: 0;
			border-radius: 10px;
			padding: 10px 14px;
			font-family: inherit;
			font-weight: 600;
			color: #ffffff;
			background: linear-gradient(120deg, var(--accent), var(--accent-2));
			cursor: pointer;
			transition: transform .18s ease;
		}

		.btn:hover { transform: translateY(-1px); }

		.grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 12px;
		}

		.endpoint {
			border: 1px solid var(--stroke);
			border-radius: 14px;
			padding: 14px;
			background: rgba(255, 255, 255, 0.88);
			display: grid;
			gap: 10px;
			animation: fade-up .4s ease;
		}

		.method {
			justify-self: start;
			color: #ffffff;
			padding: 3px 10px;
			border-radius: 999px;
			font-size: 12px;
			font-weight: 700;
			letter-spacing: 0.04em;
		}

		.url-row {
			display: grid;
			grid-template-columns: 1fr auto;
			gap: 8px;
			align-items: center;
		}

		.url-input, .field, .json {
			width: 100%;
			border: 1px solid var(--stroke);
			border-radius: 10px;
			padding: 10px 11px;
			font: inherit;
			color: var(--ink);
			background: #ffffff;
		}

		.url-input {
			font-size: 13px;
			color: #334155;
		}

		.copy {
			border: 0;
			border-radius: 10px;
			padding: 9px 12px;
			color: #ffffff;
			font-family: inherit;
			font-weight: 700;
			background: #3b82f6;
			cursor: pointer;
		}

		.desc {
			margin: 0;
			color: var(--muted);
			font-size: 13px;
			line-height: 1.5;
		}

		.badge {
			display: inline-flex;
			align-items: center;
			width: fit-content;
			border-radius: 999px;
			padding: 3px 9px;
			font-size: 11px;
			font-weight: 600;
			color: #0f172a;
			background: #e2e8f0;
		}

		.tester {
			display: grid;
			gap: 10px;
			grid-template-columns: 130px 1fr 180px;
			margin-bottom: 10px;
		}

		.json {
			min-height: 128px;
			resize: vertical;
			font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
			font-size: 13px;
		}

		.response {
			margin-top: 8px;
			background: #0f172a;
			color: #e2e8f0;
			border-radius: 12px;
			padding: 12px;
			min-height: 120px;
			overflow: auto;
			font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
			font-size: 12px;
			line-height: 1.5;
			white-space: pre-wrap;
		}

		.foot {
			margin-top: 18px;
			text-align: center;
			color: #334155;
			font-size: 13px;
		}

		dialog {
			border: 1px solid var(--stroke);
			border-radius: 14px;
			width: min(560px, calc(100% - 28px));
			padding: 0;
			overflow: hidden;
		}

		dialog::backdrop {
			background: rgba(15, 23, 42, 0.45);
			backdrop-filter: blur(2px);
		}

		.modal {
			padding: 16px;
			display: grid;
			gap: 10px;
		}

		.modal h3 {
			margin: 0;
			font-size: 21px;
		}

		.modal-actions {
			display: flex;
			justify-content: flex-end;
			gap: 8px;
			margin-top: 4px;
		}

		.btn-ghost {
			border: 1px solid var(--stroke);
			background: #ffffff;
			color: #0f172a;
		}

		@media (max-width: 980px) {
			.stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
			.grid { grid-template-columns: 1fr; }
			.tester { grid-template-columns: 1fr; }
			.top-row { justify-content: center; }
		}
	</style>
</head>
<body>
	<div class="wrap">
		<div class="top-row">
			<div class="user-chip">
				<span>Login: <?php echo htmlspecialchars($sessionUsername, ENT_QUOTES, 'UTF-8'); ?></span>
				<a id="logoutLink" class="logout-link" href="logout.php">Logout</a>
			</div>
		</div>
		<header class="top">
			<h1 class="title">API Hub</h1>
			<p class="sub">Dashboard dokumentasi endpoint dinamis langsung dari project kamu.</p>
		</header>

		<section class="stats">
			<article class="stat">
				<span id="totalEndpoints" class="stat-num">0</span>
				<span class="stat-label">Total Endpoints</span>
			</article>
			<article class="stat">
				<span id="totalMethods" class="stat-num">0</span>
				<span class="stat-label">HTTP Methods</span>
			</article>
			<article class="stat">
				<span id="totalCopies" class="stat-num">0</span>
				<span class="stat-label">Total Copies</span>
			</article>
			<article class="stat">
				<span id="totalCustom" class="stat-num">0</span>
				<span class="stat-label">Custom Endpoints</span>
			</article>
		</section>

		<section class="panel">
			<div class="panel-head">
				<h2 class="panel-title">API Endpoints</h2>
				<button id="addEndpointBtn" class="btn" type="button">+ Add Endpoint</button>
			</div>
			<div id="endpointGrid" class="grid"></div>
		</section>

		<section class="panel">
			<div class="panel-head">
				<h2 class="panel-title">Try It Out</h2>
			</div>
			<div class="tester">
				<select id="tryMethod" class="field"></select>
				<input id="tryUrl" class="field" list="urlList" placeholder="Pilih atau ketik endpoint...">
				<button id="sendBtn" type="button" class="btn">Send Request</button>
			</div>
			<datalist id="urlList"></datalist>
			<div class="tester" style="grid-template-columns: 1fr 1fr; margin-top: 6px;">
				<input id="tryApiKey" class="field" placeholder="X-API-KEY (opsional)">
				<input id="tryContentType" class="field" value="application/json" placeholder="Content-Type">
			</div>
			<textarea id="tryBody" class="json" placeholder='Body JSON (opsional), contoh: {"nama_item":"Espresso","size":"Regular","category":"Hot","harga":15000,"notes":"Strong"}'></textarea>
			<div id="responseBox" class="response">Klik Send Request untuk mencoba API</div>
		</section>

		<div class="foot">API Hub dinamis - endpoint server + custom endpoint tersimpan lokal browser.</div>
	</div>

	<dialog id="endpointModal">
		<form id="customForm" class="modal" method="dialog">
			<h3>Tambah Custom Endpoint</h3>
			<select id="customMethod" class="field" required>
				<option>GET</option>
				<option>POST</option>
				<option>PUT</option>
				<option>PATCH</option>
				<option>DELETE</option>
			</select>
			<input id="customUrl" class="field" placeholder="https://... atau /backend/api/..." required>
			<input id="customDesc" class="field" placeholder="Deskripsi endpoint" required>
			<label style="display:flex; gap:8px; align-items:center; font-size:14px; color:#334155;">
				<input id="customNeedKey" type="checkbox"> Butuh X-API-KEY
			</label>
			<div class="modal-actions">
				<button type="button" id="cancelModal" class="btn btn-ghost">Batal</button>
				<button type="submit" class="btn">Simpan</button>
			</div>
		</form>
	</dialog>

	<script>
		const serverEndpoints = <?php echo json_encode($serverEndpoints, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
		const sessionApiKey = <?php echo json_encode($sessionApiKey, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
		const localKeyEndpoints = 'custom_endpoints_v1';
		const localKeyCopies = 'total_copy_count_v1';
		const localKeyApiKey = 'api_key';

		const methodColorMap = {
			GET: '#16a34a',
			POST: '#2563eb',
			PUT: '#ca8a04',
			PATCH: '#7c3aed',
			DELETE: '#dc2626'
		};

		const endpointGrid = document.getElementById('endpointGrid');
		const totalEndpointsEl = document.getElementById('totalEndpoints');
		const totalMethodsEl = document.getElementById('totalMethods');
		const totalCopiesEl = document.getElementById('totalCopies');
		const totalCustomEl = document.getElementById('totalCustom');

		const tryMethodEl = document.getElementById('tryMethod');
		const tryUrlEl = document.getElementById('tryUrl');
		const tryApiKeyEl = document.getElementById('tryApiKey');
		const tryContentTypeEl = document.getElementById('tryContentType');
		const tryBodyEl = document.getElementById('tryBody');
		const responseBox = document.getElementById('responseBox');
		const urlList = document.getElementById('urlList');

		const modal = document.getElementById('endpointModal');
		const addEndpointBtn = document.getElementById('addEndpointBtn');
		const cancelModal = document.getElementById('cancelModal');
		const customForm = document.getElementById('customForm');
		const customMethod = document.getElementById('customMethod');
		const customUrl = document.getElementById('customUrl');
		const customDesc = document.getElementById('customDesc');
		const customNeedKey = document.getElementById('customNeedKey');
		const logoutLink = document.getElementById('logoutLink');

		function getCustomEndpoints() {
			try {
				const raw = localStorage.getItem(localKeyEndpoints);
				const parsed = raw ? JSON.parse(raw) : [];
				return Array.isArray(parsed) ? parsed : [];
			} catch (err) {
				return [];
			}
		}

		function saveCustomEndpoints(list) {
			localStorage.setItem(localKeyEndpoints, JSON.stringify(list));
		}

		function getCopyCount() {
			const n = Number(localStorage.getItem(localKeyCopies) || 0);
			return Number.isFinite(n) ? n : 0;
		}

		function setCopyCount(n) {
			localStorage.setItem(localKeyCopies, String(n));
			totalCopiesEl.textContent = String(n);
		}

		function normalizeCustom(item, index) {
			const method = String(item.method || 'GET').toUpperCase();
			const url = String(item.url || '').trim();
			const description = String(item.description || 'Custom endpoint');
			const requiresApiKey = Boolean(item.requiresApiKey);
			return {
				id: item.id || ('custom-' + index + '-' + Date.now()),
				source: 'custom',
				name: 'CUSTOM ' + method,
				method,
				url,
				description,
				requiresApiKey,
				color: methodColorMap[method] || '#475569'
			};
		}

		function getAllEndpoints() {
			const custom = getCustomEndpoints().map(normalizeCustom);
			return [...serverEndpoints, ...custom].filter(e => e.url);
		}

		function fillMethodSelect(uniqueMethods) {
			tryMethodEl.innerHTML = '';
			uniqueMethods.forEach(method => {
				const opt = document.createElement('option');
				opt.value = method;
				opt.textContent = method;
				tryMethodEl.appendChild(opt);
			});
			if (uniqueMethods.length === 0) {
				const fallback = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
				fallback.forEach(method => {
					const opt = document.createElement('option');
					opt.value = method;
					opt.textContent = method;
					tryMethodEl.appendChild(opt);
				});
			}
		}

		function fillUrlDatalist(endpoints) {
			urlList.innerHTML = '';
			endpoints.forEach(ep => {
				const opt = document.createElement('option');
				opt.value = ep.url;
				urlList.appendChild(opt);
			});
		}

		function createEndpointCard(ep) {
			const card = document.createElement('article');
			card.className = 'endpoint';

			const method = document.createElement('span');
			method.className = 'method';
			method.textContent = ep.method;
			method.style.background = ep.color || '#334155';

			const row = document.createElement('div');
			row.className = 'url-row';

			const input = document.createElement('input');
			input.className = 'url-input';
			input.value = ep.url;
			input.readOnly = true;

			const copy = document.createElement('button');
			copy.className = 'copy';
			copy.type = 'button';
			copy.textContent = 'Copy';
			copy.addEventListener('click', async () => {
				try {
					await navigator.clipboard.writeText(ep.url);
				} catch (err) {
					input.select();
					document.execCommand('copy');
				}
				setCopyCount(getCopyCount() + 1);
				copy.textContent = 'Copied';
				setTimeout(() => { copy.textContent = 'Copy'; }, 900);
			});

			const desc = document.createElement('p');
			desc.className = 'desc';
			desc.textContent = ep.description || 'Tanpa deskripsi';

			row.appendChild(input);
			row.appendChild(copy);
			card.appendChild(method);
			card.appendChild(row);
			if (ep.requiresApiKey) {
				const badge = document.createElement('span');
				badge.className = 'badge';
				badge.textContent = 'Requires X-API-KEY';
				card.appendChild(badge);
			}
			if (ep.source === 'custom') {
				const customBadge = document.createElement('span');
				customBadge.className = 'badge';
				customBadge.style.background = '#dbeafe';
				customBadge.textContent = 'Custom';
				card.appendChild(customBadge);
			}
			card.appendChild(desc);
			return card;
		}

		function refreshDashboard() {
			const all = getAllEndpoints();
			endpointGrid.innerHTML = '';
			all.forEach(ep => endpointGrid.appendChild(createEndpointCard(ep)));

			const methods = [...new Set(all.map(ep => ep.method))].sort();
			totalEndpointsEl.textContent = String(all.length);
			totalMethodsEl.textContent = String(methods.length);
			totalCustomEl.textContent = String(all.filter(ep => ep.source === 'custom').length);
			totalCopiesEl.textContent = String(getCopyCount());

			fillMethodSelect(methods);
			fillUrlDatalist(all);

			if (all.length > 0 && !tryUrlEl.value) {
				tryUrlEl.value = all[0].url;
				tryMethodEl.value = all[0].method;
				if (all[0].requiresApiKey && !tryApiKeyEl.value) {
					tryApiKeyEl.value = localStorage.getItem(localKeyApiKey) || '';
				}
			}
		}

		addEndpointBtn.addEventListener('click', () => {
			customMethod.value = 'GET';
			customUrl.value = '';
			customDesc.value = '';
			customNeedKey.checked = false;
			modal.showModal();
		});

		cancelModal.addEventListener('click', () => modal.close());

		customForm.addEventListener('submit', event => {
			event.preventDefault();
			const method = customMethod.value.toUpperCase();
			const url = customUrl.value.trim();
			const description = customDesc.value.trim();
			const requiresApiKey = customNeedKey.checked;

			if (!url || !description) {
				return;
			}

			const existing = getCustomEndpoints();
			existing.push({
				id: 'custom-' + Date.now(),
				method,
				url,
				description,
				requiresApiKey
			});
			saveCustomEndpoints(existing);
			refreshDashboard();
			modal.close();
		});

		function toFormEncodedBody(rawText) {
			const input = String(rawText || '').trim();
			if (!input) {
				return { ok: true, body: '' };
			}

			if (input.includes('=') && !input.startsWith('{')) {
				return { ok: true, body: input };
			}

			const candidates = [
				input,
				input.replace(/;\s*([,}])/g, '$1'),
				input.replace(/,\s*}/g, '}'),
				input.replace(/;\s*}/g, '}')
			];

			for (const candidate of candidates) {
				try {
					const obj = JSON.parse(candidate);
					if (obj && typeof obj === 'object' && !Array.isArray(obj)) {
						const params = new URLSearchParams();
						Object.keys(obj).forEach(key => {
							params.append(key, obj[key] == null ? '' : String(obj[key]));
						});
						return { ok: true, body: params.toString() };
					}
				} catch (err) {
					// try next candidate
				}
			}

			return {
				ok: false,
				message: 'Body untuk endpoint login/register harus object JSON valid, contoh: {"username":"u","email":"e","password":"p"}'
			};
		}

		document.getElementById('sendBtn').addEventListener('click', async () => {
			const method = (tryMethodEl.value || 'GET').toUpperCase();
			const url = tryUrlEl.value.trim();
			const apiKey = tryApiKeyEl.value.trim();
			let contentType = tryContentTypeEl.value.trim();
			const bodyText = tryBodyEl.value.trim();

			if (!url) {
				responseBox.textContent = 'URL endpoint wajib diisi.';
				return;
			}

			const headers = {};
			if (apiKey) {
				headers['X-API-KEY'] = apiKey;
			}

			const init = { method, headers };
			if (method !== 'GET' && method !== 'HEAD' && bodyText) {
				const lowerUrl = url.toLowerCase();
				const isFormPostEndpoint = lowerUrl.endsWith('/login.php') || lowerUrl.endsWith('/register.php');

				if (isFormPostEndpoint) {
					const converted = toFormEncodedBody(bodyText);
					if (!converted.ok) {
						responseBox.textContent = converted.message;
						return;
					}
					contentType = 'application/x-www-form-urlencoded';
					headers['Content-Type'] = contentType;
					init.body = converted.body;
				} else {
					if (contentType) {
						headers['Content-Type'] = contentType;
					}
					init.body = bodyText;
				}
			}

			responseBox.textContent = 'Loading...';

			const started = performance.now();
			try {
				const res = await fetch(url, init);
				const elapsed = Math.round(performance.now() - started);
				const text = await res.text();

				let pretty = text;
				try {
					const parsed = JSON.parse(text);
					pretty = JSON.stringify(parsed, null, 2);
				} catch (err) {
					// keep raw text
				}

				responseBox.textContent = [
					'Status : ' + res.status + ' ' + res.statusText,
					'Time   : ' + elapsed + ' ms',
					'URL    : ' + url,
					'--- Response ---',
					pretty
				].join('\n');
			} catch (err) {
				responseBox.textContent = 'Request gagal: ' + (err && err.message ? err.message : 'Unknown error');
			}
		});

		if (sessionApiKey) {
			localStorage.setItem(localKeyApiKey, sessionApiKey);
		}
		tryApiKeyEl.value = sessionApiKey || localStorage.getItem(localKeyApiKey) || '';

		logoutLink.addEventListener('click', function () {
			localStorage.removeItem(localKeyApiKey);
		});
		refreshDashboard();
	</script>
</body>
</html>
