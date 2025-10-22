<?php
session_start();

$timeout = 15 * 60; // 15分

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header('Location: login.php');
    exit;
}

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}

$_SESSION['LAST_ACTIVITY'] = time();
