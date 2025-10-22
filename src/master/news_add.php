<?php
session_start();
require_once('../../asset/db_connect.php');
// ログイン済みチェック（root制限なし）
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}


// 送信されたときの処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if ($title !== '') {
        $stmt = $pdo->prepare("INSERT INTO news (title, content, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$title, $content]);
        header('Location: news_manage.php?msg=added');
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
    <title>お知らせ新規登録 | 麻生祭2025</title>
    <link rel="stylesheet" href="master.css">
</head>

<body>
    <header>
        <h1>📰 お知らせ新規登録</h1>
        <a href="news_manage.php" class="back-btn">← 戻る</a>
    </header>

    <main>
        <div class="form-container">
            <?php if (isset($error)): ?>
                <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form action="" method="post" class="news-form">
                <label for="title">タイトル：</label>
                <input type="text" name="title" id="title" required placeholder="例：スマブラ大会のエントリー開始！">

                <label for="content">本文：</label>
                <textarea name="content" id="content" rows="6" placeholder="お知らせの詳細内容を入力してください。"></textarea>

                <button type="submit" class="submit-btn">登録する</button>
            </form>
        </div>
    </main>
</body>

</html>