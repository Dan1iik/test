<?php
require_once __DIR__ . '/auth.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$content = json_decode(file_get_contents(CONTENT_FILE), true) ?: [];

function respond($ok, $msg = '') {
    echo json_encode(['ok' => $ok, 'msg' => $msg]);
    exit;
}
function saveContent($content) {
    return file_put_contents(CONTENT_FILE, json_encode($content, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

// ── Produkty ──────────────────────────────────────────────
if ($action === 'save_product') {
    $tab = $_POST['tab'] ?? '';
    $idx = (int)($_POST['idx'] ?? 0);
    if (!isset($content['produkty'][$tab][$idx])) respond(false, 'Produkt nenalezen');

    $content['produkty'][$tab][$idx]['name']  = trim($_POST['name'] ?? '');
    $content['produkty'][$tab][$idx]['badge'] = trim($_POST['badge'] ?? '');
    $content['produkty'][$tab][$idx]['desc']  = trim($_POST['desc'] ?? '');
    $content['produkty'][$tab][$idx]['link']  = trim($_POST['link'] ?? '');

    $tags = array_values(array_filter(array_map('trim', explode(',', $_POST['tags'] ?? ''))));
    $content['produkty'][$tab][$idx]['tags'] = $tags;

    // Zpracování nahraného obrázku
    if (!empty($_FILES['image']['tmp_name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) respond(false, 'Nepodporovaný formát obrázku');
        $filename = 'product_' . $tab . '_' . $idx . '_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], UPLOADS_DIR . $filename);
        $content['produkty'][$tab][$idx]['image'] = UPLOADS_URL . $filename;
    }

    saveContent($content);
    respond(true, 'Produkt uložen.');
}

// ── O nás ─────────────────────────────────────────────────
if ($action === 'save_o_nas') {
    $content['o_nas']['title']     = trim($_POST['title'] ?? '');
    $content['o_nas']['text1']     = trim($_POST['text1'] ?? '');
    $content['o_nas']['text2']     = trim($_POST['text2'] ?? '');
    $content['o_nas']['sig_name']  = trim($_POST['sig_name'] ?? '');
    $content['o_nas']['sig_quote'] = trim($_POST['sig_quote'] ?? '');

    // Proofs
    $ptitles = $_POST['proof_title'] ?? [];
    $psubs   = $_POST['proof_sub']   ?? [];
    $proofs  = [];
    foreach ($ptitles as $i => $t) {
        $proofs[] = ['title' => trim($t), 'sub' => trim($psubs[$i] ?? '')];
    }
    $content['o_nas']['proofs'] = $proofs;

    // Fotky
    foreach (['photo_main','photo_sec'] as $field) {
        if (!empty($_FILES[$field]['tmp_name'])) {
            $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) continue;
            $filename = $field . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES[$field]['tmp_name'], UPLOADS_DIR . $filename);
            $content['o_nas'][$field] = UPLOADS_URL . $filename;
        }
    }

    saveContent($content);
    respond(true, 'Sekce O nás uložena.');
}

// ── Projekt ───────────────────────────────────────────────
if ($action === 'save_project') {
    $idx = (int)($_POST['idx'] ?? 0);
    if (!isset($content['projekty'][$idx])) respond(false, 'Projekt nenalezen');

    $content['projekty'][$idx]['type']  = trim($_POST['type']  ?? '');
    $content['projekty'][$idx]['name']  = trim($_POST['name']  ?? '');
    $content['projekty'][$idx]['meta']  = trim($_POST['meta']  ?? '');
    $content['projekty'][$idx]['label'] = trim($_POST['label'] ?? '');

    if (!empty($_FILES['image']['tmp_name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) respond(false, 'Nepodporovaný formát obrázku');
        $filename = 'project_' . $idx . '_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], UPLOADS_DIR . $filename);
        $content['projekty'][$idx]['image'] = UPLOADS_URL . $filename;
    }

    saveContent($content);
    respond(true, 'Projekt uložen.');
}

respond(false, 'Neznámá akce.');
