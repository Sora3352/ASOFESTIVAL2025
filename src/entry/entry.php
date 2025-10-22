<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>企画エントリー | 麻生祭2025</title>
    <link rel="stylesheet" href="entry.css">
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const studentIdInput = document.getElementById("student_id");
            const emailInput = document.getElementById("email");
            const domain = "@s.asojuku.ac.jp";

            studentIdInput.addEventListener("input", function () {
                // 半角英数字以外を除外+半角数字７文字のみ許可
                let value = studentIdInput.value.replace(/[^0-9]/g, "").slice(0, 7);
                studentIdInput.value = value;

                // メール自動反映
                if (value.length > 0) {
                    emailInput.value = value + domain;
                } else {
                    emailInput.value = "";
                }
            });
        });
    </script>
</head>

<body>
    <h1>企画エントリー</h1>
    <form action="entry_save.php" method="post">
        <label for="student_id">学籍番号（半角数字）</label>
        <input type="text" id="student_id" name="student_id" required maxlength="7" placeholder="例: 2501234">

        <label for="email">メールアドレス</label>
        <!-- readonly にして disabled ではないことが重要 -->
        <input type="text" id="email" name="email" readonly required placeholder="@s.asojuku.ac.jp が自動入力されます">

        <label for="class">クラス</label>
        <select id="class" name="class" required>
            <option value="">選択してください</option>

            <optgroup label="SD 系">
                <option value="SD4@津田">SD4（津田）</option>
                <option value="SD3A@久家">SD3A（久家）</option>
                <option value="SD3B@今村">SD3B（今村）</option>
                <option value="SD3C@香川">SD3C（香川）</option>
                <option value="SD3D@江利">SD3D（江利）</option>
                <option value="SD2A@川野">SD2A（川野）</option>
                <option value="SD2B@野馬">SD2B（野馬）</option>
                <option value="SD2C@西野">SD2C（西野）</option>
                <option value="SD2D@染矢">SD2D（染矢）</option>
                <option value="SD2E@高松">SD2E（高松）</option>
            </optgroup>

            <optgroup label="NS 系">
                <option value="NS4@打越">NS4（打越）</option>
                <option value="NS3B@久保山">NS3B（久保山）</option>
                <option value="NS3A@北島">NS3A（北島）</option>
                <option value="NS2AA1@北島">NS2AA1（北島）</option>
                <option value="NS2A@北島">NS2A（北島）</option>
                <option value="NS2B@高倉">NS2B（高倉）</option>
                <option value="NS2BA1@高倉">NS2BA1（高倉）</option>
            </optgroup>

            <optgroup label="AI・IT 系">
                <option value="AI4@山下文">AI4（山下文）</option>

                <option value="AI3@坂下">AI3（坂下）</option>
                <option value="AI2@元田">AI2（元田）</option>
                <option value="IT1A@志水">IT1A（志水）</option>
                <option value="IT1B@手嶋">IT1B（手嶋）</option>
                <option value="IT1C@山下千">IT1C（山下千）</option>
                <option value="IT1D@村上">IT1D（村上）</option>
                <option value="IT1E@毛利">IT1E（毛利）</option>
                <option value="IT1F@高橋">IT1F（高橋）</option>
                <option value="IT1G@奥野">IT1G（奥野）</option>
            </optgroup>

            <optgroup label="情ビ・経理・ビジエキ 系">
                <option value="情ビ1A@佐々木">情ビ1A（佐々木）</option>
                <option value="情ビ1B@山下香">情ビ1B（山下香）</option>
                <option value="IT経1@志水">IT経1（志水）</option>
                <option value="ビジエキ1@姫嶋">ビジエキ1（姫嶋）</option>
                <option value="経理1@山田">経理1（山田）</option>
                <option value="情ビ2A+@福地">情ビ2A+（福地）</option>
                <option value="情ビ2B+@下仮屋">情ビ2B+（下仮屋）</option>
                <option value="経理2+@新田">経理2+（新田）</option>
                <option value="ビジエキ2+@瀬﨑">ビジエキ2+（瀬﨑）</option>
            </optgroup>
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
        <button type="submit">エントリーする</button>
    </form>
</body>

</html>