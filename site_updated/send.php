<?php
// ============================================================
// Odesílání kontaktního formuláře — info@smartokna.cz
// ============================================================

header('Content-Type: application/json; charset=utf-8');

// Povolíme pouze POST požadavky
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Neplatná metoda.']);
    exit;
}

// ── Honeypot (spam ochrana) ──────────────────────────────────
if (!empty($_POST['website'])) {
    // Bot vyplnil skryté pole → tiše ignoruj
    echo json_encode(['ok' => true]);
    exit;
}

// ── Rate limiting — max 5 zpráv / hodinu z jedné IP ─────────
$ratefile = __DIR__ . '/admin/form_rate.json';
$ip       = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$now      = time();
$rate     = [];

if (file_exists($ratefile)) {
    $rate = json_decode(file_get_contents($ratefile), true) ?? [];
}

$rate[$ip] = array_filter($rate[$ip] ?? [], fn($t) => $t > $now - 3600);

if (count($rate[$ip]) >= 5) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'msg' => 'Příliš mnoho zpráv. Zkuste to znovu za hodinu nebo nás zavolejte.']);
    exit;
}

$rate[$ip][] = $now;
file_put_contents($ratefile, json_encode($rate), LOCK_EX);

// ── Validace vstupů ──────────────────────────────────────────
$jmeno   = trim(strip_tags($_POST['jmeno']   ?? ''));
$telefon = trim(strip_tags($_POST['telefon'] ?? ''));
$email   = trim(strip_tags($_POST['email']   ?? ''));
$zprava  = trim(strip_tags($_POST['zprava']  ?? ''));
$gdpr    = $_POST['gdpr'] ?? '';

if (empty($jmeno) || mb_strlen($jmeno) < 2) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Zadejte prosím jméno.']);
    exit;
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Zadejte platný e-mail.']);
    exit;
}

if (empty($zprava) || mb_strlen($zprava) < 5) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Zpráva je příliš krátká.']);
    exit;
}

if (empty($gdpr)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Je nutné souhlasit se zpracováním osobních údajů.']);
    exit;
}

// ── Sestavení e-mailu ────────────────────────────────────────
$to      = 'info@smartokna.cz';
$subject = '=?UTF-8?B?' . base64_encode('Nová poptávka z webu — ' . $jmeno) . '?=';

$body  = "Nová poptávka z webu Smart Okna\n";
$body .= str_repeat('─', 40) . "\n\n";
$body .= "Jméno:    {$jmeno}\n";
$body .= "E-mail:   {$email}\n";
if ($telefon) {
    $body .= "Telefon:  {$telefon}\n";
}
$body .= "\nZpráva:\n{$zprava}\n\n";
$body .= str_repeat('─', 40) . "\n";
$body .= "Odesláno: " . date('d.m.Y H:i') . "\n";
$body .= "IP: {$ip}\n";
$body .= "Souhlas GDPR: ANO\n";

$headers  = "From: web@smartokna.cz\r\n";
$headers .= "Reply-To: {$email}\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "Content-Transfer-Encoding: base64\r\n";
$headers .= "X-Mailer: SmartOkna-Web\r\n";

$bodyEncoded = base64_encode($body);

$sent = mail($to, $subject, $bodyEncoded, $headers);

if ($sent) {
    echo json_encode(['ok' => true]);
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Zprávu se nepodařilo odeslat. Zavolejte nám prosím na 603 182 599.']);
}
