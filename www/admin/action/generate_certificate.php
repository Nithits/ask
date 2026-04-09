<?php
// admin/action/generate_certificate.php
require_once __DIR__ . '/../../config/db.php'; 
session_start();

if (!isset($_SESSION['admin_logged_in'])) { 
    die("Unauthorized"); 
}

$id = $_GET['id'] ?? null;
if (!$id) { 
    die("Data Missing"); 
}

try {
    // ปรับ SQL ให้ดึงข้อมูลจากตารางหลัก quotations เป็นหลัก
    // และพยายาม LEFT JOIN ไปหาชื่อเครื่องมือ (ถ้าไม่มีก็ไม่เป็นไร ระบบจะไม่ Die)
    $sql = "SELECT q.*, s.title_th, s.title_en 
            FROM quotations q
            LEFT JOIN quotation_items qi ON q.id = qi.quotation_id
            LEFT JOIN services s ON qi.service_id = s.id
            WHERE q.id = ? 
            ORDER BY qi.id ASC LIMIT 1";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // เช็คข้อมูล ถ้าไม่มีเลยจริงๆ ถึงจะแจ้ง Error
    if (!$data) { 
        die("ไม่พบข้อมูลใบอ้างอิงรหัส #$id ในระบบ"); 
    }
} catch (PDOException $e) { 
    die("DB Error: " . $e->getMessage()); 
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Certificate - <?= htmlspecialchars($id) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f5f5f5; display: flex; justify-content: center; padding: 20px; margin: 0; }
        .cert-container { background: white; width: 297mm; height: 210mm; padding: 10mm; box-shadow: 0 0 20px rgba(0,0,0,0.1); box-sizing: border-box; }
        .border-outer { border: 15px solid #1a365d; height: 100%; padding: 10px; box-sizing: border-box; }
        .border-inner { border: 2px solid #1a365d; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px; box-sizing: border-box; }
        h1 { color: #1a365d; font-size: 50px; margin: 0; text-transform: uppercase; }
        h3 { color: #666; margin-top: 5px; margin-bottom: 50px; }
        .info-section { width: 80%; text-align: left; font-size: 20px; line-height: 2.2; margin-bottom: 40px; }
        .info-label { display: inline-block; width: 200px; font-weight: bold; }
        .signature-section { width: 100%; display: flex; justify-content: space-around; margin-top: 50px; }
        .sig-box { text-align: center; }
        .no-print-btn { position: fixed; top: 20px; right: 20px; padding: 10px 20px; background: #1a365d; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; z-index: 999; }
        
        @media print { 
            .no-print-btn { display: none; } 
            body { background: white; padding: 0; }
            .cert-container { box-shadow: none; margin: 0; }
            @page { size: landscape; }
        }
    </style>
</head>
<body>

<button class="no-print-btn" onclick="window.print()">🖨️ พิมพ์/บันทึก PDF</button>

<div class="cert-container">
    <div class="border-outer">
        <div class="border-inner">
            <h1>Certificate of Calibration</h1>
            <h3>ASK CALIBRATION & SERVICE CO., LTD.</h3>
            
            <div class="info-section">
                <div><span class="info-label">Certificate No:</span> CERT-<?= date('Y') ?>-<?= str_pad($id, 5, '0', STR_PAD_LEFT) ?></div>
                <div><span class="info-label">Customer Name:</span> <?= htmlspecialchars($data['company_name'] ?? 'N/A') ?></div>
                <div><span class="info-label">Instrument:</span> <?= htmlspecialchars($data['title_en'] ?: ($data['title_th'] ?: 'General Instrument')) ?></div>
                <div><span class="info-label">Calibration Date:</span> <?= date('d F Y', strtotime($data['requested_at'])) ?></div>
                <div><span class="info-label">Result:</span> <span style="color: green; font-weight: bold;">PASSED</span></div>
            </div>

            <p style="text-align:center;">This is to certify that the above instrument has been calibrated according to international standards.</p>

            <div class="signature-section">
                <div class="sig-box"><br>__________________________<br>( Quality Manager )</div>
                <div class="sig-box"><br>__________________________<br>( Authorized Signatory )</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>