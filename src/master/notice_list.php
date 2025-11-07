<?php
session_start();
require_once('../../asset/db_connect.php');

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$stmt = $pdo->query("SELECT * FROM executive_news ORDER BY created_at DESC");
$news_all = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お知らせ一覧 | 麻生祭2025</title>
    <link rel="stylesheet" href="master.css">
</head>

<body>
    <header>📢 実行委員向けお知らせ一覧</header>

    <div class="container">
        <div class="table-wrapper">
            <?php if (empty($news_all)): ?>
                <p>現在お知らせはありません。</p>
            <?php else: ?>
                <ul style="list-style:none; padding:0;">
                    <?php foreach ($news_all as $n): ?>
                        <li style="margin-bottom:15px;">
                            <a href="notice_view.php?id=<?= $n['id'] ?>" style="text-decoration:none; color:#333;">
                                <strong><?= htmlspecialchars($n['title']) ?></strong>
                            </a><br>
                            <small><?= htmlspecialchars(date('Y/m/d', strtotime($n['created_at']))) ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <a href="master_menu.php" class="back-btn">← メニューへ戻る</a>
    </div>

    <footer>© 2025 麻生祭実行委員会</footer>
</body>

</html>