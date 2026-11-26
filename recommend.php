<?php
// recommend.php : おすすめ食材
// 今日の達成率が最も低い栄養素を自動判定し、それを多く含む食材を提案する
// 栄養素のボタンを押せば、自分で選んだ栄養素のおすすめも見られる
require __DIR__ . '/db.php';

$pageTitle = 'おすすめ食材';
$today = date('Y-m-d');
$profile = getProfile();
$tg = targets($profile['activity']);
$totals = dayTotals($today);
$rates = achievementRates($totals, $tg);

// 栄養素の指定があればそれを使い、なければ達成率が最も低い栄養素を自動判定する
$auto = true;
$key = isset($_GET['n']) ? $_GET['n'] : '';
if ($key !== '' && array_key_exists($key, $NUTRIENTS)) {
    $auto = false;
} else {
    $key = null;
    foreach ($rates as $k => $r) {
        if ($key === null || $r < $rates[$key]) { $key = $k; }
    }
}

// その栄養素を多く含む食材を、含有量の多い順に5件取得する
// （$key は $NUTRIENTS のキーであることを確認済みなので、列名として使ってよい）
$stmt = db()->query(
    "SELECT * FROM foods WHERE {$key} > 0 ORDER BY {$key} DESC LIMIT 5"
);
$foods = $stmt->fetchAll();

require __DIR__ . '/header.php';
?>
<h1>おすすめ食材</h1>

<div class="chips">
  <?php foreach ($NUTRIENTS as $k => $nut): ?>
  <a class="chip <?= $k === $key ? 'on' : '' ?>" href="recommend.php?n=<?= h($k) ?>"><?= h($nut[0]) ?></a>
  <?php endforeach; ?>
</div>

<div class="card">
  <?php if ($auto && $rates[$key] < 100): ?>
  <p>今日は<strong><?= h($NUTRIENTS[$key][0]) ?></strong>が不足しているので（達成率 <?= $rates[$key] ?>％）、
  <?= h($NUTRIENTS[$key][0]) ?>を多く含む食材がおすすめです。</p>
  <?php elseif ($auto): ?>
  <p>今日はすべての栄養素が目標に達しています。参考に、
  <strong><?= h($NUTRIENTS[$key][0]) ?></strong>を多く含む食材です。</p>
  <?php else: ?>
  <p><strong><?= h($NUTRIENTS[$key][0]) ?></strong>を多く含む食材です
  （今日の達成率 <?= $rates[$key] ?>％）。</p>
  <?php endif; ?>

  <?php foreach ($foods as $i => $f): ?>
  <div class="item-row">
    <span class="nm"><span class="rank"><?= $i + 1 ?>位</span><?= h($f['name']) ?>
      <span class="small">（<?= h($f['unit']) ?>）</span></span>
    <span><?= h($NUTRIENTS[$key][0]) ?> <?= fmtNum($f[$key]) ?> <?= h($NUTRIENTS[$key][1]) ?></span>
  </div>
  <?php endforeach; ?>
  <?php if (count($foods) === 0): ?>
  <p>該当する食材がありません。</p>
  <?php endif; ?>
</div>

<p class="small">含有量は1食分あたりの概算値です。</p>

<?php require __DIR__ . '/footer.php'; ?>
