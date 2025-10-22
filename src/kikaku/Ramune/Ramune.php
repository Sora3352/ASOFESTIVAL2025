<?php
$project_name = "ラムネ早飲み";
$project_desc = "制限時間内にラムネを一気飲み！スピードと根性の勝負！";
$project_img = "../../kikaku/imgs/ramune.jpg";
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
                <li>開催日：11月9日（土）</li>
                <li>場所：本館1F 中央ホール前</li>
                <li>参加方法：当日受付制（先着順）</li>
            </ul>
        </section>

        <div class="entry-button-wrap">
            <a href="../../entry/entry.php?project=ramune" class="entry-btn">🎫 ENTRY</a>
        </div>
    </main>

    <footer>
        <p>© 2025 麻生祭実行委員会</p>
    </footer>
</body>

</html>