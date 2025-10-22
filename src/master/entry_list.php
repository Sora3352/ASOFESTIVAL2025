<?php
session_start();
require_once('../../asset/db_connect.php');

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$role = $_SESSION['role'] ?? 'staff';

// ===== 削除処理 =====
$can_delete_roles = ['chief', 'vice', 'teacher', 'core'];
if (in_array($role, $can_delete_roles, true) && isset($_POST['delete_id'])) {
    $delete_id = (int) $_POST['delete_id'];

    // 対象データを先に取得（削除ログに名前など残したい場合）
    $target_stmt = $pdo->prepare("SELECT name, student_id FROM entries WHERE id = :id");
    $target_stmt->bindValue(':id', $delete_id, PDO::PARAM_INT);
    $target_stmt->execute();
    $target = $target_stmt->fetch(PDO::FETCH_ASSOC);

    // 実際の削除
    $del = $pdo->prepare("DELETE FROM entries WHERE id = :id");
    $del->bindValue(':id', $delete_id, PDO::PARAM_INT);
    $del->execute();

    // ✅ ログ記録（ここを追加）
    $log_action = "エントリーID {$delete_id}（" . ($target['name'] ?? '不明') . "）を削除しました";
    $log = $pdo->prepare("INSERT INTO action_logs 
        (username, role, action, target_table, target_id)
        VALUES (:u, :r, :a, 'entries', :tid)");
    $log->execute([
        ':u' => $_SESSION['name'] ?? $_SESSION['username'],
        ':r' => $_SESSION['role'],
        ':a' => $log_action,
        ':tid' => $delete_id
    ]);

    // 通常処理
    $_SESSION['flash_message'] = '削除しました。';
    header('Location: entry_list.php');
    exit();
}

// ===== フラッシュメッセージ =====
$flash_message = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);

// ===== 企画名マッピング =====
$project_labels = [
    'ramune' => 'ラムネ早飲み',
    'karaoke' => 'カラオケ大会',
    'sumabara' => 'スマブラ大会',
    'bingo' => 'ビンゴ大会',
    'shateki' => '射的',
];

// ===== 検索・絞り込み・並び替え =====
$search = $_GET['search'] ?? '';
$filter_project = $_GET['filter_project'] ?? '';
$filter_class = $_GET['filter_class'] ?? '';
$sort = $_GET['sort'] ?? 'created_at';
$order = $_GET['order'] ?? 'DESC';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// SQLベース
$sql_base = "FROM entries WHERE 1";
$params = [];

if ($search !== '') {
    $sql_base .= " AND (name LIKE :search OR project LIKE :search OR class LIKE :search OR student_id LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}
if ($filter_project !== '') {
    $sql_base .= " AND project = :filter_project";
    $params[':filter_project'] = $filter_project;
}
if ($filter_class !== '') {
    $sql_base .= " AND class = :filter_class";
    $params[':filter_class'] = $filter_class;
}

// 件数取得
$count_stmt = $pdo->prepare("SELECT COUNT(*) " . $sql_base);
$count_stmt->execute($params);
$total_entries = $count_stmt->fetchColumn();
$total_pages = ceil($total_entries / $limit);

// 並び替え
$validSorts = ['project', 'class', 'created_at'];
if (!in_array($sort, $validSorts))
    $sort = 'created_at';
$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

// データ取得
$sql = "SELECT * " . $sql_base . " ORDER BY $sort $order LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v)
    $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ===== 権限関数 =====
function canEdit($role)
{
    return in_array($role, ['chief', 'vice', 'teacher', 'core', 'leader'], true);
}
function canDelete($role)
{
    return in_array($role, ['chief', 'vice', 'teacher', 'core'], true);
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>エントリー一覧 | ASO FESTIVAL 2025 管理</title>
    <link rel="stylesheet" href="master.css">
    <script>
        function confirmDelete(id) {
            if (confirm("本当に削除しますか？")) {
                document.getElementById('delete_id').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</head>

<body>
    <header>エントリー一覧</header>

    <div class="container">
        <?php if ($flash_message): ?>
            <div class="flash"><?= htmlspecialchars($flash_message) ?></div>
        <?php endif; ?>

        <!-- 🔍 検索フォーム（デザイン完全維持） -->
        <form class="search-box" method="get" action="">
            <input type="text" name="search" placeholder="名前・企画名・クラスなどで検索" value="<?= htmlspecialchars($search) ?>">

            <select name="filter_project">
                <option value="">企画を選択</option>
                <?php foreach ($project_labels as $key => $label): ?>
                    <option value="<?= $key ?>" <?= $filter_project === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>

            <select name="filter_class">
                <option value="">クラスを選択</option>
                <?php
                $classList = $pdo->query("SELECT DISTINCT class FROM entries ORDER BY class ASC")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($classList as $cls):
                    ?>
                    <option value="<?= htmlspecialchars($cls) ?>" <?= $filter_class === $cls ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cls) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">検索・絞り込み</button>
        </form>

        <!-- 📋 テーブル（UIそのまま） -->
        <div class="table-wrapper">
            <table class="entry-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>学籍番号</th>
                        <th>氏名</th>
                        <th>クラス</th>
                        <th>企画</th>
                        <th>登録日時</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $entry): ?>
                        <tr>
                            <td><?= htmlspecialchars($entry['id']) ?></td>
                            <td><?= htmlspecialchars($entry['student_id']) ?></td>
                            <td><?= htmlspecialchars($entry['name']) ?></td>
                            <td><?= htmlspecialchars($entry['class']) ?></td>
                            <td><?= htmlspecialchars($project_labels[$entry['project']] ?? htmlspecialchars($entry['project'])) ?>
                            </td>
                            <td><?= htmlspecialchars($entry['created_at']) ?></td>
                            <td>
                                <?php if (canEdit($role)): ?>
                                    <a href="entry_edit.php?id=<?= $entry['id'] ?>" class="edit-btn">編集</a>
                                <?php else: ?>
                                    <span style="color:#aaa;">－</span>
                                <?php endif; ?>

                                <?php if (canDelete($role)): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="delete_id" value="<?= $entry['id'] ?>">
                                        <button type="submit" class="delete-btn"
                                            onclick="return confirm('本当に削除しますか？');">削除</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color:#aaa;">－</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- ページネーション -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current-page"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="page-link"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

        <a href="./master_menu.php" class="back-btn">←</a>
    </div>
</body>

</html>