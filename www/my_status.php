<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. เช็คว่าลูกค้าล็อกอินหรือยัง
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    $_SESSION['error_msg'] = "กรุณาเข้าสู่ระบบเพื่อดูสถานะงานครับ";
    header("Location: login.php");
    exit();
}

require_once 'config/db.php';
include_once 'includes/language_setup.php';

// 2. ดึงอีเมลจาก Session (ที่เพิ่งแก้ใน login.php มา)
$user_email = $_SESSION['user_email'] ?? ''; 

// 3. ดึงข้อมูลจากตาราง quotations
$my_quotes = [];
if (!empty($user_email)) {
    try {
        // เรียงจากไอดีล่าสุดขึ้นก่อน
        $stmt = $pdo->prepare("SELECT * FROM quotations WHERE email = ? ORDER BY id DESC");
        $stmt->execute([$user_email]);
        $my_quotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // error_log($e->getMessage());
    }
}

include 'includes/header.php';
$curr_lang = $_SESSION['lang'] ?? 'th';

// 4. ฟังก์ชันจัดการสีและข้อความสถานะ (แก้ให้ตรงกับฝั่ง Admin แล้ว)
function getStatusBadge($status, $lang) {
    $s = strtolower($status);
    
    // เคส: สำเร็จ / เสร็จสิ้น (อิงตามค่า 'success' ในรูปแอดมินของนาย)
    if ($s == 'success' || $s == 'completed' || $s == 'finish') {
        return '<span class="badge bg-success px-3 py-2 rounded-pill shadow-sm"><i class="fas fa-check-circle me-1"></i> ' . ($lang == 'th' ? 'เสร็จสิ้น / จัดส่งแล้ว' : 'Completed') . '</span>';
    }
    // เคส: กำลังดำเนินการ / สอบเทียบ
    if ($s == 'processing' || $s == 'in progress') {
        return '<span class="badge bg-primary px-3 py-2 rounded-pill shadow-sm"><i class="fas fa-tools me-1"></i> ' . ($lang == 'th' ? 'กำลังสอบเทียบเครื่องมือ' : 'Calibration in Progress') . '</span>';
    }
    // เคส: ส่งใบเสนอราคาแล้ว
    if ($s == 'quoted' || $s == 'sent') {
        return '<span class="badge bg-info text-dark px-3 py-2 rounded-pill shadow-sm"><i class="fas fa-file-invoice-dollar me-1"></i> ' . ($lang == 'th' ? 'ส่งใบเสนอราคาแล้ว' : 'Quoted') . '</span>';
    }
    // เคส: ยกเลิก
    if ($s == 'cancelled' || $s == 'rejected') {
        return '<span class="badge bg-danger px-3 py-2 rounded-pill shadow-sm"><i class="fas fa-times-circle me-1"></i> ' . ($lang == 'th' ? 'ยกเลิก' : 'Cancelled') . '</span>';
    }
    // เคสเริ่มต้น: รอดำเนินการ (Pending)
    return '<span class="badge bg-warning text-dark px-3 py-2 rounded-pill shadow-sm"><i class="fas fa-clock me-1"></i> ' . ($lang == 'th' ? 'รอดำเนินการ / ประเมินราคา' : 'Pending') . '</span>';
}
?>

<style>
    .status-card {
        transition: all 0.3s ease;
        border: 1px solid #eee;
    }
    .status-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
    }
    .status-icon-box {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        border-radius: 12px;
        color: #F58220;
    }
</style>

<div class="container py-5" style="min-height: 75vh;">
    <div class="row mb-5">
        <div class="col-lg-8">
            <h2 class="fw-bold mb-2"><i class="fas fa-clipboard-list text-warning me-2"></i> <?php echo $curr_lang == 'th' ? 'ติดตามสถานะงาน' : 'Track Your Status'; ?></h2>
            <p class="text-muted fs-5"><?php echo $curr_lang == 'th' ? 'ตรวจสอบประวัติและขั้นตอนการดำเนินงานสอบเทียบของคุณ' : 'Check your calibration service history and current progress.'; ?></p>
        </div>
    </div>

    <?php if(empty($my_quotes)): ?>
        <div class="text-center py-5 bg-white shadow-sm rounded-4">
            <img src="assets/images/empty-box.png" alt="No data" style="width: 120px; opacity: 0.5;" class="mb-4" onerror="this.style.display='none'">
            <i class="fas fa-folder-open d-block text-light mb-3" style="font-size: 5rem;"></i>
            <h4 class="fw-bold text-muted"><?php echo $curr_lang == 'th' ? 'ไม่พบประวัติการรายการของคุณ' : 'No service history found'; ?></h4>
            <p class="text-muted mb-4">หากคุณเพิ่งส่งข้อมูล โปรดรอสักครู่เพื่อให้ระบบอัปเดต</p>
            <a href="quote.php" class="btn btn-warning fw-bold px-4 rounded-pill shadow-sm"><?php echo $curr_lang == 'th' ? 'ไปที่หน้าขอใบเสนอราคา' : 'Go to Quotation Page'; ?></a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach($my_quotes as $row): ?>
                <div class="col-12">
                    <div class="card status-card border-0 shadow-sm rounded-4 p-2">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3 border-md-end mb-3 mb-md-0">
                                    <div class="d-flex align-items-center">
                                        <div class="status-icon-box me-3">
                                            <i class="far fa-calendar-check fa-lg"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">วันที่ทำรายการ</small>
                                            <span class="fw-bold text-dark">
                                                <?php 
                                                    // ตรวจสอบชื่อคอลัมน์วันที่ (อิงตามระบบของนายที่อาจจะใช้ requested_at หรือ created_at)
                                                    $date_val = $row['requested_at'] ?? $row['created_at'] ?? null;
                                                    echo ($date_val) ? date('d/m/Y', strtotime($date_val)) : '<span class="text-muted">-</span>'; 
                                                ?>
                                            </span>
                                            <small class="text-muted d-block small"><?php echo ($date_val) ? date('H:i', strtotime($date_val)) : ''; ?> น.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3 mb-md-0 px-md-4">
                                    <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">ชื่อบริษัท / รายละเอียด</small>
                                    <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($row['company_name']); ?></h5>
                                    <div class="text-muted small">
                                        <i class="fas fa-info-circle me-1"></i> 
                                        <?php echo !empty($row['message']) ? htmlspecialchars($row['message']) : ($curr_lang == 'th' ? 'ไม่มีหมายเหตุเพิ่มเติม' : 'No additional remarks'); ?>
                                    </div>
                                </div>

                                <div class="col-md-3 text-md-end">
                                    <small class="text-muted d-block fw-bold text-uppercase mb-2" style="font-size: 0.7rem; letter-spacing: 1px;">สถานะงานปัจจุบัน</small>
                                    <?php echo getStatusBadge($row['status'], $curr_lang); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>