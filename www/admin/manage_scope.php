<?php 
require_once '../config/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['admin_logged_in'])) { header("Location: index.php"); exit(); }

// --- 1. จัดการหมวดหมู่ (Category) ---
if (isset($_POST['save_category'])) {
    $id = $_POST['cat_id'] ?? null;
    $name_en = htmlspecialchars($_POST['name_en']);
    $sort_order = $_POST['sort_order'] ?? 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($id) {
        $pdo->prepare("UPDATE scope_categories SET name_en=?, sort_order=?, is_active=? WHERE id=?")->execute([$name_en, $sort_order, $is_active, $id]);
    } else {
        $pdo->prepare("INSERT INTO scope_categories (name_en, sort_order, is_active) VALUES (?, ?, ?)")->execute([$name_en, $sort_order, $is_active]);
    }
    header("Location: manage_scope.php?status=success"); exit();
}

if (isset($_GET['delete_cat_id'])) {
    $check = $pdo->prepare("SELECT COUNT(*) FROM scope_items WHERE category_id = ?");
    $check->execute([$_GET['delete_cat_id']]);
    if ($check->fetchColumn() > 0) {
        header("Location: manage_scope.php?status=cat_error_in_use"); exit();
    } else {
        $pdo->prepare("DELETE FROM scope_categories WHERE id = ?")->execute([$_GET['delete_cat_id']]);
        header("Location: manage_scope.php?status=deleted"); exit();
    }
}

