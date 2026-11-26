-- ============================================================
-- ミタス 栄養管理アプリ データベース構築用SQL
-- 使い方：データベース（db2026）を選んだ状態で、このファイルを実行する
-- ※すでにテーブルがある状態で再実行すると、食品データなどが重複します
-- ============================================================

-- プロフィール（利用者は1人なので id=1 の1件だけを使う）
CREATE TABLE IF NOT EXISTS 11profile (
  id INT PRIMARY KEY,
  age_group VARCHAR(10),
  allergy VARCHAR(100),
  activity VARCHAR(10),
  height DECIMAL(5,1),
  weight DECIMAL(5,1)
);

-- 食品データベース（1食分あたりの栄養素の概算値）
CREATE TABLE IF NOT EXISTS 11foods (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  unit VARCHAR(20),
  energy DECIMAL(7,1),
  protein DECIMAL(6,1),
  fat DECIMAL(6,1),
  carbs DECIMAL(6,1),
  fiber DECIMAL(6,1),
  calcium DECIMAL(7,1),
  iron DECIMAL(6,1),
  vit_a DECIMAL(8,1),
  vit_c DECIMAL(6,1),
  vit_d DECIMAL(6,1)
);

-- 食事記録（1回の食事）
CREATE TABLE IF NOT EXISTS 11meals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  meal_date DATE NOT NULL,
  meal_type VARCHAR(10) NOT NULL
);

-- 食事記録の明細（食事に含まれる食品。栄養素は登録時点の値をコピーして持つ）
CREATE TABLE IF NOT EXISTS 11meal_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  meal_id INT NOT NULL,
  food_id INT,
  food_name VARCHAR(50) NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  energy DECIMAL(7,1),
  protein DECIMAL(6,1),
  fat DECIMAL(6,1),
  carbs DECIMAL(6,1),
  fiber DECIMAL(6,1),
  calcium DECIMAL(7,1),
  iron DECIMAL(6,1),
  vit_a DECIMAL(8,1),
  vit_c DECIMAL(6,1),
  vit_d DECIMAL(6,1),
  FOREIGN KEY (meal_id) REFERENCES 11meals(id) ON DELETE CASCADE
);

-- プロフィールの初期値
INSERT INTO 11profile (id, age_group, allergy, activity, height, weight)
VALUES (1, '20代', 'なし', 'ふつう', NULL, NULL);

-- 食品データ（25品目）
INSERT INTO 11foods (name, unit, energy, protein, fat, carbs, fiber, calcium, iron, vit_a, vit_c, vit_d) VALUES
('ごはん', '1杯（150g）', 234, 3.8, 0.5, 55.7, 0.5, 5, 0.2, 0, 0, 0),
('食パン', '1枚（60g）', 149, 5.6, 2.6, 26.6, 1.3, 17, 0.3, 0, 0, 0),
('うどん', '1玉（250g）', 263, 6.5, 1.0, 54.0, 2.0, 15, 0.5, 0, 0, 0),
('牛乳', '1杯（200ml）', 134, 6.6, 7.8, 9.6, 0, 220, 0, 76, 2, 0.6),
('卵', '1個（50g）', 76, 6.2, 5.2, 0.2, 0, 23, 0.9, 75, 0, 0.9),
('納豆', '1パック（50g）', 100, 8.3, 5.0, 6.1, 3.4, 45, 1.7, 0, 0, 0),
('豆腐', '1/3丁（100g）', 72, 6.6, 4.2, 1.6, 0.4, 93, 0.9, 0, 0, 0),
('味噌汁', '1杯', 40, 2.2, 1.0, 5.0, 0.8, 30, 0.5, 10, 1, 0),
('鶏の唐揚げ', '1皿（100g）', 290, 16.0, 20.0, 8.0, 0.3, 10, 0.6, 15, 1, 0.2),
('焼き鮭', '1切れ（80g）', 133, 17.9, 6.4, 0.1, 0, 12, 0.4, 26, 1, 25.6),
('さばの塩焼き', '1切れ（100g）', 247, 20.6, 16.8, 0.2, 0, 12, 1.1, 14, 0, 11.0),
('豚の生姜焼き', '1皿（100g）', 230, 17.0, 13.0, 6.0, 0.5, 8, 0.7, 5, 2, 0.3),
('サラダ', '1皿（100g）', 20, 1.0, 0.2, 4.0, 1.5, 30, 0.4, 60, 15, 0),
('ほうれん草のおひたし', '1皿（80g）', 16, 2.6, 0.3, 2.5, 2.9, 79, 1.6, 280, 15, 0),
('ブロッコリー', '1皿（80g）', 30, 3.5, 0.4, 4.2, 3.6, 31, 0.8, 54, 96, 0),
('にんじん', '1/2本（50g）', 18, 0.3, 0.1, 4.4, 1.4, 14, 0.1, 345, 2, 0),
('トマト', '1個（150g）', 28, 1.1, 0.2, 5.9, 1.5, 11, 0.3, 75, 23, 0),
('バナナ', '1本（100g）', 86, 1.1, 0.2, 22.5, 1.1, 6, 0.3, 5, 16, 0),
('りんご', '1個（250g）', 135, 0.5, 0.5, 36.0, 3.8, 8, 0.3, 8, 10, 0),
('ヨーグルト', '1個（100g）', 62, 3.6, 3.0, 4.9, 0, 120, 0, 33, 1, 0.1),
('チーズ', '1枚（20g）', 68, 4.5, 5.2, 0.3, 0, 126, 0.1, 46, 0, 0.1),
('アーモンド', '10粒（14g）', 84, 3.0, 7.4, 2.7, 1.5, 34, 0.5, 0, 0, 0),
('ひじきの煮物', '1皿（50g）', 45, 1.5, 1.0, 8.0, 2.7, 70, 1.4, 110, 0, 0),
('レバー', '1皿（50g）', 55, 9.4, 1.6, 0.3, 0, 4, 4.5, 7000, 10, 0.1),
('牡蠣', '5個（100g）', 60, 6.6, 1.4, 4.7, 0, 88, 1.9, 13, 3, 0);

-- サンプル記録（今日の日付で入ります。不要ならこのブロックを消してから実行）
INSERT INTO 11meals (meal_date, meal_type) VALUES (CURDATE(), '朝食');
SET @m1 = LAST_INSERT_ID();
INSERT INTO 11meal_items (meal_id, food_id, food_name, quantity, energy, protein, fat, carbs, fiber, calcium, iron, vit_a, vit_c, vit_d)
SELECT @m1, id, name, 1, energy, protein, fat, carbs, fiber, calcium, iron, vit_a, vit_c, vit_d
FROM 11foods WHERE name IN ('ごはん', '味噌汁', '卵');

INSERT INTO 11meals (meal_date, meal_type) VALUES (CURDATE(), '昼食');
SET @m2 = LAST_INSERT_ID();
INSERT INTO 11meal_items (meal_id, food_id, food_name, quantity, energy, protein, fat, carbs, fiber, calcium, iron, vit_a, vit_c, vit_d)
SELECT @m2, id, name, 1, energy, protein, fat, carbs, fiber, calcium, iron, vit_a, vit_c, vit_d
FROM 11foods WHERE name IN ('鶏の唐揚げ', 'ごはん', 'サラダ');
