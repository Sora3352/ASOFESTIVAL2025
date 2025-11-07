<?php
require_once('../../asset/db_connect.php');
$data = json_decode(file_get_contents('php://input'), true);

$reporter = $data['reporter'] ?? '（未記入）';
$page = $data['page'] ?? '不明';
$message = $data['message'] ?? '(内容なし)';

$stmt = $pdo->prepare("INSERT INTO bug_reports (reporter, page, message) VALUES (?, ?, ?)");
$stmt->execute([$reporter, $page, $message]);

echo "OK";
