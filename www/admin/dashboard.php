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

<div class="admin-container py-5 px-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0"><i class="bi bi-speedometer2 me-2"></i>แผงควบคุมระบบ (Dashboard)</h3>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
                <h6 class="fw-bold mb-3 text-muted text-uppercase small">สัดส่วนสถานะงาน</h6>
                <div style="max-height: 220px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="stat-card d-flex align-items-center bg-white p-4 rounded-4 shadow-sm border-start border-primary border-5">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3 p-3 rounded-circle"><i class="bi bi-file-earmark-medical fs-4"></i></div>
                        <div><small class="text-muted d-block fw-bold">ทั้งหมด</small><h2 class="fw-bold mb-0"><?= $stats['total'] ?></h2></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card d-flex align-items-center bg-white p-4 rounded-4 shadow-sm border-start border-warning border-5">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3 p-3 rounded-circle"><i class="bi bi-hourglass-split fs-4"></i></div>
                        <div><small class="text-muted d-block fw-bold">รอดำเนินการ</small><h2 class="fw-bold mb-0 text-warning"><?= $stats['pending'] ?></h2></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card d-flex align-items-center bg-white p-4 rounded-4 shadow-sm border-start border-info border-5">
                        <div class="stat-icon bg-info bg-opacity-10 text-info me-3 p-3 rounded-circle"><i class="bi bi-gear-wide-connected fs-4"></i></div>
                        <div><small class="text-muted d-block fw-bold">กำลังดำเนินการ</small><h2 class="fw-bold mb-0 text-info"><?= $stats['processing'] ?></h2></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card d-flex align-items-center bg-white p-4 rounded-4 shadow-sm border-start border-success border-5">
                        <div class="stat-icon bg-success bg-opacity-10 text-success me-3 p-3 rounded-circle"><i class="bi bi-check2-all fs-4"></i></div>
                        <div><small class="text-muted d-block fw-bold">สำเร็จแล้ว</small><h2 class="fw-bold mb-0 text-success"><?= $stats['success'] ?></h2></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if(isset($db_error)): ?>
        <div class="alert alert-warning"><i class="bi bi-exclamation-triangle-fill me-2"></i> ระบบยังไม่พบตาราง <b>quotation_items</b> กรุณาตรวจสอบฐานข้อมูลครับ</div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-0 p-4 d-flex justify-content-between align-items-center">
            <h4 class="fw-bold mb-0 text-dark">รายการขอใบเสนอราคา</h4>
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
                        <th class="text-end">ยอดประเมิน (THB)</th>
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
                                        <button type="button" class="btn btn-sm btn-outline-primary" title="รายละเอียด" onclick='showQuoteDetails(<?= json_encode($row)?>, <?= json_encode($current_items)?>)'>
                                            <i class="bi bi-card-list"></i>
                                        </button>

                                        <?php if($row['status'] == 'success'): ?>
                                            <a href="action/generate_certificate.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-success shadow-sm" title="ออกใบรับรอง (Certificate)">
                                                <i class="bi bi-patch-check-fill"></i>
                                            </a>
                                        <?php endif; ?>

                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $row['id'] ?>)">
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

