<?php 
require_once '../config/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['admin_logged_in'])) { header("Location: index.php"); exit(); }

// --- 1. จัดการระบบลบข้อมูล ---
if (isset($_GET['delete_id'])) {
    $id = $_GET['id'] ?? $_GET['delete_id'];
    $stmt = $pdo->prepare("SELECT image FROM news WHERE id = ?");
    $stmt->execute([$id]);
    $news = $stmt->fetch();
    if ($news && !empty($news['image'])) {
        $file_path = "../assets/uploads/news_img/" . $news['image'];
        if (file_exists($file_path)) { unlink($file_path); }
    }
    $pdo->prepare("DELETE FROM news WHERE id = ?")->execute([$id]);
    header("Location: manage_news.php?status=deleted");
    exit();
}

// --- 2. จัดการระบบบันทึกข้อมูล ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_news'])) {
    $id = $_POST['news_id'] ?? null;
    $title_th = htmlspecialchars($_POST['title_th']);
    $title_en = htmlspecialchars($_POST['title_en']);
    $content_th = $_POST['content_th']; // เก็บค่า HTML หรือข้อความ
    $content_en = $_POST['content_en'];
    $published_date = $_POST['published_date'];
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    
    $img_name = $_POST['old_image'] ?? null;
    $upload_dir = '../assets/uploads/news_img/';
    if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $img_name = 'news_' . time() . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $img_name);
        if (!empty($_POST['old_image'])) { @unlink($upload_dir . $_POST['old_image']); }
    }

    if ($id) {
        $stmt = $pdo->prepare("UPDATE news SET title_th=?, title_en=?, content_th=?, content_en=?, image=?, published_date=?, end_date=? WHERE id=?");
        $stmt->execute([$title_th, $title_en, $content_th, $content_en, $img_name, $published_date, $end_date, $id]);
        $status = "updated";
    } else {
        $stmt = $pdo->prepare("INSERT INTO news (title_th, title_en, content_th, content_en, image, published_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title_th, $title_en, $content_th, $content_en, $img_name, $published_date, $end_date]);
        $status = "added";
    }
    header("Location: manage_news.php?status=$status");
    exit();
}

$stmt = $pdo->query("SELECT * FROM news ORDER BY published_date DESC, id DESC");
$news_items = $stmt->fetchAll();

include 'includes/admin_header.php'; 
?>

