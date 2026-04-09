<?php
session_start();
require_once '../config/db.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        $stmt = $pdo->prepare("SELECT id, username, password FROM admins WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error_msg = "ชื่อผู้ใช้งาน หรือ รหัสผ่าน ไม่ถูกต้อง!";
        }
    } catch (PDOException $e) {
        $error_msg = "เกิดข้อผิดพลาดในการเชื่อมต่อระบบ";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - ASK Calibration</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <link rel="stylesheet" href="../assets/css/login_style.css">
</head>
<body>

    <div class="login-card p-4 p-md-5">
        <div class="text-center mb-4">
            <img src="../assets/images/favicon.png" alt="ASK Calibration Logo" class="brand-logo">
            <h4 class="fw-bold mt-2" style="color: #1e293b;">Admin Panel</h4>
            <p class="text-muted small">กรุณาลงชื่อเข้าสู่ระบบเพื่อจัดการข้อมูล</p>
        </div>

        <?php if($error_msg): ?>
            <div class="alert alert-danger border-0 small text-center error-shake mb-4" style="border-radius: 10px;">
                <i class="fas fa-exclamation-circle me-1"></i> <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">ชื่อผู้ใช้งาน</label>
                <div class="input-group">
                    <span class="input-group-text border-end-0"><i class="far fa-user"></i></span>
                    <input type="text" name="username" class="form-control border-start-0 ps-0" required placeholder="กรอกชื่อผู้ใช้งาน">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-bold text-muted">รหัสผ่าน</label>
                <div class="input-group">
                    <span class="input-group-text border-end-0"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control border-start-0 ps-0" required placeholder="กรอกรหัสผ่าน">
                </div>
            </div>

            <button type="submit" class="btn btn-login w-100 mb-3">
                เข้าสู่ระบบ <i class="fas fa-sign-in-alt ms-2"></i>
            </button>
        </form>

        <div class="text-center mt-2">
            <a href="../index.php" class="text-secondary text-decoration-none small hover-link">
                <i class="fas fa-arrow-left me-1 small"></i> กลับสู่หน้าเว็บไซต์หลัก
            </a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
</body>
</html>