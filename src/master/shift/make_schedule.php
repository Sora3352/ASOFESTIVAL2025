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

$project = $_GET['project'] ?? '';
if (!$project) {
    die('projectが必要');
}

$DATES = ['2025-12-17' => '12/17(水)', '2025-12-18' => '12/18(木)'];

/* 候補の読み込み */
$st = $pdo->prepare("SELECT * FROM shift_requests WHERE project=:p ORDER BY class,name");
$st->execute([':p' => $project]);
$candidates = $st->fetchAll(PDO::FETCH_ASSOC);

/* 既存割当の読み込み */
$st = $pdo->prepare("SELECT * FROM shift_assignments WHERE project=:p AND day IN ('2025-12-17','2025-12-18')");
$st->execute([':p' => $project]);
$assigned = [];
foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $a) {
    $assigned[$a['day']][$a['block']][] = $a;
}

/* 保存 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->beginTransaction();
    try {
        $del = $pdo->prepare("DELETE FROM shift_assignments WHERE project=:p AND day IN ('2025-12-17','2025-12-18')");
        $del->execute([':p' => $project]);

        $ins = $pdo->prepare("INSERT INTO shift_assignments (project,day,block,role,user_id,name,class)
                          VALUES(:p,:d,:b,:r,:u,:n,:c)");

        foreach (array_keys($DATES) as $day) {
            foreach (['AM', 'PM', 'FULL'] as $block) {
                $ids = $_POST["assign_{$day}_{$block}"] ?? [];
                foreach ($ids as $uid) {
                    if (!$uid)
                        continue;
                    $row = null;
                    foreach ($candidates as $cand)
                        if ((int) $cand['user_id'] === (int) $uid) {
                            $row = $cand;
                            break;
                        }
                    if (!$row)
                        continue;
                    $ins->execute([
                        ':p' => $project,
                        ':d' => $day,
                        ':b' => $block,
                        ':r' => 'staff',
                        ':u' => $uid,
                        ':n' => $row['name'],
                        ':c' => $row['class']
                    ]);
                }
            }
        }

        $pdo->commit();
        header('Location: make_schedule.php?project=' . urlencode($project));
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die('保存失敗: ' . $e->getMessage());
    }
}

/* 候補一覧を絞り込み */
function options_for($candidates, $date, $need)
{
    $out = '<option value="">-- 未選択 --</option>';
    foreach ($candidates as $c) {
        $av = json_decode($c['availability_json'] ?? "{}", true) ?: [];
        $ok = strtoupper($av[$date] ?? 'NG');
        if ($need === 'AM' && !in_array($ok, ['AM', 'FULL']))
            continue;
        if ($need === 'PM' && !in_array($ok, ['PM', 'FULL']))
            continue;
        if ($need === 'FULL' && $ok !== 'FULL')
            continue;
        $out .= '<option value="' . (int) $c['user_id'] . '">' . htmlspecialchars($c['class'] . ' ' . $c['name']) . '</option>';
    }
    return $out;
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>シフト編成（<?= htmlspecialchars($project) ?>）</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="shift.css?v=3">
</head>

<body>
    <div class="container">
        <h1>シフト編成（<?= htmlspecialchars($project) ?> / 12/17・12/18）</h1>

        <form method="post" class="card">
            <div class="row">
                <div class="input" style="pointer-events:none;">企画：<?= htmlspecialchars($project) ?></div>
                <a class="btn" href="request_list.php?project=<?= urlencode($project) ?>">申請一覧へ</a>
            </div>

            <?php foreach ($DATES as $day => $label): ?>
                <div class="card">
                    <h2><?= $label ?></h2>
                    <?php foreach (['AM', 'PM', 'FULL'] as $block): ?>
                        <div class="row">
                            <div style="min-width:70px; font-weight:700;"><?= $block ?></div>
                            <select name="assign_<?= $day ?>_<?= $block ?>[]" class="select" multiple size="6" style="flex:1;">
                                <?= options_for($candidates, $day, $block) ?>
                            </select>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <div class="row">
                <button class="btn primary">この内容で保存</button>
            </div>
        </form>
    </div>
</body>

</html>