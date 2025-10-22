<?php
$project_name = "ビンゴ大会";
$project_desc = "豪華景品をかけて運試し！毎年恒例、大盛り上がりのビンゴ大会！";
$project_img = "../../kikaku/imgs/bingo.jpg";
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $project_name ?> | 麻生祭2025</title>
    <link rel="stylesheet" href="../asset/style.css?v=2">
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
                <li>場所：本館5F ステージ</li>
                <li>参加方法：当日配布の整理券で参加可能</li>
            </ul>
        </section>
    </main>

    <footer>
        <p>© 2025 麻生祭実行委員会</p>
    </footer>
</body>

</html>