<?php
session_start();

unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_role']);

session_destroy();

header('Location: login.php');
exit;
?>