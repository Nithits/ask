<?php 
// 1. ตรวจสอบ Session และดึง Header
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

include 'includes/admin_header.php'; 
require_once __DIR__ . '/../config/db.php'; 

if (!isset($pdo)) {
    die("<div class='alert alert-danger'>Error: ไม่พบตัวแปรเชื่อมต่อฐานข้อมูล (\$pdo)</div>");
}

$quotations = [];
$quote_items = [];
$stats = ['total' => 0, 'pending' => 0, 'processing' => 0, 'success' => 0];

try {
    // ดึงข้อมูลใบเสนอราคาทั้งหมด
    $stmt = $pdo->prepare("SELECT * FROM quotations ORDER BY requested_at DESC");
    $stmt->execute();
    $quotations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // คำนวณสถิติ
    $stats['total'] = count($quotations);
    foreach($quotations as $q) {
        if(isset($stats[$q['status']])) $stats[$q['status']]++;
    }

    // ดึงข้อมูลรายการเครื่องมือ
    $item_stmt = $pdo->prepare("
        SELECT qi.*, s.title_th, s.title_en 
        FROM quotation_items qi
        LEFT JOIN services s ON qi.service_id = s.id
    ");
    $item_stmt->execute();
    $all_items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($all_items as $item) {
        $quote_items[$item['quotation_id']][] = $item;
    }

} catch (PDOException $e) { 
    $db_error = $e->getMessage();
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="admin-container py-4 px-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0"><i class="bi bi-speedometer2 me-2"></i>แผงควบคุมระบบ (Dashboard)</h3>
        <span class="badge bg-dark px-3 py-2 rounded-pill">อัปเดตล่าสุด: <?= date('d/m/Y H:i') ?></span>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
                <h5 class="fw-bold mb-4">สัดส่วนสถานะงาน</h5>
                <div style="max-height: 250px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row g-3">
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-primary border-5">
                        <small class="text-muted d-block fw-bold uppercase">ทั้งหมด</small>
                        <h2 class="fw-bold mb-0 text-primary"><?= $stats['total'] ?></h2>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-warning border-5">
                        <small class="text-muted d-block fw-bold uppercase">รอดำเนินการ</small>
                        <h2 class="fw-bold mb-0 text-warning"><?= $stats['pending'] ?></h2>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-info border-5">
                        <small class="text-muted d-block fw-bold uppercase">กำลังทำ</small>
                        <h2 class="fw-bold mb-0 text-info"><?= $stats['processing'] ?></h2>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-success border-5">
                        <small class="text-muted d-block fw-bold uppercase">สำเร็จแล้ว</small>
                        <h2 class="fw-bold mb-0 text-success"><?= $stats['success'] ?></h2>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold mb-1">จัดการคลังเครื่องมือ (Inventory)</h5>
                                <p class="mb-0 opacity-75">เพิ่มหรือแก้ไขรายการเครื่องมือวัดในระบบกลาง</p>
                            </div>
                            <a href="manage_services.php" class="btn btn-light rounded-pill px-4 fw-bold">ไปจัดการ <i class="bi bi-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-0 p-4 d-flex justify-content-between align-items-center">
            <h4 class="fw-bold mb-0">รายการขอใบเสนอราคา</h4>
            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                <input type="text" id="tableSearch" class="form-control bg-light border-0" placeholder="ค้นหาชื่อบริษัท...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle table-hover" id="quoteTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">วันที่รับเรื่อง</th>
                        <th>บริษัทและผู้ติดต่อ</th>
                        <th>ข้อมูลการสื่อสาร</th>
                        <th class="text-end">ยอดประเมินรวม (THB)</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center pe-4">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($quotations) > 0): ?>
                        <?php foreach ($quotations as $row): 
                            $current_items = $quote_items[$row['id']] ?? [];
                            $grand_total = 0;
                            foreach($current_items as $it) {
                                $grand_total += ($it['price'] * $it['qty']);
                            }
                        ?>
                            <tr>
                                <td class="ps-4">
                                    <?php $thai_time = strtotime($row['requested_at']); ?>
                                    <div class="fw-semibold text-dark"><?= date('d M Y', $thai_time) ?></div>
                                    <div class="small text-muted"><i class="bi bi-clock me-1"></i> <?= date('H:i', $thai_time) ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark text-truncate" style="max-width: 200px;"><?= htmlspecialchars($row['company_name']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($row['contact_person']) ?></div>
                                </td>
                                <td>
                                    <div class="small mb-1"><i class="bi bi-envelope-at me-2 text-primary"></i><?= htmlspecialchars($row['email']) ?></div>
                                    <div class="small"><i class="bi bi-telephone me-2 text-primary"></i><?= htmlspecialchars($row['phone']) ?></div>
                                </td>
                                <td class="text-end">
                                    <div class="fw-bold text-danger"><?= number_format($grand_total, 2) ?></div>
                                    <div class="small text-muted"><?= count($current_items) ?> รายการ</div>
                                </td>
                                <td class="text-center">
                                    <select class="form-select form-select-sm status-select mx-auto" 
                                            data-id="<?= $row['id'] ?>" 
                                            onchange="updateStatus(this)" 
                                            style="width: 120px; border-radius: 20px; font-weight: bold; <?= 
                                                ($row['status'] == 'success') ? 'background-color: #ecfdf5; border-color: #34d399; color: #065f46;' : 
                                                (($row['status'] == 'processing') ? 'background-color: #fffbeb; border-color: #fbbf24; color: #92400e;' : 
                                                'background-color: #f8fafc; color: #64748b; border-color: #e2e8f0;')
                                            ?>">
                                        <option value="pending" <?= $row['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="processing" <?= $row['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                                        <option value="success" <?= $row['status'] == 'success' ? 'selected' : '' ?>>Success</option>
                                    </select>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick='showQuoteDetails(<?= json_encode($row)?>, <?= json_encode($current_items)?>)'>
                                            <i class="bi bi-card-list"></i>
                                        </button>
                                        
                                        <a href="action/generate_pdf.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-outline-danger" title="สร้างใบเสนอราคา PDF">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>

                                        <button type="button" class="btn-action-delete btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $row['id'] ?>)">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">ไม่พบข้อมูลใบเสนอราคา</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('statusChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Processing', 'Success'],
            datasets: [{
                data: [<?= $stats['pending'] ?>, <?= $stats['processing'] ?>, <?= $stats['success'] ?>],
                backgroundColor: ['#f59e0b', '#3b82f6', '#10b981'],
                hoverOffset: 4,
                borderWidth: 0
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
            }
        }
    });
});

// ฟังก์ชันอื่นๆ (Search, UpdateStatus, Delete) ของนายผมเก็บไว้เหมือนเดิมเป๊ะครับ
</script>

<?php 
// เรียกไฟล์ Modal และ JavaScript จัดการสถานะที่นายมีอยู่แล้ว
include 'includes/quote_modal.php'; // แนะนำให้แยก Modal ไปเก็บไฟล์นี้
include 'includes/admin_footer.php'; 
?>