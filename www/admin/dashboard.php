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

try {
    $stmt = $pdo->prepare("SELECT * FROM quotations ORDER BY requested_at DESC");
    $stmt->execute();
    $quotations = $stmt->fetchAll();
    
    $stats = ['total' => count($quotations), 'pending' => 0, 'processing' => 0, 'success' => 0];
    foreach($quotations as $q) {
        if(isset($stats[$q['status']])) $stats[$q['status']]++;
    }
} catch (PDOException $e) { 
    die("<div class='alert alert-danger'>Database Error: " . $e->getMessage() . "</div>"); 
}
?>

<div class="admin-container py-5">
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="stat-card d-flex align-items-center bg-white p-3 rounded shadow-sm border-start border-primary border-4">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3 p-3 rounded-circle"><i class="bi bi-file-earmark-medical fs-4"></i></div>
                <div><small class="text-muted d-block">ทั้งหมด</small><h4 class="fw-bold mb-0"><?= $stats['total'] ?></h4></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card d-flex align-items-center bg-white p-3 rounded shadow-sm border-start border-warning border-4">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3 p-3 rounded-circle"><i class="bi bi-hourglass-split fs-4"></i></div>
                <div><small class="text-muted d-block">รอดำเนินการ</small><h4 class="fw-bold mb-0 text-warning"><?= $stats['pending'] ?></h4></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card d-flex align-items-center bg-white p-3 rounded shadow-sm border-start border-info border-4">
                <div class="stat-icon bg-info bg-opacity-10 text-info me-3 p-3 rounded-circle"><i class="bi bi-gear-wide-connected fs-4"></i></div>
                <div><small class="text-muted d-block">กำลังทำ</small><h4 class="fw-bold mb-0 text-info"><?= $stats['processing'] ?></h4></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card d-flex align-items-center bg-white p-3 rounded shadow-sm border-start border-success border-4">
                <div class="stat-icon bg-success bg-opacity-10 text-success me-3 p-3 rounded-circle"><i class="bi bi-check2-all fs-4"></i></div>
                <div><small class="text-muted d-block">สำเร็จแล้ว</small><h4 class="fw-bold mb-0 text-success"><?= $stats['success'] ?></h4></div>
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
            <table class="table mb-0 align-middle" id="quoteTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">วันที่รับเรื่อง</th>
                        <th>บริษัทและผู้ติดต่อ</th>
                        <th>ข้อมูลการสื่อสาร</th>
                        <th>หมายเหตุ</th>
                        <th>สถานะ</th>
                        <th class="text-center pe-4">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($quotations) > 0): ?>
                        <?php foreach ($quotations as $row): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold text-dark"><?= date('d M Y', strtotime($row['requested_at'])) ?></div>
                                    <div class="small text-muted"><i class="bi bi-clock me-1"></i> <?= date('H:i', strtotime($row['requested_at'])) ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark text-truncate" style="max-width: 200px;"><?= htmlspecialchars($row['company_name']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($row['contact_person']) ?></div>
                                </td>
                                <td>
                                    <div class="small mb-1"><i class="bi bi-envelope-at me-2 text-primary"></i><?= htmlspecialchars($row['email']) ?></div>
                                    <div class="small"><i class="bi bi-telephone me-2 text-primary"></i><?= htmlspecialchars($row['phone']) ?></div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded" style="min-width: 150px;">
                                        <div class="text-muted small text-truncate me-2" style="max-width: 120px;">
                                            <?= $row['message'] ? htmlspecialchars($row['message']) : 'ไม่มีหมายเหตุ' ?>
                                        </div>
                                        <?php if ($row['message']): ?>
                                            <button type="button" class="btn btn-sm btn-link p-0 text-primary" 
                                                    data-company="<?= htmlspecialchars($row['company_name']) ?>"
                                                    data-message="<?= htmlspecialchars($row['message']) ?>"
                                                    onclick="showNote(this)">
                                                <i class="bi bi-eye-fill fs-5"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm status-select" 
                                            data-id="<?= $row['id'] ?>" 
                                            onchange="updateStatus(this)" 
                                            style="<?= 
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
                                        <a href="../uploads/quotations/<?= $row['equipment_list_file'] ?>" target="_blank" class="btn btn-sm btn-outline-dark" title="ดาวน์โหลด">
                                            <i class="bi bi-download"></i>
                                        </a>

                                        <button type="button" class="btn-action-delete" onclick="confirmDelete(<?= $row['id'] ?>)" title="ลบรายการ">
                                            <i class="fa-solid fa-trash-can"></i> </button>
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

<div class="modal fade" id="noteModal" tabindex="-1" aria-labelledby="noteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 bg-light p-4" style="border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold" id="noteModalLabel"><i class="bi bi-chat-left-dots-fill me-2 text-primary"></i>รายละเอียดหมายเหตุ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4">
                    <label class="text-muted small fw-bold text-uppercase d-block mb-1">ชื่อบริษัท</label>
                    <div id="modalCompanyName" class="fw-bold fs-5 text-dark"></div>
                </div>
                <div>
                    <label class="text-muted small fw-bold text-uppercase d-block mb-1">ข้อความเพิ่มเติม</label>
                    <div id="modalNoteContent" class="p-3 bg-light rounded-3 shadow-sm" style="white-space: pre-wrap; line-height: 1.7; min-height: 100px; max-height: 300px; overflow-y: auto;"></div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-secondary px-4 py-2 rounded-pill" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// 1. ค้นหาในตาราง
document.getElementById('tableSearch').addEventListener('keyup', function() {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll('#quoteTable tbody tr');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(value) ? '' : 'none';
    });
});

/**
 * 2. ฟังก์ชันเปิด Modal หมายเหตุ
 * ดึงข้อมูลจาก Data Attribute ของปุ่มที่คลิก
 */
function showNote(btn) {
    const company = btn.getAttribute('data-company');
    const message = btn.getAttribute('data-message');

    const modalName = document.getElementById('modalCompanyName');
    const modalContent = document.getElementById('modalNoteContent');
    const modalElement = document.getElementById('noteModal');

    if (modalElement && modalName && modalContent) {
        modalName.innerText = company;
        modalContent.innerText = message;

        // เรียก Instance ของ Modal
        const noteModal = bootstrap.Modal.getOrCreateInstance(modalElement);
        noteModal.show();
    }
}

// 3. อัปเดตสถานะแบบ AJAX
function updateStatus(selectElement) {
    const id = selectElement.getAttribute('data-id');
    const status = selectElement.value;
    selectElement.disabled = true;

    const formData = new FormData();
    formData.append('id', id);
    formData.append('status', status);

    fetch('./action/update_quote_status.php', { 
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({ 
                icon: 'success', title: 'อัปเดตสำเร็จ', text: 'กำลังรีเฟรชข้อมูล...', 
                showConfirmButton: false, timer: 700, timerProgressBar: true 
            }).then(() => {
                location.reload();
            });
        } else {
            selectElement.disabled = false;
            Swal.fire('Error', data.message || 'Error updating status', 'error');
        }
    })
    .catch(err => {
        selectElement.disabled = false;
        Swal.fire('Error', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
    });
}

// 4. ยืนยันการลบ
function confirmDelete(id) {
    Swal.fire({
        title: 'ยืนยันการลบ?', text: "ข้อมูลจะถูกลบออกจากระบบถาวร!", icon: 'warning',
        showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
        confirmButtonText: 'ลบข้อมูล', cancelButtonText: 'ยกเลิก'
    }).then((result) => { 
        if (result.isConfirmed) {
            window.location.href = `action/delete_quote.php?id=${id}`; 
        }
    });
}
</script>
