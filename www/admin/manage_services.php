<?php 
require_once '../config/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php"); exit();
}

// --- 1.1 จัดการประเภท Lab ---
if (isset($_POST['add_category'])) {
    $cat_name = htmlspecialchars($_POST['new_category_name']);
    $cat_key = strtolower(str_replace(' ', '_', trim($cat_name)));
    $pdo->prepare("INSERT IGNORE INTO service_categories (category_key, category_name) VALUES (?, ?)")->execute([$cat_key, $cat_name]);
    header("Location: manage_services.php?status=cat_added"); exit();
}

if (isset($_POST['edit_category'])) {
    $pdo->prepare("UPDATE service_categories SET category_name = ? WHERE id = ?")->execute([htmlspecialchars($_POST['cat_name']), $_POST['cat_id']]);
    header("Location: manage_services.php?status=cat_updated"); exit();
}

if (isset($_GET['delete_cat_id'])) {
    $check = $pdo->prepare("SELECT COUNT(*) FROM services WHERE category = ?");
    $check->execute([$_GET['key']]);
    if ($check->fetchColumn() > 0) {
        header("Location: manage_services.php?status=cat_error_in_use"); exit();
    } else {
        $pdo->prepare("DELETE FROM service_categories WHERE id = ?")->execute([$_GET['delete_cat_id']]);
        header("Location: manage_services.php?status=cat_deleted"); exit();
    }
}

// --- 1.2 จัดการข้อมูลเครื่องมือ ---
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("SELECT image FROM services WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    $res = $stmt->fetch();
    if ($res && $res['image']) { @unlink("../assets/uploads/services/" . $res['image']); }
    $pdo->prepare("DELETE FROM services WHERE id = ?")->execute([$_GET['delete_id']]);
    header("Location: manage_services.php?status=deleted"); exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_service'])) {
    $id = $_POST['service_id'] ?? null;
    $img_name = $_POST['old_image'] ?? null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $img_name = 'svc_' . time() . '_' . uniqid() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['image']['tmp_name'], "../assets/uploads/services/" . $img_name);
        if (!empty($_POST['old_image'])) { @unlink("../assets/uploads/services/" . $_POST['old_image']); }
    }
    if ($id) {
        $pdo->prepare("UPDATE services SET category=?, title_th=?, title_en=?, image=? WHERE id=?")->execute([$_POST['category'], htmlspecialchars($_POST['title_th']), htmlspecialchars($_POST['title_en']), $img_name, $id]);
    } else {
        $pdo->prepare("INSERT INTO services (category, title_th, title_en, image) VALUES (?, ?, ?, ?)")->execute([$_POST['category'], htmlspecialchars($_POST['title_th']), htmlspecialchars($_POST['title_en']), $img_name]);
    }
    header("Location: manage_services.php?status=success"); exit();
}

$categories = $pdo->query("SELECT * FROM service_categories ORDER BY id ASC")->fetchAll();
$all_services = $pdo->query("SELECT * FROM services ORDER BY id DESC")->fetchAll();
$grouped = [];
foreach ($all_services as $s) { $grouped[$s['category']][] = $s; }

include 'includes/admin_header.php'; 
?>

