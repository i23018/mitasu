ミタス 栄養管理アプリ（PHP版）設置手順

――ファイル一覧――
index.php        トップページ（ホーム：今日の達成率）
history.php      食事履歴（日付検索・キーワード検索・変更・削除）
meal_input.php   食事入力（入力画面）
meal_confirm.php 食事入力（確認画面）
meal_complete.php 食事入力（完了画面：DBへ登録）
meal_edit.php    食事記録の変更
meal_delete.php  食事記録の削除
recommend.php    おすすめ食材
mypage.php       マイページ（プロフィール変更・目標値）
db.php           データベース接続と共通処理
header.php       共通ヘッダー
footer.php       共通フッター（下部ナビ）
editor.php       食品入力エディタ（入力・変更で共通利用）
style.css        共通スタイル
schema.sql       テーブル作成と初期データ投入用SQL
title.txt        サイトタイトル（制作条件で必要なファイル）

――設置手順――
1. MySQLで、授業のデータベース（db2026）を選んだ状態で schema.sql を実行し、
   テーブルと初期データを作成する。
2. db.php の接続情報を自分のものに書き換える。
   $DB_USER（ユーザー名）と $DB_PASS（パスワード）。
   データベース名が異なる場合は $DB_NAME も書き換える。
3. 全ファイルをサーバーの public_html フォルダに置く。
   （トップページは index.php になる）
4. title.txt が public_html にあることを確認し、
   http://tbsv.jp/db/2026/ で「ミタス」と表示されることを確認する。
5. ブラウザで https://tbsv.jp/~自分のユーザーID/ を開き、動作を確認する。

――注意――
・栄養素の値は1食分あたりの概算値です。
・サンプルの食事記録が今日の日付で2件入っています（朝食・昼食）。
  不要な場合は食事履歴から削除してください。
・各ファイルの処理内容は「処理のまとめ.txt」にまとめています。
