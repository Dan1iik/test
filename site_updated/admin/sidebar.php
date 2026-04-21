<?php
$current = basename($_SERVER['PHP_SELF']);
$nav = [
  ['panel.php',   'Přehled',   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>'],
  ['produkty.php','Produkty',  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="1"/><line x1="12" y1="3" x2="12" y2="21"/><line x1="3" y1="12" x2="21" y2="12"/></svg>'],
  ['o-nas.php',   'O nás',     '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>'],
  ['projekty.php','Projekty',  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="13" rx="2"/><path d="M8 21h8M12 17v4"/></svg>'],
];
?>
<aside class="sidebar">
  <div class="sb-logo">
    <div class="sb-logo-icon"><span></span><span></span><span></span><span></span></div>
    <span class="sb-logo-text">Smart Okna</span>
  </div>

  <div class="sb-profile">
    <div class="sb-avatar">A</div>
    <div>
      <div class="sb-name">Admin</div>
      <div class="sb-role">Správce webu</div>
    </div>
  </div>

  <nav class="sb-nav">
    <?php foreach ($nav as [$file, $label, $icon]): ?>
    <a href="<?= $file ?>" class="sb-link <?= $current===$file?'active':'' ?>">
      <?= $icon ?>
      <span><?= $label ?></span>
    </a>
    <?php endforeach; ?>
  </nav>

  <div class="sb-bottom">
    <a href="../index.html" class="sb-link" target="_blank">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
      <span>Zobrazit web</span>
    </a>
    <a href="logout.php" class="sb-link sb-logout">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      <span>Odhlásit se</span>
    </a>
  </div>
</aside>
