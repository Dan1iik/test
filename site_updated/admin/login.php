<?php
require_once __DIR__ . '/config.php';
session_name(SESSION_NAME);
session_start();

if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: panel.php'); exit;
}

// ── Brute-force ochrana ───────────────────────────────────
define('MAX_ATTEMPTS',   5);
define('LOCKOUT_SECS',   900);  // 15 minut
define('ATTEMPTS_FILE',  __DIR__ . '/login_attempts.json');

function getIp() {
    return $_SERVER['HTTP_X_FORWARDED_FOR']
        ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]
        : ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}

function loadAttempts() {
    if (!file_exists(ATTEMPTS_FILE)) return [];
    return json_decode(file_get_contents(ATTEMPTS_FILE), true) ?: [];
}

function saveAttempts($data) {
    file_put_contents(ATTEMPTS_FILE, json_encode($data));
}

function cleanOld(&$data) {
    $now = time();
    foreach ($data as $ip => $info) {
        if ($now - $info['last'] > LOCKOUT_SECS * 2) unset($data[$ip]);
    }
}

$ip       = getIp();
$attempts = loadAttempts();
cleanOld($attempts);

$ipData   = $attempts[$ip] ?? ['count' => 0, 'last' => 0];
$locked   = $ipData['count'] >= MAX_ATTEMPTS && (time() - $ipData['last']) < LOCKOUT_SECS;
$remaining = $locked ? (LOCKOUT_SECS - (time() - $ipData['last'])) : 0;
$attemptsLeft = max(0, MAX_ATTEMPTS - $ipData['count']);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$locked) {
    if (
        ($_POST['username'] ?? '') === ADMIN_USERNAME &&
        password_verify($_POST['password'] ?? '', ADMIN_PASSWORD_HASH)
    ) {
        // Úspěch — reset pokusů
        unset($attempts[$ip]);
        saveAttempts($attempts);
        $_SESSION['admin_logged_in'] = true;
        header('Location: panel.php');
        exit;
    }

    // Neúspěch — zaznamenat pokus
    $ipData['count']++;
    $ipData['last'] = time();
    $attempts[$ip]  = $ipData;
    saveAttempts($attempts);

    $attemptsLeft = max(0, MAX_ATTEMPTS - $ipData['count']);
    $locked       = $ipData['count'] >= MAX_ATTEMPTS;
    $remaining    = LOCKOUT_SECS;

    if ($locked) {
        $error = 'Příliš mnoho neúspěšných pokusů. Zkuste to znovu za 15 minut.';
    } elseif ($attemptsLeft === 1) {
        $error = 'Nesprávné přihlašovací údaje. Pozor — zbývá již 1 pokus před zablokováním.';
    } else {
        $error = 'Nesprávné přihlašovací údaje. Zbývá ' . $attemptsLeft . ' ' . ($attemptsLeft >= 5 ? 'pokusů' : ($attemptsLeft >= 2 ? 'pokusy' : 'pokus')) . '.';
    }
}

