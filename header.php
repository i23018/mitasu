<?php
// 共通ヘッダー。各ページで $pageTitle を設定し、db.php を読み込んでから require する。
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($pageTitle) ?> | ミタス 栄養管理アプリ</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="app-header">
  <div class="app-title">ミタス</div>
  <div class="app-subtitle">栄養管理アプリ</div>
</header>
<main class="container">
