<?php
require_once __DIR__ . '/config.php';
session_name(SESSION_NAME);
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