<div class="modal fade" id="quoteDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 bg-light p-4" style="border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold"><i class="bi bi-clipboard-data-fill me-2 text-primary"></i>รายละเอียดขอใบเสนอราคา</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 p-lg-5 bg-white">
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-uppercase text-muted mb-3 small">ข้อมูลลูกค้า</h6>
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="mb-2"><i class="bi bi-building me-2 text-muted"></i> <strong id="detCompany"></strong></div>
                            <div class="mb-2"><i class="bi bi-person-badge me-2 text-muted"></i> <span id="detContact"></span></div>
                            <div class="mb-2"><i class="bi bi-envelope-at me-2 text-muted"></i> <span id="detEmail"></span></div>
                            <div><i class="bi bi-telephone me-2 text-muted"></i> <span id="detPhone"></span></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-uppercase text-muted mb-3 small">หมายเหตุเพิ่มเติม</h6>
                        <div class="p-3 bg-light rounded-3 border h-100" id="detMessage" style="white-space: pre-wrap;"></div>
                    </div>
                </div>
                <h6 class="fw-bold text-uppercase text-muted mb-3 small">รายการเครื่องมือ</h6>
                <div class="table-responsive border rounded-3 mb-3">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr><th>#</th><th>ชื่อเครื่องมือ</th><th class="text-end">ราคา/หน่วย</th><th class="text-center">จำนวน</th><th class="text-end">รวม</th></tr>
                        </thead>
                        <tbody id="detItemsBody"></tbody>
                        <tfoot class="table-light">
                            <tr><td colspan="4" class="text-end fw-bold">ยอดประเมินรวมทั้งหมด :</td><td class="text-end fw-bold text-danger fs-5" id="detGrandTotal">0.00</td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// 1. วาดกราฟสถิติ (Chart.js)
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('statusChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Processing', 'Success'],
            datasets: [{
                data: [<?= $stats['pending'] ?>, <?= $stats['processing'] ?>, <?= $stats['success'] ?>],
                backgroundColor: ['#f59e0b', '#3b82f6', '#10b981'],
                borderWidth: 0
            }]
        },
        options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });
});

// 2. ฟังก์ชัน Search (ของเดิม)
document.getElementById('tableSearch').addEventListener('keyup', function() {
    let value = this.value.toLowerCase();
    document.querySelectorAll('#quoteTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(value) ? '' : 'none';
    });
});

// 3. ฟังก์ชัน Modal (ของเดิม)
function showQuoteDetails(quoteData, itemsData) {
    document.getElementById('detCompany').innerText = quoteData.company_name;
    document.getElementById('detContact').innerText = quoteData.contact_person;
    document.getElementById('detEmail').innerText = quoteData.email;
    document.getElementById('detPhone').innerText = quoteData.phone;
    document.getElementById('detMessage').innerText = quoteData.message || '-';
    const tbody = document.getElementById('detItemsBody');
    tbody.innerHTML = '';
    let grandTotal = 0;
    itemsData.forEach((item, index) => {
        const total = item.price * item.qty;
        grandTotal += total;
        tbody.innerHTML += `<tr><td>${index+1}</td><td>${item.title_th || item.title_en}</td><td class="text-end">${parseFloat(item.price).toLocaleString()}</td><td class="text-center">${item.qty}</td><td class="text-end fw-bold">${total.toLocaleString()}</td></tr>`;
    });
    document.getElementById('detGrandTotal').innerText = grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2});
    new bootstrap.Modal(document.getElementById('quoteDetailModal')).show();
}

// 4. อัปเดตสถานะ (ปรับปรุงให้รองรับ Transaction)
function updateStatus(selectElement) {
    const id = selectElement.getAttribute('data-id');
    const status = selectElement.value;
    selectElement.disabled = true;
    const formData = new FormData();
    formData.append('id', id);
    formData.append('status', status);

    fetch('./action/update_quote_status.php', { method: 'POST', body: formData })
    .then(r => r.json()).then(data => {
        if (data.status === 'success') {
            Swal.fire({ icon: 'success', title: 'บันทึกสำเร็จ', showConfirmButton: false, timer: 800 }).then(() => location.reload());
        } else {
            selectElement.disabled = false;
            Swal.fire('Error', data.message, 'error');
        }
    });
}

// 5. ลบ (ของเดิม)
function confirmDelete(id) {
    Swal.fire({ title: 'ลบรายการนี้?', text: "ข้อมูลจะหายไปถาวร!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'ลบเลย' })
    .then(r => { if (r.isConfirmed) window.location.href = `action/delete_quote.php?id=${id}`; });
}
</script>

<?php include 'includes/admin_footer.php'; ?>