<?php
// admin/action/update_quote_status.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php'; 
session_start();

// ดึงข้อมูลจาก Request
$id = $_REQUEST['id'] ?? null;
$status = $_REQUEST['status'] ?? null;
$admin_name = $_SESSION['admin_username'] ?? 'Admin'; // เก็บชื่อคนแก้ไว้ลง Log

if ($id && $status) {
    try {
        // เริ่ม Transaction เพื่อให้มั่นใจว่าถ้าบันทึก Log ไม่ได้ ข้อมูลหลักก็จะไม่เปลี่ยน
        $pdo->beginTransaction();

        // 1. ดึงสถานะปัจจุบันมาก่อน (เพื่อทำ Log สถานะเก่า)
        $stmt_old = $pdo->prepare("SELECT status FROM quotations WHERE id = ?");
        $stmt_old->execute([$id]);
        $old_status = $stmt_old->fetchColumn();

        // 2. อัปเดตสถานะใหม่ในตาราง quotations
        $stmt = $pdo->prepare("UPDATE quotations SET status = ? WHERE id = ?");
        $result = $stmt->execute([$status, $id]);

        if ($result) {
            // 3. 📍 เพิ่มการบันทึกลง Log (ตรงตาม DFD/ER ของนาย)
            $log_sql = "INSERT INTO quotation_logs (quotation_id, action_type, old_status, new_status, action_by, created_at) 
                        VALUES (?, 'status_change', ?, ?, ?, NOW())";
            $stmt_log = $pdo->prepare($log_sql);
            $stmt_log->execute([$id, $old_status, $status, $admin_name]);

            // ยืนยันการบันทึกทั้งหมด
            $pdo->commit();
            echo json_encode(['status' => 'success']);
        } else {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'DB Update Fail']);
        }

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Data Missing',
        'debug' => ['id' => $id, 'status' => $status]
    ]);
}