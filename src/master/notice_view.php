<?php
session_start();
require_once('../../asset/db_connect.php');

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// IDチェック
$id = $_GET['id'] ?? '';
if (!is_numeric($id)) {
    header('Location: master_menu.php');
    exit();
}

// お知らせ取得
$stmt = $pdo->prepare("SELECT * FROM executive_news WHERE id = ?");
$stmt->execute([$id]);
$notice = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$notice) {
    header('Location: master_menu.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($notice['title']) ?> | 実行委員お知らせ</title>
    <link rel="stylesheet" href="master.css">
</head>

<body>
    <header>📢 実行委員お知らせ</header>

    <div class="container">
        <div class="table-wrapper" style="padding:20px;">
            <h2><?= htmlspecialchars($notice['title']) ?></h2>
            <p style="color:#777; font-size:0.9em;">
                投稿日：<?= htmlspecialchars(date('Y/m/d H:i', strtotime($notice['created_at']))) ?><br>
                作成者：<?= htmlspecialchars($notice['created_by']) ?>
            </p>

            <div style="margin-top:15px; line-height:1.8;">
                <?= nl2br($notice['content']) ?>
            </div>

            <div style="margin-top:20px;">
                <a href="master_menu.php" class="back-btn">← お知らせ一覧に戻る</a>
            </div>
        </div>
    </div>

    <footer>© 2025 麻生祭実行委員会</footer>
</body>

</html>