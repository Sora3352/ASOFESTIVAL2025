<?php
require_once('../asset/db_connect.php');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: index.php');
    exit();
}

$stmt = $pdo->prepare("SELECT title, content, created_at FROM news WHERE id = :id LIMIT 1");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$news = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$news) {
    $news = [
        'title' => 'お知らせが見つかりません',
        'content' => '指定されたお知らせは存在しません。',
        'created_at' => null
    ];
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($news['title'], ENT_QUOTES, 'UTF-8') ?> | 麻生祭2025</title>
    <link rel="stylesheet" href="../asset/style.css?v=noticeFix1">
    <style>
        body {
            font-family: "Helvetica Neue", Arial, sans-serif;
            background: #f5f3ff;
            color: #333;
            margin: 0;
        }

        header {
            background: rgba(75, 0, 130, 0.9);
            color: #fff;
            text-align: center;
            padding: 1rem;
            position: sticky;
            top: 0;
        }
        header h1 {
            color: #fff;
        }

        .back-btn {
            position: absolute;
            left: 1rem;
            color: #fff;
            text-decoration: none;
            font-size: 1.5rem;
        }

        main {
            max-width: 800px;
            margin: 3rem auto;
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #4b0082;
            margin-bottom: 0.5rem;
        }

        .date {
            color: #777;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        .content {
            line-height: 1.8;
            white-space: pre-wrap;
            color: #4b0082;
        }

        a.return-link {
            display: inline-block;
            margin-top: 2rem;
            color: #4b0082;
            text-decoration: none;
        }

        a.return-link:hover {
            text-decoration: underline;
        }

        footer {
            text-align: center;
            padding: 1.5rem;
            color: #555;
            background: rgba(255, 255, 255, 0.8);
            margin-top: 3rem;
        }
    </style>
</head>

<body>
    <header>
        <a href="index.php" class="back-btn">←</a>
        <h1>お知らせ詳細</h1>
    </header>

    <main>
        <h1><?= htmlspecialchars($news['title'], ENT_QUOTES, 'UTF-8') ?></h1>
        <?php if ($news['created_at']): ?>
            <p class="date">投稿日：<?= htmlspecialchars(date('Y/m/d', strtotime($news['created_at'])), ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>
        <div class="content"><?= nl2br(htmlspecialchars($news['content'], ENT_QUOTES, 'UTF-8')) ?></div>

        <a href="news_list.php" class="return-link">← 一覧に戻る</a>
    </main>

    <footer>
        <p>© 2025 麻生祭実行委員会</p>
    </footer>
</body>

</html>