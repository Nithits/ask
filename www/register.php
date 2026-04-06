<?php
session_start();
require_once 'config/db.php';

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
        $error = "รหัสผ่านไม่ตรงกัน กรุณาลองใหม่ครับ";
    } else {
        // เช็คว่าอีเมลนี้มีคนใช้สมัครไปหรือยัง
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "อีเมลนี้มีในระบบแล้ว กรุณาใช้อีเมลอื่นครับ";
        } else {
            // เข้ารหัสผ่านก่อนบันทึก (เพื่อความปลอดภัย)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // บันทึกลงฐานข้อมูล
            $insert = $pdo->prepare("INSERT INTO users (fullname, company_name, email, phone, password) VALUES (?, ?, ?, ?, ?)");
            
            if ($insert->execute([$fullname, $company_name, $email, $phone, $hashed_password])) {
                
                // ==========================================
                // 📍 โค้ดสร้างแจ้งเตือนแอดมิน (ทำงานเมื่อสมัครสมาชิกสำเร็จ)
                // ==========================================
                $notif_title = "ลูกค้ารายใหม่ สมัครสมาชิก!";
                $notif_msg = "คุณ " . $fullname . " ได้ทำการสมัครสมาชิกเข้ามาในระบบครับ"; 
                
                $stmt_alert = $pdo->prepare("INSERT INTO admin_notifications (title, message) VALUES (?, ?)");
                $stmt_alert->execute([$notif_title, $notif_msg]);
                // ==========================================

                $success = "สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ";
            } else {
                $error = "เกิดข้อผิดพลาดในการสมัครสมาชิก ติดต่อแอดมินครับ";
            }
        }
    }
}

include 'includes/header.php'; // เรียกใช้ header ของเว็บนาย
?>

<div class="container my-5" style="max-width: 500px;">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-5">
            <h3 class="text-center fw-bold mb-4">สมัครสมาชิก</h3>
            
            <?php if($error): ?>
                <div class="alert alert-danger rounded-3"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert alert-success rounded-3"><?php echo $success; ?> <a href="login.php">คลิกที่นี่เพื่อเข้าสู่ระบบ</a></div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold">ชื่อ-นามสกุล *</label>
                    <input type="text" name="fullname" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">ชื่อบริษัท / องค์กร</label>
                    <input type="text" name="company_name" class="form-control" placeholder="ถ้าไม่มีให้ใส่ชื่อนามสกุลซ้ำได้ครับ" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">อีเมล (ใช้สำหรับล็อกอิน) *</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">เบอร์โทรศัพท์ *</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">รหัสผ่าน *</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold">ยืนยันรหัสผ่าน *</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-warning w-100 fw-bold rounded-pill">สมัครสมาชิก</button>
                <div class="text-center mt-3 small">
                    มีบัญชีอยู่แล้ว? <a href="login.php" class="text-decoration-none">เข้าสู่ระบบที่นี่</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>