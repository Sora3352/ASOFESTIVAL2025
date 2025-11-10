<?php
session_start();
require_once('../../../asset/db_connect.php');

if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit();
}
$role = $_SESSION['role'] ?? 'member';
if (!in_array($role, ['chief', 'vice', 'teacher', 'core'], true)) {
    die('権限がありません');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['project']) && !empty($_POST['deadline'])) {
        $sql = "INSERT INTO shift_deadlines(project,deadline,is_active)
            VALUES(:p,:d,1)
            ON DUPLICATE KEY UPDATE deadline=VALUES(deadline), is_active=1";
        $st = $pdo->prepare($sql);
        $st->execute([':p' => $_POST['project'], ':d' => $_POST['deadline']]);
    }
    if (!empty($_POST['deactivate_id'])) {
        $st = $pdo->prepare("UPDATE shift_deadlines SET is_active=0 WHERE id=:id");
        $st->execute([':id' => (int) $_POST['deactivate_id']]);
    }
}

$rows = $pdo->query("SELECT * FROM shift_deadlines ORDER BY project")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>申請〆切管理</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="shift.css?v=3">
</head>

<body>
    <div class="container">
        <h1>申請〆切管理（12/17・12/18共通）</h1>

        <form method="post" class="row card">
            <select name="project" class="select" required>
                <option value="">企画を選択</option>
                <option value="ramune">ラムネ早飲み</option>
                <option value="karaoke">カラオケ</option>
                <option value="sumabura">スマブラ</option>
            </select>
            <input type="datetime-local" name="deadline" class="input" required>
            <button class="btn primary">登録/更新</button>
        </form>

        <div class="card">
            <?php foreach ($rows as $r): ?>
                <div class="row" style="align-items:center;">
                    <div class="input" style="pointer-events:none; flex:1;">
                        <?= htmlspecialchars($r['project']) ?> / 〆切：<?= htmlspecialchars($r['deadline']) ?> /
                        状態：<?= $r['is_active'] ? '有効' : '無効' ?>
                    </div>
                    <?php if ($r['is_active']): ?>
                        <form method="post">
                            <input type="hidden" name="deactivate_id" value="<?= (int) $r['id'] ?>">
                            <button class="btn danger" onclick="return confirm('無効化しますか？')">無効化</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>