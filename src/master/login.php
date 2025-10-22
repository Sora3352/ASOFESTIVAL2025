<?php
session_start();
require_once('../../asset/db_connect.php');

// すでにログイン済みならメニューへ
if (isset($_SESSION['username'])) {
    header('Location: master_menu.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'ユーザー名とパスワードを入力してください。';
    } else {
        try {
            $sql = "SELECT * FROM admins WHERE username = :username LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // ✅ ログイン成功時のセッションセット
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];
                

                // ✅ 成功ログをaction_logsに記録
                $log = $pdo->prepare("INSERT INTO action_logs 
                    (username, role, action, target_table)
                    VALUES (:u, :r, :a, 'login_logs')");
                $log->execute([
                    ':u' => $user['name'] ?? $user['username'],
                    ':r' => $user['role'],
                    ':a' => 'ログイン成功'
                ]);

                // ✅ 初回ログインチェックを修正
                if (isset($user['password_changed']) && $user['password_changed'] == 0) {
                    header('Location: password_change.php');
                    exit();
                }

                // ✅ 通常ログイン時はメニューへ
                header('Location: master_menu.php');
                exit();
            } else {
                // ❌ ログイン失敗
                $log = $pdo->prepare("INSERT INTO action_logs 
                    (username, role, action, target_table)
                    VALUES (:u, 'unknown', :a, 'login_logs')");
                $log->execute([
                    ':u' => $username,
                    ':a' => 'パスワード誤りまたは未登録ユーザー'
                ]);

                $error = 'ユーザー名またはパスワードが違います。';
            }
        } catch (PDOException $e) {
            $error = 'データベースエラー: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン | ASO FESTIVAL 2025 管理</title>
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

        input[type="text"],
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

        .footer {
            text-align: center;
            font-size: 0.8rem;
            margin-top: 1rem;
            color: #ccc;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h1>ASO FESTIVAL 2025<br>管理ログイン</h1>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">ユーザー名</label>
                <input type="text" id="username" name="username" placeholder="学籍番号など" required>
            </div>

            <div class="form-group">
                <label for="password">パスワード</label>
                <input type="password" id="password" name="password" placeholder="パスワード" required>
            </div>

            <button type="submit">ログイン</button>
        </form>

        <div class="footer">
            © 2025 ASO FESTIVAL Management System
        </div>
    </div>
</body>

</html>