<?php 
require_once '../config/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['admin_logged_in'])) { header("Location: index.php"); exit(); }

$current_admin_username = $_SESSION['admin_username'] ?? '';

// --- 1. จัดการเพิ่ม/แก้ไข Admin ---
if (isset($_POST['save_admin'])) {
    $id = $_POST['admin_id'] ?? null;
    $username = trim(htmlspecialchars($_POST['username']));
    $password = $_POST['password'];

    // เช็คว่า Username ซ้ำไหม
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = ? AND id != ?");
    $stmt_check->execute([$username, $id ? $id : 0]);
    if ($stmt_check->fetchColumn() > 0) {
        header("Location: manage_admin.php?status=error_duplicate"); exit();
    }

    if ($id) {
        // อัปเดตข้อมูล (ถ้าระบุรหัสผ่านใหม่ ให้แก้รหัสผ่านด้วย ถ้าไม่ระบุให้แก้แค่ Username)
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE admins SET username=?, password=? WHERE id=?")->execute([$username, $hashed_password, $id]);
        } else {
            $pdo->prepare("UPDATE admins SET username=? WHERE id=?")->execute([$username, $id]);
        }
    } else {
        // เพิ่ม Admin ใหม่ (บังคับใส่รหัสผ่าน)
        if (empty($password)) { header("Location: manage_admin.php?status=error_password"); exit(); }
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)")->execute([$username, $hashed_password]);
    }
    
    // อัปเดต Session ถ้าเปลี่ยน Username ของตัวเอง
    if ($id && $_POST['old_username'] == $current_admin_username) {
        $_SESSION['admin_username'] = $username;
    }
    header("Location: manage_admin.php?status=success"); exit();
}

// --- 2. จัดการลบ Admin ---
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // ดึงชื่อ Admin ที่จะลบมาเช็คก่อน
    $stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
    $stmt->execute([$delete_id]);
    $admin_to_delete = $stmt->fetchColumn();

    if ($admin_to_delete === $current_admin_username) {
        // ป้องกันการลบตัวเองที่กำลังล็อกอินอยู่
        header("Location: manage_admin.php?status=error_self_delete"); exit();
    } else {
        $pdo->prepare("DELETE FROM admins WHERE id = ?")->execute([$delete_id]);
        header("Location: manage_admin.php?status=deleted"); exit();
    }
}

// --- 3. ดึงข้อมูลแสดงผล ---
$admins = $pdo->query("SELECT * FROM admins ORDER BY id ASC")->fetchAll();

include 'includes/admin_header.php'; 
?>

<div class="admin-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0"><i class="fas fa-users-cog text-danger me-2"></i> จัดการผู้ดูแลระบบ</h2>
            <p class="text-muted small mb-0">เพิ่ม ลบ หรือแก้ไขรหัสผ่านบัญชีแอดมินหลังบ้าน</p>
        </div>
        <button class="btn-brand" onclick="openAdminModal()"><i class="fas fa-user-plus me-1"></i> เพิ่มผู้ดูแลระบบ</button>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-custom mb-0 align-middle">
                <thead>
                    <tr>
                        <th style="width: 80px;" class="text-center">ID</th>
                        <th>Username (ชื่อผู้ใช้)</th>
                        <th>วันที่สร้างบัญชี</th>
                        <th class="text-center" style="width: 150px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($admins as $admin): ?>
                    <tr>
                        <td class="text-center text-muted">#<?php echo $admin['id']; ?></td>
                        <td>
                            <div class="fw-bold text-dark fs-6 d-flex align-items-center">
                                <i class="fas fa-user-shield text-warning me-2"></i> <?php echo htmlspecialchars($admin['username']); ?>
                                <?php if($admin['username'] == $current_admin_username): ?>
                                    <span class="badge bg-danger ms-2" style="font-size: 0.65rem;">You</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="text-muted small"><?php echo date('d/m/Y H:i', strtotime($admin['created_at'])); ?></td>
                        <td class="text-center">
                            <button class="btn-action-edit me-1" onclick='editAdmin(<?php echo json_encode($admin); ?>)' title="แก้ไข"><i class="fas fa-edit"></i></button>
                            <?php if($admin['username'] != $current_admin_username): ?>
                                <button class="btn-action-delete" onclick="confirmDelete(<?php echo $admin['id']; ?>)" title="ลบ"><i class="fas fa-trash"></i></button>
                            <?php else: ?>
                                <button class="btn-action-delete opacity-25" style="cursor: not-allowed;" title="ไม่สามารถลบตัวเองได้" disabled><i class="fas fa-trash"></i></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="adminModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-light p-4">
                <h5 class="modal-title fw-bold" id="adminModalTitle">บัญชีผู้ดูแลระบบ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="admin_id" id="admin_id">
                    <input type="hidden" name="old_username" id="old_username">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Username (ชื่อผู้ใช้) *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-user text-muted"></i></span>
                            <input type="text" name="username" id="admin_username" class="form-control border-start-0 ps-0" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Password (รหัสผ่าน)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-key text-muted"></i></span>
                            <input type="password" name="password" id="admin_password" class="form-control border-start-0 ps-0" placeholder="ตั้งรหัสผ่านใหม่...">
                        </div>
                        <div class="form-text text-danger" id="password_help">
                            * กรณีเพิ่มบัญชีใหม่: <strong>ต้องระบุรหัสผ่าน</strong><br>
                            * กรณีแก้ไขบัญชี: <strong>เว้นว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่าน</strong>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">ปิด</button>
                    <button type="submit" name="save_admin" class="btn-brand px-5">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const adminModal = new bootstrap.Modal(document.getElementById('adminModal'));

