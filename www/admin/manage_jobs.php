<?php 
require_once '../config/db.php';
ob_start();
include 'includes/admin_header.php';

// --- ส่วน PHP จัดการข้อมูล (คงเดิมเพื่อความทำงานที่ถูกต้อง) ---
if (isset($_POST['save_job'])) {
    $title = $_POST['job_title']; $salary = $_POST['salary']; 
    $desc = $_POST['job_description']; $req = $_POST['job_requirement'];
    $benefit = $_POST['job_benefits']; $job_id = $_POST['job_id'];
    if (!empty($job_id)) {
        $sql = "UPDATE jobs SET job_title=?, salary=?, job_description=?, job_requirement=?, job_benefits=? WHERE id=?";
        $pdo->prepare($sql)->execute([$title, $salary, $desc, $req, $benefit, $job_id]);
        $status = "updated";
    } else {
        $sql = "INSERT INTO jobs (job_title, salary, job_description, job_requirement, job_benefits, status) VALUES (?, ?, ?, ?, ?, 'open')";
        $pdo->prepare($sql)->execute([$title, $salary, $desc, $req, $benefit]);
        $status = "added";
    }
    header("Location: manage_jobs.php?status=$status"); exit();
}

if (isset($_GET['del_job'])) { 
    $pdo->prepare("DELETE FROM jobs WHERE id = ?")->execute([$_GET['del_job']]); 
    header("Location: manage_jobs.php?status=deleted"); 
    exit(); 
}

if (isset($_GET['del_app'])) { 
    $pdo->prepare("DELETE FROM job_applications WHERE id = ?")->execute([$_GET['del_app']]); 
    header("Location: manage_jobs.php?status=app_deleted"); 
    exit(); 
}

$jobs = $pdo->query("SELECT * FROM jobs ORDER BY id DESC")->fetchAll();
$apps = $pdo->query("SELECT * FROM job_applications ORDER BY id DESC")->fetchAll();
?>

