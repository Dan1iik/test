<?php
require_once __DIR__ . '/auth.php';
$content = json_decode(file_get_contents(CONTENT_FILE), true) ?: [];
$tabs = ['okna'=>'Okna','dvere'=>'Dveře','posuvne'=>'Posuvné systémy','doplnky'=>'Doplňky'];
?><!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin – Produkty</title>
<link href="https://fonts.googleapis.com/css2?family=Mona+Sans:ital,wdth,wght@0,75..125,200..900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="main">
  <div class="topbar">
    <h1>Editace produktů</h1>
  </div>
  <div class="content">
    <div id="globalAlert" class="alert"></div>

    <div class="tab-bar-adm">
      <?php $first=true; foreach ($tabs as $key=>$label): ?>
      <button class="tab-btn-adm <?= $first?'active':'' ?>" data-tab="<?= $key ?>"><?= $label ?></button>
      <?php $first=false; endforeach; ?>
    </div>

    <?php foreach ($tabs as $tabKey => $tabLabel):
      $products = $content['produkty'][$tabKey] ?? [];
    ?>
    <div class="tab-panel-adm <?= $tabKey==='okna'?'active':'' ?>" id="tab-<?= $tabKey ?>">
      <?php foreach ($products as $idx => $prod): ?>
      <div class="prod-item" id="prod-<?= $tabKey ?>-<?= $idx ?>">
        <div class="prod-item-header" onclick="toggleProd('<?= $tabKey ?>','<?= $idx ?>')">
          <div>
            <div class="pi-name"><?= htmlspecialchars($prod['name']) ?></div>
          </div>
          <span class="pi-badge"><?= htmlspecialchars($prod['badge']) ?></span>
        </div>
        <div class="prod-item-body">
          <form class="prod-form" data-tab="<?= $tabKey ?>" data-idx="<?= $idx ?>" enctype="multipart/form-data">
            <div class="form-grid">
              <div class="field">
                <label>Název produktu</label>
                <input type="text" name="name" value="<?= htmlspecialchars($prod['name']) ?>" required>
              </div>
              <div class="field">
                <label>Štítek (badge)</label>
                <input type="text" name="badge" value="<?= htmlspecialchars($prod['badge']) ?>">
              </div>
            </div>
            <div class="field">
              <label>Popis</label>
              <textarea name="desc"><?= htmlspecialchars($prod['desc']) ?></textarea>
            </div>
            <div class="field">
              <label>Tagy (oddělené čárkou)</label>
              <input type="text" name="tags" value="<?= htmlspecialchars(implode(', ', $prod['tags'] ?? [])) ?>">
              <div class="hint">Příklad: Vícekomorové, Pasivní standard, Uw 0,7+</div>
            </div>
            <div class="field">
              <label>Odkaz (URL)</label>
              <input type="text" name="link" value="<?= htmlspecialchars($prod['link'] ?? '') ?>">
              <div class="hint">Např. plastova-okna.html (nebo prázdné)</div>
            </div>
            <div class="field">
              <label>Obrázek produktu</label>
              <div class="img-upload" id="imgUpload-<?= $tabKey ?>-<?= $idx ?>">
                <input type="file" name="image" accept="image/*" onchange="previewImg(this,'imgPrev-<?= $tabKey ?>-<?= $idx ?>')">
                <div class="iu-icon"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg></div>
                <div class="iu-text">Klikněte nebo přetáhněte obrázek<br><small>JPG, PNG, WEBP – max 5 MB</small></div>
              </div>
              <div class="img-preview" id="imgPrev-<?= $tabKey ?>-<?= $idx ?>">
                <?php if (!empty($prod['image'])): ?>
                <img src="../<?= htmlspecialchars($prod['image']) ?>" alt="">
                <?php endif; ?>
              </div>
            </div>
            <div style="display:flex;gap:.8rem;align-items:center">
              <button type="submit" class="btn btn-primary btn-sm">Uložit produkt</button>
              <span class="save-status" style="font-size:.8rem;color:#15803d"></span>
            </div>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

  </div>
</div>
<script>
// Tabs
document.querySelectorAll('.tab-btn-adm').forEach(btn=>{
  btn.addEventListener('click',()=>{
    document.querySelectorAll('.tab-btn-adm').forEach(b=>b.classList.remove('active'));
    document.querySelectorAll('.tab-panel-adm').forEach(p=>p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tab-'+btn.dataset.tab).classList.add('active');
  });
});

// Toggle product accordion
function toggleProd(tab, idx) {
  const el = document.getElementById('prod-'+tab+'-'+idx);
  el.classList.toggle('open');
}

// Image preview
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

// AJAX form submit
document.querySelectorAll('.prod-form').forEach(form=>{
  form.addEventListener('submit', async e => {
    e.preventDefault();
    const btn  = form.querySelector('[type=submit]');
    const stat = form.querySelector('.save-status');
    btn.disabled = true;
    btn.textContent = 'Ukládám…';
    const fd = new FormData(form);
    fd.append('action', 'save_product');
    fd.append('tab',    form.dataset.tab);
    fd.append('idx',    form.dataset.idx);
    const res = await fetch('save.php', {method:'POST', body:fd});
    const json = await res.json();
    btn.disabled = false;
    btn.textContent = 'Uložit produkt';
    stat.textContent = json.ok ? '✓ Uloženo' : '✗ Chyba: '+json.msg;
    stat.style.color = json.ok ? '#15803d' : '#dc2626';
    setTimeout(()=>stat.textContent='', 3000);
  });
});
</script>
</body>
</html>
