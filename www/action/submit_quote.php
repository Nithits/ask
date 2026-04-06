<?php 
require_once __DIR__ . '/../config/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ฟังก์ชันส่งกลับ
function redirect_with_status($status) {
    header("Location: ../quote.php?status=" . $status);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php"); exit();
}

// 1) รับค่าจากฟอร์ม
$company_name   = trim($_POST['company_name'] ?? '');
$contact_person = trim($_POST['contact_person'] ?? '');
$email          = trim($_POST['email'] ?? '');
$phone          = trim($_POST['phone'] ?? '');
$message        = trim($_POST['message'] ?? '');

if (empty($company_name) || empty($contact_person)) {
    redirect_with_status('invalid_input');
}

// 2) จัดการไฟล์ (แนบก็ได้ ไม่แนบก็ได้)
$new_file_name = ""; 
if (isset($_FILES['equipment_file']) && $_FILES['equipment_file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['equipment_file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_file_name = 'quote_' . date('Ymd_His') . '_' . uniqid() . '.' . $ext;
    $upload_dir = __DIR__ . '/../uploads/quotations/';
    
    if (!is_dir($upload_dir)) { @mkdir($upload_dir, 0775, true); }
    move_uploaded_file($file['tmp_name'], $upload_dir . $new_file_name);
}

// 3) บันทึกลงฐานข้อมูล
try {
    $pdo->beginTransaction();

    // บันทึกลงตาราง quotations
    $sql = "INSERT INTO quotations (company_name, contact_person, email, phone, message, equipment_list_file, status) 
            VALUES (:comp, :cont, :email, :phone, :msg, :file, 'pending')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':comp'  => $company_name,
        ':cont'  => $contact_person,
        ':email' => $email,
        ':phone' => $phone,
        ':msg'   => $message,
        ':file'  => $new_file_name
    ]);

    $quotation_id = $pdo->lastInsertId();

    // บันทึกลงตาราง quotation_items (ถ้ามีการเลือกรายการจากตาราง)
    if (isset($_POST['item_id']) && is_array($_POST['item_id'])) {
        $sql_item = "INSERT INTO quotation_items (quotation_id, service_id, price, qty, total) VALUES (?, ?, ?, ?, ?)";
        $stmt_item = $pdo->prepare($sql_item);

        foreach ($_POST['item_id'] as $index => $service_id) {
            if (empty($service_id)) continue; // ข้ามถ้าไม่ได้เลือกเครื่องมือ

            $price = floatval($_POST['item_price'][$index] ?? 0);
            $qty   = intval($_POST['item_qty'][$index] ?? 1);
            $total = $price * $qty;

            $stmt_item->execute([$quotation_id, $service_id, $price, $qty, $total]);
        }
    }

    $pdo->commit();
    redirect_with_status('success');

} catch (Exception $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    
    // 🚨 ถ้ายัง Error อีก ให้เอาเครื่องหมาย // ข้างหน้าบรรทัดล่างออก แล้วกดส่งใหม่ จะเห็นสาเหตุจริงครับ
    // die("Debug Error: " . $e->getMessage());
    
    redirect_with_status('db_error');
}