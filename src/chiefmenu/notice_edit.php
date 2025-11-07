<?php
session_start();
require_once('../../asset/db_connect.php');

$allowed_roles = ['chief', 'vice', 'teacher'];
if (!in_array($_SESSION['role'] ?? '', $allowed_roles, true)) {
    header('Location: ../master/master_menu.php');
    exit();
}

$id = $_GET['id'] ?? '';
if (!is_numeric($id)) {
    header('Location: notice_manage.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM executive_news WHERE id = ?");
$stmt->execute([$id]);
$notice = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$notice) {
    header('Location: notice_manage.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $updated_by = $_SESSION['name'] ?? $_SESSION['username'];

    if ($title === '' || $content === '') {
        $error = 'すべての項目を入力してください。';
    } else {
        $stmt = $pdo->prepare("UPDATE executive_news SET title = :title, content = :content, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':title' => $title, ':content' => $content, ':id' => $id]);
        header('Location: notice_manage.php?message=' . urlencode('✅ お知らせを更新しました。'));
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>お知らせ編集 | 麻生祭2025</title>
    <link rel="stylesheet" href="../master/master.css">
</head>

<body>
    <header>✏️ お知らせ編集</header>

    <div class="edit-container">
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST">
            <label>タイトル</label>
            <input type="text" name="title" value="<?= htmlspecialchars($notice['title']) ?>" required>

            <label>本文</label>
            <textarea name="content" rows="6" required><?= htmlspecialchars($notice['content']) ?></textarea>

            <button type="submit">更新する</button>
        </form>

        <a href="notice_manage.php" class="back-btn">← 一覧に戻る</a>
    </div>

    <footer>© 2025 麻生祭実行委員会</footer>
</body>

</html>