function openAdminModal() {
    document.querySelector('#adminModal form').reset();
    document.getElementById('admin_id').value = "";
    document.getElementById('old_username').value = "";
    document.getElementById('admin_password').required = true; // บังคับใส่พาสเวิร์ดตอนเพิ่มใหม่
    document.getElementById('adminModalTitle').innerText = "เพิ่มผู้ดูแลระบบใหม่";
    adminModal.show();
}

function editAdmin(data) {
    document.querySelector('#adminModal form').reset();
    document.getElementById('admin_id').value = data.id;
    document.getElementById('old_username').value = data.username;
    document.getElementById('admin_username').value = data.username;
    document.getElementById('admin_password').required = false; // แก้ไข ไม่บังคับเปลี่ยนพาส
    document.getElementById('adminModalTitle').innerText = "แก้ไขบัญชี: " + data.username;
    adminModal.show();
}

function confirmDelete(id) {
    Swal.fire({
        title: 'ยืนยันการลบแอดมิน?',
        text: "บัญชีนี้จะไม่สามารถเข้าสู่ระบบได้อีก!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#E31E24',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ลบเลย',
        cancelButtonText: 'ยกเลิก',
        customClass: { popup: 'rounded-4' }
    }).then((result) => { if (result.isConfirmed) { window.location.href = `manage_admin.php?delete_id=${id}`; } });
}

<?php if(isset($_GET['status'])): ?>
    let icon = 'success';
    let title = 'สำเร็จ';
    let text = 'บันทึกข้อมูลเรียบร้อยแล้ว';

    <?php if($_GET['status'] == 'error_duplicate'): ?>
        icon = 'error'; title = 'ผิดพลาด'; text = 'Username นี้มีผู้ใช้งานแล้ว!';
    <?php elseif($_GET['status'] == 'error_password'): ?>
        icon = 'error'; title = 'ผิดพลาด'; text = 'กรุณาระบุรหัสผ่านสำหรับการเพิ่มบัญชีใหม่';
    <?php elseif($_GET['status'] == 'error_self_delete'): ?>
        icon = 'error'; title = 'ไม่อนุญาต'; text = 'คุณไม่สามารถลบบัญชีที่คุณกำลังล็อกอินอยู่ได้!';
    <?php elseif($_GET['status'] == 'deleted'): ?>
        text = 'ลบบัญชีผู้ดูแลระบบเรียบร้อยแล้ว';
    <?php endif; ?>

    Swal.fire({ icon: icon, title: title, text: text, showConfirmButton: false, timer: 2500, customClass: { popup: 'rounded-4' } });
    window.history.replaceState({}, document.title, window.location.pathname);
<?php endif; ?>
</script>