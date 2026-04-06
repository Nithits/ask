<?php
session_start();
require_once 'config/db.php';

// --- ส่วนจัดการระบบ 2 ภาษา ---
if (isset($_GET['lang']) && in_array($_GET['lang'], ['th', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'th';

// พจนานุกรมคำแปล (Dictionary)
$t = [
    'th' => [
        'success_title' => 'เข้าสู่ระบบสำเร็จ!',
        'welcome_msg' => 'ยินดีต้อนรับคุณ ',
        'err_login' => 'อีเมล หรือ รหัสผ่านไม่ถูกต้องครับ',
        'login_header' => 'เข้าสู่ระบบ',
        'lbl_email' => 'อีเมล (Email)',
        'lbl_pass' => 'รหัสผ่าน (Password)',
        'btn_login' => 'เข้าสู่ระบบ',
        'no_account' => 'ยังไม่มีบัญชี?',
        'btn_register' => 'สมัครสมาชิกใหม่'
    ],
    'en' => [
        'success_title' => 'Login Successful!',
        'welcome_msg' => 'Welcome, ',
        'err_login' => 'Invalid email or password.',
        'login_header' => 'Login',
        'lbl_email' => 'Email',
        'lbl_pass' => 'Password',
        'btn_login' => 'Login',
        'no_account' => "Don't have an account?",
        'btn_register' => 'Register here'
    ]
];
// ----------------------------

// ถ้าล็อกอินอยู่แล้ว ให้เด้งไปหน้าแรกเลย
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_fullname'] = $user['fullname'];
        $_SESSION['user_company'] = $user['company_name'];
        
        // 📍 เพิ่มบรรทัดนี้: เก็บอีเมลลง Session เพื่อใช้ในหน้าติดตามสถานะ
        $_SESSION['user_email'] = $user['email']; 
        
        // เซ็ตแจ้งเตือนว่าล็อกอินสำเร็จ (รองรับ 2 ภาษา)
        $_SESSION['sweet_alert'] = [
            'icon' => 'success',
            'title' => $t[$lang]['success_title'],
            'text' => $t[$lang]['welcome_msg'] . $user['fullname']
        ];
        header("Location: index.php");
        exit();
    } else {
        $error = $t[$lang]['err_login'];
    }
}

include 'includes/header.php';
?>

<style>
    .login-wrapper {
        min-height: 80vh;
        background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 15px;
    }
    .login-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        max-width: 450px; 
        width: 100%;
        padding: 40px 30px;
        position: relative; 
    }
    .lang-switcher {
        position: absolute;
        top: 20px;
        right: 25px;
        font-size: 0.85rem;
    }
</style>

<div class="login-wrapper">
    <div class="container d-flex justify-content-center">
        <div class="login-card">
            
            <div class="lang-switcher">
                <a href="?lang=th" class="text-decoration-none <?php echo $lang == 'th' ? 'fw-bold text-dark' : 'text-muted'; ?>">TH</a> | 
                <a href="?lang=en" class="text-decoration-none <?php echo $lang == 'en' ? 'fw-bold text-dark' : 'text-muted'; ?>">EN</a>
            </div>

            <div class="text-center mb-4">
                <img src="assets/images/favicon.png" alt="Logo" width="100" class="mb-3">
                <h4 class="fw-bold text-dark"><?php echo $t[$lang]['login_header']; ?></h4>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-danger rounded-3 text-center small"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <?php if(isset($_SESSION['error_msg'])): ?>
                <div class="alert alert-warning rounded-3 text-center small fw-bold"><i class="fas fa-lock"></i> <?php echo $_SESSION['error_msg']; ?></div>
                <?php unset($_SESSION['error_msg']); ?>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted"><?php echo $t[$lang]['lbl_email']; ?></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" name="email" class="form-control bg-light border-start-0" required placeholder="example@company.com">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted"><?php echo $t[$lang]['lbl_pass']; ?></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" name="password" class="form-control bg-light border-start-0" required placeholder="********">
                    </div>
                </div>
                <button type="submit" class="btn btn-warning w-100 fw-bold rounded-pill py-2 mb-3 shadow-sm text-dark"><?php echo $t[$lang]['btn_login']; ?></button>
                <div class="text-center small text-muted">
                    <?php echo $t[$lang]['no_account']; ?> <a href="register.php" class="text-decoration-none fw-bold text-primary"><?php echo $t[$lang]['btn_register']; ?></a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>