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

    // ดึงข้อมูล "รายการเครื่องมือ" ที่ผูกกับบิลทั้งหมด (ดึงมารอไว้เลยเพื่อความรวดเร็ว)
    // โดยเชื่อม (JOIN) กับตาราง services เพื่อเอาชื่อเครื่องมือมาแสดง
    $item_stmt = $pdo->prepare("
        SELECT qi.*, s.title_th, s.title_en 
        FROM quotation_items qi
        LEFT JOIN services s ON qi.service_id = s.id
    ");
    $item_stmt->execute();
    $all_items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // จัดกลุ่มรายการเครื่องมือ ให้แยกตาม ID ของบิลเสนอราคา
    foreach($all_items as $item) {
        $quote_items[$item['quotation_id']][] = $item;
    }

} catch (PDOException $e) { 
    // หากเพิ่งสร้างโปรเจกต์และยังไม่มีตาราง quotation_items ระบบจะไม่พัง แต่จะข้ามไป
    $db_error = $e->getMessage();
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

    <?php if(isset($db_error)): ?>
        <div class="alert alert-warning"><i class="bi bi-exclamation-triangle-fill me-2"></i> ระบบยังไม่พบตาราง <b>quotation_items</b> สำหรับเก็บรายการเครื่องมือ กรุณาตรวจสอบฐานข้อมูลครับ</div>
    <?php endif; ?>

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
                            // คำนวณยอดรวมของบิลนี้
                            $current_items = $quote_items[$row['id']] ?? [];
                            $grand_total = 0;
                            foreach($current_items as $it) {
                                $grand_total += ($it['price'] * $it['qty']);
                            }
                        ?>
                            <tr>
                                <td class="ps-4">
                                    <?php $thai_time = strtotime($row['requested_at'] . ' +7 hours'); ?>
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
                                            style="width: 120px; <?= 
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
                                        
                                        <button type="button" class="btn btn-sm btn-outline-primary" title="ดูรายละเอียด"
                                                onclick='showQuoteDetails(<?= json_encode($row)?>, <?= json_encode($current_items)?>)'>
                                            <i class="bi bi-card-list"></i>
                                        </button>

                                        <?php if($row['equipment_list_file']): ?>
                                        <a href="../uploads/quotations/<?= $row['equipment_list_file'] ?>" target="_blank" class="btn btn-sm btn-outline-dark" title="ดาวน์โหลดไฟล์แนบ">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <?php else: ?>
                                        <button class="btn btn-sm btn-outline-secondary" disabled title="ไม่มีไฟล์แนบ"><i class="bi bi-dash"></i></button>
                                        <?php endif; ?>

                                        <button type="button" class="btn-action-delete btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $row['id'] ?>)" title="ลบรายการ">
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
                        <h6 class="fw-bold text-uppercase text-muted mb-3 small">ข้อมูลลูกค้า (Customer Info)</h6>
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="mb-2"><i class="bi bi-building me-2 text-muted"></i> <strong id="detCompany"></strong></div>
                            <div class="mb-2"><i class="bi bi-person-badge me-2 text-muted"></i> <span id="detContact"></span></div>
                            <div class="mb-2"><i class="bi bi-envelope-at me-2 text-muted"></i> <span id="detEmail"></span></div>
                            <div><i class="bi bi-telephone me-2 text-muted"></i> <span id="detPhone"></span></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-uppercase text-muted mb-3 small">หมายเหตุเพิ่มเติม (Remarks)</h6>
                        <div class="p-3 bg-light rounded-3 border h-100" id="detMessage" style="white-space: pre-wrap; font-size: 0.95rem;"></div>
                    </div>
                </div>

                <h6 class="fw-bold text-uppercase text-muted mb-3 small">รายการเครื่องมือที่ประเมิน (Requested Items)</h6>
                <div class="table-responsive border rounded-3 mb-3">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>ชื่อเครื่องมือ (Instrument)</th>
                                <th class="text-end">ราคา/หน่วย</th>
                                <th class="text-center">จำนวน</th>
                                <th class="text-end">รวม (THB)</th>
                            </tr>
                        </thead>
                        <tbody id="detItemsBody">
                            </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end fw-bold">ยอดประเมินรวมทั้งหมด :</td>
                                <td class="text-end fw-bold text-danger fs-5" id="detGrandTotal">0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
            <div class="modal-footer border-0 p-4 bg-light" style="border-radius: 0 0 20px 20px;">
                <button type="button" class="btn btn-secondary px-4 py-2 rounded-pill fw-bold" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
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

// 2. ฟังก์ชันเปิด Modal สรุปใบเสนอราคา
function showQuoteDetails(quoteData, itemsData) {
    // ใส่ข้อมูลลูกค้า
    document.getElementById('detCompany').innerText = quoteData.company_name;
    document.getElementById('detContact').innerText = quoteData.contact_person;
    document.getElementById('detEmail').innerText = quoteData.email;
    document.getElementById('detPhone').innerText = quoteData.phone;
    
    // ใส่หมายเหตุ
    document.getElementById('detMessage').innerText = quoteData.message ? quoteData.message : '- ไม่มีหมายเหตุ -';

    // วาดตารางรายการสินค้า
    const tbody = document.getElementById('detItemsBody');
    tbody.innerHTML = '';
    let grandTotal = 0;

    if(itemsData && itemsData.length > 0) {
        itemsData.forEach((item, index) => {
            const price = parseFloat(item.price);
            const qty = parseInt(item.qty);
            const total = price * qty;
            grandTotal += total;

            // เช็คว่ามีชื่อภาษาไทยไหม ถ้าไม่มีให้เอาภาษาอังกฤษ หรือบอกว่าไม่ทราบชื่อ
            const itemName = item.title_th || item.title_en || 'เครื่องมือที่ถูกลบไปแล้ว (ID: ' + item.service_id + ')';

            tbody.innerHTML += `
                <tr>
                    <td class="text-muted">${index + 1}</td>
                    <td class="fw-medium">${itemName}</td>
                    <td class="text-end">${price.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                    <td class="text-center">${qty}</td>
                    <td class="text-end fw-bold">${total.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                </tr>
            `;
        });
    } else {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted">ลูกค้าแนบไฟล์มาเพียงอย่างเดียว ไม่มีรายการที่เลือกจากระบบ</td></tr>`;
    }

    // สรุปยอดรวม
    document.getElementById('detGrandTotal').innerText = grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    // เปิด Modal
    const modal = new bootstrap.Modal(document.getElementById('quoteDetailModal'));
    modal.show();
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
        title: 'ยืนยันการลบ?', text: "ข้อมูลใบเสนอราคาและรายการที่เกี่ยวข้องจะถูกลบถาวร!", icon: 'warning',
        showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
        confirmButtonText: 'ลบข้อมูล', cancelButtonText: 'ยกเลิก'
    }).then((result) => { 
        if (result.isConfirmed) {
            window.location.href = `action/delete_quote.php?id=${id}`; 
        }
    });
}
</script>

<?php include 'includes/admin_footer.php'; ?>