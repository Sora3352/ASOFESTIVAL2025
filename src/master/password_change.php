<?php
session_start();
require_once('../../asset/db_connect.php');

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$error = '';
$success = '';
$is_first = isset($_GET['first']) && $_GET['first'] == 1; // ← 初回ログインかチェック

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if ($new_password === '' || $confirm_password === '') {
        $error = 'すべての項目を入力してください。';
    } elseif ($new_password !== $confirm_password) {
        $error = 'パスワードが一致しません。';
    } else {
        try {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // ✅ パスワード更新＆フラグ変更
            $update = $pdo->prepare("UPDATE admins SET password = :password, password_changed = 1 WHERE username = :username");
            $update->execute([
                ':password' => $hashed_password,
                ':username' => $username
            ]);

            // ✅ action_logs に記録
            $log = $pdo->prepare("INSERT INTO action_logs (username, role, action, target_table)
                VALUES (:u, :r, :a, 'login_logs')");
            $log->execute([
                ':u' => $_SESSION['name'] ?? $username,
                ':r' => $_SESSION['role'] ?? 'unknown',
                ':a' => 'パスワード変更完了'
            ]);

            // ✅ 完了メッセージ
            $success = 'パスワードを変更しました。次回から新しいパスワードでログインしてください。';

            // ✅ 完了後はメニューへ
            header('Location: master_menu.php?changed=1');
            exit();
        } catch (PDOException $e) {
            $error = 'エラーが発生しました: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>パスワード変更 | 麻生祭2025</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 16px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }

        h1 {
            text-align: center;
            font-size: 1.4rem;
            margin-bottom: 1.5rem;
            letter-spacing: 1px;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        label {
            display: block;
            margin-bottom: 0.3rem;
            font-size: 0.9rem;
            color: #eee;
        }

        input[type="password"] {
            width: 100%;
            padding: 0.6rem;
            border-radius: 8px;
            border: none;
            outline: none;
        }

        button {
            width: 100%;
            padding: 0.7rem;
            border: none;
            border-radius: 8px;
            background-color: #4CAF50;
            color: #fff;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }

        .error {
            background: rgba(255, 0, 0, 0.2);
            color: #fff;
            text-align: center;
            padding: 0.6rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .success {
            background: rgba(0, 255, 0, 0.2);
            color: #fff;
            text-align: center;
            padding: 0.6rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .footer {
            text-align: center;
            font-size: 0.8rem;
            margin-top: 1rem;
            color: #ccc;
        }

        a {
            color: #fff;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h1>パスワード変更</h1>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
            <div class="footer"><a href="login.php">ログインページへ戻る</a></div>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label>新しいパスワード</label>
                    <input type="password" name="new_password" required>
                </div>

                <div class="form-group">
                    <label>確認用パスワード</label>
                    <input type="password" name="confirm_password" required>
                </div>

                <button type="submit">変更する</button>
            </form>
            <div class="footer">※ セキュリティのため、変更後は自動的にログアウトします。</div>
        <?php endif; ?>
    </div>
</body>

</html>