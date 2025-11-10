<?php
session_start();
require_once('../../../asset/db_connect.php');

if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit();
}
$role = $_SESSION['role'] ?? 'member';
$user_id = (int) ($_SESSION['user_id'] ?? 0);
$is_admin = in_array($role, ['chief', 'vice', 'teacher', 'core'], true);

$project = $_GET['project'] ?? '';

$where = [];
$params = [];
if ($project) {
    $where[] = "project=:p";
    $params[':p'] = $project;
}
if (!$is_admin) {
    $where[] = "user_id=:u";
    $params[':u'] = $user_id;
}  // 一般は自分の分のみ

$sql = "SELECT * FROM shift_requests";
if ($where)
    $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY class, name";
$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll(PDO::FETCH_ASSOC);

$DATES = ['2025-12-17' => '12/17(水)', '2025-12-18' => '12/18(木)'];

function badge($v)
{
    return $v === 'NG' ? '<span class="badge ng">NG</span>' : '<span class="badge ok">' . $v . '</span>';
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>申請一覧</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="shift.css?v=3">
</head>

<body>
    <div class="container">
        <h1>申請一覧</h1>

        <form class="row" method="get">
            <select name="project" class="select">
                <option value="">全企画</option>
                <option value="ramune" <?= $project === 'ramune' ? 'selected' : ''; ?>>ラムネ早飲み</option>
                <option value="karaoke" <?= $project === 'karaoke' ? 'selected' : ''; ?>>カラオケ</option>
                <option value="sumabura" <?= $project === 'sumabura' ? 'selected' : ''; ?>>スマブラ</option>
            </select>
            <button class="btn">絞り込み</button>
            <?php if ($is_admin && $project): ?>
                <a class="btn" href="make_schedule.php?project=<?= urlencode($project) ?>">この企画で編成へ</a>
            <?php endif; ?>
        </form>

        <!-- スマホ：カード -->
        <div class="list-mobile">
            <?php foreach ($rows as $r):
                $av = json_decode($r['availability_json'] ?? "{}", true) ?: [];
                ?>
                <div class="card req-item">
                    <div class="req-head"><?= htmlspecialchars($r['class']) ?>     <?= htmlspecialchars($r['name']) ?></div>
                    <div class="req-tags"><span class="badge"><?= htmlspecialchars($r['project']) ?></span></div>
                    <div>
                        <?php foreach ($DATES as $d => $label):
                            $v = strtoupper($av[$d] ?? 'NG'); ?>
                            <div><strong><?= $label ?>：</strong>
                                <?= $v === 'NG' ? '<span class="badge ng">NG</span>' : '<span class="badge ok">' . $v . '</span>' ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($r['notes'])): ?>
                        <div class="help">備考：<?= nl2br(htmlspecialchars($r['notes'])) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- タブレット以上：テーブル -->
        <table class="table">
            <thead>
                <tr>
                    <th>クラス</th>
                    <th>名前</th>
                    <th>企画</th>
                    <?php foreach ($DATES as $label): ?>
                        <th><?= $label ?></th><?php endforeach; ?>
                    <th>備考</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r):
                    $av = json_decode($r['availability_json'] ?? "{}", true) ?: [];
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($r['class']) ?></td>
                        <td><?= htmlspecialchars($r['name']) ?></td>
                        <td><?= htmlspecialchars($r['project']) ?></td>
                        <?php foreach (array_keys($DATES) as $d):
                            $v = strtoupper($av[$d] ?? 'NG'); ?>
                            <td><?= $v === 'NG' ? '<span class="badge ng">NG</span>' : '<span class="badge ok">' . $v . '</span>' ?>
                            </td>
                        <?php endforeach; ?>
                        <td><?= nl2br(htmlspecialchars($r['notes'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</body>

</html>