<?php
session_start();
// ถอยออก 1 ชั้นจาก action/ ไปหา config/db.php
require_once __DIR__ . '/../config/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. รับค่าและทำความสะอาดข้อมูล
    $fullname = htmlspecialchars(trim($_POST['fullname']));
    $email    = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone    = htmlspecialchars(trim($_POST['phone']));
    $position = htmlspecialchars(trim($_POST['position']));

    // 2. ตั้งค่าโฟลเดอร์เก็บ Resume (อ้างอิงจากรูปโครงสร้างของคุณ)
    // ใช้ Absolute Path ผ่าน __DIR__ เพื่อความแม่นยำที่สุดใน Docker
    $upload_dir = __DIR__ . '/../uploads/resumes/'; 

    $file = $_FILES['resume_file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // สร้างชื่อไฟล์ใหม่
        $new_file_name = 'resume_' . date('Ymd_His') . '_' . uniqid() . '.' . $file_ext;
        $destination = $upload_dir . $new_file_name;

        // ย้ายไฟล์
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            try {
                // 3. บันทึกลงฐานข้อมูล
                $sql = "INSERT INTO job_applications (fullname, email, phone, position, resume_file) 
                        VALUES (:fullname, :email, :phone, :position, :resume_file)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':fullname'    => $fullname,
                    ':email'       => $email,
                    ':phone'       => $phone,
                    ':position'    => $position,
                    ':resume_file' => $new_file_name
                ]);

                $_SESSION['apply_status'] = 'success';
                $_SESSION['apply_message'] = 'ส่งใบสมัครเรียบร้อยแล้ว! โปรดรอทีมงานของทางเราตอบกลับ';

            } catch (PDOException $e) {
                $_SESSION['apply_status'] = 'error';
                $_SESSION['apply_message'] = 'Database Error: ' . $e->getMessage();
            }
        } else {
            $_SESSION['apply_status'] = 'error';
            $_SESSION['apply_message'] = 'ไม่สามารถย้ายไฟล์ได้ กรุณาเช็ค Permission ของโฟลเดอร์ uploads';
        }
    } else {
        $_SESSION['apply_status'] = 'error';
        $_SESSION['apply_message'] = 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์';
    }
}

// กลับไปหน้า job.php (ซึ่งอยู่ที่ Root ตามรูป)
header("Location: ../job.php");
exit();