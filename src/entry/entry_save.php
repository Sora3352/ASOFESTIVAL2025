<?php
require_once __DIR__ . "/../../asset/db_connect.php";
require_once __DIR__ . "/../../asset/PHPMailer/src/PHPMailer.php";
require_once __DIR__ . "/../../asset/PHPMailer/src/SMTP.php";
require_once __DIR__ . "/../../asset/PHPMailer/src/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// POSTデータ受け取り
$student_id = $_POST['student_id'] ?? '';
$class = $_POST['class'] ?? '';
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$project = $_POST['project'] ?? '';

if (empty($student_id) || empty($class) || empty($name) || empty($project)) {
    echo "<script>alert('すべての項目を入力してください。');history.back();</script>";
    exit;
}

// 企画名を日本語に変換（表示用）
$projectNames = [
    'ramune' => 'ラムネ早飲み',
    'karaoke' => 'カラオケ大会',
    'sumabura' => 'スマブラトーナメント',
];
$project_display = $projectNames[$project] ?? $project;

try {
    // ====== DB登録 ======
    $sql = "INSERT INTO entries (student_id, class, name, project)
            VALUES (:student_id, :class, :name, :project)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':student_id' => $student_id,
        ':class' => $class,
        ':name' => $name,
        ':project' => $project // 英語で保存
    ]);

    // ====== メール送信 ======
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.lolipop.jp';
    $mail->SMTPAuth = true;
    $mail->Username = 'info_asofestival2025@aso-sora3597.noor.jp';
    $mail->Password = 'Haruki-20060325';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    // 送信元
    $mail->setFrom('info_asofestival2025@aso-sora3597.noor.jp', '麻生祭2025実行委員会');
    // 宛先（エントリー者）
    $mail->addAddress($email, $name);
    // 管理者にも通知
    $mail->addBCC('info_asofestival2025@aso-sora3597.noor.jp');

    // 件名と本文
    $mail->Subject = '【麻生祭2025】エントリー完了のお知らせ';
    $mail->Body = <<<EOM
{$name} さん

麻生祭2025へのエントリーを受け付けました。
以下の内容で登録されています。

＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝
学籍番号：{$student_id}
クラス　：{$class}
氏名　　：{$name}
企画　　：{$project_display}
＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝

当日はお楽しみください！

────────────────────
麻生祭2025 実行委員会
info_asofestival2025@aso-sora3597.noor.jp
────────────────────
EOM;

    $mail->send();

    // ====== 完了画面 ======
    echo <<<HTML
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>エントリー完了</title>
<style>
body { font-family: Arial, sans-serif; text-align: center; background: #f9f9f9; padding: 2rem; }
.container { background: #fff; max-width: 400px; margin: 0 auto; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
h1 { color: #4b0082; }
a { color: #fff; background: #4b0082; padding: 0.5rem 1rem; text-decoration: none; border-radius: 5px; }
a:hover { background: #663399; }
</style>
</head>
<body>
<div class="container">
<h1>エントリー完了！</h1>
<p>{$name} さん、エントリーありがとうございます。</p>
<p>参加企画：<b>{$project_display}</b></p>
<p>登録内容を <b>{$email}</b> 宛に送信しました。</p>
<!-- 迷惑メール対策 -->
<p>迷惑メール対策のため、メールが届かない場合は、迷惑メールフォルダをご確認ください。</p>
<a href="../index.php">戻る</a>
</div>
</body>
</html>
HTML;

} catch (Exception $e) {
    echo "エラー: " . $e->getMessage();
}
?>