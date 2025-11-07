<?php
session_start();
require_once('../../asset/db_connect.php');

// ===== ログイン権限確認 =====
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

$role = $_SESSION['role'];
$user_project = $_SESSION['project'] ?? ''; // 実行委員・リーダーの企画識別

// ===== ドロップダウン制御用マップ =====
$projects = [
    'sumabura' => 'スマブラ大会',
    'karaoke' => 'カラオケ大会',
    'ramune' => 'ラムネ早飲み大会'
];

// ===== 権限に応じた制御 =====
function canAccessProject($role, $user_project, $target)
{
    if (in_array($role, ['chief', 'vice', 'core', 'teacher']))
        return true;
    if (in_array($role, ['leader', 'member']))
        return $user_project === $target;
    return false;
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>エントリー一覧 | 麻生祭2025</title>
    <link rel="stylesheet" href="master.css">
    <script>
        function switchProject(select) {
            if (select.value) {
                window.location.href = select.value;
            }
        }
    </script>
</head>

<body>
    <div class="container">
        <h2 class="page-title">企画別エントリー一覧</h2>

        <div class="dropdown-container" style="text-align:center; margin-bottom:25px;">
            <label for="projectSelect" style="font-weight:bold; font-size:1.1rem; margin-right:10px;">企画を選択：</label>
            <select id="projectSelect" onchange="switchProject(this)" class="dropdown-select">
                <option value="">選択してください</option>
                <?php foreach ($projects as $key => $label): ?>
                    <?php
                    $disabled = !canAccessProject($role, $user_project, $key) ? 'disabled' : '';
                    $file = "entry_" . $key . ".php";
                    ?>
                    <option value="<?= htmlspecialchars($file) ?>" <?= $disabled ?>>
                        <?= htmlspecialchars($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="text-align:center; margin-bottom:30px;">
            <a href="master_menu.php" class="back-btn">←</a>
        </div>

        <style>
            .dropdown-select {
                appearance: none;
                -webkit-appearance: none;
                -moz-appearance: none;
                background-color: #fff;
                border: 2px solid #4b6cfb;
                border-radius: 8px;
                padding: 10px 40px 10px 15px;
                font-size: 1rem;
                font-weight: 500;
                color: #333;
                cursor: pointer;
                transition: all 0.2s ease-in-out;
                background-image: url("data:image/svg+xml;utf8,<svg fill='black' height='20' width='20' xmlns='http://www.w3.org/2000/svg'><path d='M5 8l5 5 5-5z'/></svg>");
                background-repeat: no-repeat;
                background-position: right 10px center;
                background-size: 14px;
            }

            .dropdown-select:hover {
                border-color: #3a56d4;
                box-shadow: 0 0 6px rgba(75, 108, 251, 0.4);
            }

            .dropdown-select:disabled {
                background-color: #f3f3f3;
                color: #aaa;
                cursor: not-allowed;
                border-color: #ccc;
            }
        </style>
        <!-- ここに各企画のテーブルをincludeで読み込み（デフォルトは未表示） -->
        <div class="table-wrapper" style="text-align:center; color:#555;">
            <p>上のドロップダウンから企画を選択してください。</p>
        </div>
    </div>
</body>

</html>