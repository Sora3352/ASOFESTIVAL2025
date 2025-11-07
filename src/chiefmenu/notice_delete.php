<?php
session_start();
require_once('../../asset/db_connect.php');

$allowed_roles = ['chief', 'vice', 'teacher'];
if (!in_array($_SESSION['role'] ?? '', $allowed_roles, true)) {
    header('Location: ../master/master_menu.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int) $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM executive_news WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: notice_manage.php?message=' . urlencode('🗑️ お知らせを削除しました。'));
    exit();
} else {
    header('Location: notice_manage.php');
    exit();
}
?>