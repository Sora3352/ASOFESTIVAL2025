<?php
require_once('../../asset/db_connect.php');
session_start();

/* ===== エラー表示ON ===== */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$giver = $_POST['giver'] ?? '';
$items = $_POST['items'] ?? [];

if (!$username || !$password || empty($items)) {
    die("<p style='color:red;text-align:center;'>入力内容が不足しています。</p>");
}

// ===== 認証 =====
$stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :sid LIMIT 1");
$stmt->execute([':sid' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("<p style='color:red;text-align:center;'>学籍番号が登録されていません。</p>");
}

if (!password_verify($password, $user['password'])) {
    die("<p style='color:red;text-align:center;'>パスワードが一致しません。</p>");
}

$student_name = $user['name']; // 本人の名前を取得

// ===== 履歴テーブル作成 =====
$pdo->exec("
CREATE TABLE IF NOT EXISTS receive_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(20),
    student_name VARCHAR(100),
    item VARCHAR(50),
    giver VARCHAR(50),
    received_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_receive (username, item)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// ===== 登録処理 =====
$insert = $pdo->prepare("
    INSERT IGNORE INTO receive_history (username, student_name, item, giver)
    VALUES (:username, :student_name, :item, :giver)
");

$added = [];
foreach ($items as $item) {
    $insert->execute([
        ':username' => $username,
        ':student_name' => $student_name,
        ':item' => $item,
        ':giver' => $giver
    ]);

    if ($insert->rowCount() > 0) {
        $added[] = $item;
    }

    // ✅ admins テーブルを更新（受け取ったものに応じて）
    if ($item === 'ネームストラップ') {
        $update = $pdo->prepare("UPDATE admins SET strap = 1 WHERE username = :username");
        $update->execute([':username' => $username]);
    }

    if ($item === 'IDケース') {
        $update = $pdo->prepare("UPDATE admins SET idcase = 1 WHERE username = :username");
        $update->execute([':username' => $username]);
    }
}

// ===== 結果をセット =====
if ($added) {
    $joined = implode("・", $added);
    $message = "{$student_name} さんの {$joined} の受け取りを「{$giver}」から完了しました！";
    $status = "success";
} else {
    $message = "すでに全ての物を受け取り済みです。";
    $status = "error";
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>受け取り結果</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: "Helvetica Neue", Arial, sans-serif;
            background:
                <?= ($status === 'success') ? 'linear-gradient(135deg, #4b0082, #6a0dad)' : '#333'; ?>
            ;
            color: #fff;
            text-align: center;
            padding: 50px 20px;
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
            font-size: 1.6rem;
            margin-bottom: 1rem;
        }

        p {
            font-size: 1.1rem;
            line-height: 1.6;
        }

        a.btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #fff;
            color: #4b0082;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }

        a.btn:hover {
            background: #f3e8ff;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php if ($status === 'success'): ?>
            <h1>✅ 受け取り完了</h1>
            <p><?= htmlspecialchars($message) ?></p>
        <?php else: ?>
            <h1>⚠️ 登録できませんでした</h1>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <a href="receive_form.php?giver=<?= urlencode($giver) ?>" class="btn">← 戻る</a>
    </div>
</body>

</html>