<?php
require_once('../../../asset/session_check.php');
require_once('../../../asset/db_connect.php');

$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id)
    die("ログイン情報が確認できません。");

$id = $_POST['id'] ?? 0;
$date = $_POST['request_date'];
$period = $_POST['period'];
$time = $_POST['available_time'];
$remarks = $_POST['remarks'];

// 週開始日取得（締切確認）
$stmt = $pdo->prepare("SELECT week_start FROM shift_requests WHERE id = :id AND user_id = :user_id LIMIT 1");
$stmt->execute([':id' => $id, ':user_id' => $user_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row)
    die("対象データが見つかりません。");

$limit = new DateTime($row['week_start']);
$limit->modify('-14 days');
if (new DateTime() > $limit) {
    echo "<script>alert('この週の再申請は締切を過ぎています。');location.href='request_list.php';</script>";
    exit;
}

// 更新
$update = $pdo->prepare("
    UPDATE shift_requests
    SET request_date = :date,
        period = :period,
        available_time = :time,
        remarks = :remarks,
        updated_at = NOW()
    WHERE id = :id AND user_id = :user_id
");
$update->execute([
    ':date' => $date,
    ':period' => $period,
    ':time' => $time,
    ':remarks' => $remarks,
    ':id' => $id,
    ':user_id' => $user_id
]);

header('Location: request_list.php?msg=updated');
exit;
?>