<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="admin-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0" style="color: #1a1a1a;"><i class="fas fa-bullhorn text-danger me-2"></i> จัดการข่าวสารและกิจกรรม</h2>
            <p class="text-muted small mb-0">ปรับปรุงข้อมูลกิจกรรมล่าสุดสำหรับหน้าเว็บไซต์</p>
        </div>
        <button class="btn-brand" onclick="openAddModal()">
            <i class="fas fa-plus-circle me-1"></i> เพิ่มข่าวสารใหม่
        </button>
    </div>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table custom-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">ภาพหน้าปก</th>
                        <th>หัวข้อข่าวสาร</th>
                        <th>ระยะเวลาแสดงผล</th>
                        <th>สถานะ</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($news_items as $row): 
                        $is_expired = (!empty($row['end_date']) && strtotime($row['end_date']) < strtotime(date('Y-m-d')));
                    ?>
                    <tr>
                        <td class="ps-4">
                            <?php if($row['image']): ?>
                                <img src="../assets/uploads/news_img/<?php echo $row['image']; ?>" class="img-news-preview">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center rounded-3" style="width:100px; height:65px; border: 1px dashed #ddd;">
                                    <i class="fas fa-image text-muted opacity-50"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-bold text-dark"><?php echo $row['title_th']; ?></div>
                            <div class="text-muted small fw-light"><?php echo $row['title_en']; ?></div>
                        </td>
                        <td>
                            <div class="small text-dark"><i class="far fa-calendar-check text-success me-1"></i> <?php echo date('d/m/Y', strtotime($row['published_date'])); ?></div>
                            <div class="small text-muted"><i class="far fa-calendar-times text-danger me-1"></i> <?php echo $row['end_date'] ? date('d/m/Y', strtotime($row['end_date'])) : 'ถาวร'; ?></div>
                        </td>
                        <td>
                            <?php if($is_expired): ?>
                                <span class="badge-status status-expired">หมดอายุ</span>
                            <?php else: ?>
                                <span class="badge-status status-active">แสดงผล</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <button class="btn-action-edit me-1" onclick='editNews(<?php echo json_encode($row); ?>)'><i class="fas fa-edit"></i></button>
                            <button class="btn-action-delete" onclick="confirmDelete(<?php echo $row['id']; ?>)"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="newsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 30px; overflow: hidden;">
            <div class="modal-header border-0 bg-light p-4">
                <h5 class="modal-title fw-bold m-0" id="modalTitle">รายละเอียดข่าวสาร</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4 p-lg-5">
                    <input type="hidden" name="news_id" id="news_id">
                    <input type="hidden" name="old_image" id="old_image">
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">วันที่เริ่มประกาศ *</label>
                            <input type="date" name="published_date" id="p_date" class="form-control form-control-custom" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">วันที่สิ้นสุด (ถ้ามี)</label>
                            <input type="date" name="end_date" id="e_date" class="form-control form-control-custom">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">หัวข้อข่าวสาร (ภาษาไทย) *</label>
                            <input type="text" name="title_th" id="t_th" class="form-control form-control-custom" placeholder="ระบุหัวข้อภาษาไทย" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">หัวข้อข่าวสาร (English) *</label>
                            <input type="text" name="title_en" id="t_en" class="form-control form-control-custom" placeholder="English News Title" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-muted">เนื้อหาโดยย่อ (ภาษาไทย)</label>
                            <textarea name="content_th" id="c_th" class="form-control form-control-custom" rows="3"></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-muted">เนื้อหาโดยย่อ (English)</label>
                            <textarea name="content_en" id="c_en" class="form-control form-control-custom" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">รูปภาพปกข่าว</label>
                            <div class="p-3 bg-light rounded-4 border d-flex align-items-center">
                                <div id="img_preview_box" class="me-3 d-none">
                                    <img src="" id="img_view" class="rounded-3 border shadow-sm" style="width: 80px; height: 50px; object-fit: cover;">
                                </div>
                                <input type="file" name="image" class="form-control border-0 bg-transparent shadow-none" accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" name="save_news" class="btn-brand px-5">บันทึกข้อมูลข่าวสาร</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const newsModal = new bootstrap.Modal(document.getElementById('newsModal'));

function openAddModal() {
    document.querySelector('form').reset();
    document.getElementById('news_id').value = "";
    document.getElementById('modalTitle').innerText = "เพิ่มข่าวสารใหม่";
    document.getElementById('img_preview_box').classList.add('d-none');
    document.getElementById('p_date').value = "<?php echo date('Y-m-d'); ?>";
    newsModal.show();
}

function editNews(data) {
    document.getElementById('modalTitle').innerText = "แก้ไขข้อมูลข่าวสาร";
    document.getElementById('news_id').value = data.id;
    document.getElementById('p_date').value = data.published_date;
    document.getElementById('e_date').value = data.end_date;
    document.getElementById('t_th').value = data.title_th;
    document.getElementById('t_en').value = data.title_en;
    document.getElementById('c_th').value = data.content_th;
    document.getElementById('c_en').value = data.content_en;
    document.getElementById('old_image').value = data.image;

    if(data.image) {
        document.getElementById('img_preview_box').classList.remove('d-none');
        document.getElementById('img_view').src = "../assets/uploads/news_img/" + data.image;
    } else {
        document.getElementById('img_preview_box').classList.add('d-none');
    }
    newsModal.show();
}

function confirmDelete(id) {
    Swal.fire({
        title: 'ยืนยันการลบข่าวสาร?',
        text: "ข้อมูลและรูปภาพจะถูกลบออกอย่างถาวร!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#E31E24',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก',
        customClass: { popup: 'rounded-4' }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `manage_news.php?delete_id=${id}`;
        }
    })
}

// แจ้งเตือนสถานะ
<?php if(isset($_GET['status'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'ดำเนินการสำเร็จ',
        showConfirmButton: false,
        timer: 1500,
        customClass: { popup: 'rounded-4' }
    });
    window.history.replaceState({}, document.title, window.location.pathname);
<?php endif; ?>
</script>

</body>
</html>