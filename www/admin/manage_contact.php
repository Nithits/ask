<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

// 1. ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// 2. ดึงข้อมูลข้อความติดต่อ
try {
    $stmt = $pdo->prepare("SELECT * FROM contact_messages ORDER BY created_at DESC");
    $stmt->execute();
    $messages = $stmt->fetchAll();
} catch (PDOException $e) {
    $messages = [];
}

// 3. เรียก Header (Sidebar และ Navbar จะถูกดึงมาแสดงที่นี่)
include 'includes/admin_header.php'; 
?>

<div class="admin-wrapper-inner">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold m-0" style="font-size: 1.6rem;">
            <i class="fas fa-envelope-open-text me-2 text-danger"></i> จัดการข้อความติดต่อ
        </h2>
        <div class="badge bg-dark rounded-pill px-3 py-2 shadow-sm">
            ข้อความทั้งหมด: <?php echo count($messages); ?> รายการ
        </div>
    </div>

    <div class="content-card shadow-sm">
        <div class="table-responsive">
            <table class="table custom-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 15%;">วัน-เวลา</th>
                        <th style="width: 20%;">ผู้ติดต่อ</th>
                        <th style="width: 15%;">หัวข้อ</th>
                        <th style="width: 35%;">ข้อความ</th>
                        <th style="width: 15%; text-align: center;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($messages) > 0): ?>
                        <?php foreach ($messages as $msg): ?>
                        <tr>
                            <?php 
                                // สร้างตัวแปรบวกเวลาไทย 7 ชั่วโมง
                                $thai_time_contact = strtotime($msg['created_at'] . ' +7 hours'); 
                            ?>
                            <td>
                                <div class="text-muted small">
                                    <i class="far fa-clock me-1"></i> <?php echo date('d/m/Y H:i', $thai_time_contact); ?>
                                </div>
                            </td>
                            <td>
                                <div class="user-info">
                                    <span class="name"><?php echo htmlspecialchars($msg['name']); ?></span>
                                    <span class="email"><?php echo htmlspecialchars($msg['email']); ?></span>
                                </div>
                            </td>
                            <td><span class="subject-tag"><?php echo htmlspecialchars($msg['subject'] ?: 'ทั่วไป'); ?></span></td>
                            <td><div class="msg-preview"><?php echo htmlspecialchars($msg['message']); ?></div></td>
                            <td class="text-center">
                                <button class="btn-view me-1 shadow-sm" 
                                        onclick="viewMessageDetail(this)"
                                        data-name="<?php echo htmlspecialchars($msg['name']); ?>"
                                        data-email="<?php echo htmlspecialchars($msg['email']); ?>"
                                        data-tel="<?php echo htmlspecialchars($msg['tel'] ?: '-'); ?>"
                                        data-subject="<?php echo htmlspecialchars($msg['subject'] ?: 'สอบถามทั่วไป'); ?>"
                                        data-message="<?php echo htmlspecialchars($msg['message']); ?>"
                                        data-date="<?php echo date('d/m/Y H:i', $thai_time_contact); ?>"
                                        title="เปิดอ่าน">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-delete" onclick="deleteMessage(<?php echo $msg['id']; ?>)" title="ลบข้อมูล"><i class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">ไม่พบข้อมูลข้อความติดต่อ</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 bg-light p-4">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-comment-dots me-2 text-danger"></i>รายละเอียดข้อความ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 p-lg-5">
                <div class="row g-4">
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase fw-bold">ผู้ส่งข้อความ</small>
                        <p id="view-name" class="h5 fw-bold mt-1 mb-0 text-dark"></p>
                        <p id="view-contact" class="text-danger small"></p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small class="text-muted text-uppercase fw-bold">หัวข้อติดต่อ</small>
                        <p id="view-subject" class="h5 fw-bold mt-1 text-dark"></p>
                    </div>
                </div>
                <hr class="my-4 opacity-5">
                <div id="view-message" class="p-4 rounded-4 bg-light" style="white-space: pre-wrap; line-height: 1.7; color: #444;"></div>
            </div>
            <div class="modal-footer border-0 p-4 justify-content-center">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
                <a id="reply-gmail" href="#" target="_blank" class="btn btn-danger rounded-pill px-4 shadow-sm">
                    ตอบกลับผ่าน Gmail <i class="fab fa-google ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    /**
     * ฟังก์ชันสำหรับเปิดดูรายละเอียดข้อความ
     */
    function viewMessageDetail(btn) {
        try {
            const data = {
                name: btn.getAttribute('data-name'),
                email: btn.getAttribute('data-email'),
                tel: btn.getAttribute('data-tel'),
                subject: btn.getAttribute('data-subject'),
                message: btn.getAttribute('data-message'),
                date: btn.getAttribute('data-date')
            };

            document.getElementById('view-name').innerText = data.name;
            document.getElementById('view-contact').innerText = `Email: ${data.email} | Tel: ${data.tel}`;
            document.getElementById('view-subject').innerText = data.subject;
            document.getElementById('view-message').innerText = data.message;

            const emailTo = data.email;
            const subjectRe = encodeURIComponent(`Re: ${data.subject}`);
            const bodyContent = encodeURIComponent(
                `เรียนคุณ ${data.name},\n\n` +
                `อ้างถึงข้อความที่คุณติดต่อเรามาเมื่อวันที่ ${data.date} ในหัวข้อ "${data.subject}"\n` +
                `รายละเอียด: "${data.message}"\n` +
                `--------------------------------------------------\n\n` +
                `(เขียนข้อความตอบกลับของคุณที่นี่)\n\n` +
                `ขอแสดงความนับถือ,\n` +
                `ทีมงาน ASK Calibration`
            );
            
            const gmailBtn = document.getElementById('reply-gmail');
            if (gmailBtn) {
                gmailBtn.href = `https://mail.google.com/mail/?view=cm&fs=1&to=${emailTo}&su=${subjectRe}&body=${bodyContent}`;
            }

            var myModal = new bootstrap.Modal(document.getElementById('messageModal'));
            myModal.show();

        } catch (error) {
            console.error("Error opening modal:", error);
            Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถเปิดดูรายละเอียดได้ กรุณาลองใหม่อีกครั้ง', 'error');
        }
    }

    /**
     * ฟังก์ชันสำหรับยืนยันการลบข้อความ
     */
    function deleteMessage(id) {
        Swal.fire({
            title: 'ยืนยันการลบข้อมูล?',
            text: "เมื่อลบแล้วจะไม่สามารถกู้คืนข้อมูลได้อีก!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#E31E24',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ยืนยันการลบ',
            cancelButtonText: 'ยกเลิก',
            customClass: {
                popup: 'rounded-4'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `delete_contact.php?id=${id}`;
            }
        });
    }

    /**
     * แจ้งเตือนสถานะเมื่อทำงานสำเร็จหรือผิดพลาด
     */
    <?php if(isset($_SESSION['status'])): ?>
    Swal.fire({
        icon: '<?= $_SESSION['status']; ?>',
        title: '<?= $_SESSION['message']; ?>',
        showConfirmButton: false,
        timer: 2000,
        customClass: { popup: 'rounded-4' }
    });
    <?php unset($_SESSION['status']); unset($_SESSION['message']); endif; ?>
</script>

</div> </div> </div> </div>
</body>
</html>