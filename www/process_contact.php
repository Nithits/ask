<?php
require_once 'config/db.php';
session_start(); // เพิ่ม session เพื่อส่งค่าแจ้งเตือน

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name    = htmlspecialchars(strip_tags(trim($_POST['name'])));
    $email   = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $tel     = htmlspecialchars(strip_tags(trim($_POST['tel'])));
    $subject = htmlspecialchars(strip_tags(trim($_POST['subject'])));
    $message = htmlspecialchars(strip_tags(trim($_POST['message'])));

    if (empty($name) || empty($email) || empty($message)) {
        $_SESSION['status'] = "error";
        $_SESSION['message'] = "กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน";
        header("Location: contact.php");
        exit;
    }

    try {
        $sql = "INSERT INTO contact_messages (name, email, tel, subject, message, created_at) 
                VALUES (:name, :email, :tel, :subject, :message, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':tel', $tel);
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':message', $message);
        
        if ($stmt->execute()) {
            // ส่งอีเมล (ทำงานเบื้องหลัง)
            $to = "sale1_ask@askcalibration.com";
            $email_subject = "New Contact Message: " . $subject;
            $email_body = "ชื่อ: $name\nอีเมล: $email\nเบอร์โทร: $tel\nข้อความ: $message";
            $headers = "From: webmaster@askcalibration.com";
            @mail($to, $email_subject, $email_body, $headers);

            // เก็บค่าความสำเร็จลง Session
            $_SESSION['status'] = "success";
            $_SESSION['message'] = "ส่งข้อความเรียบร้อยแล้ว ขอบคุณที่ติดต่อเรา";
            header("Location: contact.php");
            exit;
        }

    } catch (PDOException $e) {
        error_log($e->getMessage());
        $_SESSION['status'] = "error";
        $_SESSION['message'] = "เกิดข้อผิดพลาดในระบบ กรุณาลองใหม่ภายหลัง";
        header("Location: contact.php");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}