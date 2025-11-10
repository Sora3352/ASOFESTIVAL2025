<?php
session_start();
require_once('../../../asset/db_connect.php');

/* ==== セッション確認（ログイン必須） ==== */
if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit();
}
$user_id = (int) ($_SESSION['user_id'] ?? 0);
$username = $_SESSION['username'] ?? '';
$class = $_SESSION['class'] ?? '';
$role = $_SESSION['role'] ?? 'member';
$is_admin = in_array($role, ['chief', 'vice', 'teacher', 'core'], true);

/* ==== 固定日付 ==== */
$DATES = [
    '2025-12-17' => '12/17(水)',
    '2025-12-18' => '12/18(木)',
];

/* ==== 企画選択 ==== */
$project = $_GET['project'] ?? 'karaoke';

/* ==== 企画の締切取得 ==== */
$ddl = null;
$st = $pdo->prepare("SELECT * FROM shift_deadlines WHERE project=:p AND is_active=1");
$st->execute([':p' => $project]);
$ddl = $st->fetch(PDO::FETCH_ASSOC);

$now = new DateTime();
$editable = true;
if ($ddl) {
    $deadline = new DateTime($ddl['deadline']);
    if ($now > $deadline && !$is_admin)
        $editable = false;
}

/* ==== 既存申請の取得 ==== */
$req = null;
$st = $pdo->prepare("SELECT * FROM shift_requests WHERE user_id=:u AND project=:p");
$st->execute([':u' => $user_id, ':p' => $project]);
$req = $st->fetch(PDO::FETCH_ASSOC);

$availability = ['2025-12-17' => 'NG', '2025-12-18' => 'NG'];
if ($req) {
    $decoded = json_decode($req['availability_json'] ?? '{}', true);
    foreach ($availability as $d => $v) {
        if (isset($decoded[$d]))
            $availability[$d] = strtoupper($decoded[$d]);
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>シフト希望申請（12/17・12/18）</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="shift.css?v=3">
</head>

<body>
    <div class="container">
        <h1>シフト希望申請（12/17・12/18）</h1>

        <div class="row">
            <form method="get" class="row" style="flex:1">
                <select name="project" class="select">
                    <option value="ramune" <?= $project === 'ramune' ? 'selected' : ''; ?>>ラムネ早飲み</option>
                    <option value="karaoke" <?= $project === 'karaoke' ? 'selected' : ''; ?>>カラオケ</option>
                    <option value="sumabura" <?= $project === 'sumabura' ? 'selected' : ''; ?>>スマブラ</option>
                </select>
                <button class="btn">表示</button>
            </form>
        </div>

        <?php if ($ddl): ?>
            <div class="notice">この企画の申請〆切：<strong><?= htmlspecialchars($ddl['deadline']) ?></strong></div>
        <?php else: ?>
            <div class="help">この企画は現在、〆切未設定です。</div>
        <?php endif; ?>

        <?php if (!$editable): ?>
            <div class="notice">〆切を過ぎているため、本人編集はできません（管理者は可）。</div>
        <?php endif; ?>

        <div class="card">
            <h2>基本情報</h2>
            <div class="row">
                <div class="input" style="pointer-events:none;">氏名：<?= htmlspecialchars($username) ?></div>
                <div class="input" style="pointer-events:none;">クラス：<?= htmlspecialchars($class) ?></div>
                <div class="input" style="pointer-events:none;">企画：<?= htmlspecialchars($project) ?></div>
            </div>
        </div>

        <form action="request_save.php" method="post" class="card"
            <?= $editable ? '' : 'style="opacity:.7;pointer-events:none;"' ?>>
            <input type="hidden" name="project" value="<?= htmlspecialchars($project) ?>">

            <h2>出られる時間</h2>
            <?php foreach ($DATES as $date => $label):
                $v = $availability[$date] ?? 'NG'; ?>
                <div class="row" style="align-items:center;">
                    <div style="min-width:110px; font-weight:700;"><?= $label ?></div>
                    <label class="inline"><input type="radio" name="avail[<?= $date ?>]" value="NG"
                            <?= $v === 'NG' ? 'checked' : ''; ?>>不可</label>
                    <label class="inline"><input type="radio" name="avail[<?= $date ?>]" value="AM"
                            <?= $v === 'AM' ? 'checked' : ''; ?>>午前</label>
                    <label class="inline"><input type="radio" name="avail[<?= $date ?>]" value="PM"
                            <?= $v === 'PM' ? 'checked' : ''; ?>>午後</label>
                    <label class="inline"><input type="radio" name="avail[<?= $date ?>]" value="FULL"
                            <?= $v === 'FULL' ? 'checked' : ''; ?>>終日</label>
                </div>
            <?php endforeach; ?>

            <h2>備考</h2>
            <textarea name="notes" class="textarea"
                placeholder="例：17日は15時以降OKなど"><?= htmlspecialchars($req['notes'] ?? '') ?></textarea>

            <?php if ($editable): ?>
                <div class="row">
                    <button class="btn primary" type="submit">希望を保存</button>
                    <a class="btn" href="request_list.php?project=<?= urlencode($project) ?>">みんなの申請を見る</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</body>

</html>