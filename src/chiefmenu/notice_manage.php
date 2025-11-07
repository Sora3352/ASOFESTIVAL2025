<?php
session_start();
require_once('../../asset/db_connect.php');

$allowed_roles = ['chief', 'vice', 'teacher'];
if (!in_array($_SESSION['role'] ?? '', $allowed_roles, true)) {
    header('Location: ../master/master_menu.php');
    exit();
}

$stmt = $pdo->query("SELECT * FROM executive_news ORDER BY created_at DESC");
$notices = $stmt->fetchAll(PDO::FETCH_ASSOC);

$message = $_GET['message'] ?? '';
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>実行委員お知らせ管理 | 麻生祭2025</title>
    <link rel="stylesheet" href="../master/master.css">
</head>

<body>
    <header>📰 実行委員お知らせ管理</header>

    <div class="container">
        <?php if ($message): ?>
            <div class="flash"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="menu" style="text-align:right; margin-bottom:10px;">
            <a href="notice_add.php" class="edit-btn">＋ 新規追加</a>
        </div>

        <div class="table-wrapper">
            <table class="entry-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>タイトル</th>
                        <th>作成者</th>
                        <th>作成日時</th>
                        <th>編集</th>
                        <th>削除</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($notices)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">お知らせはまだありません。</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($notices as $n): ?>
                            <tr>
                                <td><?= htmlspecialchars($n['id']) ?></td>
                                <td><?= htmlspecialchars($n['title']) ?></td>
                                <td><?= htmlspecialchars($n['created_by']) ?></td>
                                <td><?= htmlspecialchars($n['created_at']) ?></td>
                                <td><a href="notice_edit.php?id=<?= $n['id'] ?>" class="edit-btn">編集</a></td>
                                <td>
                                    <form method="POST" action="notice_delete.php" style="display:inline;"
                                        onsubmit="return confirm('本当に削除しますか？');">
                                        <input type="hidden" name="id" value="<?= $n['id'] ?>">
                                        <button type="submit" class="delete-btn">削除</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <a href="../master/master_menu.php" class="back-btn">← メニューへ戻る</a>
    </div>

    <footer>© 2025 麻生祭実行委員会</footer>
</body>

</html>