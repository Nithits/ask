<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../quote.php");
    exit();
}

// รับค่า
$company_name   = $_POST['company_name'] ?? '';
$contact_person = $_POST['contact_person'] ?? '';
$email          = $_POST['email'] ?? '';
$phone          = $_POST['phone'] ?? '';
$message        = $_POST['message'] ?? ''; 

// จัดการไฟล์
if (!isset($_FILES['equipment_file']) || $_FILES['equipment_file']['error'] !== 0) {
    header("Location: ../quote.php?status=file_error");
    exit();
}

$file = $_FILES['equipment_file'];
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$new_file_name = "quote_" . time() . "_" . uniqid() . "." . $ext;
$upload_dir = "../uploads/quotations/";
$upload_path = $upload_dir . $new_file_name;

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if (move_uploaded_file($file['tmp_name'], $upload_path)) {
    try {
        // ตัด requested_at ออกเพราะ DB บันทึกให้อัตโนมัติ
        $sql = "INSERT INTO quotations (
                    company_name, 
                    contact_person, 
                    email, 
                    phone, 
                    message, 
                    equipment_list_file, 
                    status
                ) VALUES (
                    :comp, :cont, :email, :phone, :msg, :file, 'pending'
                )";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':comp'  => $company_name,
            ':cont'  => $contact_person,
            ':email' => $email,
            ':phone' => $phone,
            ':msg'   => $message,
            ':file'  => $new_file_name
        ]);

        header("Location: ../quote.php?status=success");
        exit();

    } catch (PDOException $e) {
        // ถ้าผิดพลาด ให้แสดงเพื่อดูว่าติดอะไร
        die("Database Error: " . $e->getMessage());
    }
} else {
    header("Location: ../quote.php?status=upload_failed");
    exit();
}