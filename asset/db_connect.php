<?php
/**
 * データベース接続設定
 * ASO FESTIVAL 2025
 */

// データベース接続情報
$servername = "mysql320.phy.lolipop.lan";
$username = "LAA1607503";
$password = "aso2025";
$dbname = "LAA1607503-aso2025";

try {
    // PDOを使用してMySQLに接続
    $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
    
} catch (PDOException $e) {
    // エラーハンドリング
    error_log("データベース接続エラー: " . $e->getMessage());
    }

/**
 * データベース接続を取得する関数
 * @return PDO データベース接続オブジェクト
 */
function getDbConnection() {
    global $pdo;
    return $pdo;
}

/**
 * データベース接続を閉じる関数
 */
function closeDbConnection() {
    global $pdo;
    $pdo = null;
}
?>
