<?php
// meal_edit.php : 食事記録の変更
// 記録を読み込んで編集フォームに表示し、保存時は明細を入れ替える
require __DIR__ . '/db.php';

$pageTitle = '記録の変更';
$id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

$stmt = db()->prepare("SELECT * FROM 11meals WHERE id = ?");
$stmt->execute(array($id));
$meal = $stmt->fetch();

if ($meal && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['items_json'])) {
    $date = isset($_POST['date']) ? $_POST['date'] : $meal['meal_date'];
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) { $date = $meal['meal_date']; }
    $mealType = isset($_POST['meal_type']) ? $_POST['meal_type'] : $meal['meal_type'];
    $mealTypes = array('朝食', '昼食', '夕食', '間食・夜食');
    if (!in_array($mealType, $mealTypes, true)) { $mealType = $meal['meal_type']; }
    $items = json_decode($_POST['items_json'], true);
    if (is_array($items) && count($items) > 0) {
        $pdo = db();
        $pdo->beginTransaction();
        $upd = $pdo->prepare("UPDATE 11meals SET meal_date = ?, meal_type = ? WHERE id = ?");
        $upd->execute(array($date, $mealType, $id));
        $del = $pdo->prepare("DELETE FROM 11meal_items WHERE meal_id = ?");
        $del->execute(array($id));
        insertMealItems($id, $items);
        $pdo->commit();
        header('Location: history.php?date=' . urlencode($date) . '&msg=' . urlencode('変更を保存しました'));
        exit;
    }
}

// 編集フォーム用の初期データを作る
$initialRows = array();
$foodsForJs = foodsForJs();
$unitMap = array();
foreach ($foodsForJs as $f) { $unitMap[$f['food_id']] = $f['unit']; }

if ($meal) {
    $itemStmt = db()->prepare("SELECT * FROM 11meal_items WHERE meal_id = ? ORDER BY id");
    $itemStmt->execute(array($id));
    foreach ($itemStmt->fetchAll() as $it) {
        $nut = array();
        foreach (array_keys($NUTRIENTS) as $k) { $nut[$k] = (float)$it[$k]; }
        $fid = ($it['food_id'] === null) ? null : (int)$it['food_id'];
        $initialRows[] = array(
            'food_id' => $fid,
            'name' => $it['food_name'],
            'unit' => ($fid !== null && isset($unitMap[$fid])) ? $unitMap[$fid] : '',
            'qty' => (int)$it['quantity'],
            'nutrients' => $nut,
        );
    }
    $date = $meal['meal_date'];
    $mealType = $meal['meal_type'];
}

require __DIR__ . '/header.php';
?>
<h1>記録の変更</h1>

<?php if (!$meal): ?>
<div class="card">指定された記録が見つかりません。<a href="history.php">食事履歴に戻る</a></div>
<?php else: ?>
<form id="mealForm" method="post" action="meal_edit.php">
  <input type="hidden" name="id" value="<?= (int)$id ?>">
  <label>日付</label>
  <input type="date" name="date" value="<?= h($date) ?>">

  <label>どの食事か</label>
  <select name="meal_type">
    <?php foreach (array('朝食', '昼食', '夕食', '間食・夜食') as $t): ?>
    <option value="<?= h($t) ?>" <?= $t === $mealType ? 'selected' : '' ?>><?= h($t) ?></option>
    <?php endforeach; ?>
  </select>

  <?php require __DIR__ . '/editor.php'; ?>

  <button type="submit" class="btn">変更を保存する</button>
  <a class="btn sub" href="history.php?date=<?= h($date) ?>">キャンセル</a>
</form>
<?php endif; ?>

<?php require __DIR__ . '/footer.php'; ?>
