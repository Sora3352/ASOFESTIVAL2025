<?php
// =============================
// ASO FESTIVAL 2025 エントリー完了画面
// =============================

// URLパラメータからエントリーナンバー取得
$entry_number = $_GET['no'] ?? null;
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>エントリー完了 | 麻生祭2025</title>
    <link rel="stylesheet" href="entry.css">
    <style>
        body {
            font-family: "Helvetica Neue", Arial, sans-serif;
            background: #f9f9ff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .complete-container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            max-width: 480px;
            width: 90%;
            text-align: center;
        }

        h1 {
            color: #4b0082;
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        p {
            font-size: 1rem;
            color: #333;
            margin: 0.5rem 0;
        }

        .entry-number {
            font-size: 1.3rem;
            font-weight: bold;
            color: #4b0082;
            margin: 1rem 0;
        }

        a {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.6rem 1.4rem;
            background: #4b0082;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            transition: background 0.3s;
        }

        a:hover {
            background: #5e2ca5;
        }
    </style>
</head>

<body>
    <div class="complete-container">
        <h1>エントリー完了！</h1>

        <?php if ($entry_number): ?>
            <p>エントリーナンバー：</p>
            <p class="entry-number"><?= htmlspecialchars($entry_number, ENT_QUOTES, 'UTF-8'); ?></p>
            <p>確認メールを送信しました。<br>受信ボックスまたは迷惑メールフォルダをご確認ください。</p>

            <div class="line-connect">
                <p>大会のお知らせはLINE公式アカウントから配信されます。</p>
                <a href="https://lin.ee/U3vJ03z" target="_blank">
                    <img src="https://scdn.line-apps.com/n/line_add_friends/btn/ja.png" alt="友だち追加" height="36" border="0">
                </a>
            </div>

        <?php else: ?>
            <p>このページは直接アクセスできません。</p>
        <?php endif; ?>

        <a href="../index.php">トップへ戻る</a>
    </div>
</body>

</html>