<?php
require_once('../asset/db_connect.php');

// お知らせ全件取得
$stmt = $pdo->query("SELECT id, title, created_at FROM news ORDER BY created_at DESC");
$news_all = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お知らせ一覧 | 麻生祭2025</title>
    <link rel="stylesheet" href="../asset/style.css?v=publicNewsListWide">
    <style>
        body {
            font-family: "Helvetica Neue", Arial, sans-serif;
            background: #f5f3ff;
            margin: 0;
            color: #333;
        }

        header {
            background: rgba(75, 0, 130, 0.9);
            color: #fff;
            text-align: center;
            padding: 1rem;
            position: sticky;
            top: 0;
            font-weight: bold;
        }

        .back-btn {
            position: absolute;
            left: 1rem;
            color: #fff;
            text-decoration: none;
            font-size: 1.5rem;
        }

        /* ===== メインエリアをワイドに ===== */
        .container {
            max-width: 1100px;   /* ← 横幅広げたポイント */
            margin: 3rem auto;
            background: #fff;
            padding: 3rem 4rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .table-wrapper {
            margin-bottom: 2rem;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding: 15px 0;
            font-size: 1.1rem;
        }

        a.news-link {
            text-decoration: none;
            color: #333;
            flex-grow: 1;
            transition: color 0.2s;
        }

        a.news-link:hover {
            color: #4b0082;
            text-decoration: underline;
        }

        small {
            color: #666;
            min-width: 100px;
            text-align: right;
        }

        .back-home {
            display: inline-block;
            margin-top: 1rem;
            color: #4b0082;
            text-decoration: none;
            transition: 0.2s;
        }

        .back-home:hover {
            text-decoration: underline;
        }

        footer {
            text-align: center;
            padding: 1.5rem;
            color: #555;
            background: rgba(255, 255, 255, 0.8);
            margin-top: 3rem;
        }

        /* ===== スマホ対応 ===== */
        @media (max-width: 800px) {
            .container {
                width: 92%;
                padding: 1.5rem;
            }
            li {
                flex-direction: column;
                align-items: flex-start;
            }
            small {
                text-align: left;
                margin-top: 5px;
            }
        }
    </style>
</head>

<body>
    <header>
        <a href="index.php" class="back-btn">←</a>
        <h1>お知らせ一覧</h1>
    </header>

    <div class="container">
        <div class="table-wrapper">
            <?php if (empty($news_all)): ?>
                <p>現在お知らせはありません。</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($news_all as $n): ?>
                        <li>
                            <a href="news_detail.php?id=<?= $n['id'] ?>" class="news-link">
                                <strong><?= htmlspecialchars($n['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </a>
                            <small><?= htmlspecialchars(date('Y/m/d', strtotime($n['created_at'])), ENT_QUOTES, 'UTF-8') ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div style="text-align:center;">
            <a href="index.php" class="back-home">← トップへ戻る</a>
        </div>
    </div>

    <footer>
        <p>© 2025 麻生祭実行委員会</p>
    </footer>
</body>
</html>
