<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../../asset/db_connect.php');
require_once('../../asset/PHPMailer/src/PHPMailer.php');
require_once('../../asset/PHPMailer/src/SMTP.php');
require_once('../../asset/PHPMailer/src/Exception.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $class = trim($_POST['class'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $project = trim($_POST['project'] ?? '');
    $mode = trim($_POST['participation_type'] ?? '');
    $second_id = trim($_POST['second_student_id'] ?? '');
    $second_email = trim($_POST['second_email'] ?? '');
    $second_class = trim($_POST['second_class'] ?? '');
    $second_name = trim($_POST['second_name'] ?? '');
    $preliminary = isset($_POST['preliminary']) ? 1 : 0;
    $agreement = isset($_POST['agreement']) ? 1 : 0;
    $nintendo_agreement = isset($_POST['nintendo_agreement']) ? 1 : 0; // ← 任天堂大会規約

    if ($student_id && $email && $class && $name && $project && $agreement) {
        try {
            // ===== エントリーナンバー生成 =====
            $prefix = '';
            switch ($project) {
                case 'karaoke':
                    $prefix = 'Ka';
                    break;
                case 'ramune':
                    $prefix = 'Rm';
                    break;
                case 'sumabura':
                    $prefix = 'Sm';
                    break;
                default:
                    $prefix = 'AS';
            }
            $rand = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $entry_number = $prefix . $rand . '.2025';

            // ===== DB登録 =====
            $sql = "INSERT INTO entries 
        (student_id, class, name, project, mode, 
         second_student_id, second_email, second_class, second_name, 
         preliminary, agreement, nintendo_agreement, entry_number, created_at)
        VALUES
        (:student_id, :class, :name, :project, :mode,
         :second_student_id, :second_email, :second_class, :second_name,
         :preliminary, :agreement, :nintendo_agreement, :entry_number, NOW())";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':student_id' => $student_id,
                ':class' => $class,
                ':name' => $name,
                ':project' => $project,
                ':mode' => $mode,
                ':second_student_id' => $second_id,
                ':second_email' => $second_email,
                ':second_class' => $second_class,
                ':second_name' => $second_name,
                ':preliminary' => $preliminary,
                ':agreement' => $agreement,
                ':nintendo_agreement' => $nintendo_agreement,
                ':entry_number' => $entry_number
            ]);

            // ===== メール送信準備 =====
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.lolipop.jp';
            $mail->SMTPAuth = true;
            $mail->Username = 'info_money_me@aso-sora3597.noor.jp';
            $mail->Password = getenv('SMTP_PASS'); // ← .htaccessで安全管理
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;
            $mail->CharSet = 'UTF-8';
            $mail->setFrom('info_money_me@aso-sora3597.noor.jp', '麻生祭2025実行委員会');
            $mail->Subject = '【麻生祭2025】エントリー完了のお知らせ';

            // ===== メール本文 =====
            $subject = "【麻生祭2025】エントリー完了のお知らせ";
            $body = "{$name} さん\n\n";
            $body .= "麻生祭2025 {$project} へのエントリーを受け付けました。\n";
            $body .= "エントリーナンバー： {$entry_number}\n";

            if ($mode)
                $body .= "参加形態： {$mode}\n";
            if ($mode === '2vs2' && $second_name) {
                $body .= "\nペア： {$second_name} さん\n";
            }

            // ✅ 任天堂大会規約：同意していたらのみ表示（スマブラ限定）
            if ($project === 'sumabura' && $nintendo_agreement === 1) {
                $body .= "\n任天堂大会規約に同意済み\n";
            }

            // ✅ 麻生祭大会出場規約：同意していたらのみ表示
            if ($agreement === 1) {
                $body .= "麻生祭大会出場規約に同意済み\n";
            }

            // ✅ 予選会了承：同意していたらのみ表示
            if ($preliminary === 1) {
                $body .= "予選会了承済み\n";
            }

            $body .= "\n※このメールは自動送信です。\n\n麻生祭2025実行委員会";

            // ===== 代表者へ送信 =====
            $mail->addAddress($email, $name);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->send();

            // ===== 2人目にも送信（スマブラ2vs2） =====
            if ($project === 'sumabura' && $mode === '2vs2' && !empty($second_email)) {
                $mail2 = new PHPMailer(true);
                $mail2->isSMTP();
                $mail2->Host = 'smtp.lolipop.jp';
                $mail2->SMTPAuth = true;
                $mail2->Username = 'info_money_me@aso-sora3597.noor.jp';
                $mail2->Password = getenv('SMTP_PASS');
                $mail2->SMTPSecure = 'ssl';
                $mail2->Port = 465;
                $mail2->CharSet = 'UTF-8';
                $mail2->setFrom('info_money_me@aso-sora3597.noor.jp', '麻生祭2025実行委員会');
                $mail2->addAddress($second_email, $second_name);
                $mail2->Subject = $subject;
                $mail2->Body = "{$second_name} さん\n\n{$name} さんとのペアでスマブラ大会にエントリーが完了しました。\n\nエントリーナンバー： {$entry_number}\n\n麻生祭2025実行委員会";
                $mail2->send();
            }

            // ===== 完了後はリダイレクト =====
            header("Location: entry_complete.php?no=" . urlencode($entry_number));
            exit();

        } catch (Exception $e) {
            echo "<p style='color:red;text-align:center;'>エラーが発生しました: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
        }
    } else {
        echo "<p style='color:red;text-align:center;'>未入力項目があります。入力内容をご確認ください。</p>";
    }
}
?>