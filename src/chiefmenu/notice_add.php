<?php
session_start();
require_once('../../asset/db_connect.php');

$allowed_roles = ['chief', 'vice', 'teacher'];
if (!in_array($_SESSION['role'] ?? '', $allowed_roles, true)) {
    header('Location: ../master/master_menu.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $created_by = $_SESSION['name'] ?? $_SESSION['username'];

    if ($title === '' || $content === '') {
        $error = 'すべての項目を入力してください。';
    } else {
        $stmt = $pdo->prepare("INSERT INTO executive_news (title, content, created_by) VALUES (:title, :content, :created_by)");
        $stmt->execute([':title' => $title, ':content' => $content, ':created_by' => $created_by]);
        header('Location: notice_manage.php?message=' . urlencode('✅ お知らせを追加しました。'));
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>お知らせ追加 | 麻生祭2025</title>
    <link rel="stylesheet" href="../master/master.css">
</head>

<body>
    <header>🆕 お知らせ追加</header>

    <div class="edit-container">
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST">
            <label>タイトル</label>
            <input type="text" name="title" required>

            <label>本文</label>
            <textarea name="content" rows="6" required></textarea>

            <button type="submit">追加する</button>
        </form>

        <a href="notice_manage.php" class="back-btn">← 一覧に戻る</a>
    </div>

    <footer>© 2025 麻生祭実行委員会</footer>
</body>

</html>