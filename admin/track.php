<?php
require_once __DIR__ . '/config.php';

// Ignorovat boty a admin samotného
$ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
$bots = ['bot','crawl','spider','slurp','mediapartners','facebookexternalhit','twitterbot'];
foreach ($bots as $b) { if (strpos($ua, $b) !== false) goto respond; }

$file = VISITS_FILE;
$lock = fopen($file . '.lock', 'c+');
if ($lock && flock($lock, LOCK_EX)) {
    $data = json_decode(file_get_contents($file), true) ?: ['total'=>0,'daily'=>[]];
    $today = date('Y-m-d');
    $data['total'] = ($data['total'] ?? 0) + 1;
    $data['daily'][$today] = ($data['daily'][$today] ?? 0) + 1;
    // Drž jen posledních 90 dní
    if (count($data['daily']) > 90) {
        ksort($data['daily']);
        $data['daily'] = array_slice($data['daily'], -90, 90, true);
    }
    file_put_contents($file, json_encode($data));
    flock($lock, LOCK_UN);
    fclose($lock);
}

respond:
header('Content-Type: image/gif');
header('Cache-Control: no-store, no-cache, must-revalidate');
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