<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="admin-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0"><i class="fas fa-microscope text-danger me-2"></i> จัดการ</h2>
            <p class="text-muted small mb-0">จัดการหมวดหมู่ Lab และรายการเครื่องมือวัด</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-white shadow-sm border rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#catModal">จัดการประเภท Lab</button>
            <button class="btn-brand" onclick="openAddModal()"><i class="fas fa-plus me-1"></i> เพิ่มเครื่องมือ</button>
        </div>
    </div>

    <div class="accordion" id="labAcc">
        <?php foreach ($categories as $cat): 
            $count = count($grouped[$cat['category_key']] ?? []);
        ?>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#lab_<?php echo $cat['id']; ?>">
                    <div class="d-flex align-items-center w-100">
                        <i class="fas fa-folder text-warning me-3"></i>
                        <span class="me-auto"><?php echo $cat['category_name']; ?></span>
                        <span class="badge-count me-3"><?php echo $count; ?> รายการ</span>
                    </div>
                </button>
            </h2>
            <div id="lab_<?php echo $cat['id']; ?>" class="accordion-collapse collapse" data-bs-parent="#labAcc">
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>ภาพ</th>
                                <th>รายการเครื่องมือ</th>
                                <th class="text-center">จัดการบริการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($count > 0): foreach($grouped[$cat['category_key']] as $row): ?>
                            <tr>
                                <td style="width: 80px;"><img src="../assets/uploads/services/<?php echo $row['image'] ?: 'default.png'; ?>" class="img-svc"></td>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo $row['title_th']; ?></div>
                                    <div class="small text-muted"><?php echo $row['title_en']; ?></div>
                                </td>
                                <td class="text-center" style="width: 150px;">
                                    <button class="btn-action-edit me-1" onclick='editService(<?php echo json_encode($row); ?>)'><i class="fas fa-edit"></i></button>
                                    <button class="btn-action-delete" onclick="confirmDeleteService(<?php echo $row['id']; ?>)"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="3" class="text-center py-4 text-muted small">ยังไม่มีรายการข้อมูลใน Lab นี้</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="modal fade" id="serviceModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 30px; overflow: hidden;">
            <div class="modal-header border-0 bg-light p-4">
                <h5 class="modal-title fw-bold" id="modalTitle">ข้อมูลเครื่องมือวัด</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4 p-lg-5">
                    <input type="hidden" name="service_id" id="service_id">
                    <input type="hidden" name="old_image" id="old_image">
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">หมวดหมู่ Lab *</label>
                            <select name="category" id="cat_select" class="form-select rounded-4 p-3" required>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat['category_key']; ?>"><?php echo $cat['category_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">ชื่อเครื่องมือ (TH) *</label>
                            <input type="text" name="title_th" id="t_th" class="form-control rounded-4 p-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">ชื่อเครื่องมือ (EN) *</label>
                            <input type="text" name="title_en" id="t_en" class="form-control rounded-4 p-3" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">อัปโหลดรูปภาพ</label>
                            <div class="p-3 bg-light rounded-4 border">
                                <div id="img_preview_box" class="mb-2 d-none">
                                    <img src="" id="img_view" class="rounded-3 border" style="width: 80px; height: 80px; object-fit: cover;">
                                </div>
                                <input type="file" name="image" class="form-control border-0 bg-transparent shadow-none">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">ปิด</button>
                    <button type="submit" name="save_service" class="btn-brand px-5">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="catModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold">หมวดหมู่ห้องปฏิบัติการ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="" method="POST" class="mb-4">
                    <div class="input-group">
                        <input type="text" name="new_category_name" class="form-control rounded-start-pill border-end-0 ps-4" placeholder="ชื่อ Lab ใหม่..." required>
                        <button class="btn btn-danger rounded-end-pill px-4" name="add_category">เพิ่ม</button>
                    </div>
                </form>
                <div class="list-group list-group-flush border rounded-4 overflow-hidden">
                    <?php foreach($categories as $cat): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                        <span class="fw-medium"><?php echo $cat['category_name']; ?></span>
                        <div class="btn-group">
                            <a href="?delete_cat_id=<?php echo $cat['id']; ?>&key=<?php echo $cat['category_key']; ?>" class="text-danger small ms-3" onclick="return confirm('ลบหมวดหมู่นี้?')"><i class="fas fa-trash"></i></a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const serviceModal = new bootstrap.Modal(document.getElementById('serviceModal'));

function openAddModal() {
    document.querySelector('#serviceModal form').reset();
    document.getElementById('service_id').value = "";
    document.getElementById('modalTitle').innerText = "เพิ่มข้อมูลเครื่องมือใหม่";
    document.getElementById('img_preview_box').classList.add('d-none');
    serviceModal.show();
}

function editService(data) {
    document.getElementById('modalTitle').innerText = "แก้ไขข้อมูลเครื่องมือ";
    document.getElementById('service_id').value = data.id;
    document.getElementById('cat_select').value = data.category;
    document.getElementById('t_th').value = data.title_th;
    document.getElementById('t_en').value = data.title_en;
    document.getElementById('old_image').value = data.image || "";
    if(data.image) {
        document.getElementById('img_preview_box').classList.remove('d-none');
        document.getElementById('img_view').src = "../assets/uploads/services/" + data.image;
    } else {
        document.getElementById('img_preview_box').classList.add('d-none');
    }
    serviceModal.show();
}

function confirmDeleteService(id) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: "ข้อมูลเครื่องมือจะถูกลบอย่างถาวร!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#E31E24',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ลบเลย',
        cancelButtonText: 'ยกเลิก',
        customClass: { popup: 'rounded-4' }
    }).then((result) => { if (result.isConfirmed) { window.location.href = `manage_services.php?delete_id=${id}`; } });
}

<?php if(isset($_GET['status'])): ?>
    Swal.fire({
        icon: '<?php echo (strpos($_GET['status'], 'error') !== false) ? 'error' : 'success'; ?>',
        title: 'ดำเนินการสำเร็จ',
        showConfirmButton: false, timer: 1500,
        customClass: { popup: 'rounded-4' }
    });
    window.history.replaceState({}, document.title, window.location.pathname);
<?php endif; ?>
</script>