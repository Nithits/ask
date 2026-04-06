<?php
session_start();
unset($_SESSION['user_logged_in']);
unset($_SESSION['user_id']);
unset($_SESSION['user_fullname']);
unset($_SESSION['user_company']);
header("Location: index.php");
exit();
?>