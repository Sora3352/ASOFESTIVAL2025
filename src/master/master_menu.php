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
    <?php
    // 実行委員向けお知らせを取得（executive_news テーブルから）
    $news_stmt = $pdo->query("SELECT * FROM executive_news ORDER BY created_at DESC LIMIT 3");
    $exec_news = $news_stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="news-box" style="max-width:600px; margin:30px auto; text-align:left;">
        <h3 style="margin-bottom:10px; color:white; text-align:center">📢 実行委員向けお知らせ</h3>
        <ul style="list-style:none; padding-left:0;">
            <?php if (empty($exec_news)): ?>
                <li style="color:white;">現在お知らせはありません。</li>
            <?php else: ?>
                <?php foreach ($exec_news as $n): ?>
                    <?php $is_new = (strtotime($n['created_at']) > strtotime('-3 days')); ?>
                    <li style="margin-bottom:12px; color:white;">
                        <a href="notice_view.php?id=<?= $n['id'] ?>" style="text-decoration:none; color:#fff;">
                            <strong><?= htmlspecialchars($n['title']) ?></strong>
                            <?php if ($is_new): ?>
                                <span style="color:#ff4b4b; font-weight:bold; font-size:0.85em;">🆕NEW</span>
                            <?php endif; ?>
                        </a><br>
                        <small style="opacity:0.8;"><?= htmlspecialchars(date('Y/m/d', strtotime($n['created_at']))) ?></small>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>

        <?php if (!empty($exec_news)): ?>
            <div style="text-align:right; margin-top:10px;">
                <a href="notice_list.php" style="color:#ffffff; text-decoration:none; font-weight:bold; background:rgba(255,255,255,0.15);
                      padding:6px 12px; border-radius:8px; transition:0.3s;">
                    ▶ すべてのお知らせを見る
                </a>
            </div>
        <?php endif; ?>
    </div>


    <div class="menu-container">
        <a href="executive_list.php" class="menu-item">👥 実行委員一覧</a>
        <a href="calendar.php" class="menu-item">📅 カレンダー管理</a>
        <a href="./news_manage.php" class="menu-item">📰 お知らせ管理</a>
        <a href="entry_list.php" class="menu-item">🗂️ エントリー一覧</a>
        <!-- ===== シフト関連メニュー ===== -->
        <a href="shift/request.php" class="menu-item">🕒 シフト申請</a>
        <a href="shift/request_list.php" class="menu-item">📋 シフト申請一覧</a>


        <?php if (in_array($role, ['chief', 'vice', 'teacher', 'core'], true)): ?>
            <a href="shift/shift_deadline_manage.php" class="menu-item">📆 シフト締切管理</a>
            <a href="receive_qr.php" class="menu-item">👜 備品受け取り</a>


        <?php endif; ?>
        <?php if (in_array($role, ['chief', 'vice', 'teacher'], true)): ?>
            <!-- 運営専用メニュー -->
            <a href="bug_report_list.php" class="menu-item">🐞 バグ報告一覧</a>
            <a href="action_log.php" class="menu-item">🗒️ 操作ログ</a>
            <a href="../chiefmenu/setting_display.php" class="menu-item">🎨 表示設定</a>
            <a href="../chiefmenu/notice_manage.php" class="menu-item">📰 実行委員お知らせ管理</a>
            <a href="../chiefmenu/system_reset.php" class="menu-item">🧹 データリセット</a>
            <a href="../chiefmenu/handover.php" class="menu-item">📂 引き継ぎ資料</a>
        <?php endif; ?>
    </div>

    <form action="logout.php" method="post">
        <button type="submit" class="logout-btn">ログアウト</button>
    </form>

    <footer>© 2025 ASO FESTIVAL 管理システム</footer>
</body>

</html>