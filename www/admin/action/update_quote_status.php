<?php
// admin/action/update_quote_status.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php'; 
session_start();

// ใช้ $_REQUEST เพื่อดึงข้อมูลไม่ว่าจะเป็น POST หรือ GET (กรณีโดน Redirect)
$id = $_REQUEST['id'] ?? null;
$status = $_REQUEST['status'] ?? null;

if ($id && $status) {
    try {
        $stmt = $pdo->prepare("UPDATE quotations SET status = ? WHERE id = ?");
        $result = $stmt->execute([$status, $id]);
        if ($result) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'DB Fail']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Data Missing',
        'debug' => ['method' => $_SERVER['REQUEST_METHOD'], 'id' => $id, 'status' => $status]
    ]);
}