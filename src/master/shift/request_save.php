<?php
session_start();
require_once('../../../asset/db_connect.php');

if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit();
}

// ===== admins からユーザー情報を取得（usernameベース） =====
$username_sess = $_SESSION['username'] ?? '';

$stmt = $pdo->prepare("SELECT id, username, name, project FROM admins WHERE username = :username LIMIT 1");
$stmt->execute([':username' => $username_sess]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    echo "<script>alert('ユーザー情報を取得できません。再ログインしてください。');history.back();</script>";
    exit;
}

$user_id = $admin['id'];
$username = $admin['username'];
$name = $admin['name'];
$project = $admin['project'];

// ===== POSTデータ =====
$week_start = $_POST['week_start'] ?? null;
$dates = $_POST['request_date'] ?? [];
$periods = $_POST['period'] ?? [];
$start_times = $_POST['start_time'] ?? [];
$end_times = $_POST['end_time'] ?? [];
$remarks = $_POST['remarks'] ?? [];
$rests = $_POST['rest_flag'] ?? [];

if (!$week_start || empty($dates)) {
    echo "<script>alert('入力内容が不足しています。再度申請してください。');history.back();</script>";
    exit;
}

// ===== 締切チェック =====
$stmt = $pdo->prepare("SELECT deadline_date FROM shift_deadlines WHERE week_start = :week LIMIT 1");
$stmt->execute([':week' => $week_start]);
$dl = $stmt->fetch(PDO::FETCH_ASSOC);

if ($dl) {
    $deadline = new DateTime($dl['deadline_date']);
    if (new DateTime() > $deadline) {
        echo "<script>alert('この週（" . date('Y/m/d', strtotime($week_start)) . "〜）の締切 " . $deadline->format('Y/m/d') . " を過ぎています。');history.back();</script>";
        exit;
    }
} else {
    $limit = new DateTime($week_start);
    $limit->modify('-14 days');
    if (new DateTime() > $limit) {
        echo "<script>alert('この週の申請締切（デフォルト2週間前）が過ぎています。');history.back();</script>";
        exit;
    }
}

// ===== 既存データがあるかチェック（再申請対応） =====
$check = $pdo->prepare("SELECT COUNT(*) FROM shift_requests WHERE user_id = :uid AND week_start = :week");
$check->execute([':uid' => $user_id, ':week' => $week_start]);
$exists = $check->fetchColumn();

// ===== データ上書きまたは新規 =====
if ($exists > 0) {
    // 既存データを一旦削除（再登録で上書き）
    $del = $pdo->prepare("DELETE FROM shift_requests WHERE user_id = :uid AND week_start = :week");
    $del->execute([':uid' => $user_id, ':week' => $week_start]);
    $is_reapply = true;
} else {
    $is_reapply = false;
}

$sql = "INSERT INTO shift_requests 
        (user_id, username, name, project, week_start, request_date, period, available_time, remarks)
        VALUES (:user_id, :username, :name, :project, :week_start, :request_date, :period, :available_time, :remarks)";
$stmt = $pdo->prepare($sql);

for ($i = 0; $i < count($dates); $i++) {
    $is_rest = isset($rests[$i]);

    $period = $is_rest ? '-' : ($periods[$i] ?? '');
    $available_time = $is_rest ? '休み希望' : (($start_times[$i] ?? '') . "〜" . ($end_times[$i] ?? ''));
    $remark = $is_rest ? ($remarks[$i] ?: '休み希望') : ($remarks[$i] ?? '');

    $stmt->execute([
        ':user_id' => $user_id,
        ':username' => $username,
        ':name' => $name,
        ':project' => $project,
        ':week_start' => $week_start,
        ':request_date' => $dates[$i],
        ':period' => $period,
        ':available_time' => $available_time,
        ':remarks' => $remark,
    ]);
}

// ===== 完了メッセージ =====
$msg = $is_reapply ? 'reapplied' : 'success';
header("Location: request_list.php?msg={$msg}");
exit;
?>