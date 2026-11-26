<?php
// meal_delete.php : 食事記録の削除
// 確認画面を表示し、「削除する」が押されたら明細と一緒に削除する
require __DIR__ . '/db.php';

$pageTitle = '記録の削除';
$id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

$stmt = db()->prepare("SELECT * FROM 11meals WHERE id = ?");
$stmt->execute(array($id));
$meal = $stmt->fetch();

if ($meal && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $meal['meal_date'];
    $pdo = db();
    $pdo->beginTransaction();
    $delItems = $pdo->prepare("DELETE FROM 11meal_items WHERE meal_id = ?");
    $delItems->execute(array($id));
    $delMeal = $pdo->prepare("DELETE FROM 11meals WHERE id = ?");
    $delMeal->execute(array($id));
    $pdo->commit();
    header('Location: history.php?date=' . urlencode($date) . '&msg=' . urlencode('削除しました'));
    exit;
}

$items = array();
if ($meal) {
    $itemStmt = db()->prepare("SELECT * FROM 11meal_items WHERE meal_id = ? ORDER BY id");
    $itemStmt->execute(array($id));
    $items = $itemStmt->fetchAll();
}

require __DIR__ . '/header.php';
?>
<h1>記録の削除</h1>

<?php if (!$meal): ?>
<div class="card">指定された記録が見つかりません。<a href="history.php">食事履歴に戻る</a></div>
<?php else: ?>
<div class="card">
  <p>次の記録を削除しますか？</p>
  <table>
    <tr><th>日付</th><td><?= h($meal['meal_date']) ?></td></tr>
    <tr><th>食事</th><td><?= h($meal['meal_type']) ?></td></tr>
  </table>
  <?php foreach ($items as $it): ?>
  <div class="item-row">
    <span class="nm"><?= h($it['food_name']) ?></span>
    <span>× <?= (int)$it['quantity'] ?></span>
  </div>
  <?php endforeach; ?>
</div>

<form method="post" action="meal_delete.php">
  <input type="hidden" name="id" value="<?= (int)$id ?>">
  <button type="submit" class="btn danger">削除する</button>
  <a class="btn sub" href="history.php?date=<?= h($meal['meal_date']) ?>">キャンセル</a>
</form>
<?php endif; ?>

<?php require __DIR__ . '/footer.php'; ?>
