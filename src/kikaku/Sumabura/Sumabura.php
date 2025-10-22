<?php
$project_name = "スマブラ大会";
$project_desc = "最強を決めろ！麻生最強スマブラプレイヤー決定戦！";
$project_img = "../../kikaku/imgs/sumabura.jpg";
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $project_name ?> | 麻生祭2025</title>
    <link rel="stylesheet" href="../asset/style.css?v=3">
</head>

<body>
    <header>
        <a href="../../index.php" class="back-btn">←</a>
        <h1><?= $project_name ?></h1>
    </header>

    <main class="project-page">
        <section class="project-hero">
            <img src="<?= $project_img ?>" alt="<?= $project_name ?>" class="project-img">
            <p><?= $project_desc ?></p>
        </section>

        <section class="project-detail">
            <h2>📅 開催概要</h2>
            <ul>
                <li>開催日：11月10日（日）</li>
                <li>場所：本館4F e-sportsルーム</li>
                <li>ルール：シングル戦・トーナメント形式</li>
            </ul>
        </section>

        <div class="entry-button-wrap">
            <a href="../../entry/entry.php?project=sumabura" class="entry-btn">🎫 ENTRY</a>
        </div>
    </main>

    <footer>
        <p>© 2025 麻生祭実行委員会</p>
    </footer>
</body>

</html>