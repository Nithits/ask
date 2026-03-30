<?php 
require_once __DIR__ . '/../config/db.php';

// เปิด session ถ้าจะใช้ภายหลัง
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ฟังก์ชัน redirect กลับพร้อม status
function redirect_with_status($status) {
    header("Location: ../quote.php?status=" . urlencode($status));
    exit();
}

// ต้องเข้าผ่าน POST เท่านั้น
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit();
}

// -----------------------------
// 1) รับค่าจาก Form
// -----------------------------
$company_name   = isset($_POST['company_name']) ? trim($_POST['company_name']) : '';
$contact_person = isset($_POST['contact_person']) ? trim($_POST['contact_person']) : '';
$email          = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone          = isset($_POST['phone']) ? trim($_POST['phone']) : '';

// รับค่าหมายเหตุ (ตรวจเช็คทั้ง 2 ชื่อเพื่อความชัวร์)
$message        = isset($_POST['message']) ? trim($_POST['message']) : (isset($_POST['additional_remarks']) ? trim($_POST['additional_remarks']) : '');

// ตรวจสอบข้อมูลบังคับ
if ($company_name === '' || $contact_person === '' || $email === '' || $phone === '') {
    redirect_with_status('invalid_input');
}

// ตรวจสอบอีเมล
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with_status('invalid_email');
}

// ตรวจสอบว่ามีไฟล์ส่งมาหรือไม่ (ใช้ชื่อ equipment_file ตามหน้าฟอร์ม)
if (!isset($_FILES['equipment_file']) || $_FILES['equipment_file']['error'] === UPLOAD_ERR_NO_FILE) {
    redirect_with_status('no_file');
}

$file = $_FILES['equipment_file'];

// -----------------------------
// 2) ตรวจสอบ error ของไฟล์
// -----------------------------
if ($file['error'] !== UPLOAD_ERR_OK) {
    redirect_with_status('upload_error');
}

// -----------------------------
// 3) ตั้งค่า path อัปโหลด
// -----------------------------
$upload_dir = __DIR__ . '/../uploads/quotations/';

// ถ้ายังไม่มีโฟลเดอร์ ให้สร้างอัตโนมัติ
if (!is_dir($upload_dir)) {
    if (!@mkdir($upload_dir, 0775, true)) {
        redirect_with_status('mkdir_failed');
    }
}

// -----------------------------
// 4) ตรวจสอบชนิดและขนาดไฟล์
// -----------------------------
$original_name = $file['name'];
$tmp_name      = $file['tmp_name'];
$file_size     = $file['size'];

$allowed_ext = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
$file_ext    = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
$max_size    = 5 * 1024 * 1024; // 5MB

if (!in_array($file_ext, $allowed_ext, true)) {
    redirect_with_status('invalid_type');
}

if ($file_size > $max_size) {
    redirect_with_status('file_too_large');
}

// -----------------------------
// 5) ตั้งชื่อไฟล์ใหม่
// -----------------------------
$new_file_name = 'quote_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
$destination   = $upload_dir . $new_file_name;

// -----------------------------
// 6) ย้ายไฟล์
// -----------------------------
if (!move_uploaded_file($tmp_name, $destination)) {
    redirect_with_status('move_failed');
}

// -----------------------------
// 7) บันทึกลงฐานข้อมูล
// -----------------------------
try {
    // แก้ไขชื่อคอลัมน์เป็น equipment_list_file (ใช้ขีดล่างให้ตรงกับฐานข้อมูลจริง)
    $sql = "INSERT INTO quotations (
                company_name,
                contact_person,
                email,
                phone,
                message, 
                equipment_list_file,
                status
            ) VALUES (
                :company_name,
                :contact_person,
                :email,
                :phone,
                :message,
                :equipment_list_file,
                'pending'
            )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':company_name'        => htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'),
        ':contact_person'      => htmlspecialchars($contact_person, ENT_QUOTES, 'UTF-8'),
        ':email'               => $email,
        ':phone'               => htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'),
        ':message'             => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'), 
        ':equipment_list_file' => $new_file_name
    ]);

    // บันทึกสำเร็จ กลับไปหน้าฟอร์มพร้อมแจ้งเตือน
    redirect_with_status('success');

} catch (PDOException $e) {
    // ถ้า insert DB ไม่สำเร็จ ลบไฟล์ที่เพิ่งอัปโหลดทิ้งเพื่อไม่ให้หนัก Server
    if (file_exists($destination)) {
        unlink($destination);
    }
    
    // ปิด Debug เพื่อให้ระบบแจ้งเตือนทำงานได้ตามปกติ
    redirect_with_status('db_error');
}
?>