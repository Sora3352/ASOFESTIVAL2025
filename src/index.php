<?php
require_once('../asset/db_connect.php');

// 最新お知らせ（別テーブルから1件取得）
$stmt_latest = $pdo->query("SELECT message FROM latest_news ORDER BY updated_at DESC LIMIT 1");
$latest = $stmt_latest->fetch(PDO::FETCH_ASSOC);

// 一覧表示用（newsテーブルから）
$stmt_news = $pdo->query("SELECT title, created_at FROM news ORDER BY created_at DESC LIMIT 5");
$newsList = $stmt_news->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>麻生祭2025</title>
    <link rel="stylesheet" href="../asset/style.css?v=21">
    <!-- ファビコン -->
    <link rel="icon" type="image/png" href="../img/ASOFEST2025_favicon_transparent.png">
</head>

<body>
    <header>
        <h1>麻生祭 2025</h1>
        <a href="master/login.php" class="admin-login">🔐 実行委員用</a>
    </header>

    <main class="index-main">
        <section class="welcome hero">
            <h2>ようこそ、麻生祭2025へ！</h2>
            <p>今年も楽しい企画が盛りだくさん！<br>
                下のメニューから各コーナーにアクセスできます。</p>
        </section>
        <!-- ==== 最新お知らせバナー ==== -->
        <div class="news-banner">
            <div class="news-scroll">
                🔔
                <?php if ($latest): ?>
                    <?= htmlspecialchars($latest['message'], ENT_QUOTES, 'UTF-8') ?>
                <?php else: ?>
                    最新のお知らせは現在ありません。
                <?php endif; ?>
            </div>
        </div>

        <!-- ==== お知らせ一覧枠 ==== -->
        <section class="news-section">
            <h2>📰 お知らせ一覧</h2>
            <ul class="news-list">
                <?php if (!empty($newsList)): ?>
                    <?php foreach ($newsList as $news): ?>
                        <li>
                            <?= htmlspecialchars(date('m/d', strtotime($news['created_at'])), ENT_QUOTES, 'UTF-8') ?>
                            <?= htmlspecialchars($news['title'], ENT_QUOTES, 'UTF-8') ?>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>現在お知らせはありません。</li>
                <?php endif; ?>
            </ul>
        </section>

        <!-- ==== 企画BOX（横スクロール） ==== -->
        <div class="menu-container">
            <a href="kikaku/Karaoke/Karaoke.php" class="menu-item">🎤 カラオケ大会</a>
            <a href="kikaku/Shateki/Shateki.php" class="menu-item">🎯 射的コーナー</a>
            <a href="kikaku/Bingo/Bingo.php" class="menu-item">🎲 ビンゴ大会</a>
            <a href="kikaku/Ramune/Ramune.php" class="menu-item">🥤 ラムネ早飲み</a>
            <a href="kikaku/Sumabura/Sumabura.php" class="menu-item">🎮 スマブラ大会</a>
        </div>

        <!-- ==== 大会ENTRYボタン ==== -->
        <div class="entry-button-wrap">
            <a href="entry/entry.php" class="entry-btn">🎫 大会ENTRY</a>
        </div>

    </main>

    <footer>
        <p>© 2025 麻生祭実行委員会</p>
    </footer>
</body>

</html>