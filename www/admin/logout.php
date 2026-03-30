<?php
session_start();
// ล้างตัวแปร Session ทั้งหมด
$_SESSION = array();

// ทำลาย Session
session_destroy();

// เด้งกลับไปหน้า Login
header("Location: index.php");
exit();
?>