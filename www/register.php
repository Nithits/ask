<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';
include_once 'includes/language_setup.php'; // โหลดตัวตั้งค่าภาษา

$curr_lang = $_SESSION['lang'] ?? 'th'; // ดึงภาษาปัจจุบัน

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = htmlspecialchars(trim($_POST['fullname']));
    $company_name = htmlspecialchars(trim($_POST['company_name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // เช็คว่ารหัสผ่านตรงกันไหม
    if ($password !== $confirm_password) {
        $error = ($curr_lang == 'th') ? "รหัสผ่านไม่ตรงกัน กรุณาลองใหม่ครับ" : "Passwords do not match. Please try again.";
    } else {
        // เช็คว่าอีเมลนี้มีคนใช้สมัครไปหรือยัง
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = ($curr_lang == 'th') ? "อีเมลนี้มีในระบบแล้ว กรุณาใช้อีเมลอื่นครับ" : "This email is already registered. Please use another one.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // บันทึกลงฐานข้อมูล
            $insert = $pdo->prepare("INSERT INTO users (fullname, company_name, email, phone, password) VALUES (?, ?, ?, ?, ?)");
            
            if ($insert->execute([$fullname, $company_name, $email, $phone, $hashed_password])) {
                
                // แจ้งเตือนแอดมิน
                $notif_title = "New Member Registration!";
                $notif_msg = "User: " . $fullname . " has registered via the website."; 
                
                $stmt_alert = $pdo->prepare("INSERT INTO admin_notifications (title, message) VALUES (?, ?)");
                $stmt_alert->execute([$notif_title, $notif_msg]);

                $success = ($curr_lang == 'th') ? "สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ" : "Registration successful! Please login.";
            } else {
                $error = ($curr_lang == 'th') ? "เกิดข้อผิดพลาดในการสมัครสมาชิก" : "An error occurred during registration.";
            }
        }
    }
}

include 'includes/header.php'; 
?>

<div class="container my-5" style="max-width: 500px;">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-5">
            <h3 class="text-center fw-bold mb-4">
                <?php echo ($curr_lang == 'th') ? 'สมัครสมาชิก' : 'Sign Up'; ?>
            </h3>
            
            <?php if($error): ?>
                <div class="alert alert-danger rounded-3"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert alert-success rounded-3">
                    <?php echo $success; ?> 
                    <a href="login.php"><?php echo ($curr_lang == 'th') ? 'คลิกที่นี่เพื่อเข้าสู่ระบบ' : 'Click here to login'; ?></a>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold"><?php echo ($curr_lang == 'th') ? 'ชื่อ-นามสกุล' : 'Full Name'; ?> *</label>
                    <input type="text" name="fullname" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold"><?php echo ($curr_lang == 'th') ? 'ชื่อบริษัท / องค์กร' : 'Company Name'; ?></label>
                    <input type="text" name="company_name" class="form-control" placeholder="<?php echo ($curr_lang == 'th') ? 'ชื่อบริษัทของคุณ' : 'Your company name'; ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold"><?php echo ($curr_lang == 'th') ? 'อีเมล (ใช้สำหรับล็อกอิน)' : 'Email (for Login)'; ?> *</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold"><?php echo ($curr_lang == 'th') ? 'เบอร์โทรศัพท์' : 'Phone Number'; ?> *</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold"><?php echo ($curr_lang == 'th') ? 'รหัสผ่าน' : 'Password'; ?> *</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold"><?php echo ($curr_lang == 'th') ? 'ยืนยันรหัสผ่าน' : 'Confirm Password'; ?> *</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-warning w-100 fw-bold rounded-pill shadow-sm">
                    <?php echo ($curr_lang == 'th') ? 'สมัครสมาชิก' : 'Register Now'; ?>
                </button>
                <div class="text-center mt-3 small">
                    <?php echo ($curr_lang == 'th') ? 'มีบัญชีอยู่แล้ว?' : 'Already have an account?'; ?> 
                    <a href="login.php" class="text-decoration-none fw-bold">
                        <?php echo ($curr_lang == 'th') ? 'เข้าสู่ระบบที่นี่' : 'Login here'; ?>
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>