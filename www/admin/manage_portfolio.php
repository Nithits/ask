<?php 
require_once '../config/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['admin_logged_in'])) { header("Location: index.php"); exit(); }

// --- 1. จัดการหมวดหมู่ผลงาน (Category) ---
if (isset($_POST['add_category'])) {
    $cat_name = htmlspecialchars($_POST['new_category_name']);
    $cat_key = strtolower(str_replace(' ', '_', trim($cat_name)));
    $pdo->prepare("INSERT IGNORE INTO portfolio_categories (category_key, category_name) VALUES (?, ?)")->execute([$cat_key, $cat_name]);
    header("Location: manage_portfolio.php?status=cat_added"); exit();
}

if (isset($_GET['delete_cat_id'])) {
    // เช็คว่ามีรูปในหมวดหมู่นี้ไหมก่อนลบ
    $check = $pdo->prepare("SELECT COUNT(*) FROM portfolio_items WHERE category_key = ?");
    $check->execute([$_GET['key']]);
    if ($check->fetchColumn() > 0) {
        header("Location: manage_portfolio.php?status=cat_error_in_use"); exit();
    } else {
        $pdo->prepare("DELETE FROM portfolio_categories WHERE id = ?")->execute([$_GET['delete_cat_id']]);
        header("Location: manage_portfolio.php?status=cat_deleted"); exit();
    }
}

// --- 2. จัดการรูปภาพผลงาน (Items) ---
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("SELECT image FROM portfolio_items WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    $res = $stmt->fetch();
    if ($res && $res['image']) { @unlink("../assets/uploads/portfolio/" . $res['image']); }
    $pdo->prepare("DELETE FROM portfolio_items WHERE id = ?")->execute([$_GET['delete_id']]);
    header("Location: manage_portfolio.php?status=deleted"); exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_portfolio'])) {
    $id = $_POST['item_id'] ?? null;
    $img_name = $_POST['old_image'] ?? null;
    $upload_dir = '../assets/uploads/portfolio/';
    if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $img_name = 'port_' . time() . '_' . uniqid() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $img_name);
        if (!empty($_POST['old_image'])) { @unlink($upload_dir . $_POST['old_image']); }
    }

    if ($id) {
        $pdo->prepare("UPDATE portfolio_items SET category_key=?, title_th=?, title_en=?, image=? WHERE id=?")->execute([$_POST['category_key'], htmlspecialchars($_POST['title_th']), htmlspecialchars($_POST['title_en']), $img_name, $id]);
    } else {
        $pdo->prepare("INSERT INTO portfolio_items (category_key, title_th, title_en, image) VALUES (?, ?, ?, ?)")->execute([$_POST['category_key'], htmlspecialchars($_POST['title_th']), htmlspecialchars($_POST['title_en']), $img_name]);
    }
    header("Location: manage_portfolio.php?status=success"); exit();
}

// ดึงข้อมูลมาแสดงผล
$categories = $pdo->query("SELECT * FROM portfolio_categories ORDER BY id ASC")->fetchAll();
$all_items = $pdo->query("SELECT * FROM portfolio_items ORDER BY id DESC")->fetchAll();
$grouped = [];
foreach ($all_items as $item) { $grouped[$item['category_key']][] = $item; }

include 'includes/admin_header.php'; 
?>

