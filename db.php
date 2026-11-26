<?php
// ============================================================
// ミタス 栄養管理アプリ
// db.php : データベース接続と共通処理
// ============================================================

// ★データベース接続情報（自分の環境に合わせて書き換えること）
$DB_HOST = 'localhost';
$DB_NAME = 'db2026';              // 授業で使うデータベース名
$DB_USER = 'ここに自分のユーザー名';  // ★自分のものに書き換える
$DB_PASS = 'ここに自分のパスワード';  // ★自分のものに書き換える

// データベースへ接続する（PDO）。接続は1回だけ行い使い回す。
function db() {
    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
        $pdo = new PDO($dsn, $DB_USER, $DB_PASS, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ));
    }
    return $pdo;
}

// HTML表示用に文字列をエスケープする
function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// 数値の表示用整形（小数第1位まで、末尾の .0 は消す）
function fmtNum($v) {
    $s = number_format((float)$v, 1);
    if (substr($s, -2) === '.0') {
        $s = substr($s, 0, -2);
    }
    return $s;
}

// 栄養素の定義（キー => [表示名, 単位]）
$NUTRIENTS = array(
    'energy'  => array('エネルギー', 'kcal'),
    'protein' => array('たんぱく質', 'g'),
    'fat'     => array('脂質', 'g'),
    'carbs'   => array('炭水化物', 'g'),
    'fiber'   => array('食物繊維', 'g'),
    'calcium' => array('カルシウム', 'mg'),
    'iron'    => array('鉄', 'mg'),
    'vit_a'   => array('ビタミンA', 'µg'),
    'vit_c'   => array('ビタミンC', 'mg'),
    'vit_d'   => array('ビタミンD', 'µg'),
);

// 1日の目標値を返す。エネルギーのみ活動量で変わる。
function targets($activity) {
    $t = array(
        'energy' => 2200, 'protein' => 65, 'fat' => 60, 'carbs' => 320,
        'fiber' => 21, 'calcium' => 750, 'iron' => 7.5,
        'vit_a' => 850, 'vit_c' => 100, 'vit_d' => 8.5,
    );
    if ($activity === '低い') { $t['energy'] = 2000; }
    if ($activity === '高い') { $t['energy'] = 2600; }
    return $t;
}

// プロフィール（1件）を取得する。未登録なら既定値を返す。
function getProfile() {
    $row = db()->query("SELECT * FROM 11profile WHERE id = 1")->fetch();
    if (!$row) {
        return array(
            'age_group' => '20代', 'allergy' => 'なし', 'activity' => 'ふつう',
            'height' => '', 'weight' => '',
        );
    }
    return $row;
}

// 指定した日付の栄養素ごとの合計（全食品の合計）を返す
function dayTotals($date) {
    global $NUTRIENTS;
    $cols = array();
    foreach (array_keys($NUTRIENTS) as $k) {
        $cols[] = "SUM(mi.{$k} * mi.quantity) AS {$k}";
    }
    $sql = "SELECT " . implode(', ', $cols)
         . " FROM 11meals m JOIN 11meal_items mi ON mi.meal_id = m.id"
         . " WHERE m.meal_date = ?";
    $stmt = db()->prepare($sql);
    $stmt->execute(array($date));
    $row = $stmt->fetch();
    $totals = array();
    foreach (array_keys($NUTRIENTS) as $k) {
        $totals[$k] = ($row && $row[$k] !== null) ? (float)$row[$k] : 0.0;
    }
    return $totals;
}

// 食品データベースの全件を、画面（JavaScript）で使う形に変換して返す
function foodsForJs() {
    global $NUTRIENTS;
    $foods = db()->query("SELECT * FROM 11foods ORDER BY id")->fetchAll();
    $out = array();
    foreach ($foods as $f) {
        $nut = array();
        foreach (array_keys($NUTRIENTS) as $k) { $nut[$k] = (float)$f[$k]; }
        $out[] = array(
            'food_id' => (int)$f['id'], 'name' => $f['name'],
            'unit' => $f['unit'], 'nutrients' => $nut,
        );
    }
    return $out;
}

// 食事記録の明細（食品）を 11meal_items テーブルへ登録する。
// 食品データベースにある食品は、サーバー側で栄養素の値を取り直して保存する
// （画面から送られた値をそのまま信用しないため）。手入力の食品は栄養素を0とする。
function insertMealItems($mealId, $items) {
    global $NUTRIENTS;
    $keys = array_keys($NUTRIENTS);
    $cols = implode(', ', $keys);
    $marks = implode(', ', array_fill(0, count($keys), '?'));
    $ins = db()->prepare(
        "INSERT INTO 11meal_items (meal_id, food_id, food_name, quantity, {$cols})
         VALUES (?, ?, ?, ?, {$marks})"
    );
    $foodStmt = db()->prepare("SELECT * FROM 11foods WHERE id = ?");
    foreach ($items as $it) {
        $nut = array();
        foreach ($keys as $k) { $nut[$k] = 0; }
        $foodId = null;
        $name = isset($it['name']) ? (string)$it['name'] : '';
        if (!empty($it['food_id'])) {
            $foodStmt->execute(array((int)$it['food_id']));
            $food = $foodStmt->fetch();
            if ($food) {
                $foodId = (int)$food['id'];
                $name = $food['name'];
                foreach ($keys as $k) { $nut[$k] = (float)$food[$k]; }
            }
        }
        if ($name === '') { continue; }
        $qty = max(1, (int)(isset($it['qty']) ? $it['qty'] : 1));
        $params = array($mealId, $foodId, $name, $qty);
        foreach ($keys as $k) { $params[] = $nut[$k]; }
        $ins->execute($params);
    }
}

// 達成率（％、整数）を栄養素ごとに計算する
function achievementRates($totals, $tg) {
    $rates = array();
    foreach ($tg as $k => $target) {
        $rates[$k] = ($target > 0) ? (int)round($totals[$k] / $target * 100) : 0;
    }
    return $rates;
}
