<?php
// ============================================================
// KONFIGURACE ADMINISTRACE
// ============================================================
// Změňte heslo níže. Při prvním spuštění na serveru se
// automaticky zahashuje a tento soubor se přepíše.

define('ADMIN_USERNAME', 'admin');
$__plain = 'SmartOkna2024!';   // <-- ZDE ZMĚŇTE HESLO

// --- Auto-hash při prvním spuštění ---
if (strpos($__plain, '$2y$') !== 0) {
    $hash = password_hash($__plain, PASSWORD_DEFAULT);
    $newContent = "<?php\n"
        . "// ============================================================\n"
        . "// KONFIGURACE ADMINISTRACE\n"
        . "// ============================================================\n"
        . "// Heslo je zahashované. Pro změnu hesla smažte tento soubor\n"
        . "// a vytvořte nový s plaintext heslem — auto-hash proběhne znovu.\n\n"
        . "define('ADMIN_USERNAME', 'admin');\n"
        . "\$__plain = '" . addslashes($hash) . "';\n\n"
        . "if (strpos(\$__plain, '\$2y\$') !== 0) {\n"
        . "    \$hash = password_hash(\$__plain, PASSWORD_DEFAULT);\n"
        . "    // (již zahashováno)\n"
        . "}\n"
        . "define('ADMIN_PASSWORD_HASH', \$__plain);\n\n"
        . "define('SESSION_NAME', 'smartokna_admin');\n"
        . "define('CONTENT_FILE', __DIR__ . '/../content.json');\n"
        . "define('VISITS_FILE',  __DIR__ . '/../visits.json');\n"
        . "define('UPLOADS_DIR',  __DIR__ . '/../uploads/');\n"
        . "define('UPLOADS_URL',  '../uploads/');\n";
    file_put_contents(__FILE__, $newContent);
    define('ADMIN_PASSWORD_HASH', $hash);
} else {
    define('ADMIN_PASSWORD_HASH', $__plain);
}

define('SESSION_NAME', 'smartokna_admin');
define('CONTENT_FILE', __DIR__ . '/../content.json');
define('VISITS_FILE',  __DIR__ . '/../visits.json');
define('UPLOADS_DIR',  __DIR__ . '/../uploads/');
define('UPLOADS_URL',  '../uploads/');
