<?php
session_start();
require_once('../../asset/db_connect.php');

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$role = $_SESSION['role'] ?? 'staff';
$can_edit_roles = ['chief', 'vice', 'teacher', 'core', 'leader'];
if (!in_array($role, $can_edit_roles, true)) {
    header('Location: entry_list.php');
    exit();
}

$id = $_GET['id'] ?? '';
if ($id === '' || !is_numeric($id)) {
    header('Location: entry_list.php');
    exit();
}

// 企画名マッピング
$project_labels = [
    'ramune' => 'ラムネ早飲み',
    'karaoke' => 'カラオケ大会',
    'sumabara' => 'スマブラ大会',
    'bingo' => 'ビンゴ大会',
    'shateki' => '射的',
];

// データ取得
$stmt = $pdo->prepare("SELECT * FROM entries WHERE id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$entry = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$entry) {
    $_SESSION['flash_message'] = '指定されたデータが見つかりません。';
    header('Location: entry_list.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $name = trim($_POST['name']);
    $class = trim($_POST['class']);
    $project = trim($_POST['project']);

    if ($student_id === '' || $name === '' || $class === '' || $project === '') {
        $error = 'すべての項目を入力してください。';
    } else {
        $sql = "UPDATE entries 
                SET student_id = :student_id, name = :name, class = :class, project = :project
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':student_id', $student_id);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':class', $class);
        $stmt->bindValue(':project', $project);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // ✅ ログ記録（ここを追加）
        $log = $pdo->prepare("INSERT INTO action_logs 
            (username, role, action, target_table, target_id)
            VALUES (:u, :r, :a, 'entries', :tid)");
        $log->execute([
            ':u' => $_SESSION['name'] ?? $_SESSION['username'],
            ':r' => $_SESSION['role'],
            ':a' => "エントリーID {$id} を編集しました",
            ':tid' => $id
        ]);

        $_SESSION['flash_message'] = '更新しました。';
        header('Location: entry_list.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>エントリー編集 | ASO FESTIVAL 2025 管理</title>
    <link rel="stylesheet" href="master.css">
</head>

<body>
    <header>エントリー編集</header>

    <div class="edit-container">
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>学籍番号</label>
            <input type="text" name="student_id" value="<?= htmlspecialchars($entry['student_id']) ?>">

            <label>氏名</label>
            <input type="text" name="name" value="<?= htmlspecialchars($entry['name']) ?>">

            <label>クラス</label>
            <input type="text" name="class" value="<?= htmlspecialchars($entry['class']) ?>">

            <label>企画名</label>
            <select name="project">
                <?php foreach ($project_labels as $key => $label): ?>
                    <option value="<?= $key ?>" <?= $entry['project'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit">更新する</button>
        </form>

        <a href="entry_list.php" class="back-btn">← 一覧へ戻る</a>
    </div>
</body>

</html>