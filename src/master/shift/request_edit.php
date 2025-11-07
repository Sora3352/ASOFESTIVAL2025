<?php
require_once('../../../asset/session_check.php');
require_once('../../../asset/db_connect.php');

$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id)
    die("ログイン情報が確認できません。");

$id = $_GET['id'] ?? 0;

// 自分の申請だけ取得
$stmt = $pdo->prepare("SELECT * FROM shift_requests WHERE id = :id AND user_id = :user_id LIMIT 1");
$stmt->execute([':id' => $id, ':user_id' => $user_id]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request)
    die("申請が見つかりません。");

// 締切チェック
$limit = new DateTime($request['week_start']);
$limit->modify('-14 days');
if (new DateTime() > $limit) {
    echo "<script>alert('この週の再申請は締切を過ぎています。');location.href='request_list.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>再申請 - シフト活動</title>
    <link rel="stylesheet" href="shift.css">
</head>

<body>
    <div class="container">
        <h1>シフト活動 再申請</h1>

        <form action="request_update.php" method="post">
            <input type="hidden" name="id" value="<?= htmlspecialchars($request['id']) ?>">

            <label>日付：</label>
            <input type="date" name="request_date" value="<?= htmlspecialchars($request['request_date']) ?>" required>

            <label>終わりコマ：</label>
            <select name="period" required>
                <?php foreach (['1限', '2限', '3限', '4限'] as $p): ?>
                    <option value="<?= $p ?>" <?= $p === $request['period'] ? 'selected' : '' ?>><?= $p ?></option>
                <?php endforeach; ?>
            </select>

            <label>活動可能時間：</label>
            <input type="text" name="available_time" value="<?= htmlspecialchars($request['available_time']) ?>">

            <label>備考：</label>
            <input type="text" name="remarks" value="<?= htmlspecialchars($request['remarks']) ?>">

            <button type="submit" class="submit-btn">再申請する</button>
        </form>

        <a href="request_list.php" class="back-btn">← 戻る</a>
    </div>
</body>

</html>