<?php
// meal_complete.php : 食事入力（完了ステップ）
// 確認画面から送られた内容をデータベースへ登録し、完了を表示する
require __DIR__ . '/db.php';

$pageTitle = '登録完了';
$done = false;
$date = '';
$mealType = '';
$items = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['items_json'])) {
    $date = isset($_POST['date']) ? $_POST['date'] : '';
    $mealType = isset($_POST['meal_type']) ? $_POST['meal_type'] : '';
    $decoded = json_decode($_POST['items_json'], true);
    if (is_array($decoded)) { $items = $decoded; }

    $mealTypes = array('朝食', '昼食', '夕食', '間食・夜食');
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
        && in_array($mealType, $mealTypes, true)
        && count($items) > 0) {
        $pdo = db();
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO 11meals (meal_date, meal_type) VALUES (?, ?)");
        $stmt->execute(array($date, $mealType));
        $mealId = (int)$pdo->lastInsertId();
        insertMealItems($mealId, $items);
        $pdo->commit();
        $done = true;
    }
}

require __DIR__ . '/header.php';
?>
<h1>登録完了</h1>

<?php if ($done): ?>
<div class="card">
  <p>次の内容で登録しました。</p>
  <table>
    <tr><th>日付</th><td><?= h($date) ?></td></tr>
    <tr><th>食事</th><td><?= h($mealType) ?></td></tr>
    <tr><th>品数</th><td><?= count($items) ?> 品</td></tr>
  </table>
</div>
<p>
  <a class="btn" href="index.php">ホームへ戻る</a>
  <a class="btn sub" href="history.php?date=<?= h($date) ?>">食事履歴を見る</a>
</p>
<?php else: ?>
<div class="card">登録できる内容がありませんでした。<a href="meal_input.php">食事入力に戻る</a></div>
<?php endif; ?>

<?php require __DIR__ . '/footer.php'; ?>
