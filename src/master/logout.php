<?php
session_start();

// セッション変数を全削除
$_SESSION = [];

// セッションクッキーも削除
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// セッション破棄
session_destroy();

// index.phpへリダイレクト
header('Location: ../index.php');
exit();
