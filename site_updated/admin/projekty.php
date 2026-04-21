<?php
require_once __DIR__ . '/auth.php';
$content  = json_decode(file_get_contents(CONTENT_FILE), true) ?: [];
$projekty = $content['projekty'] ?? [];
?><!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin – Projekty</title>
<link href="https://fonts.googleapis.com/css2?family=Mona+Sans:ital,wdth,wght@0,75..125,200..900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="main">
  <div class="topbar">
    <h1>Editace projektů</h1>
  </div>
  <div class="content">

    <?php foreach ($projekty as $idx => $proj): ?>
    <div class="card">
      <div class="card-title">
        <span>Projekt <?= $idx+1 ?>: <?= htmlspecialchars($proj['name']) ?></span>
      </div>
      <form class="proj-form" data-idx="<?= $idx ?>" enctype="multipart/form-data">
        <div class="form-grid">
          <div class="field">
            <label>Název projektu</label>
            <input type="text" name="name" value="<?= htmlspecialchars($proj['name']) ?>" required>
          </div>
          <div class="field">
            <label>Typ (kategorie)</label>
            <input type="text" name="type" value="<?= htmlspecialchars($proj['type']) ?>">
            <div class="hint">Např. Plastová okna + vstupní dveře</div>
          </div>
        </div>
        <div class="form-grid">
          <div class="field">
            <label>Meta informace</label>
            <input type="text" name="meta" value="<?= htmlspecialchars($proj['meta']) ?>">
            <div class="hint">Např. 18 ks oken · Smart SP 7000 · RAL 7016</div>
          </div>
          <div class="field">
            <label>Štítek (lokace)</label>
            <input type="text" name="label" value="<?= htmlspecialchars($proj['label']) ?>">
            <div class="hint">Např. Rodinný dům · Praha</div>
          </div>
        </div>
        <div class="field">
          <label>Fotografie projektu</label>
          <div class="img-upload">
            <input type="file" name="image" accept="image/*" onchange="previewImg(this,'projPrev<?= $idx ?>')">
            <div class="iu-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg></div>
            <div class="iu-text">Klikněte nebo přetáhněte fotografii<br><small>JPG, PNG, WEBP – doporučeno 800×500 px</small></div>
          </div>
          <div class="img-preview" id="projPrev<?= $idx ?>">
            <?php if (!empty($proj['image'])): ?>
            <img src="../<?= htmlspecialchars($proj['image']) ?>" alt="">
            <?php endif; ?>
          </div>
        </div>
        <div style="display:flex;gap:.8rem;align-items:center">
          <button type="submit" class="btn btn-primary btn-sm">Uložit projekt</button>
          <span class="save-status" style="font-size:.8rem;color:#15803d"></span>
        </div>
      </form>
    </div>
    <?php endforeach; ?>

  </div>
</div>
<script>
function previewImg(input, previewId) {
  const prev = document.getElementById(previewId);
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      prev.style.display = 'block';
      prev.innerHTML = '<img src="'+e.target.result+'" alt="">';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

document.querySelectorAll('.proj-form').forEach(form => {
  form.addEventListener('submit', async e => {
    e.preventDefault();
    const btn  = form.querySelector('[type=submit]');
    const stat = form.querySelector('.save-status');
    btn.disabled = true; btn.textContent = 'Ukládám…';
    const fd = new FormData(form);
    fd.append('action', 'save_project');
    fd.append('idx',    form.dataset.idx);
    const res  = await fetch('save.php', {method:'POST', body:fd});
    const json = await res.json();
    btn.disabled = false; btn.textContent = 'Uložit projekt';
    stat.textContent = json.ok ? '✓ Uloženo' : '✗ Chyba: '+json.msg;
    stat.style.color = json.ok ? '#15803d' : '#dc2626';
    setTimeout(()=>stat.textContent='', 3000);
  });
});
</script>
</body>
</html>
