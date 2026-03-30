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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>

    <style>
        /* จัดการสไตล์เมนูในมือถือ โดยใช้ระบบ Offcanvas ของ Bootstrap */
        @media (max-width: 991.98px) {
            .offcanvas-start {
                width: 280px !important;
                background-color: #212529 !important;
            }
            .sidebar.offcanvas-lg {
                display: flex !important; 
            }
            .sidebar a {
                color: #fff !important;
            }
            .sidebar a:hover, .sidebar a.active {
                background-color: rgba(255,255,255,0.1) !important;
            }
            .sidebar .menu-label {
                color: #adb5bd !important;
            }
            #sidebarMenu a {
                position: relative;
                z-index: 1060 !important; 
                pointer-events: auto !important;
            }
        }
    </style>

    <script>
    // สคริปต์เสริม: ทำให้คลิกเมนูในมือถือแล้วเมนูพับเก็บ พร้อมเปลี่ยนหน้าเนียนๆ
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarLinks = document.querySelectorAll('#sidebarMenu a');
        
        sidebarLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                const url = this.getAttribute('href');
                
                if (url && url !== '#' && url !== '') {
                    e.preventDefault(); 
                    
                    // เช็คว่าถ้าเป็นจอมือถือ (ความกว้างน้อยกว่า 992px)
                    if (window.innerWidth < 992) {
                        const myOffcanvas = document.getElementById('sidebarMenu');
                        const bsOffcanvas = bootstrap.Offcanvas.getInstance(myOffcanvas) || new bootstrap.Offcanvas(myOffcanvas);
                        
                        bsOffcanvas.hide(); // สั่งพับเมนู

                        setTimeout(() => {
                            window.location.href = url;
                        }, 300);
                    } else {
                        // หน้าจอคอมปกติ กดแล้วเปลี่ยนหน้าเลย
                        window.location.href = url;
                    }
                }
            });
        });
    });
    </script>
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0 flex-nowrap">
        
        <div class="sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
            
            <div class="d-flex justify-content-between align-items-center d-lg-none p-3 border-bottom border-secondary">
                <h5 class="m-0 text-white" id="sidebarMenuLabel"><i class="fas fa-shield-alt me-2"></i> ASK ADMIN</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
            </div>

            <div class="sidebar-brand d-none d-lg-block">
                <h5 class="m-0"><i class="fas fa-shield-alt me-2"></i> ASK ADMIN</h5>
            </div>

            <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
            
            <div class="offcanvas-body d-flex flex-column p-0">
                <div class="menu-label mt-3 mt-lg-0">Main Management</div>
                <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="fas fa-file-invoice-dollar me-2"></i> รายการใบเสนอราคา
                </a>
                <a href="manage_contact.php" class="<?php echo ($current_page == 'manage_contact.php') ? 'active' : ''; ?>">
                    <i class="fas fa-envelope-open-text me-2"></i> ข้อความติดต่อ
                </a>

                <div class="menu-label">Website Content</div>
                <a href="manage_services.php" class="<?php echo ($current_page == 'manage_services.php') ? 'active' : ''; ?>">
                    <i class="fas fa-microscope me-2"></i> จัดการระบบสอบเทียบ
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
                
                <a href="logout.php" class="logout-btn mt-auto mb-3">
                    <i class="fas fa-power-off me-2"></i> ออกจากระบบ
                </a>
            </div>
        </div>

        <div class="main-content w-100">
            <nav class="top-navbar d-flex justify-content-between align-items-center shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <button class="btn btn-light d-lg-none me-3 shadow-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
                        <i class="fas fa-bars fs-5"></i>
                    </button>
                    <span class="navbar-brand mb-0 h4 fw-bold">Dashboard</span>
                </div>
                
                <div class="admin-profile shadow-sm">
                    <i class="fas fa-user-circle text-danger fs-4"></i>
                    <div class="d-none d-md-block">
                        <small class="text-muted d-block" style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase;">Logged in as</small>
                        <span class="fw-bold" style="font-size: 0.85rem;"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></span>
                    </div>
                </div>
            </nav>
            
            <div class="p-4 p-lg-5">