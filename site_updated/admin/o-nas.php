<?php
require_once __DIR__ . '/auth.php';
$content = json_decode(file_get_contents(CONTENT_FILE), true) ?: [];
$onas = $content['o_nas'] ?? [];
?><!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin – O nás</title>
<link href="https://fonts.googleapis.com/css2?family=Mona+Sans:ital,wdth,wght@0,75..125,200..900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="main">
  <div class="topbar">
    <h1>Editace sekce O nás</h1>
  </div>
  <div class="content">
    <div id="globalAlert" class="alert"></div>

    <form id="onasForm" enctype="multipart/form-data">

      <div class="card">
        <div class="card-title">Texty</div>
        <div class="field">
          <label>Nadpis sekce</label>
          <textarea name="title" style="min-height:70px"><?= htmlspecialchars($onas['title'] ?? '') ?></textarea>
          <div class="hint">Pro zalomení řádku použijte Enter</div>
        </div>
        <div class="field">
          <label>Text odstavce 1</label>
          <textarea name="text1"><?= htmlspecialchars($onas['text1'] ?? '') ?></textarea>
        </div>
        <div class="field">
          <label>Text odstavce 2</label>
          <textarea name="text2"><?= htmlspecialchars($onas['text2'] ?? '') ?></textarea>
        </div>
      </div>

      <div class="card">
        <div class="card-title">Podpis / citát</div>
        <div class="form-grid">
          <div class="field">
            <label>Jméno a pozice</label>
            <input type="text" name="sig_name" value="<?= htmlspecialchars($onas['sig_name'] ?? '') ?>">
          </div>
          <div class="field">
            <label>Citát</label>
            <input type="text" name="sig_quote" value="<?= htmlspecialchars($onas['sig_quote'] ?? '') ?>">
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-title">Důvěryhodnostní body</div>
        <?php
        $proofs = $onas['proofs'] ?? [['title'=>'','sub'=>''],['title'=>'','sub'=>''],['title'=>'','sub'=>''],['title'=>'','sub'=>'']];
        foreach ($proofs as $i => $proof):
        ?>
        <div class="form-grid" style="margin-bottom:.5rem">
          <div class="field" style="margin-bottom:0">
            <label>Bod <?= $i+1 ?> – Název</label>
            <input type="text" name="proof_title[]" value="<?= htmlspecialchars($proof['title']) ?>">
          </div>
          <div class="field" style="margin-bottom:0">
            <label>Bod <?= $i+1 ?> – Popis</label>
            <input type="text" name="proof_sub[]" value="<?= htmlspecialchars($proof['sub']) ?>">
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="card">
        <div class="card-title">Fotografie</div>
        <div class="form-grid">
          <div>
            <div class="field">
              <label>Hlavní fotografie (velká)</label>
              <div class="img-upload">
                <input type="file" name="photo_main" accept="image/*" onchange="previewImg(this,'prevMain')">
                <div class="iu-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg></div>
                <div class="iu-text">Nahrát hlavní foto<br><small>JPG, PNG, WEBP</small></div>
              </div>
              <div class="img-preview" id="prevMain">
                <?php if (!empty($onas['photo_main'])): ?>
                <img src="../<?= htmlspecialchars($onas['photo_main']) ?>" alt="">
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div>
            <div class="field">
              <label>Vedlejší fotografie (menší)</label>
              <div class="img-upload">
                <input type="file" name="photo_sec" accept="image/*" onchange="previewImg(this,'prevSec')">
                <div class="iu-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg></div>
                <div class="iu-text">Nahrát vedlejší foto<br><small>JPG, PNG, WEBP</small></div>
              </div>
              <div class="img-preview" id="prevSec">
                <?php if (!empty($onas['photo_sec'])): ?>
                <img src="../<?= htmlspecialchars($onas['photo_sec']) ?>" alt="">
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div style="display:flex;gap:.8rem;align-items:center;margin-bottom:2rem">
        <button type="submit" class="btn btn-primary">Uložit sekci O nás</button>
        <span id="saveStatus" style="font-size:.88rem"></span>
      </div>
    </form>

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

document.getElementById('onasForm').addEventListener('submit', async e => {
  e.preventDefault();
  const btn  = e.target.querySelector('[type=submit]');
  const stat = document.getElementById('saveStatus');
  btn.disabled = true; btn.textContent = 'Ukládám…';
  const fd = new FormData(e.target);
  fd.append('action', 'save_o_nas');
  const res  = await fetch('save.php', {method:'POST', body:fd});
  const json = await res.json();
  btn.disabled = false; btn.textContent = 'Uložit sekci O nás';
  stat.textContent = json.ok ? '✓ Uloženo' : '✗ Chyba: '+json.msg;
  stat.style.color = json.ok ? '#15803d' : '#dc2626';
  setTimeout(()=>stat.textContent='', 4000);
});
</script>
</body>
</html>
