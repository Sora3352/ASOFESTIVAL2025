<?php
require_once('../../asset/db_connect.php');
session_start();

// IDチェック
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: executive_list.php");
    exit();
}

$id = (int) $_GET['id'];

// データ取得（adminsテーブルから）
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$id]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$member) {
    header("Location: executive_list.php");
    exit();
}

// 更新処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $class = $_POST['class'];
    $project = $_POST['project'];
    $role = $_POST['role'];

    $update = $pdo->prepare("UPDATE admins 
        SET username = ?, name = ?, email = ?, class = ?, project = ?, role = ? 
        WHERE id = ?");
    $update->execute([$username, $name, $email, $class, $project, $role, $id]);

    // ✅ ログ記録を追加（編集した人を残す）
    $log_action = "実行委員ID {$id}（{$name}）を編集しました";
    $log = $pdo->prepare("INSERT INTO action_logs 
        (username, role, action, target_table, target_id)
        VALUES (:u, :r, :a, 'admins', :tid)");
    $log->execute([
        ':u' => $_SESSION['name'] ?? $_SESSION['username'],
        ':r' => $_SESSION['role'],
        ':a' => $log_action,
        ':tid' => $id
    ]);

    header("Location: executive_list.php?message=updated");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>実行委員編集 | 麻生祭2025</title>
    <link rel="stylesheet" href="master.css?v=4">
</head>

<body>
    <header>実行委員編集</header>

    <div class="edit-container">
        <form method="POST">

            <label>ID</label>
            <input type="text" value="<?= htmlspecialchars($member['id']) ?>" disabled>

            <label>学籍番号</label>
            <input type="text" name="username" value="<?= htmlspecialchars($member['username']) ?>">

            <label>氏名</label>
            <input type="text" name="name" value="<?= htmlspecialchars($member['name']) ?>">

            <label>メールアドレス</label>
            <input type="email" name="email" value="<?= htmlspecialchars($member['email']) ?>">

            <label>クラス</label>
            <input type="text" name="class" value="<?= htmlspecialchars($member['class']) ?>">

            <label>担当企画</label>
            <select name="project">
                <option value="ビンゴ" <?= $member['project'] === 'ビンゴ' ? 'selected' : ''; ?>>ビンゴ</option>
                <option value="カラオケ" <?= $member['project'] === 'カラオケ' ? 'selected' : ''; ?>>カラオケ</option>
                <option value="ラムネ" <?= $member['project'] === 'ラムネ' ? 'selected' : ''; ?>>ラムネ</option>
                <option value="射的" <?= $member['project'] === '射的' ? 'selected' : ''; ?>>射的</option>
                <option value="スマブラ" <?= $member['project'] === 'スマブラ' ? 'selected' : ''; ?>>スマブラ</option>
            </select>

            <label>役職</label>
            <select name="role">
                <option value="chief" <?= $member['role'] === 'chief' ? 'selected' : ''; ?>>実行委員長</option>
                <option value="vice" <?= $member['role'] === 'vice' ? 'selected' : ''; ?>>副実行委員長</option>
                <option value="teacher" <?= $member['role'] === 'teacher' ? 'selected' : ''; ?>>先生</option>
                <option value="core" <?= $member['role'] === 'core' ? 'selected' : ''; ?>>コアメンバー</option>
                <option value="leader" <?= $member['role'] === 'leader' ? 'selected' : ''; ?>>企画リーダー</option>
                <option value="member" <?= $member['role'] === 'member' ? 'selected' : ''; ?>>実行委員</option>
            </select>

            <button type="submit">更新</button>
        </form>

        <a href="executive_list.php" class="back-btn">←</a>
    </div>

    <footer>
        <p>© 2025 麻生祭実行委員会</p>
    </footer>
</body>

</html>
