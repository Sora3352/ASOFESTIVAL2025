<?php
require_once('../../asset/db_connect.php');
session_start();

// ログイン中ユーザーのrole取得（login.phpで$_SESSION['role']をセットしている前提）
$currentRole = $_SESSION['role'] ?? '';

// ===== 削除処理 =====
$can_delete_roles = ['chief', 'vice', 'teacher', 'core'];

if (in_array($currentRole, $can_delete_roles, true) && isset($_POST['delete_id'])) {
    $delete_id = (int) $_POST['delete_id'];

    // 対象ユーザー情報を取得（ログに残すため）
    $target_stmt = $pdo->prepare("SELECT name FROM admins WHERE id = :id");
    $target_stmt->bindValue(':id', $delete_id, PDO::PARAM_INT);
    $target_stmt->execute();
    $target = $target_stmt->fetch(PDO::FETCH_ASSOC);

    // 実際に削除
    $del = $pdo->prepare("DELETE FROM admins WHERE id = :id");
    $del->bindValue(':id', $delete_id, PDO::PARAM_INT);
    $del->execute();

    // ✅ ログ記録（削除した人・対象者を残す）
    $log_action = "実行委員ID {$delete_id}（" . ($target['name'] ?? '不明') . "）を削除しました";
    $log = $pdo->prepare("INSERT INTO action_logs 
        (username, role, action, target_table, target_id)
        VALUES (:u, :r, :a, 'admins', :tid)");
    $log->execute([
        ':u' => $_SESSION['name'] ?? $_SESSION['username'],
        ':r' => $_SESSION['role'],
        ':a' => $log_action,
        ':tid' => $delete_id
    ]);

    header("Location: executive_list.php?message=deleted");
    exit();
}

// ===== 検索条件の取得 =====
$selected_project = $_GET['project'] ?? '';
$keyword = $_GET['keyword'] ?? '';
$message = $_GET['message'] ?? '';

// ===== SQL組み立て =====
$sql = "SELECT id, name, username AS student_id, email, class, project, role, strap, idcase FROM admins WHERE 1=1";

if ($selected_project && $selected_project !== 'all') {
    $sql .= " AND project = :project";
}
if (!empty($keyword)) {
    $sql .= " AND (name LIKE :keyword OR username LIKE :keyword OR class LIKE :keyword)";
}
$sql .= " ORDER BY id ASC";

$stmt = $pdo->prepare($sql);

if ($selected_project && $selected_project !== 'all') {
    $stmt->bindValue(':project', $selected_project, PDO::PARAM_STR);
}
if (!empty($keyword)) {
    $stmt->bindValue(':keyword', "%$keyword%", PDO::PARAM_STR);
}

$stmt->execute();
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ===== 役職変換関数 =====
function convertRole($role)
{
    $roles = [
        'chief' => '実行委員長',
        'vice' => '副実行委員長',
        'teacher' => '先生',
        'core' => 'コアメンバー',
        'leader' => '企画リーダー',
        'member' => '実行委員'
    ];
    return $roles[$role] ?? $role;
}

// ===== 編集権限チェック =====
function canEdit($currentRole)
{
    $editableRoles = ['chief', 'vice', 'teacher', 'core'];
    return in_array($currentRole, $editableRoles, true);
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>実行委員一覧</title>
    <link rel="stylesheet" href="master.css">
    <style>
        /* ===== 検索フォーム部分のみ追記 ===== */
        .search-box {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #fff;
            border-bottom: 2px solid #ccc;
            padding: 10px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .search-box form {
            display: flex;
            width: 100%;
            gap: 10px;
        }

        .search-box select,
        .search-box input[type="text"] {
            padding: 6px 10px;
            border-radius: 5px;
            border: 1px solid #aaa;
        }

        .search-box button {
            border: none;
            border-radius: 5px;
            padding: 6px 12px;
            color: #fff;
            background-color: #4CAF50;
            cursor: pointer;
        }

        .search-box button:hover {
            opacity: 0.9;
        }

        .message {
            text-align: center;
            color: green;
            font-weight: bold;
            margin: 10px 0;
        }
    </style>
</head>

<body>
    <header>実行委員一覧</header>

    <!-- ✅ 更新・削除完了メッセージ -->
    <?php if ($message === 'updated'): ?>
        <p class="message">✅ 更新が完了しました。</p>
    <?php elseif ($message === 'deleted'): ?>
        <p class="message" style="color:red;">🗑️ 実行委員を削除しました。</p>
    <?php endif; ?>

    <!-- 🔍 絞込検索フォーム -->
    <div class="search-box">
        <form method="get" action="">
            <select name="project">
                <option value="all">全ての企画</option>
                <option value="ビンゴ" <?= $selected_project === 'ビンゴ' ? 'selected' : '' ?>>ビンゴ</option>
                <option value="カラオケ" <?= $selected_project === 'カラオケ' ? 'selected' : '' ?>>カラオケ</option>
                <option value="ラムネ早飲み" <?= $selected_project === 'ラムネ早飲み' ? 'selected' : '' ?>>ラムネ早飲み</option>
                <option value="射的" <?= $selected_project === '射的' ? 'selected' : '' ?>>射的</option>
                <option value="スマブラ" <?= $selected_project === 'スマブラ' ? 'selected' : '' ?>>スマブラ</option>
            </select>
            <input type="text" name="keyword" placeholder="氏名・学籍番号・クラスで検索" value="<?= htmlspecialchars($keyword) ?>">
            <button type="submit">検索</button>
            <button type="button" onclick="window.location.href='executive_list.php'">リセット</button>
        </form>
    </div>

    <div class="container">
        <div class="table-wrapper">
            <table class="entry-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>氏名</th>
                        <th>学籍番号</th>
                        <th>メールアドレス</th>
                        <th>クラス</th>
                        <th>担当企画</th>
                        <th>役職</th>
                        <th>ストラップ</th> <!-- ✅ 追加 -->
                        <th>IDケース</th> <!-- ✅ 追加 -->
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $m): ?>
                        <tr>
                            <td><?= htmlspecialchars($m['id']) ?></td>
                            <td><?= htmlspecialchars($m['name']) ?></td>
                            <td><?= htmlspecialchars($m['student_id']) ?></td>
                            <td><?= htmlspecialchars($m['email']) ?></td>
                            <td><?= htmlspecialchars($m['class']) ?></td>
                            <td><?= htmlspecialchars($m['project']) ?></td>
                            <td><?= htmlspecialchars(convertRole($m['role'])) ?></td>

                            <!-- ✅ ストラップとIDケースの受け取り状況を表示 -->
                            <td style="text-align:center;"><?= $m['strap'] ? '✅' : '❌' ?></td>
                            <td style="text-align:center;"><?= $m['idcase'] ? '✅' : '❌' ?></td>

                            <td>
                                <?php if (canEdit($currentRole)): ?>
                                    <a href="executive_edit.php?id=<?= $m['id'] ?>" class="edit-btn">編集</a>
                                <?php else: ?>
                                    <span style="color:#aaa;">－</span>
                                <?php endif; ?>

                                <?php if (in_array($currentRole, ['chief', 'vice', 'teacher', 'core'], true)): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="delete_id" value="<?= $m['id'] ?>">
                                        <button type="submit" class="delete-btn" onclick="return confirm('本当に削除しますか？');">
                                            削除
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span style="color:#aaa;">－</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>
        <a href="master_menu.php" class="back-btn">←</a>
        <p>© 2025 麻生祭実行委員会</p>
    </footer>
</body>

</html>