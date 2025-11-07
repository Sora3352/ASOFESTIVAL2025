<?php
session_start();
require_once('../../asset/db_connect.php');

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$role = $_SESSION['role'] ?? 'staff';
$user_project = $_SESSION['project'] ?? '';

// ===== 削除処理（entry_list.phpのロジックを完全継承） =====
$can_delete_roles = ['chief', 'vice', 'teacher', 'core'];
if (in_array($role, $can_delete_roles, true) && isset($_POST['delete_id'])) {
    $delete_id = (int) $_POST['delete_id'];

    $target_stmt = $pdo->prepare("SELECT name, student_id FROM entries WHERE id = :id");
    $target_stmt->bindValue(':id', $delete_id, PDO::PARAM_INT);
    $target_stmt->execute();
    $target = $target_stmt->fetch(PDO::FETCH_ASSOC);

    $del = $pdo->prepare("DELETE FROM entries WHERE id = :id");
    $del->bindValue(':id', $delete_id, PDO::PARAM_INT);
    $del->execute();

    $log_action = "スマブラ大会のエントリーID {$delete_id}（" . ($target['name'] ?? '不明') . "）を削除しました";
    $log = $pdo->prepare("INSERT INTO action_logs (username, role, action, target_table, target_id)
                          VALUES (:u, :r, :a, 'entries', :tid)");
    $log->execute([
        ':u' => $_SESSION['name'] ?? $_SESSION['username'],
        ':r' => $_SESSION['role'],
        ':a' => $log_action,
        ':tid' => $delete_id
    ]);

    $_SESSION['flash_message'] = '削除しました。';
    header('Location: entry_sumabura.php');
    exit();
}

// ===== フラッシュメッセージ =====
$flash_message = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);

// ===== 権限関数 =====
function canEdit($role, $user_project)
{
    return in_array($role, ['chief', 'vice', 'teacher', 'core'], true)
        || ($role === 'leader' && $user_project === 'sumabura');
}
function canDelete($role)
{
    return in_array($role, ['chief', 'vice', 'teacher', 'core'], true);
}

// ===== アクセス制御 =====
if ($role === 'member' && $user_project !== 'sumabura') {
    echo "<script>alert('アクセス権限がありません。');window.location.href='entry_list.php';</script>";
    exit();
}

// ===== 検索・絞り込み・ページネーション =====
$search = $_GET['search'] ?? '';
$filter_class = $_GET['filter_class'] ?? '';
$sort = $_GET['sort'] ?? 'created_at';
$order = $_GET['order'] ?? 'DESC';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$sql_base = "FROM entries WHERE project = 'sumabura'";
$params = [];

if ($search !== '') {
    $sql_base .= " AND (name LIKE :search OR class LIKE :search OR student_id LIKE :search)";
    $params[':search'] = '%' . $search . '%';
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
$validSorts = ['class', 'created_at'];
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
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スマブラ大会 | ASO FESTIVAL 2025 管理</title>
    <link rel="stylesheet" href="master.css">
</head>

<body>
    <header>スマブラ大会エントリー一覧</header>

    <div class="container">
        <?php if ($flash_message): ?>
            <div class="flash"><?= htmlspecialchars($flash_message) ?></div>
        <?php endif; ?>

        <!-- 🔍 検索フォーム -->
        <form class="search-box" method="get" action="">
            <input type="text" name="search" placeholder="名前・クラスなどで検索" value="<?= htmlspecialchars($search) ?>">

            <select name="filter_class">
                <option value="">クラスを選択</option>
                <?php
                $classList = $pdo->query("SELECT DISTINCT class FROM entries WHERE project='sumabura' ORDER BY class ASC")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($classList as $cls):
                    ?>
                    <option value="<?= htmlspecialchars($cls) ?>" <?= $filter_class === $cls ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cls) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">検索・絞り込み</button>
        </form>

        <!-- 📋 スマブラ専用テーブル -->
        <div class="table-wrapper">
            <table class="entry-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>学籍番号</th>
                        <th>氏名</th>
                        <th>クラス</th>
                        <th>参加形態</th>
                        <th>2人目学籍番号</th>
                        <th>2人目クラス</th>
                        <th>2人目氏名</th>
                        <th>予選会同意</th>
                        <th>麻生祭大会出場規約</th>
                        <th>任天堂大会規約</th>
                        <th>エントリーナンバー</th>
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
                            <td><?= htmlspecialchars($entry['mode']) ?></td>
                            <td><?= htmlspecialchars($entry['second_student_id']) ?></td>
                            <td><?= htmlspecialchars($entry['second_class']) ?></td>
                            <td><?= htmlspecialchars($entry['second_name']) ?></td>
                            <td><?= $entry['preliminary'] ? '✔' : '-' ?></td>
                            <td><?= $entry['agreement'] ? '✔' : '-' ?></td>
                            <td><?= $entry['nintendo_agreement'] ? '✔' : '-' ?></td>
                            <td><?= htmlspecialchars($entry['entry_number']) ?></td>
                            <td><?= htmlspecialchars($entry['created_at']) ?></td>
                            <td>
                                <?php if (canEdit($role, $user_project)): ?>
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

        <a href="entry_list.php" class="back-btn">←</a>
    </div>
</body>

</html>