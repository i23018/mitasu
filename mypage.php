<?php
// mypage.php : マイページ
// プロフィールの表示と変更、1日の目標値の表示を行う
require __DIR__ . '/db.php';

$pageTitle = 'マイページ';
$msg = '';

$ageGroups = array('10代', '20代', '30代', '40代', '50代', '60代以上');
$activities = array('低い', 'ふつう', '高い');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ageGroup = isset($_POST['age_group']) ? $_POST['age_group'] : '20代';
    if (!in_array($ageGroup, $ageGroups, true)) { $ageGroup = '20代'; }
    $allergy = isset($_POST['allergy']) ? trim($_POST['allergy']) : '';
    if ($allergy === '') { $allergy = 'なし'; }
    $activity = isset($_POST['activity']) ? $_POST['activity'] : 'ふつう';
    if (!in_array($activity, $activities, true)) { $activity = 'ふつう'; }
    $height = (isset($_POST['height']) && is_numeric($_POST['height'])) ? (float)$_POST['height'] : null;
    $weight = (isset($_POST['weight']) && is_numeric($_POST['weight'])) ? (float)$_POST['weight'] : null;

    $stmt = db()->prepare(
        "REPLACE INTO profile (id, age_group, allergy, activity, height, weight)
         VALUES (1, ?, ?, ?, ?, ?)"
    );
    $stmt->execute(array($ageGroup, $allergy, $activity, $height, $weight));
    $msg = 'プロフィールを保存しました。';
}

$profile = getProfile();
$tg = targets($profile['activity']);

require __DIR__ . '/header.php';
?>
<h1>マイページ</h1>

<?php if ($msg !== ''): ?>
<div class="msg"><?= h($msg) ?></div>
<?php endif; ?>

<form method="post" action="mypage.php">
  <div class="card">
    <label>年齢</label>
    <select name="age_group">
      <?php foreach ($ageGroups as $g): ?>
      <option value="<?= h($g) ?>" <?= $g === $profile['age_group'] ? 'selected' : '' ?>><?= h($g) ?></option>
      <?php endforeach; ?>
    </select>

    <label>アレルギー</label>
    <input type="text" name="allergy" value="<?= h($profile['allergy']) ?>">

    <label>活動量</label>
    <select name="activity">
      <?php foreach ($activities as $a): ?>
      <option value="<?= h($a) ?>" <?= $a === $profile['activity'] ? 'selected' : '' ?>><?= h($a) ?></option>
      <?php endforeach; ?>
    </select>

    <label>身長（cm）</label>
    <input type="number" name="height" step="0.1" value="<?= h($profile['height']) ?>">

    <label>体重（kg）</label>
    <input type="number" name="weight" step="0.1" value="<?= h($profile['weight']) ?>">

    <button type="submit" class="btn">変更を保存する</button>
  </div>
</form>

<div class="card">
  <strong>1日の目標値（目安）</strong>
  <p class="small">エネルギーの目標は活動量「<?= h($profile['activity']) ?>」の場合の値です。</p>
  <table>
    <?php foreach ($NUTRIENTS as $k => $nut): ?>
    <tr>
      <th><?= h($nut[0]) ?></th>
      <td><?= fmtNum($tg[$k]) ?> <?= h($nut[1]) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>

<?php require __DIR__ . '/footer.php'; ?>
