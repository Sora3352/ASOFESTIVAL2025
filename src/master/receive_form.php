<?php
require_once('../../asset/db_connect.php');

// URLパラメータから配布者を取得
$giver = $_GET['giver'] ?? '不明';
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>麻生祭 配布物受け取りフォーム</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #4b0082, #6a0dad);
            color: #fff;
            text-align: center;
            padding: 30px;
            min-height: 100vh;
        }

        .container {
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            margin: auto;
        }

        h1 {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            text-align: left;
            margin-left: 10%;
            margin-top: 8px;
        }

        input[type="checkbox"] {
            transform: scale(1.3);
            margin-right: 8px;
        }

        input[type="text"],
        input[type="password"] {
            width: 85%;
            padding: 10px;
            font-size: 1.1em;
            border: none;
            border-radius: 8px;
            margin: 10px 0;
        }

        button {
            width: 90%;
            background: #fff;
            color: #4b0082;
            border: none;
            font-size: 1.1em;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.2s;
        }

        button:hover {
            background: #f3e8ff;
        }

        small {
            display: block;
            margin-top: 10px;
            color: #ddd;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🎫 麻生祭 受け取りフォーム</h1>
        <p>受け取るものを選択し、本人確認をしてください。</p>

        <form method="POST" action="receive_process.php">
            <h3>📦 受け取るもの</h3>
            <label><input type="checkbox" name="items[]" value="ネームストラップ">ネームストラップ</label>
            <label><input type="checkbox" name="items[]" value="IDケース">IDケース</label>

            <h3>🧍 本人確認</h3>
            <input type="text" name="username" placeholder="学籍番号（例：2401003）" required><br>
            <input type="password" name="password" placeholder="パスワード" required><br>

            <input type="hidden" name="giver" value="<?= htmlspecialchars($giver) ?>">

            <button type="submit">受け取りを確定</button>
        </form>

        <small>配布担当：<?= htmlspecialchars($giver) ?></small>
    </div>
</body>

</html>