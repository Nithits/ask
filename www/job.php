<?php 
// 1. เริ่มต้นระบบ Config และ Logic
include_once 'includes/language_setup.php'; 
include 'includes/header.php'; 
require_once 'config/db.php'; 

// ดึงภาษาปัจจุบัน
$curr_lang = $_SESSION['lang'] ?? 'th';

// 2. ดึงข้อมูลงานเฉพาะที่สถานะ open
try {
    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE status = 'open' ORDER BY id DESC");
    $stmt->execute();
    $jobs = $stmt->fetchAll();
} catch (PDOException $e) {
    $jobs = [];
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<section class="career-hero">
    <div class="container animate__animated animate__fadeIn">
        
        <h1 class="display-4 mb-2">
            <?php echo $curr_lang == 'th' ? 'ร่วมงานกับ <span>ASK Calibration</span>' : 'Career at <span>ASK Calibration</span>'; ?>
        </h1>
        
        <div class="title-underline-modern"></div>
        
        <p class="lead mt-3 mx-auto fs-5" style="max-width: 600px; font-weight: 500; line-height: 1.6;">
            <?php echo $curr_lang == 'th' 
                ? 'เรากำลังมองหาบุคลากรที่มีความมุ่งมั่นและพร้อมจะเติบโตไปด้วยกัน' 
                : 'We are looking for passionate individuals to grow with us.'; ?>
        </p>
    </div>
</section>

<div class="career-wrapper container pb-5">
    <div class="row g-4 justify-content-center">
        
        <div class="col-lg-4">
            <div class="job-sidebar">
                <?php if (!empty($jobs)): ?>
                    <?php foreach($jobs as $index => $job): ?>
                        <?php 
                            $j_title = ($curr_lang == 'th') ? $job['job_title'] : ($job['job_title_en'] ?? $job['job_title']);
                            $j_desc = ($curr_lang == 'th') ? $job['job_description'] : ($job['job_description_en'] ?? $job['job_description']);
                        ?>
                        <div class="nav-card shadow-sm <?php echo $index === 0 ? 'active' : ''; ?>" 
                             onclick="switchJob('job-<?= $job['id']; ?>', this)">
                            <small class="text-danger fw-bold text-uppercase">ASK Calibration</small>
                            <h5 class="fw-bold mt-1"><?= htmlspecialchars($j_title); ?></h5>
                            <p class="small text-muted mb-0"><?= mb_strimwidth(strip_tags($j_desc), 0, 80, "..."); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="job-detail-panel bg-white p-4 p-md-5 rounded-4 shadow-sm">
                <?php if(empty($jobs)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-4x text-muted opacity-25 mb-4"></i>
                        <h3 class="text-muted"><?php echo $curr_lang == 'th' ? 'ขออภัย ยังไม่มีตำแหน่งงานว่างในขณะนี้' : 'Sorry, no vacancies at the moment.'; ?></h3>
                        <a href="index" class="btn btn-outline-danger rounded-pill mt-3 px-4"><?php echo $curr_lang == 'th' ? 'กลับหน้าหลัก' : 'Back to Home'; ?></a>
                    </div>
                <?php endif; ?>

                <?php foreach($jobs as $index => $job): ?>
                    <?php 
                        $j_title = ($curr_lang == 'th') ? $job['job_title'] : ($job['job_title_en'] ?? $job['job_title']);
                        $j_desc = ($curr_lang == 'th') ? $job['job_description'] : ($job['job_description_en'] ?? $job['job_description']);
                        $j_req = ($curr_lang == 'th') ? $job['job_requirement'] : ($job['job_requirement_en'] ?? $job['job_requirement']);
                        $j_benefit = ($curr_lang == 'th') ? $job['job_benefits'] : ($job['job_benefits_en'] ?? $job['job_benefits']);
                    ?>
                    <div id="job-<?= $job['id']; ?>" class="job-pane animate__animated animate__fadeIn" style="<?= $index === 0 ? '' : 'display:none;'; ?>">
                        <h2 class="fw-bold mb-4"><?= htmlspecialchars($j_title); ?></h2>

                        <div class="salary-box d-flex align-items-center mb-5">
                            <div class="icon-box me-4">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div>
                                <small class="text-muted text-uppercase fw-bold"><?php echo $curr_lang == 'th' ? 'เงินเดือน / ค่าตอบแทน' : 'Salary / Remuneration'; ?></small>
                                <div class="fs-4 fw-bold text-danger"><?= $job['salary'] ?: ($curr_lang == 'th' ? 'ตามระเบียบบริษัท' : 'Negotiable'); ?></div>
                            </div>
                        </div>

                        <div class="mb-5">
                            <h5 class="fw-bold mb-3 text-dark"><i class="fas fa-briefcase text-danger me-2"></i> <?php echo $curr_lang == 'th' ? 'รายละเอียดงาน' : 'Job Description'; ?></h5>
                            <div class="text-muted ps-4" style="line-height: 1.8;"><?= nl2br(htmlspecialchars($j_desc)); ?></div>
                        </div>

                        <div class="mb-5">
                            <h5 class="fw-bold mb-3 text-dark"><i class="fas fa-user-graduate text-danger me-2"></i> <?php echo $curr_lang == 'th' ? 'คุณสมบัติผู้สมัคร' : 'Requirements'; ?></h5>
                            <div class="text-muted ps-4" style="line-height: 1.8;"><?= nl2br(htmlspecialchars($j_req)); ?></div>
                        </div>

                        <div class="mb-5">
                            <h5 class="fw-bold mb-3 text-dark"><i class="fas fa-star text-danger me-2"></i> <?php echo $curr_lang == 'th' ? 'สวัสดิการ' : 'Benefits'; ?></h5>
                            <div class="ps-4">
                                <?php 
                                    if(!empty($j_benefit)) {
                                        $arr = explode("\n", $j_benefit);
                                        foreach($arr as $b) {
                                            if(trim($b) != "") echo '<div class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>'.htmlspecialchars($b).'</div>';
                                        }
                                    } else { echo '<p class="text-muted">Attractive benefits package</p>'; }
                                ?>
                            </div>
                        </div>

                        <button type="button" class="btn btn-danger w-100 py-3 rounded-pill fw-bold shadow-lg mt-4" style="background:var(--brand-gradient); border:none;" onclick="toggleForm('form-<?= $job['id']; ?>')">
                            <?php echo $curr_lang == 'th' ? 'สนใจสมัครตำแหน่งนี้' : 'Apply for this position'; ?> <i class="fas fa-paper-plane ms-2"></i>
                        </button>

                        <div id="form-<?= $job['id']; ?>" class="mt-5 p-4 rounded-4 bg-light" style="display:none; border: 1px dashed #ddd;">
                            <h4 class="fw-bold mb-4 text-center"><?php echo $curr_lang == 'th' ? 'ส่งใบสมัคร' : 'Submit Application'; ?></h4>
                            <form action="action/submit_job.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="position" value="<?= htmlspecialchars($j_title); ?>">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="small fw-bold mb-1"><?php echo $curr_lang == 'th' ? 'ชื่อ-นามสกุล *' : 'Full Name *'; ?></label>
                                        <input type="text" name="fullname" class="form-control rounded-3" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small fw-bold mb-1"><?php echo $curr_lang == 'th' ? 'อีเมล *' : 'Email *'; ?></label>
                                        <input type="email" name="email" class="form-control rounded-3" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small fw-bold mb-1"><?php echo $curr_lang == 'th' ? 'เบอร์โทรศัพท์ *' : 'Phone *'; ?></label>
                                        <input type="tel" name="phone" class="form-control rounded-3" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="small fw-bold mb-1"><?php echo $curr_lang == 'th' ? 'แนบ Resume / CV (PDF เท่านั้น) *' : 'Upload Resume (PDF only) *'; ?></label>
                                        <input type="file" name="resume_file" class="form-control rounded-3" accept=".pdf" required>
                                    </div>
                                    <div class="col-12 mt-4">
                                        <button type="submit" class="btn btn-dark w-100 py-3 rounded-pill"><?php echo $curr_lang == 'th' ? 'ยืนยันการส่งใบสมัคร' : 'Confirm Application'; ?></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function switchJob(id, el) {
        document.querySelectorAll('.job-pane').forEach(p => p.style.display = 'none');
        document.getElementById(id).style.display = 'block';
        document.querySelectorAll('.nav-card').forEach(c => c.classList.remove('active'));
        el.classList.add('active');
        if (window.innerWidth < 992) {
            document.getElementById(id).scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function toggleForm(formId) {
        var f = document.getElementById(formId);
        f.style.display = (f.style.display === 'none' || f.style.display === '') ? 'block' : 'none';
        if(f.style.display === 'block') f.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
</script>
<?php if (isset($_SESSION['apply_status'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            icon: '<?= $_SESSION['apply_status'] ?>',
            title: '<?= $_SESSION['apply_status'] === 'success' ? "สำเร็จ!" : "เกิดข้อผิดพลาด!" ?>',
            text: '<?= $_SESSION['apply_message'] ?>',
            confirmButtonColor: '#E31E24',
            timer: 3000,
            timerProgressBar: true
        });
    </script>
    <?php 
        // ล้างค่า Session ทันทีเพื่อให้แจ้งเตือนไม่เด้งซ้ำเมื่อรีเฟรชหน้าจอ
        unset($_SESSION['apply_status']); 
        unset($_SESSION['apply_message']); 
    ?>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>