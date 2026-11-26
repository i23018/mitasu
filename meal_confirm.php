<?php
// meal_confirm.php : 食事入力（確認ステップ）
// 入力内容を表示し、「登録する」で meal_complete.php へ、
// 「戻って修正」で meal_input.php へ、内容を引き継いで送る
require __DIR__ . '/db.php';

$pageTitle = '入力内容の確認';

$date = isset($_POST['date']) ? $_POST['date'] : '';
$mealType = isset($_POST['meal_type']) ? $_POST['meal_type'] : '';
$itemsJson = isset($_POST['items_json']) ? $_POST['items_json'] : '';
$items = json_decode($itemsJson, true);
if (!is_array($items)) { $items = array(); }

require __DIR__ . '/header.php';
?>
<h1>入力内容の確認</h1>

<?php if (count($items) === 0): ?>
<div class="card">入力内容がありません。<a href="meal_input.php">食事入力に戻る</a></div>
<?php else: ?>
<div class="card">
  <p>次の内容で登録しますか？</p>
  <table>
    <tr><th>日付</th><td><?= h($date) ?></td></tr>
    <tr><th>食事</th><td><?= h($mealType) ?></td></tr>
  </table>
  <h2>食事内容</h2>
  <?php foreach ($items as $it): ?>
  <div class="item-row">
    <span class="nm"><?= h(isset($it['name']) ? $it['name'] : '') ?></span>
    <span>× <?= (int)(isset($it['qty']) ? $it['qty'] : 1) ?></span>
  </div>
  <?php endforeach; ?>
</div>

<form method="post" action="">
  <input type="hidden" name="date" value="<?= h($date) ?>">
  <input type="hidden" name="meal_type" value="<?= h($mealType) ?>">
  <input type="hidden" name="items_json" value="<?= h($itemsJson) ?>">
  <button type="submit" class="btn sub" formaction="meal_input.php">戻って修正</button>
  <button type="submit" class="btn" formaction="meal_complete.php">登録する</button>
</form>
<?php endif; ?>

<?php require __DIR__ . '/footer.php'; ?>
