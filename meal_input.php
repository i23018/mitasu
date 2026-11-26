<?php
// meal_input.php : 食事入力（入力ステップ）
// 日付・食事区分・食品を入力し、確認画面（meal_confirm.php）へ送る
require __DIR__ . '/db.php';

$pageTitle = '食事入力';

// 確認画面から「戻って修正」で戻ってきた場合は、入力内容を引き継ぐ
$date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) { $date = date('Y-m-d'); }
$mealType = isset($_POST['meal_type']) ? $_POST['meal_type'] : '朝食';
$mealTypes = array('朝食', '昼食', '夕食', '間食・夜食');
if (!in_array($mealType, $mealTypes, true)) { $mealType = '朝食'; }

$initialRows = array();
if (isset($_POST['items_json'])) {
    $decoded = json_decode($_POST['items_json'], true);
    if (is_array($decoded)) { $initialRows = $decoded; }
}

$foodsForJs = foodsForJs();

require __DIR__ . '/header.php';
?>
<h1>食事入力</h1>

<form id="mealForm" method="post" action="meal_confirm.php">
  <label>日付</label>
  <input type="date" name="date" value="<?= h($date) ?>">

  <label>どの食事か</label>
  <select name="meal_type">
    <?php foreach ($mealTypes as $t): ?>
    <option value="<?= h($t) ?>" <?= $t === $mealType ? 'selected' : '' ?>><?= h($t) ?></option>
    <?php endforeach; ?>
  </select>

  <?php require __DIR__ . '/editor.php'; ?>

  <button type="submit" class="btn">確認へ進む</button>
</form>

<?php require __DIR__ . '/footer.php'; ?>
