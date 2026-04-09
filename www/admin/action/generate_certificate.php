<?php
// admin/action/generate_certificate.php
require_once __DIR__ . '/../../config/db.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) { die("Unauthorized"); }

$id = $_GET['id'] ?? null;
if (!$id) { die("Data Missing"); }

try {
    $sql = "SELECT q.*, s.title_th, s.title_en 
            FROM quotations q
            JOIN quotation_items qi ON q.id = qi.quotation_id
            JOIN services s ON qi.service_id = s.id
            WHERE q.id = ? AND q.status = 'success' LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$data) { die("งานยังไม่เสร็จหรือไม่มีข้อมูล"); }
} catch (PDOException $e) { die($e->getMessage()); }
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Certificate - <?= $id ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f5f5f5; display: flex; justify-content: center; padding: 20px; }
        .cert-container { background: white; width: 297mm; height: 210mm; padding: 10mm; box-shadow: 0 0 20px rgba(0,0,0,0.1); position: relative; box-sizing: border-box; }
        .border-outer { border: 15px solid #1a365d; height: 100%; padding: 10px; box-sizing: border-box; }
        .border-inner { border: 2px solid #1a365d; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px; box-sizing: border-box; }
        h1 { color: #1a365d; font-size: 50px; margin: 0; text-transform: uppercase; }
        h3 { color: #666; margin-top: 5px; margin-bottom: 50px; }
        .info-section { width: 80%; text-align: left; font-size: 20px; line-height: 2; margin-bottom: 40px; }
        .signature-section { width: 100%; display: flex; justify-content: space-around; margin-top: 50px; }
        .sig-box { text-align: center; }
        .no-print-btn { position: fixed; top: 20px; right: 20px; padding: 10px 20px; background: #1a365d; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        
        /* สั่งซ่อนปุ่มเวลาจะพิมพ์จริงๆ */
        @media print { 
            .no-print-btn { display: none; } 
            body { background: white; padding: 0; }
            .cert-container { box-shadow: none; margin: 0; }
        }
    </style>
</head>
<body>

<button class="no-print-btn" onclick="window.print()">กดเพื่อบันทึกเป็น PDF / พิมพ์ใบเซอร์</button>

<div class="cert-container">
    <div class="border-outer">
        <div class="border-inner">
            <h1>Certificate of Calibration</h1>
            <h3>ASK CALIBRATION & SERVICE CO., LTD.</h3>
            
            <div class="info-section">
                <strong>Certificate No:</strong> CERT-<?= date('Y') ?>-<?= str_pad($id, 4, '0', STR_PAD_LEFT) ?><br>
                <strong>Customer Name:</strong> <?= htmlspecialchars($data['company_name']) ?><br>
                <strong>Instrument:</strong> <?= htmlspecialchars($data['title_en'] ?: $data['title_th']) ?><br>
                <strong>Calibration Date:</strong> <?= date('d F Y', strtotime($data['requested_at'])) ?><br>
                <strong>Result:</strong> <span style="color: green; font-weight: bold;">PASSED</span>
            </div>

            <p>This is to certify that the above instrument has been calibrated according to international standards.</p>

            <div class="signature-section">
                <div class="sig-box">
                    <br>__________________________<br>( Quality Manager )
                </div>
                <div class="sig-box">
                    <br>__________________________<br>( Authorized Signatory )
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>