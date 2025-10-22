<?php
session_start();
require_once('../../asset/db_connect.php');

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$name = $_SESSION['name'] ?? $_SESSION['username'];
$role = $_SESSION['role'] ?? 'staff';
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理メニュー | ASO FESTIVAL 2025</title>
    <link rel="stylesheet" href="master.css">
</head>

<body>
    <header>ASO FESTIVAL 2025 管理メニュー</header>

    <?php if (isset($_GET['changed'])): ?>
        <p style="color:green; text-align:center;">✅ パスワードを変更しました！</p>
    <?php endif; ?>

    <div class="welcome">
        <h2>ようこそ <?= htmlspecialchars($_SESSION['name'], ENT_QUOTES, 'UTF-8') ?> さん</h2>
        権限：
        <?php
        switch ($role) {
            case 'chief':
                echo '実行委員長（管理者）';
                break;
            case 'vice':
                echo '副実行委員長';
                break;
            case 'teacher':
                echo '先生（全権限）';
                break;
            case 'core':
                echo 'コアメンバー';
                break;
            case 'leader':
                echo '企画リーダー';
                break;
            case 'member':
                echo '実行委員';
                break;
            default:
                echo '未設定';
                break;
        }
        ?>
    </div>

    <div class="menu-container">
        <a href="executive_list.php" class="menu-item">👥 実行委員一覧</a>
        <a href="calendar.php" class="menu-item">📅 カレンダー管理</a>
        <a href="./news_manage.php" class="menu-item">📰 お知らせ管理</a>
        <a href="entry_list.php" class="menu-item">🗂️ エントリー一覧</a>

        <?php if (in_array($role, ['chief', 'vice', 'teacher'], true)): ?>
            <a href="action_log.php" class="menu-item">🗒️ 操作ログ</a>
        <?php endif; ?>
    </div>

    <form action="logout.php" method="post">
        <button type="submit" class="logout-btn">ログアウト</button>
    </form>

    <footer>© 2025 ASO FESTIVAL 管理システム</footer>
</body>

</html>