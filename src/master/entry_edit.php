<?php
session_start();
require_once('../../asset/db_connect.php');

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$role = $_SESSION['role'] ?? 'staff';
$user_project = $_SESSION['project'] ?? '';

// ===== 権限関数 =====
function canEdit($role, $user_project, $target_project)
{
    if (in_array($role, ['chief', 'vice', 'teacher', 'core']))
        return true;
    if ($role === 'leader' && $user_project === $target_project)
        return true;
    return false;
}

// ===== データ取得 =====
$id = $_GET['id'] ?? null;
if (!$id) {
    die("無効なアクセスです。");
}

$stmt = $pdo->prepare("SELECT * FROM entries WHERE id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    die("データが見つかりません。");
}

$project = $row['project'];

// ===== アクセス制御 =====
if ($role === 'member' && $user_project !== $project) {
    echo "<script>alert('アクセス権限がありません。');window.location.href='entry_list.php';</script>";
    exit();
}

$editable = canEdit($role, $user_project, $project);

// ===== 本人情報編集権限 =====
$can_edit_personal = in_array($role, ['chief', 'teacher']); // ← 実行委員長・先生のみ

// ===== 更新処理 =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $editable) {
    $entry_number = $_POST['entry_number'] ?? $row['entry_number'];

    // 個人情報（chief / teacher のみ）
    if ($can_edit_personal) {
        $student_id = $_POST['student_id'] ?? $row['student_id'];
        $name = $_POST['name'] ?? $row['name'];
        $class = $_POST['class'] ?? $row['class'];
    } else {
        $student_id = $row['student_id'];
        $name = $row['name'];
        $class = $row['class'];
    }

    if ($project === 'sumabura') {
        $mode = $_POST['mode'] ?? $row['mode'];
        $second_student_id = $_POST['second_student_id'] ?? $row['second_student_id'];
        $second_class = $_POST['second_class'] ?? $row['second_class'];
        $second_name = $_POST['second_name'] ?? $row['second_name'];

        $sql = "UPDATE entries SET 
                    student_id=:student_id, name=:name, class=:class,
                    mode=:mode, second_student_id=:second_student_id, second_class=:second_class,
                    second_name=:second_name, entry_number=:entry_number
                WHERE id=:id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':student_id' => $student_id,
            ':name' => $name,
            ':class' => $class,
            ':mode' => $mode,
            ':second_student_id' => $second_student_id,
            ':second_class' => $second_class,
            ':second_name' => $second_name,
            ':entry_number' => $entry_number,
            ':id' => $id
        ]);
    } else { // karaoke / ramune
        $sql = "UPDATE entries SET 
                    student_id=:student_id, name=:name, class=:class, entry_number=:entry_number
                WHERE id=:id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':student_id' => $student_id,
            ':name' => $name,
            ':class' => $class,
            ':entry_number' => $entry_number,
            ':id' => $id
        ]);
    }

    // ===== 編集ログ =====
    $log_action = "エントリーID {$id}（{$project}）を編集しました";
    $log = $pdo->prepare("INSERT INTO action_logs (username, role, action, target_table, target_id)
                          VALUES (:u, :r, :a, 'entries', :tid)");
    $log->execute([
        ':u' => $_SESSION['name'] ?? $_SESSION['username'],
        ':r' => $_SESSION['role'],
        ':a' => $log_action,
        ':tid' => $id
    ]);

    $_SESSION['flash_message'] = '更新しました。';
    header("Location: entry_{$project}.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>エントリー編集 | ASO FESTIVAL 2025 管理</title>
    <link rel="stylesheet" href="master.css">
    <style>
        .edit-form {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            max-width: 960px;
            margin: 0 auto;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
            margin-top: 14px;
        }

        .form-label {
            min-width: 120px;
            font-weight: 700;
            color: #333;
        }

        .form-control,
        .form-select {
            flex: 1 1 220px;
            min-width: 220px;
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .inline-fields {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            flex: 1;
        }

        .checks {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            margin-top: 6px;
        }

        .actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 18px;
        }
    </style>
</head>

<body>
    <header>エントリー編集</header>

    <div class="container">
        <form method="post" class="edit-form">
            <p><strong>企画：</strong> <?= htmlspecialchars($project) ?></p>

            <div class="form-row">
                <span class="form-label">学籍番号</span>
                <input type="text" name="student_id" class="form-control"
                    value="<?= htmlspecialchars($row['student_id']) ?>" <?= $can_edit_personal ? '' : 'readonly' ?>>
            </div>

            <div class="form-row">
                <span class="form-label">氏名</span>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($row['name']) ?>"
                    <?= $can_edit_personal ? '' : 'readonly' ?>>
            </div>

            <div class="form-row">
                <span class="form-label">クラス</span>
                <select name="class" class="form-select" <?= $can_edit_personal ? '' : 'disabled' ?>>
                    <option value="">選択してください</option>
                    <?php include '../entry/class_options.php'; ?>
                </select>
            </div>
            <script>document.addEventListener("DOMContentLoaded", () => { document.querySelector('[name="class"]').value = "<?= htmlspecialchars($row['class']) ?>"; });</script>

            <?php if ($project === 'sumabura'): ?>
                <div class="form-row">
                    <span class="form-label">参加形態</span>
                    <select name="mode" class="form-select" <?= $editable ? '' : 'disabled' ?>>
                        <option value="1vs1" <?= $row['mode'] === '1vs1' ? 'selected' : ''; ?>>1vs1</option>
                        <option value="2vs2" <?= $row['mode'] === '2vs2' ? 'selected' : ''; ?>>2vs2</option>
                    </select>
                </div>

                <div class="form-row">
                    <span class="form-label">2人目情報</span>
                    <div class="inline-fields">
                        <input type="text" name="second_student_id" class="form-control" placeholder="2人目学籍番号"
                            value="<?= htmlspecialchars($row['second_student_id']) ?>" <?= $editable ? '' : 'readonly' ?>>
                        <select name="second_class" class="form-select" <?= $editable ? '' : 'disabled' ?>>
                            <option value="">選択してください</option>
                            <?php include '../entry/class_options.php'; ?>
                        </select>
                        <input type="text" name="second_name" class="form-control" placeholder="2人目氏名"
                            value="<?= htmlspecialchars($row['second_name']) ?>" <?= $editable ? '' : 'readonly' ?>>
                    </div>
                </div>
                <script>document.addEventListener("DOMContentLoaded", () => { document.querySelector('[name="second_class"]').value = "<?= htmlspecialchars($row['second_class']) ?>"; });</script>

                <div class="form-row">
                    <span class="form-label">同意状況</span>
                    <div class="checks">
                        <label><input type="checkbox" <?= $row['preliminary'] ? 'checked' : '' ?> disabled> 予選会</label>
                        <label><input type="checkbox" <?= $row['agreement'] ? 'checked' : '' ?> disabled> 麻生祭大会出場規約</label>
                        <label><input type="checkbox" <?= $row['nintendo_agreement'] ? 'checked' : '' ?> disabled>
                            任天堂大会規約</label>
                    </div>
                </div>
            <?php elseif ($project === 'karaoke'): ?>
                <div class="form-row">
                    <span class="form-label">同意状況</span>
                    <div class="checks">
                        <label><input type="checkbox" <?= $row['preliminary'] ? 'checked' : '' ?> disabled> 予選会</label>
                        <label><input type="checkbox" <?= $row['agreement'] ? 'checked' : '' ?> disabled> 麻生祭大会出場規約</label>
                    </div>
                </div>
            <?php elseif ($project === 'ramune'): ?>
                <div class="form-row">
                    <span class="form-label">同意状況</span>
                    <div class="checks">
                        <label><input type="checkbox" <?= $row['agreement'] ? 'checked' : '' ?> disabled> 麻生祭大会出場規約</label>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-row">
                <span class="form-label">エントリーナンバー</span>
                <input type="text" name="entry_number" class="form-control"
                    value="<?= htmlspecialchars($row['entry_number']) ?>" <?= $editable ? '' : 'readonly' ?>>
            </div>

            <div class="actions">
                <?php if ($editable): ?>
                    <button type="submit" class="edit-btn">更新</button>
                <?php else: ?>
                    <span style="color:#888;">閲覧のみ（編集権限がありません）</span>
                <?php endif; ?>
                <a href="entry_<?= htmlspecialchars($project) ?>.php" class="back-btn">←</a>
            </div>
        </form>
    </div>
</body>

</html>