// --- 2. จัดการรายการเครื่องมือ (Items) ---
if (isset($_POST['save_item'])) {
    $id = $_POST['item_id'] ?? null;
    $category_id = $_POST['category_id'];
    $instrument = htmlspecialchars($_POST['instrument']);
    $reference = !empty($_POST['reference']) ? htmlspecialchars($_POST['reference']) : NULL;
    $range_value = !empty($_POST['range_value']) ? htmlspecialchars($_POST['range_value']) : NULL;
    $sort_order = $_POST['sort_order'] ?? 0;
    $is_group_header = isset($_POST['is_group_header']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($id) {
        $pdo->prepare("UPDATE scope_items SET category_id=?, instrument=?, reference=?, range_value=?, is_group_header=?, sort_order=?, is_active=? WHERE id=?")->execute([$category_id, $instrument, $reference, $range_value, $is_group_header, $sort_order, $is_active, $id]);
    } else {
        $pdo->prepare("INSERT INTO scope_items (category_id, instrument, reference, range_value, is_group_header, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)")->execute([$category_id, $instrument, $reference, $range_value, $is_group_header, $sort_order, $is_active]);
    }
    header("Location: manage_scope.php?status=success"); exit();
}

if (isset($_GET['delete_item_id'])) {
    $pdo->prepare("DELETE FROM scope_items WHERE id = ?")->execute([$_GET['delete_item_id']]);
    header("Location: manage_scope.php?status=deleted"); exit();
}

// --- 3. ดึงข้อมูลแสดงผล ---
$categories = $pdo->query("SELECT * FROM scope_categories ORDER BY sort_order ASC, id ASC")->fetchAll();
$all_items = $pdo->query("SELECT * FROM scope_items ORDER BY sort_order ASC, id ASC")->fetchAll();
$grouped = [];
foreach ($all_items as $item) { $grouped[$item['category_id']][] = $item; }

include 'includes/admin_header.php'; 
?>

<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .group-header-row { background-color: #fff1f2 !important; border-left: 4px solid #E31E24; }
    .status-badge { padding: 4px 10px; border-radius: 5px; font-size: 0.75rem; font-weight: 500; }
    .status-on { background: #ecfdf5; color: #10b981; }
    .status-off { background: #fef2f2; color: #ef4444; }
</style>

<div class="admin-container p-4 p-lg-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0"><i class="fas fa-list-alt text-danger me-2"></i> จัดการขอบข่ายการให้บริการ</h2>
            <p class="text-muted small mb-0">จัดการหมวดหมู่และรายการเครื่องมือในหน้า Scope</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-white shadow-sm border rounded-pill px-4 fw-bold" onclick="openCatModal()">จัดการหมวดหมู่</button>
            <button class="btn-brand" onclick="openItemModal()"><i class="fas fa-plus me-1"></i> เพิ่มรายการเครื่องมือ</button>
        </div>
    </div>

    <div class="accordion" id="scopeAcc">
        <?php foreach ($categories as $cat): 
            $count = count($grouped[$cat['id']] ?? []);
        ?>
        <div class="accordion-item mb-3 border-0 shadow-sm rounded-4 overflow-hidden">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cat_<?php echo $cat['id']; ?>">
                    <div class="d-flex align-items-center w-100">
                        <i class="fas fa-folder text-warning me-3 fs-4"></i>
                        <div class="me-auto">
                            <span class="fw-bold text-dark d-block"><?php echo htmlspecialchars($cat['name_en']); ?></span>
                        </div>
                        <span class="status-badge <?php echo $cat['is_active'] ? 'status-on' : 'status-off'; ?> me-3">
                            <?php echo $cat['is_active'] ? 'แสดงผล' : 'ซ่อน'; ?>
                        </span>
                        <span class="badge-count me-3"><?php echo $count; ?> รายการ</span>
                    </div>
                </button>
            </h2>
            <div id="cat_<?php echo $cat['id']; ?>" class="accordion-collapse collapse" data-bs-parent="#scopeAcc">
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>ลำดับ</th>
                                <th>รายการเครื่องมือ</th>
                                <th>Reference Method</th>
                                <th>Calibration Range</th>
                                <th class="text-center">สถานะ</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($count > 0): foreach($grouped[$cat['id']] as $row): ?>
                            <tr class="<?php echo $row['is_group_header'] ? 'group-header-row' : ''; ?>">
                                <td><?php echo $row['sort_order']; ?></td>
                                <td>
                                    <?php if($row['is_group_header']): ?>
                                        <div class="fw-bold text-danger"><i class="fas fa-caret-right me-1"></i> <?php echo htmlspecialchars($row['instrument']); ?> (หัวข้อย่อย)</div>
                                    <?php else: ?>
                                        <div class="fw-medium text-dark"><?php echo htmlspecialchars($row['instrument']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['reference'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['range_value'] ?? '-'); ?></td>
                                <td class="text-center">
                                    <span class="status-badge <?php echo $row['is_active'] ? 'status-on' : 'status-off'; ?>">
                                        <?php echo $row['is_active'] ? 'เปิด' : 'ปิด'; ?>
                                    </span>
                                </td>
                                <td class="text-center" style="width: 150px;">
                                    <button class="btn-action-edit me-1" onclick='editItem(<?php echo json_encode($row); ?>)'><i class="fas fa-edit"></i></button>
                                    <button class="btn-action-delete" onclick="confirmDelete('?delete_item_id=<?php echo $row['id']; ?>')"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted small">ยังไม่มีรายการข้อมูลในหมวดหมู่นี้</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="modal fade" id="catModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold m-0" id="catModalTitle">จัดการหมวดหมู่ (Categories)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="" method="POST" id="catForm" class="mb-4 bg-light p-3 rounded-4 border">
                    <input type="hidden" name="cat_id" id="cat_id">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">ชื่อหมวดหมู่ (Category Name) *</label>
                            <input type="text" name="name_en" id="cat_name_en" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">ลำดับการแสดงผล</label>
                            <input type="number" name="sort_order" id="cat_sort_order" class="form-control" value="0">
                        </div>
                        <div class="col-12 d-flex align-items-center mt-2">
                            <div class="form-check form-switch fs-5">
                                <input class="form-check-input" type="checkbox" name="is_active" id="cat_is_active" checked>
                                <label class="form-check-label fs-6 ms-2 mt-1">เปิดใช้งาน (Active)</label>
                            </div>
                        </div>
                        <div class="col-12 text-end mt-3">
                            <button type="button" class="btn btn-secondary rounded-pill px-4" onclick="resetCatForm()">เคลียร์ฟอร์ม</button>
                            <button type="submit" name="save_category" class="btn-brand px-4">บันทึกหมวดหมู่</button>
                        </div>
                    </div>
                </form>
                
                <h6 class="fw-bold mb-3">รายการหมวดหมู่ที่มีอยู่</h6>
                <div class="list-group list-group-flush border rounded-4 overflow-hidden">
                    <?php foreach($categories as $cat): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                        <div>
                            <span class="fw-bold text-dark me-2"><?php echo htmlspecialchars($cat['name_en']); ?></span>
                            <span class="badge bg-secondary">ลำดับ: <?php echo $cat['sort_order']; ?></span>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary" onclick='editCategory(<?php echo json_encode($cat); ?>)'><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('?delete_cat_id=<?php echo $cat['id']; ?>')"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 30px; overflow: hidden;">
            <div class="modal-header border-0 bg-light p-4">
                <h5 class="modal-title fw-bold" id="itemModalTitle">ข้อมูลเครื่องมือ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4 p-lg-5">
                    <input type="hidden" name="item_id" id="item_id">
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">หมวดหมู่ *</label>
                            <select name="category_id" id="item_category_id" class="form-select rounded-4 p-3" required>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name_en']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch p-3 bg-light rounded-4 border">
                                <input class="form-check-input ms-0 me-3 mt-1 fs-5" type="checkbox" name="is_group_header" id="item_is_group_header">
                                <label class="form-check-label fw-bold text-danger">ตั้งเป็นหัวข้อย่อย (Group Header)</label>
                                <div class="small text-muted ms-5 mt-1">ถ้าเลือกข้อนี้ ข้อมูลอ้างอิงและช่วงการวัดจะไม่แสดงในตาราง</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">รายการเครื่องมือ *</label>
                            <input type="text" name="instrument" id="item_instrument" class="form-control rounded-4 p-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Reference Method</label>
                            <input type="text" name="reference" id="item_reference" class="form-control rounded-4 p-3" placeholder="(เว้นว่างได้)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Calibration Range</label>
                            <input type="text" name="range_value" id="item_range_value" class="form-control rounded-4 p-3" placeholder="(เว้นว่างได้)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">ลำดับการแสดงผล</label>
                            <input type="number" name="sort_order" id="item_sort_order" class="form-control rounded-4 p-3" value="0">
                        </div>
                        <div class="col-md-6 d-flex align-items-center mt-5">
                            <div class="form-check form-switch fs-5">
                                <input class="form-check-input" type="checkbox" name="is_active" id="item_is_active" checked>
                                <label class="form-check-label fs-6 ms-2 mt-1 fw-bold">เปิดใช้งาน</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">ปิด</button>
                    <button type="submit" name="save_item" class="btn-brand px-5">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const catModal = new bootstrap.Modal(document.getElementById('catModal'));
const itemModal = new bootstrap.Modal(document.getElementById('itemModal'));

function openCatModal() {
    resetCatForm();
    catModal.show();
}

function resetCatForm() {
    document.getElementById('catForm').reset();
    document.getElementById('cat_id').value = "";
    document.getElementById('cat_is_active').checked = true;
}

function editCategory(data) {
    document.getElementById('cat_id').value = data.id;
    document.getElementById('cat_name_en').value = data.name_en;
    document.getElementById('cat_sort_order').value = data.sort_order;
    document.getElementById('cat_is_active').checked = data.is_active == 1;
}

function openItemModal() {
    document.querySelector('#itemModal form').reset();
    document.getElementById('item_id').value = "";
    document.getElementById('item_is_active').checked = true;
    document.getElementById('itemModalTitle').innerText = "เพิ่มรายการเครื่องมือใหม่";
    itemModal.show();
}

function editItem(data) {
    document.getElementById('itemModalTitle').innerText = "แก้ไขรายการเครื่องมือ";
    document.getElementById('item_id').value = data.id;
    document.getElementById('item_category_id').value = data.category_id;
    document.getElementById('item_instrument').value = data.instrument;
    document.getElementById('item_reference').value = data.reference;
    document.getElementById('item_range_value').value = data.range_value;
    document.getElementById('item_sort_order').value = data.sort_order;
    document.getElementById('item_is_group_header').checked = data.is_group_header == 1;
    document.getElementById('item_is_active').checked = data.is_active == 1;
    itemModal.show();
}

function confirmDelete(url) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: "ข้อมูลจะถูกลบอย่างถาวร!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#E31E24',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ลบเลย',
        cancelButtonText: 'ยกเลิก',
        customClass: { popup: 'rounded-4' }
    }).then((result) => { if (result.isConfirmed) { window.location.href = url; } });
}

<?php if(isset($_GET['status'])): ?>
    Swal.fire({
        icon: '<?php echo (strpos($_GET['status'], 'error') !== false) ? 'error' : 'success'; ?>',
        title: 'ดำเนินการสำเร็จ',
        <?php if($_GET['status'] == 'cat_error_in_use'): ?>
        text: 'ไม่สามารถลบหมวดหมู่ได้ เนื่องจากมีรายการเครื่องมืออยู่ด้านใน',
        <?php endif; ?>
        showConfirmButton: false, timer: 2000,
        customClass: { popup: 'rounded-4' }
    });
    window.history.replaceState({}, document.title, window.location.pathname);
<?php endif; ?>
</script>