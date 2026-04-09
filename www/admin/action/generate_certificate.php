<?php
// admin/action/generate_certificate.php
// ถอยหลัง 2 ชั้นเพื่อไปหา config/db.php (จาก admin/action/ ไปที่ root)
require_once __DIR__ . '/../../config/db.php'; 
session_start();

// ตรวจสอบ Login แอดมิน
if (!isset($_SESSION['admin_logged_in'])) { 
    die("Unauthorized: กรุณาเข้าสู่ระบบก่อน"); 
}

$id = $_GET['id'] ?? null;
if (!$id) { 
    die("Error: ไม่พบรหัสใบเสนอราคา"); 
}

try {
    // ปรับ SQL ให้ดึงข้อมูลที่จำเป็นและ Join ให้ถูกจุด
    // เราเน้นดึงข้อมูล Quotation และชื่อ Service จากชิ้นแรกที่เจอในรายการ
    $sql = "SELECT q.*, s.title_th, s.title_en 
            FROM quotations q
            LEFT JOIN quotation_items qi ON q.id = qi.quotation_id
            LEFT JOIN services s ON qi.service_id = s.id
            WHERE q.id = ? AND q.status = 'success' 
            LIMIT 1";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$data) { 
        die("Error: ไม่พบข้อมูลงานที่สำเร็จแล้ว (ใบอ้างอิง #$id)"); 
    }
} catch (PDOException $e) { 
    die("Database Error: " . $e->getMessage()); 
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate - <?= htmlspecialchars($id) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Sarabun', sans-serif; 
            background-color: #f5f5f5; 
            display: flex; 
            justify-content: center; 
            padding: 20px; 
            margin: 0;
        }
        .cert-container { 
            background: white; 
            width: 297mm; 
            height: 210mm; 
            padding: 10mm; 
            box-shadow: 0 0 20px rgba(0,0,0,0.1); 
            position: relative; 
            box-sizing: border-box; 
        }
        .border-outer { 
            border: 15px solid #1a365d; 
            height: 100%; 
            padding: 10px; 
            box-sizing: border-box; 
        }
        .border-inner { 
            border: 2px solid #1a365d; 
            height: 100%; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
            padding: 40px; 
            box-sizing: border-box; 
            position: relative;
        }
        h1 { color: #1a365d; font-size: 50px; margin: 0; text-transform: uppercase; font-weight: 700; }
        h3 { color: #666; margin-top: 5px; margin-bottom: 50px; font-weight: 400; }
        
        .info-section { width: 80%; text-align: left; font-size: 22px; line-height: 2.2; margin-bottom: 30px; }
        .info-label { display: inline-block; width: 220px; font-weight: bold; }
        
        .cert-note { font-size: 18px; color: #444; margin-top: 20px; text-align: center; }
        
        .signature-section { width: 100%; display: flex; justify-content: space-around; margin-top: 60px; }
        .sig-box { text-align: center; font-size: 18px; }
        
        .no-print-btn { 
            position: fixed; 
            top: 20px; 
            right: 20px; 
            padding: 12px 25px; 
            background: #1a365d; 
            color: white; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: bold; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            z-index: 1000;
        }
        .no-print-btn:hover { background: #2c5282; }
        
        @media print { 
            .no-print-btn { display: none; } 
            body { background: white; padding: 0; }
            .cert-container { box-shadow: none; margin: 0; }
            @page { size: landscape; margin: 0; }
        }
    </style>
</head>
<body>

<button class="no-print-btn" onclick="window.print()">🖨️ พิมพ์ใบเซอร์ / บันทึกเป็น PDF</button>

<div class="cert-container">
    <div class="border-outer">
        <div class="border-inner">
            <h1>Certificate of Calibration</h1>
            <h3>ASK CALIBRATION & SERVICE CO., LTD.</h3>
            
            <div class="info-section">
                <div><span class="info-label">Certificate No:</span> CERT-<?= date('Y') ?>-<?= str_pad($id, 5, '0', STR_PAD_LEFT) ?></div>
                <div><span class="info-label">Customer Name:</span> <?= htmlspecialchars($data['company_name']) ?></div>
                <div><span class="info-label">Instrument:</span> <?= htmlspecialchars($data['title_en'] ?: $data['title_th'] ?: 'N/A') ?></div>
                <div><span class="info-label">Calibration Date:</span> <?= date('d F Y', strtotime($data['requested_at'])) ?></div>
                <div><span class="info-label">Result:</span> <span style="color: green; font-weight: bold;">PASSED</span></div>
            </div>

            <p class="cert-note">This is to certify that the above instrument has been calibrated according to international standards <br> and the results are traceable to the National Institute of Metrology.</p>

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