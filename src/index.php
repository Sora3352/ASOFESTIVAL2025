<?php
require_once('../asset/db_connect.php');

// 最新お知らせ
$stmt_latest = $pdo->query("SELECT message FROM latest_news ORDER BY updated_at DESC LIMIT 1");
$latest = $stmt_latest->fetch(PDO::FETCH_ASSOC);

// 一覧表示（4件まで）
$stmt_news = $pdo->query("SELECT id, title, created_at FROM news ORDER BY created_at DESC LIMIT 4");
$newsList = $stmt_news->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>麻生祭2025</title>
    <link rel="stylesheet" href="../asset/style.css?v=EdgeFix2">
    <link rel="icon" type="image/png" href="../img/ASOFEST2025_favicon_transparent.png">
    <style>
        /* ===== お知らせエリア調整 ===== */
        .news-section {
            text-align: center;
            color: white;
        }

        .news-list {
            list-style: none;
            padding: 0;
            margin: 1rem auto 2rem;
            max-width: 500px;
        }

        .news-list li {
            margin: 0.5rem 0;
            font-size: 1rem;
        }

        .news-link {
            color: white;
            text-decoration: underline;
            transition: opacity 0.2s;
        }

        .news-link:hover {
            opacity: 0.8;
        }

        .all-news-btn {
            display: inline-block;
            background: linear-gradient(45deg, #4b0082, #6a0dad);
            color: white;
            padding: 0.7rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
        }

        .all-news-btn:hover {
            background: linear-gradient(45deg, #6a0dad, #8a2be2);
            transform: scale(1.05);
        }

        /* ===== 最新お知らせテロップ ===== */
        .latest-news-bar {
            background: linear-gradient(90deg, #4b0082, #6a0dad);
            color: white;
            overflow: hidden;
            white-space: nowrap;
            padding: 0.5rem 0;
            font-weight: bold;
            font-size: 1rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        .scroll-text {
            display: inline-block;
            padding-left: 100%;
            animation: scroll-text 18s linear infinite;
        }

        @keyframes scroll-text {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-100%);
            }
        }

        /* ==== LINE追加ボタン ==== */
        .line-btn {
            display: inline-block;
            background: linear-gradient(90deg, #06c755, #00a34b);
            color: #fff;
            padding: 14px 36px;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: bold;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            transition: 0.25s ease;
        }

        .line-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.35);
            filter: brightness(1.1);
        }

        /* ==== ✅ スマホ用調整（幅600px以下） ==== */
        @media (max-width: 600px) {
            .entry-button-wrap {
                gap: 0.6rem;
                /* ボタン間の余白を少し狭く */
            }

            .entry-btn,
            .line-btn {
                padding: 10px 22px;
                /* 全体を少し小さく */
                font-size: 1rem;
                /* 文字も少し小さく */
                border-radius: 40px;
            }
        }
    </style>
</head>

<body>
    <!-- ===== 背景アニメーション ===== -->
    <div class="poster-bg">
        <div class="poster-track">
            <img src="../img/poster_long.png" alt="麻生祭ポスター">
            <img src="../img/poster_long.png" alt="麻生祭ポスター">
            <img src="../img/poster_long.png" alt="麻生祭ポスター">
            <img src="../img/poster_long.png" alt="麻生祭ポスター">
            <img src="../img/poster_long.png" alt="麻生祭ポスター">
            <img src="../img/poster_long.png" alt="麻生祭ポスター">
            <img src="../img/poster_long.png" alt="麻生祭ポスター">
        </div>
    </div>

    <!-- ===== ヘッダー ===== -->
    <header>
        <!-- ハンバーガーメニュー -->
        <button class="menu-toggle" id="menuToggle" aria-label="メニューを開閉">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <h1>麻生祭 2025</h1>
    </header>

    <!-- 全体メニュー -->
    <nav class="global-nav" id="globalNav">
        <ul>
            <li><a href="https://aso-sora3597.noor.jp/ASOFESTIVAL2025/src/index.php">🏠 ホーム</a></li>

            <!-- ▼ 実行委員企画（折りたたみ式） -->
            <li class="has-submenu">
                <button class="submenu-toggle">🎪 実行委員企画</button>
                <ul class="submenu">
                    <li><a href="https://aso-sora3597.noor.jp/ASOFESTIVAL2025/src/kikaku/Karaoke/Karaoke.php">カラオケ大会</a>
                    </li>
                    <li><a
                            href="https://aso-sora3597.noor.jp/ASOFESTIVAL2025/src/kikaku/Sumabura/Sumabura.php">スマブラ大会</a>
                    </li>
                    <li><a href="https://aso-sora3597.noor.jp/ASOFESTIVAL2025/src/kikaku/Ramune/Ramune.php">ラムネ早飲み</a>
                    </li>
                    <li><a href="https://aso-sora3597.noor.jp/ASOFESTIVAL2025/src/kikaku/Shateki/Shateki.php">射的</a>
                    </li>
                    <li><a href="https://aso-sora3597.noor.jp/ASOFESTIVAL2025/src/kikaku/Bingo/Bingo.php">ビンゴ大会</a></li>
                </ul>
            </li>

            <li><a href="https://aso-sora3597.noor.jp/ASOFESTIVAL2025/src/entry/entry.php">📝 エントリー</a></li>
            <li><a href="https://aso-sora3597.noor.jp/ASOFESTIVAL2025/src/master/login.php">🔑 管理者ログイン</a></li>
        </ul>
    </nav>
    <main class="index-main">
        <section class="welcome hero">
            <h2>ようこそ、麻生祭2025へ！</h2>
            <p>今年も楽しい企画が盛りだくさん！<br>下のメニューから各コーナーにアクセスできます。</p>
        </section>

        <!-- 最新お知らせ -->
        <!-- 最新お知らせ（流れるテロップ） -->
        <?php if (!empty($latest['message'])): ?>
            <div class="latest-news-bar">
                <div class="scroll-text">
                    <?= htmlspecialchars($latest['message'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- お知らせ一覧 -->
        <section class="news-section">
            <h2>📰 お知らせ一覧</h2>
            <ul class="news-list">
                <?php if (!empty($newsList)): ?>
                    <?php foreach ($newsList as $news): ?>
                        <li>
                            <?= htmlspecialchars(date('m/d', strtotime($news['created_at'])), ENT_QUOTES, 'UTF-8') ?>
                            <a href="news_detail.php?id=<?= (int) $news['id'] ?>" class="news-link">
                                <?= htmlspecialchars($news['title'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>現在お知らせはありません。</li>
                <?php endif; ?>
            </ul>
            <a href="news_list.php" class="all-news-btn">すべてのお知らせを見る</a>
        </section>
        </div>


        <!-- メニュー -->
        <div class="menu-container">
            <a href="kikaku/Karaoke/Karaoke.php" class="menu-item">🎤 カラオケ大会</a>
            <a href="kikaku/Shateki/Shateki.php" class="menu-item">🎯 射的コーナー</a>
            <a href="kikaku/Bingo/Bingo.php" class="menu-item">🎲 ビンゴ大会</a>
            <a href="kikaku/Ramune/Ramune.php" class="menu-item">🥤 ラムネ早飲み</a>
            <a href="kikaku/Sumabura/Sumabura.php" class="menu-item">🎮 スマブラ大会</a>
        </div>

        <!-- エントリーボタン -->
        <div class="entry-button-wrap">
            <a href="entry/entry.php" class="entry-btn">🎫 大会ENTRY</a>

            <!-- 🟢 LINE追加ボタン（デザイン統一） -->
            <a href="https://lin.ee/U3vJ03z" target="_blank" class="line-btn">
                <img src="https://upload.wikimedia.org/wikipedia/commons/4/41/LINE_logo.svg" alt="LINE" height="20"
                    style="vertical-align:middle;margin-right:8px;">
                友だち追加
            </a>
        </div>
    </main>

    <footer style="background:#4b0082; color:#fff; text-align:center; padding:1rem; margin-top:2rem;">
        <p style="margin:0; font-size:0.9rem;">
            &copy; 2025 麻生祭実行委員会 |
            <a href="bug_report.html" style="color:#fff; text-decoration:underline;">🪲 バグ報告フォーム</a>
        </p>
    </footer>

    <!-- ===== 背景アニメーション制御スクリプト ===== -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const track = document.querySelector(".poster-track");
            const poster = document.querySelector(".poster-track img");

            const speed = 0.7;
            let x = 0;
            let posterWidth = 0;

            poster.addEventListener("load", () => {
                posterWidth = poster.offsetWidth;
                loop();
            });

            function loop() {
                x -= speed;
                if (x <= -posterWidth) x += posterWidth;
                track.style.transform = `translateX(${x}px)`;
                requestAnimationFrame(loop);
            }
        });
    </script>
    <script src="../asset/menu.js"></script>

</body>

</html>