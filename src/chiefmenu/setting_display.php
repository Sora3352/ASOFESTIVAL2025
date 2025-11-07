<?php
session_start();
require_once('../../asset/db_connect.php');

// 実行委員長・副・先生のみアクセス可能
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['chief', 'vice', 'teacher'])) {
    header('Location: ../master/master_menu.php');
    exit();
}

// システム設定テーブルがない場合に備えて初期データを作る
$pdo->exec("
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// 設定を取得
$stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// 既存の値またはデフォルト
$festival_name = $settings['festival_name'] ?? '麻生祭2025';
$theme_color = $settings['theme_color'] ?? '#5A3BE7';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $festival_name = trim($_POST['festival_name']);
    $theme_color = trim($_POST['theme_color']);

    if ($festival_name !== '') {
        // festival_name
        $stmt = $pdo->prepare("
            INSERT INTO system_settings (setting_key, setting_value)
            VALUES ('festival_name', :val)
            ON DUPLICATE KEY UPDATE setting_value = :val
        ");
        $stmt->execute([':val' => $festival_name]);
    }

    if ($theme_color !== '') {
        // theme_color
        $stmt = $pdo->prepare("
            INSERT INTO system_settings (setting_key, setting_value)
            VALUES ('theme_color', :val)
            ON DUPLICATE KEY UPDATE setting_value = :val
        ");
        $stmt->execute([':val' => $theme_color]);
    }

    $message = '✅ 設定を更新しました！';
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>表示設定 | ASO FESTIVAL 管理</title>
    <link rel="stylesheet" href="../master/master.css">
    <style>
        .setting-container {
            max-width: 600px;
            margin: 30px auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .setting-container label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        .setting-container input[type="text"],
        .setting-container input[type="color"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .setting-container button {
            display: block;
            width: 100%;
            margin-top: 20px;
            padding: 10px;
            border: none;
            border-radius: 8px;
            background-color: #5A3BE7;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }

        .setting-container button:hover {
            opacity: 0.9;
        }

        .message {
            text-align: center;
            color: green;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <header>🎨 表示設定</header>

    <div class="setting-container">
        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="festival_name">システム名（例：麻生祭2025）</label>
            <input type="text" id="festival_name" name="festival_name" value="<?= htmlspecialchars($festival_name) ?>">

            <label for="theme_color">テーマカラー</label>
            <input type="color" id="theme_color" name="theme_color" value="<?= htmlspecialchars($theme_color) ?>">

            <button type="submit">更新する</button>
        </form>
    </div>

    <a href="../master/master_menu.php" class="back-btn">← 戻る</a>

    <footer>© <?= htmlspecialchars($festival_name) ?> 管理システム</footer>
</body>

</html>