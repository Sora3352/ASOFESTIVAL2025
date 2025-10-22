<?php
require_once('../../asset/db_connect.php');
session_start();

// ===== 権限制御 =====
$role = $_SESSION['role'] ?? '';
$allowed_roles = ['chief', 'vice', 'teacher'];
if (!in_array($role, $allowed_roles, true)) {
    header('Location: master_menu.php');
    exit();
}

// ===== フィルター・検索条件 =====
$filter = $_GET['filter'] ?? 'all';
$keyword = $_GET['keyword'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$order = $_GET['order'] ?? 'DESC';
$export = isset($_GET['export']);

// ===== SQL組み立て =====
$sql = "SELECT * FROM action_logs WHERE 1=1";
$params = [];

if ($filter !== 'all') {
    $sql .= " AND target_table = :filter";
    $params[':filter'] = $filter;
}
if ($keyword !== '') {
    $sql .= " AND (username LIKE :kw OR action LIKE :kw)";
    $params[':kw'] = "%{$keyword}%";
}
if ($date_from !== '') {
    $sql .= " AND created_at >= :from";
    $params[':from'] = $date_from . " 00:00:00";
}
if ($date_to !== '') {
    $sql .= " AND created_at <= :to";
    $params[':to'] = $date_to . " 23:59:59";
}
$sql .= " ORDER BY created_at $order";

$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v)
    $stmt->bindValue($k, $v);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ===== CSV出力 =====
if ($export) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="action_logs.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', '日時', 'ユーザー', '役職', '内容', '種別']);
    foreach ($logs as $log) {
        fputcsv($output, [
            $log['id'],
            $log['created_at'],
            $log['username'],
            $log['role'],
            $log['action'],
            $log['target_table']
        ]);
    }
    fclose($output);
    exit();
}

// ===== ラベル設定 =====
function tableLabel($table)
{
    $labels = [
        'admins' => ['実行委員', '#3b82f6'],
        'entries' => ['エントリー', '#10b981'],
        'login_logs' => ['ログイン', '#f59e0b'],
        'system' => ['システム', '#8b5cf6'],
    ];
    return $labels[$table] ?? ['その他', '#9ca3af'];
}

function convertRole($r)
{
    return [
        'chief' => '実行委員長',
        'vice' => '副実行委員長',
        'teacher' => '先生',
        'core' => 'コアメンバー',
        'leader' => '企画リーダー',
        'member' => '実行委員',
    ][$r] ?? $r;
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>操作ログ一覧 | 麻生祭2025 管理</title>
    <link rel="stylesheet" href="master.css">
    <style>
        .search-box {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #fff;
            border-bottom: 2px solid #ccc;
            padding: 10px 15px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        select,
        input[type="text"],
        input[type="date"] {
            padding: 6px 10px;
            border-radius: 5px;
            border: 1px solid #aaa;
        }

        button {
            border: none;
            border-radius: 5px;
            padding: 6px 12px;
            cursor: pointer;
        }

        .btn-search {
            background-color: #4CAF50;
            color: #fff;
        }

        .btn-csv {
            background-color: #2563eb;
            color: #fff;
        }

        .btn-reset {
            background-color: #9ca3af;
            color: #fff;
        }

        .btn-order {
            background-color: #f59e0b;
            color: #fff;
        }

        .log-type {
            color: #fff;
            font-size: 0.85em;
            padding: 3px 8px;
            border-radius: 8px;
        }

        .table-wrapper {
            margin-top: 10px;
        }

        .entry-table th,
        .entry-table td {
            text-align: center;
            padding: 8px;
        }

        .entry-table td:nth-child(4) {
            text-align: left;
        }

        .filter-title {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <header>操作ログ一覧</header>

    <!-- 🔍 絞り込み・検索 -->
    <div class="search-box">
        <form method="get" action="">
            <span class="filter-title">種類:</span>
            <select name="filter">
                <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>すべて</option>
                <option value="admins" <?= $filter === 'admins' ? 'selected' : '' ?>>実行委員一覧</option>
                <option value="entries" <?= $filter === 'entries' ? 'selected' : '' ?>>エントリー</option>
                <option value="login_logs" <?= $filter === 'login_logs' ? 'selected' : '' ?>>ログインログ</option>
            </select>

            <span class="filter-title">検索:</span>
            <input type="text" name="keyword" placeholder="ユーザー名・内容で検索" value="<?= htmlspecialchars($keyword) ?>">

            <span class="filter-title">期間:</span>
            <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>">～
            <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>">

            <button type="submit" class="btn-search">検索</button>
            <button type="button" class="btn-reset" onclick="window.location.href='action_log.php'">リセット</button>
            <button type="submit" name="export" value="1" class="btn-csv">CSV出力</button>
            <button type="submit" name="order" value="<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>" class="btn-order">
                <?= $order === 'ASC' ? '🕒 新しい順' : '⏫ 古い順' ?>
            </button>
        </form>
    </div>

    <div class="container">
        <div class="table-wrapper">
            <table class="entry-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>日時</th>
                        <th>操作ユーザー</th>
                        <th>役職</th>
                        <th>内容</th>
                        <th>種別</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php foreach ($logs as $log): ?>
                            <?php [$label, $color] = tableLabel($log['target_table']); ?>
                            <tr>
                                <td><?= htmlspecialchars($log['id']) ?></td>
                                <td><?= htmlspecialchars($log['created_at']) ?></td>
                                <td><?= htmlspecialchars($log['username']) ?></td>
                                <td><?= htmlspecialchars(convertRole($log['role'])) ?></td>
                                <td><?= htmlspecialchars($log['action']) ?></td>
                                <td><span class="log-type" style="background-color: <?= $color ?>;"><?= $label ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">該当するログはありません。</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>
        <a href="master_menu.php" class="back-btn">←</a>
        <p>© 2025 麻生祭実行委員会</p>
    </footer>
</body>

</html>