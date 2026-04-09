<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ตรวจสอบ Login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - ASK Calibration</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="../assets/css/styleadmin.css">

    <style>
        /* สไตล์เพิ่มเติมเพื่อให้รองรับ Mobile */
        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed;
                left: -280px; /* ซ่อนไว้ทางซ้าย */
                top: 0;
                bottom: 0;
                width: 280px;
                z-index: 1050;
                transition: 0.3s;
                display: block !important; /* บังคับให้แสดงเมื่อมีคลาส mobile-show */
            }
            .sidebar.mobile-show {
                left: 0;
            }
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
            }
            /* พื้นหลังดำโปร่งแสงตอนเปิดเมนู */
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 1040;
            }
            .sidebar-overlay.show {
                display: block;
            }
        }
    </style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="container-fluid p-0">
    <div class="row g-0">
        <div class="sidebar" id="sidebarMenu">
            <div class="sidebar-brand d-flex justify-content-between align-items-center">
                <h5 class="m-0"><i class="fas fa-shield-alt me-2"></i> ASK ADMIN</h5>
                <button class="btn text-white d-lg-none" id="closeSidebar">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
            
            <div class="menu-label">Main Management</div>
            <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar me-2"></i> รายการใบเสนอราคา
            </a>
            <a href="manage_contact.php" class="<?php echo ($current_page == 'manage_contact.php') ? 'active' : ''; ?>">
                <i class="fas fa-envelope-open-text me-2"></i> ข้อความติดต่อ
            </a>
            <a href="manage_users.php" class="<?php echo ($current_page == 'manage_users.php') ? 'active' : ''; ?>">
             <i class="fas fa-users me-2"></i> จัดการข้อมูลลูกค้า </a>
             
            <div class="menu-label">Website Content</div>
            
            <a href="manage_services.php" class="<?php echo ($current_page == 'manage_services.php') ? 'active' : ''; ?>">
                <i class="fas fa-microscope me-2"></i> จัดการบริการ 
            </a>
            
            <a href="manage_portfolio.php" class="<?php echo ($current_page == 'manage_portfolio.php') ? 'active' : ''; ?>">
                <i class="fas fa-images me-2"></i> จัดการผลงานบริษัท
            </a>

            <a href="manage_scope.php" class="<?php echo ($current_page == 'manage_scope.php') ? 'active' : ''; ?>">
                <i class="fas fa-list-alt me-2"></i> จัดการขอบข่ายการให้บริการ
            </a>

            <a href="manage_news.php" class="<?php echo ($current_page == 'manage_news.php') ? 'active' : ''; ?>">
                <i class="fas fa-newspaper me-2"></i> จัดการข่าวสาร
            </a>
            <a href="manage_jobs.php" class="<?php echo ($current_page == 'manage_jobs.php') ? 'active' : ''; ?>">
                <i class="fas fa-user-tie me-2"></i> จัดการการรับสมัครงาน
            </a>
            <a href="manage_admin.php" class="<?php echo ($current_page == 'manage_admin.php') ? 'active' : ''; ?>">
                <i class="fas fa-users-cog me-2"></i> จัดการผู้ดูแลระบบ
            </a>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-power-off me-2"></i> ออกจากระบบ
            </a>
        </div>

        <div class="main-content">
            <nav class="top-navbar d-flex justify-content-between align-items-center shadow-sm px-3">
                <div class="d-flex align-items-center">
                    <button class="btn btn-light d-lg-none me-3" id="toggleSidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <span class="navbar-brand mb-0 h1" style="font-size: 1.2rem;">Dashboard</span>
                </div>
                
                <div class="admin-profile shadow-sm">
                    <i class="fas fa-user-circle text-danger fs-4"></i>
                    <div class="d-none d-md-block">
                        <small class="text-muted d-block" style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase;">Logged in as</small>
                        <span class="fw-bold" style="font-size: 0.85rem;"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></span>
                    </div>
                </div>
            </nav>
            
            <div class="p-3 p-lg-5">

<script>
    // สคริปต์สำหรับควบคุม Sidebar บนมือถือ
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('toggleSidebar');
        const closeBtn = document.getElementById('closeSidebar');
        const sidebar = document.getElementById('sidebarMenu');
        const overlay = document.getElementById('sidebarOverlay');

        function toggleMenu() {
            sidebar.classList.toggle('mobile-show');
            overlay.classList.toggle('show');
        }

        if(toggleBtn) toggleBtn.addEventListener('click', toggleMenu);
        if(closeBtn) closeBtn.addEventListener('click', toggleMenu);
        if(overlay) overlay.addEventListener('click', toggleMenu);
    });
</script>