<?php
session_start();
require_once('../../asset/db_connect.php');
// ログイン済みチェック（root制限なし）
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}


// IDチェック
if (!isset($_GET['id'])) {
    header('Location: news_manage.php');
    exit;
}

$id = (int) $_GET['id'];

// 該当データ取得
$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$news = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$news) {
    die("該当するお知らせが見つかりません。");
}

// 更新処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if ($title !== '') {
        $stmt = $pdo->prepare("UPDATE news SET title=?, content=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$title, $content, $id]);
        header('Location: news_manage.php?msg=updated');
        exit;
    } else {
        $error = "タイトルを入力してください。";
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>お知らせ編集 | 麻生祭2025</title>
    <link rel="stylesheet" href="master.css">
</head>

<body>
    <header>
        <h1>📝 お知らせ編集</h1>
        <a href="news_manage.php" class="back-btn">← 戻る</a>
    </header>

    <main>
        <div class="form-container">
            <?php if (isset($error)): ?>
                <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form action="" method="post" class="news-form">
                <label for="title">タイトル：</label>
                <input type="text" name="title" id="title"
                    value="<?= htmlspecialchars($news['title'], ENT_QUOTES, 'UTF-8') ?>" required>

                <label for="content">本文：</label>
                <textarea name="content" id="content"
                    rows="6"><?= htmlspecialchars($news['content'], ENT_QUOTES, 'UTF-8') ?></textarea>

                <button type="submit" class="submit-btn">更新する</button>
            </form>
        </div>
    </main>
</body>

</html>