<?php
$project_name = "カラオケ大会";
$project_desc = "麻生祭恒例のカラオケバトル！今年も熱唱者求む！優勝者には豪華賞品！";
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $project_name ?> | 麻生祭2025</title>
    <link rel="stylesheet" href="../../asset/style.css?v=karaokeFlow1">
    <link rel="icon" type="image/png" href="../../../img/ASOFEST2025_favicon_transparent.png">
    <style>
        /* ===== ポスター背景たれ流し ===== */
        .poster-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .poster-track {
            display: flex;
            width: max-content;
            animation: posterFlow 30s linear infinite;
        }

        .poster-track img {
            height: 100vh;
            width: auto;
            flex-shrink: 0;
            object-fit: contain;
            opacity: 0.85;
        }

        @keyframes posterFlow {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(-50%);
            }
        }

        /* ===== 全体レイアウト ===== */
        body {
            margin: 0;
            font-family: "Helvetica Neue", Arial, sans-serif;
            color: #333;
            overflow-x: hidden;
        }

        header {
            background: rgba(75, 0, 130, 0.9);
            color: white;
            text-align: center;
            padding: 1rem;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .back-btn {
            position: absolute;
            left: 1rem;
            color: white;
            text-decoration: none;
            font-size: 1.5rem;
        }

        main {
            position: relative;
            z-index: 1;
            padding: 3rem 1rem 4rem;
            display: flex;
            justify-content: center;
        }

        .content-box {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            padding: 2rem;
            max-width: 700px;
            width: 90%;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
        }

        h1 {
            font-size: 1.8rem;
            margin: 0;
        }

        h2 {
            margin-top: 1rem;
            color: #4b0082;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        li {
            margin: 0.5rem 0;
            font-size: 1.1rem;
        }

        .entry-button-wrap {
            text-align: center;
            margin-top: 2rem;
        }

        .entry-btn {
            display: inline-block;
            background: linear-gradient(45deg, #ff6600, #ff3366);
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .entry-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
        }

        footer {
            text-align: center;
            padding: 1.5rem;
            color: #555;
            background: rgba(255, 255, 255, 0.6);
            margin-top: 3rem;
            position: relative;
            z-index: 1;
        }

        @media (max-width: 600px) {
            .content-box {
                padding: 1.5rem;
            }

            h1 {
                font-size: 1.5rem;
            }

            li {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>
    <!-- ===== ポスターたれ流し背景 ===== -->
    <div class="poster-bg">
        <div class="poster-track">
            <img src="../../../img/poster/karaoke.png" alt="麻生祭ポスター">
            <img src="../../../img/poster/karaoke.png" alt="麻生祭ポスター">
            <img src="../../../img/poster/karaoke.png" alt="麻生祭ポスター">
            <img src="../../../img/poster/karaoke.png" alt="麻生祭ポスター">
            <img src="../../../img/poster/karaoke.png" alt="麻生祭ポスター">
        </div>
    </div>

    <header>
        <a href="../../index.php" class="back-btn">←</a>
        <h1><?= $project_name ?></h1>
    </header>

    <main>
        <div class="content-box">
            <section class="project-hero">
                <p><?= $project_desc ?></p>
            </section>

            <section class="project-detail">
                <h2>📅 開催概要</h2>
                <ul>
                    <li>大会予選日：未定‼</li>
                    <li>大会本戦日：12月18日（木）</li>
                    <li>場所：本戦1号館教室未定‼</li>
                    <li>参加人数：集まるまで‼先生も歌うよね！！？？</li>
                </ul>
            </section>

            <div class="entry-button-wrap">
                <a href="../../entry/entry.php" class="entry-btn">🎫 ENTRY</a>
            </div>
        </div>
    </main>

    <footer>
        <p>© 2025 麻生祭実行委員会</p>
    </footer>
</body>

</html>