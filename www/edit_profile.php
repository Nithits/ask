<?php
session_start();
require_once 'config/db.php';

// เช็คว่าล็อกอินหรือยัง ถ้ายังให้เด้งไปหน้า login
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- ส่วนจัดการระบบ 2 ภาษา ---
if (isset($_GET['lang']) && in_array($_GET['lang'], ['th', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'th';

$t = [
    'th' => [
        'title' => 'แก้ไขโปรไฟล์',
        'lbl_fullname' => 'ชื่อ-นามสกุล (Full Name)',
        'lbl_company' => 'ชื่อบริษัท/องค์กร (Company)',
        'lbl_email' => 'อีเมล (ไม่สามารถเปลี่ยนได้)',
        'lbl_phone' => 'เบอร์โทรศัพท์ (Phone)',
        'lbl_new_pass' => 'รหัสผ่านใหม่ (เว้นว่างไว้ถ้าไม่ต้องการเปลี่ยน)',
        'btn_save' => 'บันทึกข้อมูล',
        'success_title' => 'สำเร็จ!',
        'success_msg' => 'อัปเดตข้อมูลโปรไฟล์เรียบร้อยแล้ว'
    ],
    'en' => [
        'title' => 'Edit Profile',
        'lbl_fullname' => 'Full Name',
        'lbl_company' => 'Company Name',
        'lbl_email' => 'Email (Cannot be changed)',
        'lbl_phone' => 'Phone Number',
        'lbl_new_pass' => 'New Password (Leave blank to keep current)',
        'btn_save' => 'Save Changes',
        'success_title' => 'Success!',
        'success_msg' => 'Profile updated successfully.'
    ]
];
// ----------------------------

// เมื่อมีการกดปุ่มบันทึกข้อมูล (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = htmlspecialchars(trim($_POST['fullname']));
    $company_name = htmlspecialchars(trim($_POST['company_name']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $new_password = $_POST['new_password'];

    // ถ้ามีการกรอกรหัสผ่านใหม่
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET fullname = ?, company_name = ?, phone = ?, password = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$fullname, $company_name, $phone, $hashed_password, $user_id]);
    } else {
        // ถ้าไม่เปลี่ยนรหัสผ่าน อัปเดตแค่ข้อมูลทั่วไป
        $sql = "UPDATE users SET fullname = ?, company_name = ?, phone = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$fullname, $company_name, $phone, $user_id]);
    }

    // อัปเดตข้อมูลใน Session ใหม่ด้วย จะได้แสดงผลหน้าเว็บถูกต้อง
    $_SESSION['user_fullname'] = $fullname;
    $_SESSION['user_company'] = $company_name;

    // แจ้งเตือนสำเร็จ
    $_SESSION['sweet_alert'] = [
        'icon' => 'success',
        'title' => $t[$lang]['success_title'],
        'text' => $t[$lang]['success_msg']
    ];
    
    header("Location: edit_profile.php");
    exit();
}

// ดึงข้อมูลปัจจุบันของ User มาแสดงในฟอร์ม
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

include 'includes/header.php';
?>

<style>
    .profile-wrapper {
        min-height: 80vh;
        background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
        padding: 40px 15px;
    }
    .profile-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        padding: 40px 30px;
        position: relative;
    }
    .lang-switcher {
        position: absolute;
        top: 20px;
        right: 25px;
        font-size: 0.85rem;
    }
    .user-avatar {
        width: 80px;
        height: 80px;
        background-color: #f8f9fa;
        color: #ffc107;
        font-size: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin: 0 auto 20px;
        border: 3px solid #ffe69c;
    }
</style>

<div class="profile-wrapper">
    <div class="container d-flex justify-content-center">
        <div class="profile-card col-md-8 col-lg-6">
            
            <div class="lang-switcher">
                <a href="?lang=th" class="text-decoration-none <?php echo $lang == 'th' ? 'fw-bold text-dark' : 'text-muted'; ?>">TH</a> | 
                <a href="?lang=en" class="text-decoration-none <?php echo $lang == 'en' ? 'fw-bold text-dark' : 'text-muted'; ?>">EN</a>
            </div>

            <div class="text-center mb-4">
                <div class="user-avatar">
                    <i class="fas fa-user-edit"></i>
                </div>
                <h4 class="fw-bold text-dark"><?php echo $t[$lang]['title']; ?></h4>
            </div>

            <form action="edit_profile.php" method="POST">
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted"><?php echo $t[$lang]['lbl_email']; ?></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control bg-light border-start-0 text-muted" value="<?php echo htmlspecialchars($user['email']); ?>" readonly disabled>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted"><?php echo $t[$lang]['lbl_fullname']; ?> <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                        <input type="text" name="fullname" class="form-control border-start-0" required value="<?php echo htmlspecialchars($user['fullname']); ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted"><?php echo $t[$lang]['lbl_company']; ?></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-building text-muted"></i></span>
                        <input type="text" name="company_name" class="form-control border-start-0" value="<?php echo htmlspecialchars($user['company_name'] ?? ''); ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted"><?php echo $t[$lang]['lbl_phone']; ?></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-phone text-muted"></i></span>
                        <input type="text" name="phone" class="form-control border-start-0" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted"><?php echo $t[$lang]['lbl_new_pass']; ?></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-key text-muted"></i></span>
                        <input type="password" name="new_password" class="form-control border-start-0" placeholder="********">
                    </div>
                </div>

                <hr class="mb-4">

                <button type="submit" class="btn btn-warning w-100 fw-bold rounded-pill py-2 shadow-sm text-dark">
                    <i class="fas fa-save me-2"></i> <?php echo $t[$lang]['btn_save']; ?>
                </button>
                
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>