// Formát zbývajícího času
function formatTime($secs) {
    $m = floor($secs / 60);
    $s = $secs % 60;
    return $m > 0 ? "{$m} min " . ($s > 0 ? "{$s} s" : '') : "{$s} s";
}
?><!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin – Smart Okna</title>
<link href="https://fonts.googleapis.com/css2?family=Mona+Sans:ital,wdth,wght@0,75..125,200..900&display=swap" rel="stylesheet">
<style>
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  body{background:#f0f2f6;font-family:'Mona Sans','Helvetica Neue',sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh}
  .card{background:#fff;border-radius:20px;padding:3rem 2.5rem;width:100%;max-width:400px;box-shadow:0 20px 60px rgba(0,0,0,.1)}
  .logo{display:flex;align-items:center;gap:.6rem;margin-bottom:2.5rem;justify-content:center}
  .logo-icon{width:40px;height:40px;background:#1a5fa8;border-radius:10px;display:grid;grid-template-columns:1fr 1fr;gap:3px;padding:8px}
  .logo-icon span{background:rgba(255,255,255,.9);border-radius:2px}
  .logo-text{font-size:1.2rem;font-weight:800;color:#0f1117;letter-spacing:-.02em}
  h1{font-size:1.5rem;font-weight:800;color:#0f1117;margin-bottom:.4rem;text-align:center}
  .sub{color:#8a90a8;font-size:.88rem;text-align:center;margin-bottom:2rem}
  label{display:block;font-size:.75rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#4a5068;margin-bottom:.4rem}
  input{width:100%;padding:.8rem 1rem;border:1.5px solid #e2e5ec;border-radius:10px;font-size:.95rem;font-family:inherit;outline:none;transition:border-color .2s;color:#0f1117}
  input:focus{border-color:#1a5fa8}
  .field{margin-bottom:1.2rem}
  .btn{width:100%;padding:.9rem;background:#1a5fa8;color:#fff;border:none;border-radius:10px;font-size:.9rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;cursor:pointer;font-family:inherit;transition:background .2s,transform .15s;margin-top:.5rem}
  .btn:hover:not(:disabled){background:#2775c9;transform:translateY(-1px)}
  .btn:disabled{background:#8a90a8;cursor:not-allowed;transform:none}
  .error{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:.75rem 1rem;border-radius:8px;font-size:.85rem;margin-bottom:1.2rem}
  .locked-box{background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;padding:1.4rem;text-align:center;margin-bottom:1.2rem}
  .locked-icon{font-size:2rem;margin-bottom:.5rem}
  .locked-title{font-size:.95rem;font-weight:800;color:#c2410c;margin-bottom:.3rem}
  .locked-sub{font-size:.82rem;color:#9a3412}
  .countdown{font-size:1.4rem;font-weight:800;color:#c2410c;margin:.6rem 0 .2rem;letter-spacing:-.02em}
  .back{text-align:center;margin-top:1.5rem;font-size:.8rem;color:#8a90a8}
  .back a{color:#1a5fa8;text-decoration:none;font-weight:600}
  .attempts-bar{height:4px;background:#f0f2f6;border-radius:2px;margin-bottom:1.2rem;overflow:hidden}
  .attempts-fill{height:100%;border-radius:2px;transition:width .3s,background .3s}
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <div class="logo-icon"><span></span><span></span><span></span><span></span></div>
    <span class="logo-text">Smart Okna</span>
  </div>
  <h1>Přihlášení</h1>
  <p class="sub">Administrační panel</p>

  <?php if ($locked): ?>
  <div class="locked-box">
    <div class="locked-icon">🔒</div>
    <div class="locked-title">Přístup dočasně zablokován</div>
    <div class="locked-sub">Příliš mnoho neúspěšných pokusů.</div>
    <div class="countdown" id="countdown"><?= formatTime($remaining) ?></div>
    <div class="locked-sub">Zkuste to znovu po uplynutí doby.</div>
  </div>
  <button class="btn" disabled>Přihlásit se →</button>
  <?php else: ?>

  <?php if ($ipData['count'] > 0): ?>
  <?php $pct = round(($ipData['count'] / MAX_ATTEMPTS) * 100); ?>
  <?php $color = $pct >= 80 ? '#dc2626' : ($pct >= 60 ? '#f59e0b' : '#1a5fa8'); ?>
  <div class="attempts-bar"><div class="attempts-fill" style="width:<?= $pct ?>%;background:<?= $color ?>"></div></div>
  <?php endif; ?>

  <?php if ($error): ?>
  <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="field">
      <label>Uživatelské jméno</label>
      <input type="text" name="username" value="<?= htmlspecialchars($_POST['username']??'') ?>" required autofocus autocomplete="username">
    </div>
    <div class="field">
      <label>Heslo</label>
      <input type="password" name="password" required autocomplete="current-password">
    </div>
    <button type="submit" class="btn">Přihlásit se →</button>
  </form>
  <?php endif; ?>

  <div class="back"><a href="../index.html">← Zpět na web</a></div>
</div>

<?php if ($locked && $remaining > 0): ?>
<script>
let secs = <?= $remaining ?>;
const el = document.getElementById('countdown');
function fmt(s) {
  const m = Math.floor(s/60), r = s%60;
  return m > 0 ? m+'m '+(r>0?r+'s':'') : r+'s';
}
const t = setInterval(() => {
  secs--;
  if (secs <= 0) { clearInterval(t); location.reload(); return; }
  el.textContent = fmt(secs);
}, 1000);
</script>
<?php endif; ?>

</body>
</html>
