<?php
session_start();
// เชื่อมต่อฐานข้อมูล
require_once '../config/db.php'; 

// ดึงข้อมูลลูกค้า
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

// เรียกใช้ Header ของ Admin
include 'includes/admin_header.php'; 
?>

<style>
    .admin-card { 
        border-radius: 12px; 
        background: #fff; 
        box-shadow: 0 4px 20px rgba(0,0,0,0.03); 
    }
    .table-custom th { 
        border-bottom: 1px solid #eee; 
        color: #888; 
        font-weight: 600; 
        font-size: 13px; 
        padding: 15px; 
    }
    .table-custom td { 
        border-bottom: 1px solid #f9f9f9; 
        padding: 15px; 
        font-size: 14px; 
        color: #333; 
    }
    .table-custom tr:hover { 
        background-color: #fafafa; 
    }
    .action-btn { 
        width: 32px; 
        height: 32px; 
        display: inline-flex; 
        align-items: center; 
        justify-content: center; 
        border-radius: 6px; 
        border: 1px solid #ffeeba; 
        background: #fff; 
        color: #dc3545; 
        transition: 0.2s; 
        text-decoration: none;
    }
    .action-btn:hover { 
        background: #dc3545; 
        color: #fff; 
        border-color: #dc3545; 
    }
    .contact-info i { 
        width: 18px; 
        margin-right: 5px; 
    }
</style>

<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <h4 class="fw-bold m-0 text-dark">จัดการข้อมูลลูกค้า</h4>
    </div>

    <div class="admin-card p-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold m-0">รายชื่อลูกค้าที่สมัครสมาชิกในระบบ</h5>
            <div class="search-box" style="width: 250px;">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-custom align-middle">
                <thead>
                    <tr>
                        <th width="15%">วันที่สมัคร</th>
                        <th width="30%">บริษัทและผู้ติดต่อ</th>
                        <th width="35%">ข้อมูลการสื่อสาร</th>
                        <th width="20%" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($users)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">ยังไม่มีลูกค้าสมัครสมาชิก</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $row): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark"><?php echo date('d M Y', strtotime($row['created_at'])); ?></div>
                                <div class="text-muted small"><i class="far fa-clock me-1"></i> <?php echo date('H:i', strtotime($row['created_at'])); ?></div>
                            </td>
                            
                            <td>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['company_name'] ? $row['company_name'] : '-'); ?></div>
                                <div class="text-muted small"><?php echo htmlspecialchars($row['fullname']); ?></div>
                            </td>

                            <td class="contact-info">
                                <div class="mb-1 text-muted">
                                    <i class="far fa-envelope text-primary"></i> <?php echo htmlspecialchars($row['email']); ?>
                                </div>
                                <div class="text-muted">
                                    <i class="fas fa-phone-alt text-info"></i> <?php echo htmlspecialchars($row['phone']); ?>
                                </div>
                            </td>

                            <td class="text-center">
                                <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $row['id']; ?>)" class="action-btn shadow-sm" title="ลบข้อมูล">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmDelete(userId) {
    Swal.fire({
        title: 'ยืนยันการลบข้อมูล?',
        text: "เมื่อลบแล้วจะไม่สามารถกู้คืนข้อมูลได้อีก!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ยืนยันการลบ',
        cancelButtonText: 'ยกเลิก',
        customClass: {
            confirmButton: 'btn btn-danger shadow-sm px-4 rounded-pill',
            cancelButton: 'btn btn-secondary shadow-sm px-4 ms-2 rounded-pill'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            // ถ้ากดยืนยัน ให้วิ่งไปลบที่ไฟล์ delete_user.php
            window.location.href = 'action/delete_user.php?id=' + userId;
        }
    });
}
</script>

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
<?php 
// เรียกใช้ Footer ของ Admin
include 'includes/admin_footer.php'; 
?>