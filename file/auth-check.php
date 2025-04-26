<?php
// session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: unauthorized.php");
    exit();
}
?>