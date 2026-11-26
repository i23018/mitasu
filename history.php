<?php
// history.php : 食事履歴
// 日付検索・キーワード検索、記録の変更・削除、その日の合計と達成率を表示する
require __DIR__ . '/db.php';

$pageTitle = '食事履歴';

// 日付（不正な形式なら今日にする）
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) { $date = date('Y-m-d'); }
$kw = isset($_GET['kw']) ? trim($_GET['kw']) : '';
$prev = date('Y-m-d', strtotime($date . ' -1 day'));
$next = date('Y-m-d', strtotime($date . ' +1 day'));

// その日の記録を取得（朝食→昼食→夕食→間食・夜食の順）
$stmt = db()->prepare(
    "SELECT * FROM 11meals WHERE meal_date = ?
     ORDER BY FIELD(meal_type, '朝食', '昼食', '夕食', '間食・夜食'), id"
);
$stmt->execute(array($date));
$meals = $stmt->fetchAll();

$itemStmt = db()->prepare("SELECT * FROM 11meal_items WHERE meal_id = ? ORDER BY id");

// その日の合計と達成率
$profile = getProfile();
$tg = targets($profile['activity']);
$totals = dayTotals($date);
$rates = achievementRates($totals, $tg);

require __DIR__ . '/header.php';
?>
<h1>食事履歴</h1>

<?php if (isset($_GET['msg'])): ?>
<div class="msg"><?= h($_GET['msg']) ?></div>
<?php endif; ?>

<form method="get" action="history.php">
  <label>日付で検索</label>
  <div class="date-nav">
    <a href="history.php?date=<?= h($prev) ?>&kw=<?= h($kw) ?>">← 前日</a>
    <input type="date" name="date" value="<?= h($date) ?>" style="flex:1; margin:0;">
    <a href="history.php?date=<?= h($next) ?>&kw=<?= h($kw) ?>">翌日 →</a>
  </div>
  <label>食品名で検索</label>
  <input type="text" name="kw" value="<?= h($kw) ?>" placeholder="例：唐揚げ">
  <button type="submit" class="btn">検索</button>
</form>

<h2><?= h(date('Y年n月j日', strtotime($date))) ?> の記録</h2>

<?php
$shown = 0;
foreach ($meals as $meal):
    $itemStmt->execute(array($meal['id']));
    $items = $itemStmt->fetchAll();
    // キーワードがある場合は食品名で絞り込む
    if ($kw !== '') {
        $filtered = array();
        foreach ($items as $it) {
            if (mb_strpos($it['food_name'], $kw) !== false) { $filtered[] = $it; }
        }
        $items = $filtered;
    }
    if (count($items) === 0) { continue; }
    $shown++;
?>
<div class="card">
  <div class="meal-head">
    <span class="meal-type"><?= h($meal['meal_type']) ?></span>
    <span>
      <a class="btn small sub" href="meal_edit.php?id=<?= (int)$meal['id'] ?>">変更</a>
      <a class="btn small danger" href="meal_delete.php?id=<?= (int)$meal['id'] ?>">削除</a>
    </span>
  </div>
  <?php foreach ($items as $it): ?>
  <div class="item-row">
    <span class="nm"><?= h($it['food_name']) ?></span>
    <span>× <?= (int)$it['quantity'] ?></span>
  </div>
  <?php endforeach; ?>
</div>
<?php endforeach; ?>

<?php if ($shown === 0): ?>
<div class="card">この条件の記録はありません。</div>
<?php endif; ?>

<div class="card">
  <strong>この日の合計</strong>
  <table>
    <?php foreach ($NUTRIENTS as $k => $nut): ?>
    <tr>
      <th><?= h($nut[0]) ?></th>
      <td><?= fmtNum($totals[$k]) ?> <?= h($nut[1]) ?></td>
      <td>達成率 <?= $rates[$k] ?>％</td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>

<?php require __DIR__ . '/footer.php'; ?>
