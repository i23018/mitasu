<?php
// index.php : トップページ（ホーム）
// 今日の達成率と、最も不足している栄養素を表示する
require __DIR__ . '/db.php';

$pageTitle = 'ホーム';
$today = date('Y-m-d');
$profile = getProfile();
$tg = targets($profile['activity']);
$totals = dayTotals($today);
$rates = achievementRates($totals, $tg);

// 達成率が最も低い栄養素を探す
$lowKey = null;
foreach ($rates as $k => $r) {
    if ($lowKey === null || $r < $rates[$lowKey]) { $lowKey = $k; }
}

require __DIR__ . '/header.php';
?>
<h1>ミタス　栄養管理アプリ</h1>
<p class="small"><?= h(date('Y年n月j日')) ?>（今日）の達成率</p>

<?php if ($rates[$lowKey] < 100): ?>
<div class="alert-card">
  今日は<strong><?= h($NUTRIENTS[$lowKey][0]) ?></strong>が不足気味です（達成率 <?= $rates[$lowKey] ?>％）。<br>
  <a href="recommend.php?n=<?= h($lowKey) ?>"><?= h($NUTRIENTS[$lowKey][0]) ?>を補える食材を見る</a>
</div>
<?php else: ?>
<div class="card">今日はすべての栄養素が目標に達しています。</div>
<?php endif; ?>

<div class="card">
<?php foreach ($NUTRIENTS as $k => $nut): ?>
  <div>
    <span class="nutrient-name"><?= h($nut[0]) ?>　<?= $rates[$k] ?>％</span>
    <span class="nutrient-val"><?= fmtNum($totals[$k]) ?> / <?= fmtNum($tg[$k]) ?> <?= h($nut[1]) ?></span>
  </div>
  <div class="bar"><i class="<?= $rates[$k] < 50 ? 'low' : '' ?>" style="width: <?= min(100, $rates[$k]) ?>%"></i></div>
<?php endforeach; ?>
</div>

<div class="menu-grid">
  <a class="menu-card" href="history.php">食事履歴</a>
  <a class="menu-card" href="meal_input.php">食事入力</a>
  <a class="menu-card" href="recommend.php">おすすめ食材</a>
  <a class="menu-card" href="mypage.php">マイページ</a>
</div>

<?php require __DIR__ . '/footer.php'; ?>
