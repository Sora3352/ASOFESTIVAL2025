<?php
session_start();
require_once('../../../asset/db_connect.php');

if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit();
}

$username = $_SESSION['username'];
$stmt = $pdo->prepare("SELECT id, username, name, project FROM admins WHERE username = :username LIMIT 1");
$stmt->execute([':username' => $username]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    echo "<script>alert('ユーザー情報が見つかりません。');history.back();</script>";
    exit;
}

$user_id = $admin['id'];
$name = $admin['name'];
$project = $admin['project'];

// ===== 編集モード確認 =====
$edit_week = $_GET['edit_week'] ?? '';
$existing_data = [];

// ✅ 締切情報（デフォルト2週間前 or 設定テーブル）
$deadline_message = '';
$is_deadline_passed = false;

if ($edit_week) {
    // 既存データ取得
    $stmt = $pdo->prepare("SELECT * FROM shift_requests WHERE user_id = :uid AND week_start = :week_start ORDER BY request_date ASC");
    $stmt->execute([':uid' => $user_id, ':week_start' => $edit_week]);
    $existing_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 締切情報
    $stmt = $pdo->prepare("SELECT deadline_date FROM shift_deadlines WHERE week_start = :week_start LIMIT 1");
    $stmt->execute([':week_start' => $edit_week]);
    $deadline_row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($deadline_row) {
        $deadline_date = new DateTime($deadline_row['deadline_date']);
    } else {
        $deadline_date = new DateTime($edit_week);
        $deadline_date->modify('-14 days');
    }

    $today = new DateTime();
    $diff_days = (int) $today->diff($deadline_date)->format('%r%a');

    if ($diff_days < 0) {
        $is_deadline_passed = true;
        $deadline_message = "⚠️ この週の締切（" . $deadline_date->format('Y/m/d') . "）を過ぎています。再編集はできません。";
    } else {
        $deadline_message = "⏰ 締切：" . $deadline_date->format('Y/m/d') . "（あと{$diff_days}日）";
    }
}

// ===== 週リスト生成 =====
$today = new DateTime();
$end_target = new DateTime('2025-12-15');
$weeks = [];
$start = clone $today;
$start->modify('monday this week');

