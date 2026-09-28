<?php
require_once 'includes/auth.php';

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie('PHPSESSID', '', time() - 42000, $params['path']);
}
session_destroy();

header('Location: login.php');
exit;
