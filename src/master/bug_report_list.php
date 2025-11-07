<?php
//------------------------------------------------------
// 麻生祭2025 - バグ報告一覧画面
// JSフォームから登録されたバグ報告を表示
//------------------------------------------------------

session_start();
require_once('../../asset/db_connect.php');

// 🔐 ログインチェック（masterログイン済みユーザーのみ）
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// データ取得
$stmt = $pdo->query("SELECT * FROM bug_reports ORDER BY submitted_at DESC");
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>バグ報告一覧 | 麻生祭2025</title>
    <link rel="stylesheet" href="master.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
            color: #222;
            /* ✅ テーブル文字を濃くする */
            background: #fff;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            vertical-align: top;
            color: #222;
            /* ✅ ここでも明示的に指定 */
        }

        th {
            background: #eee;
            color: #000;
            /* ✅ ヘッダー文字は黒に */
        }

        td {
            background: #fafafa;
        }
    </style>
</head>

<body>
    <header>
        <h1>🪲 バグ報告一覧</h1>
    </header>

    <main>
        <?php if (empty($reports)): ?>
            <p class="no-data">現在、報告はありません。</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>報告者</th> <!-- ←追加 -->
                        <th>ページ名</th>
                        <th>内容</th>
                        <th>日時</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['id'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($r['reporter'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($r['page'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="message"><?= nl2br(htmlspecialchars($r['message'], ENT_QUOTES, 'UTF-8')) ?></td>
                            <td><?= htmlspecialchars($r['submitted_at'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <a href="master_menu.php" class="back-btn">←</a>
    </main>
</body>

</html>