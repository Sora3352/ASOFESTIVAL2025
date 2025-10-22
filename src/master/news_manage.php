<?php
session_start();
require_once('../../asset/db_connect.php');

// ログイン済みチェック（root制限なし）
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// ===== 最新お知らせの取得・更新 =====
$stmt_latest = $pdo->query("SELECT * FROM latest_news ORDER BY updated_at DESC LIMIT 1");
$current_latest = $stmt_latest->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['latest_message'])) {
    $latest_message = trim($_POST['latest_message']);

    if ($latest_message === '') {
        $latest_error = "内容を入力してください。";
    } else {
        if ($current_latest) {
            $stmt = $pdo->prepare("UPDATE latest_news SET message = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$latest_message, $current_latest['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO latest_news (message, updated_at) VALUES (?, NOW())");
            $stmt->execute([$latest_message]);
        }
        header('Location: news_manage.php?latest_updated=1');
        exit;
    }
}

// ===== 一覧データ取得 =====
$stmt_news = $pdo->query("SELECT * FROM news ORDER BY created_at DESC");
$newsList = $stmt_news->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>お知らせ管理 | 麻生祭2025</title>
    <link rel="stylesheet" href="master.css">
</head>

<body>
    <header>
        <h1>📰 お知らせ管理</h1>
        <a href="master_menu.php" class="back-btn">← 戻る</a>
    </header>

    <main>
        <div class="content">

            <!-- ✅ 通知表示 -->
            <?php if (isset($_GET['success'])): ?>
                <p class="notice success">✅ 新しいお知らせを登録しました。</p>
            <?php elseif (isset($_GET['deleted'])): ?>
                <p class="notice success">✅ お知らせを削除しました。</p>
            <?php elseif (isset($_GET['latest_updated'])): ?>
                <p class="notice success">✅ 最新お知らせを更新しました。</p>
            <?php elseif (isset($latest_error)): ?>
                <p class="notice error"><?= htmlspecialchars($latest_error) ?></p>
            <?php endif; ?>

            <!-- 🔔 最新お知らせ編集フォーム -->
            <section class="latest-section form-container">
                <h2>🔔 最新お知らせ（トップページバナー）</h2>
                <form action="" method="post">
                    <textarea name="latest_message" rows="3"
                        placeholder="ここに最新お知らせを入力してください"><?= htmlspecialchars($current_latest['message'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    <button type="submit" class="btn btn-blue">更新する</button>
                </form>
            </section>

            <!-- 📰 一般お知らせ一覧 -->
            <div class="news-manage-header">
                <a href="news_add.php" class="btn btn-blue">＋ 新規お知らせ追加</a>
            </div>

            <table class="news-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>タイトル</th>
                        <th>投稿日</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($newsList): ?>
                        <?php foreach ($newsList as $news): ?>
                            <tr>
                                <td><?= htmlspecialchars($news['id']) ?></td>
                                <td><?= htmlspecialchars($news['title']) ?></td>
                                <td><?= htmlspecialchars(date('Y/m/d H:i', strtotime($news['created_at']))) ?></td>
                                <td>
                                    <a href="news_edit.php?id=<?= $news['id'] ?>">編集</a> |
                                    <a href="news_delete.php?id=<?= $news['id'] ?>"
                                        onclick="return confirm('本当に削除しますか？');">削除</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">お知らせはまだ登録されていません。</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>
    </main>
</body>

</html>