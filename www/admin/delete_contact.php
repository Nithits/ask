<?php
session_start();
require_once '../config/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = :id");
        $stmt->bindParam(':id', $id);
        
        // แก้ไขจุดนี้: เปลี่ยนจาก . เป็น ->
        if ($stmt->execute()) { 
            $_SESSION['status'] = 'success';
            $_SESSION['message'] = 'ลบข้อมูลเรียบร้อยแล้ว';
        } else {
            $_SESSION['status'] = 'error';
            $_SESSION['message'] = 'ไม่สามารถลบข้อมูลได้';
        }
    } catch (PDOException $e) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Error: ' . $e->getMessage();
    }
}

header("Location: manage_contact.php");
exit;