<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="admin-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0"><i class="fas fa-images text-danger me-2"></i> จัดการผลงานบริษัท</h2>
            <p class="text-muted small mb-0">จัดการหมวดหมู่และเพิ่มรูปภาพผลงาน (1 หมวดหมู่ใส่ได้หลายรูป)</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-white shadow-sm border rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#catModal">จัดการหมวดหมู่ผลงาน</button>
            <button class="btn-brand" onclick="openAddModal()"><i class="fas fa-plus me-1"></i> เพิ่มรูปผลงาน</button>
        </div>
    </div>

    <div class="accordion" id="portAcc">
        <?php foreach ($categories as $cat): 
            $count = count($grouped[$cat['category_key']] ?? []);
        ?>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cat_<?php echo $cat['id']; ?>">
                    <div class="d-flex align-items-center w-100">
                        <i class="fas fa-folder-open text-warning me-3"></i>
                        <span class="me-auto"><?php echo $cat['category_name']; ?></span>
                        <span class="badge-count me-3"><?php echo $count; ?> รูปภาพ</span>
                    </div>
                </button>
            </h2>
            <div id="cat_<?php echo $cat['id']; ?>" class="accordion-collapse collapse" data-bs-parent="#portAcc">
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>รูปภาพผลงาน</th>
                                <th>คำอธิบายภาพ (Caption)</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($count > 0): foreach($grouped[$cat['category_key']] as $row): ?>
                            <tr>
                                <td style="width: 100px;"><img src="../assets/uploads/portfolio/<?php echo $row['image'] ?: 'default.png'; ?>" class="img-svc"></td>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['title_th']); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($row['title_en']); ?></div>
                                </td>
                                <td class="text-center" style="width: 150px;">
                                    <button class="btn-action-edit me-1" onclick='editPortfolio(<?php echo json_encode($row); ?>)'><i class="fas fa-edit"></i></button>
                                    <button class="btn-action-delete" onclick="confirmDelete(<?php echo $row['id']; ?>)"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="3" class="text-center py-4 text-muted small">ยังไม่มีรูปภาพในหมวดหมู่นี้</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="modal fade" id="portModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 30px; overflow: hidden;">
            <div class="modal-header border-0 bg-light p-4">
                <h5 class="modal-title fw-bold" id="modalTitle">ข้อมูลรูปภาพผลงาน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4 p-lg-5">
                    <input type="hidden" name="item_id" id="item_id">
                    <input type="hidden" name="old_image" id="old_image">
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">หมวดหมู่ผลงาน *</label>
                            <select name="category_key" id="cat_select" class="form-select rounded-4 p-3" required>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat['category_key']; ?>"><?php echo $cat['category_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">คำอธิบายภาพ (TH) *</label>
                            <input type="text" name="title_th" id="t_th" class="form-control rounded-4 p-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">คำอธิบายภาพ (EN) *</label>
                            <input type="text" name="title_en" id="t_en" class="form-control rounded-4 p-3" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">อัปโหลดรูปภาพผลงาน *</label>
                            <div class="p-3 bg-light rounded-4 border">
                                <div id="img_preview_box" class="mb-2 d-none">
                                    <img src="" id="img_view" class="rounded-3 border" style="width: 100px; height: 70px; object-fit: cover;">
                                </div>
                                <input type="file" name="image" id="image_input" class="form-control border-0 bg-transparent shadow-none" accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">ปิด</button>
                    <button type="submit" name="save_portfolio" class="btn-brand px-5">บันทึกรูปภาพ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="catModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold">หมวดหมู่ผลงาน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="" method="POST" class="mb-4">
                    <div class="input-group">
                        <input type="text" name="new_category_name" class="form-control rounded-start-pill border-end-0 ps-4" placeholder="ชื่อหมวดหมู่ใหม่..." required>
                        <button class="btn btn-danger rounded-end-pill px-4" name="add_category">เพิ่ม</button>
                    </div>
                </form>
                <div class="list-group list-group-flush border rounded-4 overflow-hidden">
                    <?php foreach($categories as $cat): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                        <span class="fw-medium"><?php echo $cat['category_name']; ?></span>
                        <a href="?delete_cat_id=<?php echo $cat['id']; ?>&key=<?php echo $cat['category_key']; ?>" class="text-danger small ms-3" onclick="return confirm('ลบหมวดหมู่นี้?')"><i class="fas fa-trash"></i></a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const portModal = new bootstrap.Modal(document.getElementById('portModal'));

function openAddModal() {
    document.querySelector('#portModal form').reset();
    document.getElementById('item_id').value = "";
    document.getElementById('image_input').required = true; // บังคับอัพรูปตอนเพิ่มใหม่
    document.getElementById('modalTitle').innerText = "เพิ่มรูปภาพผลงานใหม่";
    document.getElementById('img_preview_box').classList.add('d-none');
    portModal.show();
}

function editPortfolio(data) {
    document.getElementById('modalTitle').innerText = "แก้ไขข้อมูลรูปภาพ";
    document.getElementById('item_id').value = data.id;
    document.getElementById('cat_select').value = data.category_key;
    document.getElementById('t_th').value = data.title_th;
    document.getElementById('t_en').value = data.title_en;
    document.getElementById('old_image').value = data.image || "";
    document.getElementById('image_input').required = false; // แก้ไข ไม่ต้องอัพรูปใหม่ก็ได้

    if(data.image) {
        document.getElementById('img_preview_box').classList.remove('d-none');
        document.getElementById('img_view').src = "../assets/uploads/portfolio/" + data.image;
    } else {
        document.getElementById('img_preview_box').classList.add('d-none');
    }
    portModal.show();
}

function confirmDelete(id) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: "รูปภาพและข้อมูลจะถูกลบอย่างถาวร!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#E31E24',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ลบเลย',
        cancelButtonText: 'ยกเลิก',
        customClass: { popup: 'rounded-4' }
    }).then((result) => { if (result.isConfirmed) { window.location.href = `manage_portfolio.php?delete_id=${id}`; } });
}

<?php if(isset($_GET['status'])): ?>
    Swal.fire({
        icon: '<?php echo (strpos($_GET['status'], 'error') !== false) ? 'error' : 'success'; ?>',
        title: 'ดำเนินการสำเร็จ',
        <?php if(isset($_GET['status']) && $_GET['status'] == 'cat_error_in_use'): ?>
        text: 'ไม่สามารถลบได้ เนื่องจากมีรูปภาพอยู่ในหมวดหมู่นี้',
        <?php endif; ?>
        showConfirmButton: false, timer: 2000,
        customClass: { popup: 'rounded-4' }
    });
    window.history.replaceState({}, document.title, window.location.pathname);
<?php endif; ?>
</script>