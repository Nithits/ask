<?php
session_start();
// ถอยหลัง 2 สเต็ปเพื่อไปหาไฟล์ db.php
require_once '../../config/db.php'; 

// เช็คว่ามีการส่งไอดี (id) มาให้ลบหรือไม่
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // สั่งลบข้อมูลลูกค้าจากฐานข้อมูล
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    
    if ($stmt->execute([$id])) {
        // ลบสำเร็จ ให้สร้าง Session แจ้งเตือนส่งกลับไปหน้าเดิม
        $_SESSION['sweet_alert'] = [
            'icon' => 'success',
            'title' => 'ลบข้อมูลสำเร็จ!',
            'text' => 'ระบบได้ทำการลบลูกค้าออกจากฐานข้อมูลเรียบร้อยแล้ว'
        ];
    } else {
        // เผื่อมี Error
        $_SESSION['sweet_alert'] = [
            'icon' => 'error',
            'title' => 'เกิดข้อผิดพลาด!',
            'text' => 'ไม่สามารถลบข้อมูลได้ โปรดลองอีกครั้ง'
        ];
    }
}

// ลบเสร็จปุ๊บ สั่งให้เด้งกลับไปหน้าจัดการลูกค้าทันที
header("Location: ../manage_users.php");
exit();
?>