<?php
// admin/action/generate_certificate.php
require_once __DIR__ . '/../../vendor/autoload.php'; // เรียกใช้ mPDF
require_once __DIR__ . '/../../config/db.php';
session_start();

// ตรวจสอบความปลอดภัย (ต้องเป็นแอดมินเท่านั้น)
if (!isset($_SESSION['admin_logged_in'])) {
    die("Unauthorized access");
}

$id = $_GET['id'] ?? null;

if (!$id) {
    die("ไม่พบรหัสอ้างอิง");
}

try {
    // 1. ดึงข้อมูลใบเสนอราคาและรายการเครื่องมือ (เฉพาะที่ Success)
    // เชื่อมตารางตาม ER ของนาย: quotations -> quotation_items -> services
    $sql = "SELECT q.*, qi.price, qi.qty, s.title_th, s.title_en 
            FROM quotations q
            JOIN quotation_items qi ON q.id = qi.quotation_id
            JOIN services s ON qi.service_id = s.id
            WHERE q.id = ? AND q.status = 'success' LIMIT 1";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        die("ไม่พบข้อมูล หรือสถานะงานยังไม่เสร็จสมบูรณ์");
    }

    // 2. สร้างเนื้อหา HTML สำหรับใบรับรอง
    $html = '
    <div style="border: 15px solid #1a365d; padding: 40px; text-align: center; font-family: garuda;">
        <div style="border: 2px solid #1a365d; padding: 20px;">
            <h1 style="color: #1a365d; font-size: 35px; margin-bottom: 5px;">CERTIFICATE OF CALIBRATION</h1>
            <h3 style="color: #666; margin-top: 0;">ASK Calibration & Service Co., Ltd.</h3>
            
            <div style="margin: 40px 0; text-align: left; line-height: 1.8;">
                <p><b>Certificate No:</b> CERT-' . date('Y') . '-' . str_pad($id, 4, '0', STR_PAD_LEFT) . '</p>
                <p><b>Customer Name:</b> ' . htmlspecialchars($data['company_name']) . '</p>
                <p><b>Instrument:</b> ' . htmlspecialchars($data['title_en'] ?: $data['title_th']) . '</p>
                <p><b>Calibration Date:</b> ' . date('d F Y', strtotime($data['requested_at'])) . '</p>
                <p><b>Result:</b> PASSED</p>
            </div>

            <div style="margin-top: 60px;">
                <p>This is to certify that the above instrument has been calibrated</p>
                <p>according to the international standards and factory specifications.</p>
            </div>

            <div style="margin-top: 80px; width: 100%;">
                <table style="width: 100%; border: none;">
                    <tr>
                        <td style="text-align: center; border: none;">
                            <br>__________________________<br>
                            ( Quality Manager )
                        </td>
                        <td style="text-align: center; border: none;">
                            <br>__________________________<br>
                            ( Authorized Signatory )
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>';

    // 3. ตั้งค่า mPDF และสั่งเจนไฟล์
    $mpdf = new \Mpdf\Mpdf([
        'default_font' => 'garuda',
        'format' => 'A4-L' // แนวนอน (Landscape) จะดูเป็นใบเซอร์มากกว่า
    ]);
    
    $mpdf->WriteHTML($html);
    $mpdf->Output("Certificate_".$id.".pdf", "I");

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}