while ($start <= $end_target) {
    $end = clone $start;
    $end->modify('+6 days');
    $weeks[] = [
        'label' => $start->format('Y/m/d') . "〜" . $end->format('m/d'),
        'value' => $start->format('Y-m-d')
    ];
    $start->modify('+1 week');
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>シフト活動リクエスト申請</title>
    <link rel="stylesheet" href="shift.css">
    <style>
        .deadline-info {
            text-align: center;
            margin: 10px 0;
            padding: 8px;
            border-radius: 8px;
            color: #fff;
        }

        .deadline-info.ok {
            background: #6b4fa3;
        }

        .deadline-info.over {
            background: #c94c4c;
        }

        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1><?= $edit_week ? '📝 シフト再編集（' . htmlspecialchars($edit_week) . '週）' : '実行委員活動リクエスト申請' ?></h1>

        <!-- 締切情報表示 -->
        <?php if ($edit_week): ?>
            <div class="deadline-info <?= $is_deadline_passed ? 'over' : 'ok' ?>">
                <?= htmlspecialchars($deadline_message) ?>
            </div>
        <?php endif; ?>

        <form action="request_save.php" method="post" id="requestForm">
            <label>申請週（締切：2週間前まで）</label>
            <select name="week_start" id="weekSelect" required <?= $edit_week ? 'disabled' : '' ?>>
                <option value="">週を選択</option>
                <?php foreach ($weeks as $w): ?>
                    <option value="<?= $w['value'] ?>" <?= $edit_week === $w['value'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($w['label']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($edit_week): ?>
                <input type="hidden" name="week_start" value="<?= htmlspecialchars($edit_week) ?>">
            <?php endif; ?>

            <div id="requestList"></div>

            <button type="submit" class="submit-btn" <?= $is_deadline_passed ? 'disabled' : '' ?>>
                <?= $edit_week ? '再申請する' : '一週間分を申請する' ?>
            </button>
        </form>

        <a href="request_list.php" class="back-btn">← 一覧に戻る</a>
    </div>

    <script>
        // ======= PHPから既存データを埋め込み =======
        const existingData = <?= json_encode($existing_data, JSON_UNESCAPED_UNICODE) ?>;

        // ======= 週選択イベント =======
        document.addEventListener('DOMContentLoaded', () => {
            const weekSelect = document.getElementById('weekSelect');
            const container = document.getElementById('requestList');

            function renderWeek(weekStart) {
                container.innerHTML = '';
                if (!weekStart) return;

                const monday = new Date(weekStart);
                for (let i = 0; i < 5; i++) {
                    const d = new Date(monday);
                    d.setDate(monday.getDate() + i);
                    const dateStr = d.toISOString().split('T')[0];
                    const label = d.toLocaleDateString('ja-JP', { month: 'numeric', day: 'numeric', weekday: 'short' });

                    // 既存データ取得（あれば）
                    const ex = existingData.find(e => e.request_date === dateStr);

                    const isRest = ex ? ex.available_time === "休み希望" : false;
                    const remarks = ex ? ex.remarks : '';
                    const period = ex ? ex.period : '1限';
                    const start = ex && ex.available_time && !isRest ? ex.available_time.split('〜')[0] : '11:00';
                    const end = ex && ex.available_time && !isRest ? ex.available_time.split('〜')[1] : '20:00';

                    const block = document.createElement('div');
                    block.classList.add('day-block');
                    block.innerHTML = `
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;">
          <label>${label}</label>
          <label><input type="checkbox" class="rest-check" name="rest_flag[]" ${isRest ? 'checked' : ''}> 休み希望</label>
        </div>
        <input type="hidden" name="request_date[]" value="${dateStr}">

        <label>終わりコマ：</label>
        <select name="period[]" class="period-select" ${isRest ? 'disabled' : ''}>
          <option value="1限" ${period === "1限" ? "selected" : ""}>1限</option>
          <option value="2限" ${period === "2限" ? "selected" : ""}>2限</option>
          <option value="3限" ${period === "3限" ? "selected" : ""}>3限</option>
          <option value="4限" ${period === "4限" ? "selected" : ""}>4限</option>
        </select>

        <div class="time-input">
          <label>活動可能時間：</label>
          <input type="time" name="start_time[]" class="start-time" min="08:00" max="20:00" value="${start}" ${isRest ? 'disabled' : ''}>
          ～
          <input type="time" name="end_time[]" class="end-time" min="08:00" max="20:00" value="${end}" ${isRest ? 'disabled' : ''}>
        </div>

        <label>備考：</label>
        <input type="text" name="remarks[]" value="${remarks}" ${isRest ? 'required' : ''} placeholder="${isRest ? '休み理由を入力してください' : '例：3限空きコマ 4限授業など'}">
      `;
                    container.appendChild(block);
                }
                attachListeners();
            }

            if (weekSelect.value) renderWeek(weekSelect.value);
            weekSelect.addEventListener('change', e => renderWeek(e.target.value));

            function attachListeners() {
                const selects = document.querySelectorAll('.period-select');
                const checks = document.querySelectorAll('.rest-check');

                selects.forEach((sel, idx) => {
                    sel.addEventListener('change', function () {
                        const start = document.querySelectorAll('.start-time')[idx];
                        const end = document.querySelectorAll('.end-time')[idx];
                        switch (this.value) {
                            case '1限': start.value = '11:00'; break;
                            case '2限': start.value = '12:45'; break;
                            case '3限': start.value = '15:00'; break;
                            case '4限': start.value = '16:45'; break;
                        }
                        end.value = '20:00';
                    });
                });

                checks.forEach((chk, idx) => {
                    chk.addEventListener('change', function () {
                        const period = document.querySelectorAll('.period-select')[idx];
                        const start = document.querySelectorAll('.start-time')[idx];
                        const end = document.querySelectorAll('.end-time')[idx];
                        const remarks = document.getElementsByName('remarks[]')[idx];
                        if (this.checked) {
                            period.disabled = true;
                            start.disabled = true;
                            end.disabled = true;
                            remarks.required = true;
                            remarks.placeholder = '休み理由を入力してください';
                        } else {
                            period.disabled = false;
                            start.disabled = false;
                            end.disabled = false;
                            remarks.required = false;
                            remarks.placeholder = '例：3限空きコマ 4限授業など';
                        }
                    });
                });
            }
        });
    </script>
</body>

</html>