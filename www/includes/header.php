<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. เรียกใช้งานระบบภาษา
include_once 'includes/language_setup.php';

// 2. จัดการรับค่าการเปลี่ยนภาษา (ถ้ามี)
if (isset($_GET['lang'])) {
    $requested_lang = $_GET['lang'];
    if (in_array($requested_lang, ['th', 'en'])) {
        $_SESSION['lang'] = $requested_lang;
    }
}

// 3. กำหนดตัวแปรภาษาปัจจุบันสำหรับใช้ใน HTML
$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'th';

// 4. ตัวแปรตรวจสอบหน้าปัจจุบัน
$current_page = basename($_SERVER['PHP_SELF']); 
$query_params = $_GET; 

// ฟังก์ชันสร้าง Link เปลี่ยนภาษา
function getLangUrl($lang_code) {
    global $current_page, $query_params;
    $new_params = $query_params;
    $new_params['lang'] = $lang_code; 
    return $current_page . '?' . http_build_query($new_params);
}

// ฟังก์ชันเช็คสถานะเมนู Active
function isMenu($page_name) {
    global $current_page;
    if ($page_name === 'news.php' && $current_page === 'news_detail.php') return 'active';
    if ($page_name === 'portfolio.php' && $current_page === 'portfolio_detail.php') return 'active';
    return ($current_page === $page_name) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASK Calibration & Service</title>
    
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        .navbar-nav .nav-link {
            white-space: nowrap !important; 
        }
    </style>
</head>
<body style="font-family: 'Prompt', sans-serif;">

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top py-3">
    <div class="container">
        
        <a class="navbar-brand" href="index.php">
            <img src="assets/images/favicon.png" alt="ASK Calibration Logo" style="height: 60px; object-fit: contain;">
        </a>
        
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="mainMenu">
            
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                <li class="nav-item"><a class="nav-link px-3 <?php echo isMenu('index.php'); ?>" href="index.php"><?php echo $lang == 'th' ? 'หน้าแรก' : 'Home'; ?></a></li>
                <li class="nav-item"><a class="nav-link px-3 <?php echo isMenu('about.php'); ?>" href="about.php"><?php echo $lang == 'th' ? 'เกี่ยวกับเรา' : 'About Us'; ?></a></li>
                <li class="nav-item"><a class="nav-link px-3 <?php echo isMenu('services.php'); ?>" href="services.php"><?php echo $lang == 'th' ? 'บริการ' : 'Services'; ?></a></li>
                <li class="nav-item"><a class="nav-link px-3 <?php echo isMenu('portfolio.php'); ?>" href="portfolio.php"><?php echo $lang == 'th' ? 'ผลงาน' : 'Portfolio'; ?></a></li>
                <li class="nav-item"><a class="nav-link px-3 <?php echo isMenu('news.php'); ?>" href="news.php"><?php echo $lang == 'th' ? 'ข่าวสาร' : 'News'; ?></a></li>
                <li class="nav-item"><a class="nav-link px-3 <?php echo isMenu('scope.php'); ?>" href="scope.php"><?php echo $lang == 'th' ? 'ขอบข่ายการให้บริการ' : 'Scope of services'; ?></a></li>
                <li class="nav-item"><a class="nav-link px-3 <?php echo isMenu('job.php'); ?>" href="job.php"><?php echo $lang == 'th' ? 'สมัครงาน' : 'Careers'; ?></a></li>
                <li class="nav-item"><a class="nav-link px-3 <?php echo isMenu('contact.php'); ?>" href="contact.php"><?php echo $lang == 'th' ? 'ติดต่อ' : 'Contact'; ?></a></li>
                <li class="nav-item"><a class="nav-link px-3 <?php echo isMenu('quote.php'); ?>" href="quote.php"><?php echo $lang == 'th' ? 'ขอใบเสนอราคา' : 'Request a quote'; ?></a></li>
            </ul>
            
            <div class="d-flex align-items-center mt-3 mt-lg-0 ms-lg-3 gap-3">
                <div class="btn-group shadow-sm" role="group">
                    <a href="<?php echo getLangUrl('th'); ?>" class="btn btn-outline-secondary btn-sm btn-lang <?php echo $lang == 'th' ? 'active' : ''; ?>">TH</a>
                    <a href="<?php echo getLangUrl('en'); ?>" class="btn btn-outline-secondary btn-sm btn-lang <?php echo $lang == 'en' ? 'active' : ''; ?>">EN</a>
                </div>
                
                <?php if(isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle rounded-pill fw-bold border shadow-sm px-3" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle text-primary me-1"></i> 
                            <span class="d-none d-sm-inline"><?php echo htmlspecialchars($_SESSION['user_fullname']); ?></span>
                            <span class="d-inline d-sm-none"><?php echo $lang == 'th' ? 'บัญชี' : 'Account'; ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow mt-2" aria-labelledby="userMenu">
                            
                            <li>
                                <a class="dropdown-item py-2 fw-medium" href="my_status.php">
                                    <i class="fas fa-tasks me-2 text-warning"></i> <?php echo $lang == 'th' ? 'ติดตามสถานะงาน' : 'Track Status'; ?>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 fw-medium" href="edit_profile.php">
                                    <i class="fas fa-user-edit me-2 text-info"></i> <?php echo $lang == 'th' ? 'แก้ไขข้อมูลส่วนตัว' : 'Edit Profile'; ?>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger fw-bold py-2" href="logout_user.php">
                                    <i class="fas fa-sign-out-alt me-2"></i> <?php echo $lang == 'th' ? 'ออกจากระบบ' : 'Logout'; ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-warning fw-bold rounded-pill shadow-sm px-4">
                        <i class="fas fa-sign-in-alt me-1"></i> <?php echo $lang == 'th' ? 'เข้าสู่ระบบ' : 'Login'; ?>
                    </a>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if(isset($_SESSION['sweet_alert'])): ?>
    <script>
        Swal.fire({
            icon: '<?php echo $_SESSION['sweet_alert']['icon']; ?>',
            title: '<?php echo $_SESSION['sweet_alert']['title']; ?>',
            text: '<?php echo $_SESSION['sweet_alert']['text']; ?>',
            timer: 2500,
            showConfirmButton: false,
            backdrop: `rgba(0,0,0,0.4)`
        });
    </script>
<?php 
    unset($_SESSION['sweet_alert']); 
endif; 
?>