<?php
session_start();
require_once '../../config/db.php'; // ปรับ path ให้ตรงกับไฟล์เชื่อมต่อฐานข้อมูลของนายนะครับ

// 1. ดึงข้อมูลแจ้งเตือนที่ "ยังไม่เคยเด้งป็อปอัป" (is_notified = 0)
$stmt = $pdo->query("SELECT * FROM admin_notifications WHERE is_notified = 0 ORDER BY created_at ASC");
$alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(count($alerts) > 0) {
    // 2. ถ้ามีแจ้งเตือนใหม่ ให้อัปเดตสถานะเป็น "เด้งเตือนแล้ว" (is_notified = 1) จะได้ไม่เด้งซ้ำรัวๆ
    $pdo->query("UPDATE admin_notifications SET is_notified = 1 WHERE is_notified = 0");
}

// 3. ส่งข้อมูลกลับไปให้หน้าเว็บในรูปแบบ JSON
header('Content-Type: application/json');
echo json_encode($alerts);
exit();
?>