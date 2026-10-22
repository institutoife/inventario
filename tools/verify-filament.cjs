// Browser verification uses its own SQLite database, account and PHP server.
const fs = require('fs');
const path = require('path');
const os = require('os');
const net = require('net');
const { spawn, spawnSync } = require('child_process');
const root = path.resolve(__dirname, '..');
const reportDir = fs.mkdtempSync(path.join(os.tmpdir(), 'inventario-filament-'));
const php = 'C:\\xampp\\php\\php.exe';
const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const delay = ms => new Promise(resolve => setTimeout(resolve, ms));
const freePort = () => new Promise(resolve => {
  const listener = net.createServer();
  listener.listen(0, '127.0.0.1', () => { const port = listener.address().port; listener.close(() => resolve(port)); });
});
let server, chrome, socket;
const failures = [], runtimeErrors = [];
const assert = (condition, message) => { if (!condition) throw new Error(message); };
(async () => {
  const port = await freePort();
  const browserPort = await freePort();
  const origin = `http://127.0.0.1:${port}`;
  const database = path.join(reportDir, 'verification.sqlite');
  fs.writeFileSync(database, '');
  const env = { ...process.env, APP_ENV: 'testing', APP_URL: origin, DB_CONNECTION: 'sqlite', DB_DATABASE: database, DB_URL: '', CACHE_STORE: 'array', SESSION_DRIVER: 'file', QUEUE_CONNECTION: 'sync', MAIL_MAILER: 'array', INITIAL_ADMIN_EMAIL: '', INITIAL_ADMIN_PASSWORD: '' };
  assert(env.DB_DATABASE.startsWith(reportDir) && env.DB_CONNECTION === 'sqlite', 'Verification database must be isolated.');
  const fixture = `<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();
if (config('database.default') !== 'sqlite' || app()->environment() !== 'testing') { throw new RuntimeException('Unsafe test environment'); }
Illuminate\\Support\\Facades\\Artisan::call('migrate', ['--force' => true]);
Illuminate\\Support\\Facades\\Artisan::call('db:seed', ['--class' => Database\\Seeders\\FoundationSeeder::class, '--force' => true]);
$user = App\\Models\\User::create(['name' => 'Administración de prueba', 'email' => 'review@example.test', 'password' => 'ReviewPassword123!', 'active' => true]);
$user->roles()->attach(App\\Models\\Role::where('code', 'superadmin')->sole());
$unit = App\\Models\\OrganizationalUnit::create(['code' => 'ADM', 'name' => 'Dirección administrativa']);
$office = App\\Models\\Office::create(['code' => 'OF-01', 'name' => 'Oficina central', 'organizational_unit_id' => $unit->id]);
$category = App\\Models\\AssetCategory::create(['code' => 'EQ', 'name' => 'Equipos de computación']);
$budget = App\\Models\\BudgetItem::create(['code' => '43110', 'name' => 'Equipo de oficina']);
for ($index = 1; $index <= 3; $index++) {
  app(App\\Actions\\Assets\\SaveAsset::class)->execute($user, ['code' => 'AF-00'.$index, 'description' => ['Computadora de escritorio para contabilidad', 'Impresora de red para administración', 'Monitor de oficina central'][$index - 1], 'registered_on' => '2026-10-01', 'organizational_unit_id' => $unit->id, 'office_id' => $office->id, 'asset_category_id' => $category->id, 'budget_item_id' => $budget->id, 'physical_status_id' => App\\Models\\PhysicalStatus::where('code', 'B')->sole()->id, 'brand' => 'Marca de prueba', 'reason' => 'Fixture aislado para verificación de interfaz']);
}
echo 'Fixture SQLite creado';
`;
  const seeded = spawnSync(php, [], { cwd: root, env, input: fixture, encoding: 'utf8', windowsHide: true });
  assert(seeded.status === 0, `Fixture failed: ${seeded.stdout} ${seeded.stderr}`);
  const serverLog = fs.openSync(path.join(reportDir, 'php-server.log'), 'w');
  server = spawn(php, ['-S', `127.0.0.1:${port}`, path.join(root, 'vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php')], { cwd: path.join(root, 'public'), env, windowsHide: true, stdio: ['ignore', serverLog, serverLog] });
  chrome = spawn(chromePath, ['--headless=new', '--disable-gpu', '--no-first-run', '--no-default-browser-check', `--remote-debugging-port=${browserPort}`, `--user-data-dir=${path.join(reportDir, 'browser-profile')}`, '--window-size=1440,1080', 'about:blank'], { windowsHide: true, stdio: 'ignore' });
  let target;
  for (let attempt = 0; attempt < 100; attempt++) {
    try { const targets = await (await fetch(`http://127.0.0.1:${browserPort}/json`)).json(); target = targets.find(item => item.type === 'page'); if (target) break; } catch {}
    await delay(100);
  }
  assert(target, 'Chrome did not start');
  socket = new WebSocket(target.webSocketDebuggerUrl);
  await new Promise((resolve, reject) => { socket.addEventListener('open', resolve, { once: true }); socket.addEventListener('error', reject, { once: true }); });
  let sequence = 0;
  const pending = new Map();
  socket.addEventListener('message', event => {
    const message = JSON.parse(String(event.data));
    if (message.id) { const task = pending.get(message.id); if (task) { pending.delete(message.id); message.error ? task.reject(new Error(JSON.stringify(message.error))) : task.resolve(message.result); } }
    if (message.method === 'Runtime.exceptionThrown') runtimeErrors.push(message.params.exceptionDetails.exception?.description || message.params.exceptionDetails.text);
    if (message.method === 'Network.responseReceived' && message.params.response.status >= 400) failures.push({ url: message.params.response.url, status: message.params.response.status });
  });
  const call = (method, params = {}) => new Promise((resolve, reject) => { const id = ++sequence; pending.set(id, { resolve, reject }); socket.send(JSON.stringify({ id, method, params })); });
  const evaluate = async expression => { const response = await call('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true }); if (response.exceptionDetails) throw new Error(JSON.stringify(response.exceptionDetails)); return response.result.value; };
  const waitFor = async (expression, description) => { for (let attempt = 0; attempt < 120; attempt++) { if (await evaluate(expression).catch(() => false)) return; await delay(100); } fs.writeFileSync(path.join(reportDir, "failure.json"), JSON.stringify({ description, runtimeErrors, failures, page: await evaluate("({url:location.href,text:document.body.innerText,livewire:!!window.Livewire})") }, null, 2)); throw new Error(`Timeout: ${description}; report: ${reportDir}`); };
  const navigate = async suffix => { await call('Page.navigate', { url: origin + suffix }); await waitFor(`document.readyState === 'complete' && location.pathname === ${JSON.stringify(suffix)} && ${suffix === '/' ? 'true' : '!!window.Livewire'}`, suffix); };
  const screenshot = async name => { await delay(350); const shot = await call('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false }); fs.writeFileSync(path.join(reportDir, name), Buffer.from(shot.data, 'base64')); };
  await call('Page.enable'); await call('Runtime.enable'); await call('Network.enable');
  await call('Emulation.setDeviceMetricsOverride', { width: 1440, height: 1080, deviceScaleFactor: 1, mobile: false });
  await navigate('/');
  await screenshot('public-home-desktop.png');
  const loginPosition = await evaluate(`(() => { const button=document.querySelector('.account-actions a'); const brand=document.querySelector('.brand'); const rect=button.getBoundingClientRect(); return { label:button.innerText, left:rect.left, right:rect.right, top:rect.top, brandRight:brand.getBoundingClientRect().right, width:innerWidth }; })()`);
  assert(loginPosition.label === 'Iniciar sesión' && loginPosition.left > loginPosition.brandRight && loginPosition.top < 100, 'Login button must be in the top right header');
  await call('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 1, mobile: true });
  await navigate('/');
  assert(await evaluate(`document.documentElement.scrollWidth <= innerWidth + 1`), 'Public mobile page overflows');
  await screenshot('public-home-mobile.png');
  await evaluate(`document.querySelector('.account-actions a').click()`);
  await waitFor(`location.pathname === '/panel/login' && document.readyState === 'complete' && !!window.Livewire`, 'login button navigation');
  await call('Emulation.setDeviceMetricsOverride', { width: 1440, height: 1080, deviceScaleFactor: 1, mobile: false });
  await evaluate(`(() => { for (const [id,value] of [['data.email','review@example.test'],['data.password','ReviewPassword123!']]) { const input=document.querySelector(id==='data.email'?'input[type=email]':'input[type=password]'); input.value=value; input.dispatchEvent(new Event('input',{bubbles:true})); input.dispatchEvent(new Event('change',{bubbles:true})); } document.querySelector('form').requestSubmit(); })()`);
  await waitFor(`location.pathname === '/panel' && document.body.innerText.includes('Inicio') && document.body.innerText.includes('Activos registrados')`, 'login and dashboard');
  await screenshot('dashboard-desktop.png');
  await navigate('/');
  assert(await evaluate(`document.body.innerText.includes('Ir al panel') && !!document.querySelector('button[aria-label="Cerrar sesión"]')`), 'Authenticated home controls are missing');
  await screenshot('public-home-authenticated-desktop.png');
  await navigate('/panel/assets');
  await waitFor(`document.body.innerText.includes('AF-001')`, 'asset table');
  await screenshot('assets-desktop.png');
  await evaluate(`(() => { const input=document.querySelector('input[type=search]'); input.value='Impresora'; input.dispatchEvent(new Event('input',{bubbles:true})); })()`);
  await waitFor(`document.querySelector('tbody')?.innerText.includes('AF-002') && !document.querySelector('tbody')?.innerText.includes('AF-001')`, 'Livewire table search');
  await navigate('/panel/assets/create');
  await waitFor(`document.body.innerText.includes('Identificación') && document.body.innerText.includes('Motivo del registro')`, 'asset form');
  await screenshot('asset-form-desktop.png');
  await navigate('/panel/assets/1');
  await waitFor(`document.body.innerText.includes('Datos principales')`, 'asset detail');
  await screenshot('asset-detail-desktop.png');
  await navigate('/panel/audit-trail');
  await waitFor(`document.body.innerText.includes('Ver detalle')`, 'audit table');
  await evaluate(`Array.from(document.querySelectorAll('button')).find(button => button.innerText.includes('Ver detalle')).click()`);
  await waitFor(`document.body.innerText.includes('Detalle de auditoría') && document.body.innerText.includes('Fixture aislado')`, 'audit modal');
  await screenshot('audit-desktop.png');
  await call('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 1, mobile: true });
  await navigate('/panel/assets');
  await waitFor(`document.body.innerText.includes('AF-001')`, 'mobile table');
  await screenshot('assets-mobile.png');
  await navigate('/panel/assets/create');
  await screenshot('asset-form-mobile.png');
  const overflowing = await evaluate(`document.documentElement.scrollWidth > innerWidth + 1`);
  assert(!overflowing, 'Mobile form overflows the viewport');
  await navigate('/');
  assert(await evaluate(`document.documentElement.scrollWidth <= innerWidth + 1`), 'Authenticated mobile page overflows');
  await screenshot('public-home-authenticated-mobile.png');
  await evaluate(`document.querySelector('.account-actions form').requestSubmit()`);
  await waitFor(`location.pathname === '/' && document.body.innerText.includes('Iniciar sesión') && !document.body.innerText.includes('Ir al panel')`, 'logout returns to public home');
  await call('Page.navigate', { url: origin + '/panel' });
  await waitFor(`location.pathname === '/panel/login' && !!window.Livewire`, 'panel remains protected after logout');
  assert(runtimeErrors.length === 0, `Browser runtime errors: ${runtimeErrors.join('\n')}`);
  assert(failures.length === 0, `Failed requests: ${JSON.stringify(failures)}`);
  const report = { passed: true, checks: ['Public home desktop and mobile', 'Login button in top right', 'Authenticated home controls', 'Logout returns home and panel remains protected', 'Login through Livewire', 'Dashboard', 'Asset table', 'Livewire search', 'Create form', 'View page', 'Audit modal', 'Mobile table and form', 'No JavaScript errors', 'No failed HTTP requests'], database: 'isolated SQLite', reportDir };
  fs.writeFileSync(path.join(reportDir, 'results.json'), JSON.stringify(report, null, 2));
  process.stdout.write(JSON.stringify(report, null, 2) + '\n');
})().catch(error => { process.stderr.write(error.stack + '\n'); process.exitCode = 1; }).finally(async () => {
  try { socket?.close(); } catch {}
  try { chrome?.kill(); } catch {}
  try { server?.kill(); } catch {}
});
