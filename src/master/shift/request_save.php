<?php
session_start();
require_once('../../../asset/db_connect.php');

if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = (int) ($_SESSION['user_id'] ?? 0);
$username = $_SESSION['username'] ?? '';
$class = $_SESSION['class'] ?? '';
$role = $_SESSION['role'] ?? 'member';
$is_admin = in_array($role, ['chief', 'vice', 'teacher', 'core'], true);

$project = $_POST['project'] ?? '';
$avail = $_POST['avail'] ?? [];
$notes = $_POST['notes'] ?? '';
if (!$user_id || !$project) {
    die('不正アクセス');
}

$DATES = ['2025-12-17', '2025-12-18'];

/* 締切チェック */
$st = $pdo->prepare("SELECT * FROM shift_deadlines WHERE project=:p AND is_active=1");
$st->execute([':p' => $project]);
$ddl = $st->fetch(PDO::FETCH_ASSOC);

$editable = true;
if ($ddl) {
    $now = new DateTime();
    $deadline = new DateTime($ddl['deadline']);
    if ($now > $deadline && !$is_admin)
        $editable = false;
}
if (!$editable) {
    die('〆切後のため編集できません');
}

/* 整形 */
$data = [];
foreach ($DATES as $d) {
    $v = strtoupper($avail[$d] ?? 'NG');
    if (!in_array($v, ['NG', 'AM', 'PM', 'FULL'], true))
        $v = 'NG';
    $data[$d] = $v;
}
$availability_json = json_encode($data, JSON_UNESCAPED_UNICODE);

/* upsert */
$st = $pdo->prepare("SELECT id FROM shift_requests WHERE user_id=:u AND project=:p");
$st->execute([':u' => $user_id, ':p' => $project]);
$id = $st->fetchColumn();

if ($id) {
    $q = $pdo->prepare("UPDATE shift_requests SET availability_json=:aj, notes=:n WHERE id=:id");
    $q->execute([':aj' => $availability_json, ':n' => $notes, ':id' => $id]);
} else {
    $q = $pdo->prepare("INSERT INTO shift_requests (user_id,name,class,project,availability_json,notes)
                      VALUES (:u,:nm,:cl,:p,:aj,:n)");
    $q->execute([':u' => $user_id, ':nm' => $username, ':cl' => $class, ':p' => $project, ':aj' => $availability_json, ':n' => $notes]);
}

header("Location: request.php?project=" . urlencode($project));
exit;
