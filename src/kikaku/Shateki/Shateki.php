<?php
$project_name = "射的コーナー";
$project_desc = "狙って撃って景品ゲット！大人も子供も楽しめる人気コーナー！";
$project_img = "../../kikaku/imgs/shateki.jpg";
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
                <li>開催日：11月9日（土）〜10日（日）</li>
                <li>場所：本館1F 廊下特設ブース</li>
                <li>参加方法：当日受付（1回100円で3発）</li>
            </ul>
        </section>
    </main>

    <footer>
        <p>© 2025 麻生祭実行委員会</p>
    </footer>
</body>

</html>