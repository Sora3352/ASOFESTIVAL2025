<?php
session_start();
require_once('../../../asset/db_connect.php');

// 管理者のみ
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['chief', 'vice', 'teacher', 'core'], true)) {
    header('Location: ../master_menu.php');
    exit();
}

// ===== DBテーブル（未作成対策：一度だけ作成） =====
$pdo->exec("
CREATE TABLE IF NOT EXISTS shift_deadlines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    week_start DATE NOT NULL UNIQUE,
    deadline_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// ===== 今週〜2025/12/15までの週リスト =====
$today = new DateTime();
$end_target = new DateTime('2025-12-15');

$weeks = [];
$start = (clone $today)->modify('monday this week');
while ($start <= $end_target) {
    $end = (clone $start)->modify('+6 days');
    $weeks[] = [
        'label' => $start->format('Y/m/d') . '〜' . $end->format('m/d'),
        'value' => $start->format('Y-m-d'),
    ];
    $start->modify('+1 week');
}

// ===== 保存処理（週開始日をキーにする方式） =====
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 例: $_POST['deadline_date']['2025-12-15'] = '2025-12-05'
        $deadlines = $_POST['deadline_date'] ?? [];

        $stmt = $pdo->prepare("
            INSERT INTO shift_deadlines (week_start, deadline_date)
            VALUES (:week_start, :deadline_date)
            ON DUPLICATE KEY UPDATE deadline_date = VALUES(deadline_date)
        ");

        foreach ($deadlines as $week_start => $deadline_date) {
            $deadline_date = trim($deadline_date ?? '');
            if ($deadline_date === '')
                continue; // 未入力はスキップ
            $stmt->execute([
                ':week_start' => $week_start,
                ':deadline_date' => $deadline_date,
            ]);
        }
        header('Location: shift_deadline_manage.php?updated=1');
        exit();
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

// ===== 既存データの読み込み（※2カラム限定でOK） =====
$rows = $pdo->query("SELECT week_start, deadline_date FROM shift_deadlines")
    ->fetchAll(PDO::FETCH_KEY_PAIR); // ['YYYY-mm-dd' => 'YYYY-mm-dd']
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>シフト提出締切管理</title>
    <link rel="stylesheet" href="shift.css">
    <style>
        input[type="date]{width:180px;padding:.4rem;}
 table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: .5rem;
            text-align: center;
        }

        th {
            background: #6b4fa3;
            color: #fff;
        }

        .notice {
            color: #666;
            font-size: .9rem;
            text-align: center;
            margin-top: .5rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>📆 シフト週別締切管理</h1>

        <?php if (isset($_GET['updated'])): ?>
            <p style="color:green;text-align:center;">✅ 締切を更新しました。</p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p style="color:#c00;text-align:center;">エラー: <?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <table>
                <thead>
                    <tr>
                        <th>週（開始日）</th>
                        <th>締切日</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($weeks as $w):
                        $week = $w['value'];
                        $val = $rows[$week] ?? '';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($w['label']) ?><br><small><?= htmlspecialchars($week) ?></small></td>
                            <td>
                                <!-- 週開始日を name のキーにする -->
                                <input type="date" name="deadline_date[<?= htmlspecialchars($week) ?>]"
                                    value="<?= htmlspecialchars($val) ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="notice">※ 未入力の週は保存時スキップされます（既存設定は保持）。</div>
            <br>
            <button type="submit" class="submit-btn">保存する</button>
        </form>

        <a href="../master_menu.php" class="back-btn">← 管理メニューに戻る</a>
    </div>
</body>

</html>