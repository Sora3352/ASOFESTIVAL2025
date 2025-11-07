<?php
session_start();
require_once('../../../asset/db_connect.php');

if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit();
}

$current_role = $_SESSION['role'] ?? '';
$username = $_SESSION['username'] ?? '';
$current_user_id = $_SESSION['user_id'] ?? 0;

// user_idがない場合はadminsから補完
if (!$current_user_id && $username) {
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $username]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $current_user_id = $row['id'] ?? 0;
}

// 管理者判定
$is_admin = in_array($current_role, ['chief', 'vice', 'teacher', 'core'], true);

// 絞り込み条件
$where = [];
$params = [];

if (!$is_admin) {
    $where[] = "user_id = :user_id";
    $params[':user_id'] = $current_user_id;
} else {
    if (!empty($_GET['project'])) {
        $where[] = "project = :project";
        $params[':project'] = $_GET['project'];
    }
    if (!empty($_GET['week_start'])) {
        $where[] = "week_start = :week_start";
        $params[':week_start'] = $_GET['week_start'];
    }
    if (!empty($_GET['name'])) {
        $where[] = "name LIKE :name";
        $params[':name'] = '%' . $_GET['name'] . '%';
    }
}

// SQL構築
$sql = "SELECT * FROM shift_requests";
if ($where)
    $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY week_start DESC, request_date ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// プロジェクト一覧
$projects = $pdo->query("SELECT DISTINCT project FROM admins ORDER BY project")->fetchAll(PDO::FETCH_COLUMN);

// 締切リスト（shift_deadlines）
$deadline_rows = $pdo->query("SELECT week_start, deadline_date FROM shift_deadlines")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>活動リクエスト一覧</title>
    <link rel="stylesheet" href="shift.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }

        th {
            background: #6b4fa3;
            color: white;
        }

        tr.rest {
            background: #f3f3f3;
            color: #666;
        }

        button.edit-btn {
            background: #6b4fa3;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 5px 8px;
            cursor: pointer;
        }

        button.edit-btn:hover {
            background: #5a3ea0;
        }

        button.edit-btn.disabled {
            background: #ccc;
            color: #777;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>📋 シフト活動リクエスト一覧</h1>

        <?php if (isset($_GET['msg'])): ?>
            <div style="text-align:center;margin:10px 0;">
                <?php if ($_GET['msg'] === 'success'): ?>
                    <p style="color:green;">✅ 申請が完了しました。</p>
                <?php elseif ($_GET['msg'] === 'reapplied'): ?>
                    <p style="color:orange;">🔄 再申請が完了しました。</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($is_admin): ?>
            <form method="get" class="filter-form" style="text-align:center;margin-bottom:10px;">
                <select name="project">
                    <option value="">企画すべて</option>
                    <?php foreach ($projects as $p): ?>
                        <option value="<?= htmlspecialchars($p) ?>" <?= ($_GET['project'] ?? '') === $p ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="date" name="week_start" value="<?= htmlspecialchars($_GET['week_start'] ?? '') ?>">
                <input type="text" name="name" placeholder="氏名で検索" value="<?= htmlspecialchars($_GET['name'] ?? '') ?>">
                <button type="submit">絞り込み</button>
            </form>
        <?php endif; ?>

        <?php if (empty($requests)): ?>
            <p style="text-align:center;">申請はまだありません。</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <?php if ($is_admin): ?>
                            <th>氏名</th>
                            <th>学籍番号</th>
                            <th>担当企画</th>
                        <?php endif; ?>
                        <th>週開始日</th>
                        <th>希望日</th>
                        <th>終わりコマ</th>
                        <th>活動可能時間</th>
                        <th>備考</th>
                        <th>申請日時</th>
                        <?php if (!$is_admin): ?>
                            <th>操作</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $r): ?>
                        <?php
                        $week = $r['week_start'];
                        $deadline_str = $deadline_rows[$week] ?? '';
                        $is_closed = false;

                        if ($deadline_str) {
                            $deadline_date = new DateTime($deadline_str);
                            $is_closed = (new DateTime() > $deadline_date);
                        } else {
                            // 締切未設定 → デフォルト2週間前
                            $limit = new DateTime($week);
                            $limit->modify('-14 days');
                            $is_closed = (new DateTime() > $limit);
                        }
                        ?>
                        <tr class="<?= $r['available_time'] === '休み希望' ? 'rest' : '' ?>">
                            <?php if ($is_admin): ?>
                                <td><?= htmlspecialchars($r['name']) ?></td>
                                <td><?= htmlspecialchars($r['username']) ?></td>
                                <td><?= htmlspecialchars($r['project']) ?></td>
                            <?php endif; ?>
                            <td><?= htmlspecialchars(date('Y/m/d', strtotime($r['week_start']))) ?></td>
                            <td><?= htmlspecialchars(date('Y/m/d', strtotime($r['request_date']))) ?></td>
                            <td><?= htmlspecialchars($r['period']) ?></td>
                            <td><?= htmlspecialchars($r['available_time']) ?></td>
                            <td><?= htmlspecialchars($r['remarks']) ?></td>
                            <td><?= htmlspecialchars(date('Y/m/d H:i', strtotime($r['created_at']))) ?></td>
                            <?php if (!$is_admin): ?>
                                <td>
                                    <?php if ($is_closed): ?>
                                        <button class="edit-btn disabled" disabled>締切済</button>
                                    <?php else: ?>
                                        <form action="request.php" method="get" style="margin:0;">
                                            <input type="hidden" name="edit_week" value="<?= htmlspecialchars($r['week_start']) ?>">
                                            <button type="submit" class="edit-btn">📝再編集</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <a href="../master_menu.php" class="back-btn">← メニューへ戻る</a>
    </div>
</body>

</html>