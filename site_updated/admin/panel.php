<?php
require_once __DIR__ . '/auth.php';

$visits = json_decode(file_get_contents(VISITS_FILE), true) ?: ['total'=>0,'daily'=>[]];
$today  = date('Y-m-d');
$todayV = $visits['daily'][$today] ?? 0;

// Tento týden
$weekV = 0;
for ($i = 0; $i < 7; $i++) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $weekV += $visits['daily'][$d] ?? 0;
}
// Tento měsíc
$monthV = 0;
$prefix = date('Y-m');
foreach ($visits['daily'] as $d => $v) {
    if (strpos($d, $prefix) === 0) $monthV += $v;
}

// Posledních 14 dní pro graf
$chartLabels = [];
$chartData   = [];
for ($i = 13; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $chartLabels[] = date('j.n', strtotime($d));
    $chartData[]   = $visits['daily'][$d] ?? 0;
}
?><!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin – Přehled</title>
<link href="https://fonts.googleapis.com/css2?family=Mona+Sans:ital,wdth,wght@0,75..125,200..900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
</head>
<body>
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="main">
  <div class="topbar">
    <h1>Přehled návštěvnosti</h1>
    <div class="topbar-right">
      <span style="font-size:.8rem;color:#8a90a8"><?= date('j. n. Y') ?></span>
    </div>
  </div>
  <div class="content">

    <div class="stats-row">
      <div class="stat-card accent">
        <div class="sc-label">Celkem návštěv</div>
        <div class="sc-val"><?= number_format($visits['total'], 0, ',', ' ') ?></div>
        <div class="sc-sub">od spuštění webu</div>
      </div>
      <div class="stat-card">
        <div class="sc-label">Dnes</div>
        <div class="sc-val"><?= $todayV ?></div>
        <div class="sc-sub"><?= date('j. n. Y') ?></div>
      </div>
      <div class="stat-card">
        <div class="sc-label">Tento týden</div>
        <div class="sc-val"><?= $weekV ?></div>
        <div class="sc-sub">posledních 7 dní</div>
      </div>
      <div class="stat-card">
        <div class="sc-label">Tento měsíc</div>
        <div class="sc-val"><?= $monthV ?></div>
        <div class="sc-sub"><?= date('F Y') ?></div>
      </div>
    </div>

    <div class="card">
      <div class="card-title">Návštěvy – posledních 14 dní</div>
      <div class="chart-wrap">
        <canvas id="visitsChart"></canvas>
      </div>
    </div>

    <div class="card">
      <div class="card-title">Nejnavštěvovanější dny (Top 10)</div>
      <table class="table">
        <thead><tr><th>Datum</th><th>Návštěvy</th></tr></thead>
        <tbody>
        <?php
        $sorted = $visits['daily'];
        arsort($sorted);
        $top = array_slice($sorted, 0, 10, true);
        foreach ($top as $d => $v):
        ?>
        <tr>
          <td><?= date('j. n. Y', strtotime($d)) ?></td>
          <td><?= $v ?></td>
        </tr>
        <?php endforeach; if (empty($top)): ?>
        <tr><td colspan="2" style="color:#8a90a8;text-align:center;padding:2rem">Zatím žádná data. Návštěvy se zaznamenávají automaticky.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="card">
      <div class="card-title">Rychlé odkazy na editaci</div>
      <div style="display:flex;gap:.8rem;flex-wrap:wrap">
        <a href="produkty.php" class="btn btn-primary">Editovat produkty</a>
        <a href="o-nas.php"    class="btn btn-primary">Editovat O nás</a>
        <a href="projekty.php" class="btn btn-primary">Editovat projekty</a>
      </div>
    </div>

  </div>
</div>
<script>
const ctx = document.getElementById('visitsChart').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: <?= json_encode($chartLabels) ?>,
    datasets: [{
      label: 'Návštěvy',
      data: <?= json_encode($chartData) ?>,
      backgroundColor: 'rgba(26,95,168,.15)',
      borderColor: '#1a5fa8',
      borderWidth: 2,
      borderRadius: 6,
      borderSkipped: false,
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#8a90a8' } },
      y: { grid: { color: '#f0f2f6' }, ticks: { font: { size: 11 }, color: '#8a90a8', precision: 0 }, beginAtZero: true }
    }
  }
});
</script>
</body>
</html>
