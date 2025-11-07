<?php
// ここではDB接続・セッション不要なため省略OK（必要なら再追加可能）
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>企画エントリー | 麻生祭2025</title>
    <link rel="stylesheet" href="entry.css">

    <!-- 学籍番号→メール自動入力 -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const studentIdInput = document.getElementById("student_id");
            const emailInput = document.getElementById("email");
            const domain = "@s.asojuku.ac.jp";

            studentIdInput.addEventListener("input", function () {
                // 半角英数字のみ許可
                let value = studentIdInput.value.replace(/[^a-zA-Z0-9]/g, "").slice(0, 7);
                studentIdInput.value = value;
                emailInput.value = value.length > 0 ? value + domain : "";
            });
        });
    </script>
</head>

<body>
    <h1>企画エントリー</h1>

    <form action="entry_save.php" method="post" id="entryForm">
        <!-- === 基本情報 === -->
        <label for="student_id">学籍番号（半角数字）</label>
        <input type="text" id="student_id" name="student_id" required maxlength="7" placeholder="例: 2501234">

        <label for="email">メールアドレス</label>
        <input type="text" id="email" name="email" readonly required placeholder="@s.asojuku.ac.jp が自動入力されます">

        <label for="class">クラス</label>
        <select id="class" name="class" required>
            <option value="">選択してください</option>
            <?php include 'class_options.php'; ?>
        </select>

        <label for="name">名前</label>
        <input type="text" id="name" name="name" required>

        <?php
        // URLパラメータから「project」取得（例: entry.php?project=karaoke）
        $selected_project = $_GET['project'] ?? '';
        ?>

        <label for="project">参加企画</label>
        <select id="project" name="project" required>
            <option value="">選択してください</option>
            <option value="ramune" <?= $selected_project === 'ramune' ? 'selected' : '' ?>>ラムネ早飲み</option>
            <option value="karaoke" <?= $selected_project === 'karaoke' ? 'selected' : '' ?>>カラオケ</option>
            <option value="sumabura" <?= $selected_project === 'sumabura' ? 'selected' : '' ?>>スマブラ</option>
        </select>

        <!-- ▼ 動的追加領域（スマブラ時の参加形態、2人目情報） -->
        <div id="second-player" style="display:none;"></div>

        <!-- ▼ 規約・チェックボックス挿入エリア -->
        <div id="rules-section" class="checkbox-group"></div>

        <!-- === 送信ボタン === -->
        <button type="submit">エントリーする</button>
    </form>

    <!-- 新規スクリプト -->
    <script src="entry.js?v=2"></script>
</body>

</html>