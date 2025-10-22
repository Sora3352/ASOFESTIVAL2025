<?php
$project_name = "カラオケ大会";
$project_desc = "麻生祭恒例のカラオケバトル！今年も熱唱者求む！優勝者には豪華賞品！";
$project_img = "../../kikaku/imgs/karaoke.jpg";
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
                <li>場所：本館5F ステージホール</li>
                <li>参加人数：最大10名（ソロまたはデュエットOK）</li>
            </ul>
        </section>

        <div class="entry-button-wrap">
            <a href="../../entry/entry.php?project=karaoke" class="entry-btn">🎫 ENTRY</a>
        </div>
    </main>

    <footer>
        <p>© 2025 麻生祭実行委員会</p>
    </footer>
</body>

</html>