<?php
session_start();
require_once('../../asset/db_connect.php');
require_once('../../asset/phpqrcode/qrlib.php');

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['chief', 'core', 'vice', 'teacher'], true)) {
    header('Location: master_menu.php');
    exit();
}

$name = $_SESSION['name'] ?? '不明';
$role = $_SESSION['role'] ?? 'member';

$target_url = "https://aso-sora3597.noor.jp/ASOFESTIVAL2025/src/master/receive_form.php?giver=" . urlencode($name);

$tempDir = '../../asset/tmp/';
if (!file_exists($tempDir))
    mkdir($tempDir, 0777, true);

$qrFile = $tempDir . 'receive_qr_' . $username . '.png';
QRcode::png($target_url, $qrFile, 8, 2);
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>配布QRコード</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #4b0082, #6a0dad);
            color: #fff;
            text-align: center;
            padding: 40px 20px;
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

        img {
            margin-top: 1.5rem;
            border: 8px solid #fff;
            border-radius: 12px;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #333;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
        }

        .back-btn:hover {
            background: #555;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🎫 配布用QRコード</h1>
        <p>学生はこのQRを読み取って受け取りフォームを開いてください。</p>
        <img src="<?= htmlspecialchars($qrFile) ?>" alt="受け取りQRコード" width="250" height="250">
        <p>担当：<?= htmlspecialchars($name) ?>（<?= htmlspecialchars($role) ?>）</p>
        <a href="master_menu.php" class="back-btn">← メニューに戻る</a>
    </div>
</body>

</html>