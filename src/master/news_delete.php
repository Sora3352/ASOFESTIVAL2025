<?php
session_start();
require_once('../../asset/db_connect.php');
// ログイン済みチェック（root制限なし）
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}


// URLパラメータ確認
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: news_manage.php?error=invalid');
    exit;
}

$id = intval($_GET['id']);

// 対象データ確認
$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$news = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$news) {
    header('Location: news_manage.php?error=notfound');
    exit;
}

// 削除処理
$stmt_delete = $pdo->prepare("DELETE FROM news WHERE id = ?");
$stmt_delete->execute([$id]);

header('Location: news_manage.php?deleted=1');
exit;
?>