<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<div class="container-fluid py-5 px-lg-5">
    
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background: #ecfdf5; color: #10b981;"><i class="fas fa-briefcase"></i></div>
                <div><h3 class="fw-bold mb-0"><?= count($jobs) ?></h3><p class="text-muted mb-0">ตำแหน่งที่เปิดรับ</p></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background: #fff7ed; color: #f59e0b;"><i class="fas fa-user-tie"></i></div>
                <div><h3 class="fw-bold mb-0"><?= count($apps) ?></h3><p class="text-muted mb-0">ใบสมัครทั้งหมด</p></div>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn-add-job mt-2" onclick="openAddModal()">
                <i class="fas fa-plus-circle me-2"></i> เพิ่มตำแหน่งงานใหม่
            </button>
        </div>
    </div>

    <div class="glass-card mb-5">
        <div class="card-header-custom">
            <h5 class="fw-bold m-0"><i class="fas fa-layer-group text-danger me-2"></i>จัดการตำแหน่งงาน</h5>
        </div>
        <div class="table-responsive">
            <table class="table custom-table">
                <thead>
                    <tr>
                        <th class="ps-4">ตำแหน่งงาน</th>
                        <th>เงินเดือน</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($jobs as $j): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark"><?= htmlspecialchars($j['job_title']) ?></div>
                            <div class="small text-muted">Status: <span class="text-success">Open</span></div>
                        </td>
                        <td><span class="badge-soft-blue"><?= htmlspecialchars($j['salary']) ?: 'ตามตกลง' ?></span></td>
                        <td class="text-center">
                            <button class="btn-action-view me-2" 
                                data-id="<?= $j['id'] ?>" data-title="<?= htmlspecialchars($j['job_title']) ?>"
                                data-salary="<?= htmlspecialchars($j['salary']) ?>" data-desc="<?= htmlspecialchars($j['job_description']) ?>"
                                data-req="<?= htmlspecialchars($j['job_requirement']) ?>" data-ben="<?= htmlspecialchars($j['job_benefits']) ?>"
                                onclick="openEditModal(this)" title="แก้ไขข้อมูล"><i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-action-delete" onclick="confirmDelete('?del_job=<?= $j['id'] ?>')" title="ลบข้อมูล"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="glass-card">
        <div class="card-header-custom">
            <h5 class="fw-bold m-0"><i class="fas fa-id-card text-primary me-2"></i>รายชื่อผู้สมัครงาน</h5>
        </div>
        <div class="table-responsive">
            <table class="table custom-table">
                <thead>
                    <tr>
                        <th class="ps-4">ผู้สมัคร</th>
                        <th>ตำแหน่งที่สมัคร</th>
                        <th>ข้อมูลติดต่อ</th>
                        <th class="text-center">Resume</th>
                        <th class="text-center">ลบ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($apps as $app): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold"><?= htmlspecialchars($app['fullname']) ?></div>
                            <div class="small text-muted">วันที่สมัคร: <?= date('d M Y', strtotime($app['applied_at'])) ?></div>
                        </td>
                        <td><span class="badge-soft-red"><?= htmlspecialchars($app['position']) ?></span></td>
                        <td>
                            <div class="small fw-bold"><?= htmlspecialchars($app['phone']) ?></div>
                            <div class="small text-muted"><?= htmlspecialchars($app['email']) ?></div>
                        </td>
                        <td class="text-center">
                            <a href="../uploads/resumes/<?= htmlspecialchars($app['resume_file']) ?>" target="_blank" class="btn btn-sm btn-dark rounded-pill px-3">View PDF</a>
                        </td>
                        <td class="text-center">
                            <button class="btn-action-delete" onclick="confirmDelete('?del_app=<?= $app['id'] ?>')" title="ลบข้อมูล"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="jobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 30px; overflow: hidden;">
            <div class="modal-header border-0 bg-light p-4">
                <h5 class="fw-bold m-0" id="modalTitle">ตำแหน่งงาน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="jobForm">
                <input type="hidden" name="job_id" id="job_id">
                <div class="modal-body p-4 p-lg-5">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ชื่อตำแหน่งงาน *</label>
                            <input type="text" name="job_title" id="job_title" class="form-control" style="border-radius: 12px;" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">เงินเดือน</label>
                            <input type="text" name="salary" id="salary" class="form-control" style="border-radius: 12px;">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">รายละเอียดงาน</label>
                            <textarea name="job_description" id="job_description" class="form-control" style="border-radius: 12px;" rows="4"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">คุณสมบัติ</label>
                            <textarea name="job_requirement" id="job_requirement" class="form-control" style="border-radius: 12px;" rows="4"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">สวัสดิการ</label>
                            <textarea name="job_benefits" id="job_benefits" class="form-control" style="border-radius: 12px;" rows="4"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">ปิด</button>
                    <button type="submit" name="save_job" class="btn-add-job" id="submitBtn">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    var myModal;
    document.addEventListener('DOMContentLoaded', function() {
        myModal = new bootstrap.Modal(document.getElementById('jobModal'));
    });

    function openAddModal() {
        document.getElementById('jobForm').reset();
        document.getElementById('job_id').value = '';
        document.getElementById('modalTitle').innerHTML = 'เพิ่มตำแหน่งงานใหม่';
        myModal.show();
    }

    function openEditModal(btn) {
        document.getElementById('job_id').value = btn.getAttribute('data-id');
        document.getElementById('job_title').value = btn.getAttribute('data-title');
        document.getElementById('salary').value = btn.getAttribute('data-salary');
        document.getElementById('job_description').value = btn.getAttribute('data-desc');
        document.getElementById('job_requirement').value = btn.getAttribute('data-req');
        document.getElementById('job_benefits').value = btn.getAttribute('data-ben');
        document.getElementById('modalTitle').innerHTML = 'แก้ไขตำแหน่งงาน';
        myModal.show();
    }

    function confirmDelete(url) {
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: "ข้อมูลนี้จะถูกลบอย่างถาวร!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#E31E24',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ลบข้อมูล',
            cancelButtonText: 'ยกเลิก',
            customClass: { popup: 'rounded-4' }
        }).then((result) => { if (result.isConfirmed) { window.location.href = url; } });
    }

    // Status Notify
    <?php if(isset($_GET['status'])): ?>
        Swal.fire({ icon: 'success', title: 'ดำเนินการสำเร็จ', showConfirmButton: false, timer: 1500, customClass: { popup: 'rounded-4' } });
        window.history.replaceState({}, document.title, window.location.pathname);
    <?php endif; ?>
</script>