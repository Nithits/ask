<?php 
// 1. เรียกใช้ระบบภาษาและ Header
include_once 'includes/language_setup.php'; 
include 'includes/header.php'; 
require_once 'config/db.php';

$curr_lang = $_SESSION['lang'] ?? 'th';

// 1. ดึงข้อมูลหมวดหมู่
try {
    $stmt_cat = $pdo->query("SELECT * FROM service_categories ORDER BY id ASC");
    $categories_db = $stmt_cat->fetchAll();
} catch (PDOException $e) {
    $categories_db = [];
}

// 2. ดึงข้อมูลบริการ
try {
    $stmt = $pdo->query("SELECT * FROM services ORDER BY id DESC");
    $all_services = $stmt->fetchAll();
} catch (PDOException $e) {
    $all_services = []; 
}

// จัดกลุ่มเครื่องมือตาม Category
$grouped_services = [];
foreach ($all_services as $service) {
    $grouped_services[$service['category']][] = $service;
}
?>

<section class="service-banner">
    <div class="container animate__animated animate__fadeIn">
        
        <h1 class="display-4 mb-2">
            <?php echo $curr_lang == 'th' ? 'ขอบเขตเครื่องมือที่ให้บริการ' : 'SCOPE OF SERVICES'; ?>
        </h1>
        
        <div class="title-underline-gradient"></div>
        
        <p class="lead mt-3">
            <?php echo $curr_lang == 'th' ? 'มาตรฐานห้องปฏิบัติการ ISO/IEC 17025' : 'Laboratory Standard ISO/IEC 17025'; ?>
        </p>
    </div>
</section>

<div class="container my-5 pb-5" style="min-height: 60vh;">
    
    <div class="service-tabs-wrapper container">
        <ul class="nav nav-pills service-tabs gap-2 justify-content-center" id="serviceTabs" role="tablist">
            <?php if (!empty($categories_db)): ?>
                <?php foreach($categories_db as $index => $cat): ?>
                <li class="nav-item">
                    <button class="nav-link <?php echo $index === 0 ? 'active' : ''; ?>" 
                            id="tab-<?php echo $cat['category_key']; ?>" 
                            data-bs-toggle="pill" 
                            data-bs-target="#pane-<?php echo $cat['category_key']; ?>" 
                            type="button" role="tab">
                        <?php echo htmlspecialchars($curr_lang == 'th' ? $cat['category_name'] : ($cat['category_name_en'] ?? $cat['category_name'])); ?>
                    </button>
                </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>

    <div class="tab-content mt-5" id="serviceTabsContent">
        <?php foreach($categories_db as $index => $cat): ?>
        <div class="tab-pane fade <?php echo $index === 0 ? 'show active' : ''; ?>" 
             id="pane-<?php echo $cat['category_key']; ?>" role="tabpanel">
            
            <div class="row g-4">
                <?php 
                $key = $cat['category_key'];
                if(isset($grouped_services[$key]) && count($grouped_services[$key]) > 0): 
                    foreach($grouped_services[$key] as $service):
                        $img_src = !empty($service['image']) ? "assets/uploads/services/" . $service['image'] : "https://via.placeholder.com/600x450?text=ASK+Calibration";
                        $title = ($curr_lang == 'th') ? $service['title_th'] : ($service['title_en'] ?? $service['title_th']);
                ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 instrument-card shadow-sm">
                                <div class="instrument-img-container">
                                    <img src="<?php echo $img_src; ?>" alt="<?php echo $title; ?>">
                                </div>
                                <div class="card-title-box">
                                    <h5 class="fw-bold m-0" style="font-size: 1.1rem;"><?php echo htmlspecialchars($title); ?></h5>
                                </div>
                            </div>
                        </div>
                <?php 
                    endforeach;
                else: 
                ?>
                    <div class="col-12 text-center py-5">
                        <div class="opacity-25 mb-3 text-muted">
                            <i class="fas fa-box-open fa-4x"></i>
                        </div>
                        <h5 class="text-muted fw-light">
                            <?php echo $curr_lang == 'th' ? 'ยังไม่มีข้อมูลเครื่องมือในหมวดหมู่นี้' : 'No instruments available in this category.'; ?>
                        </h5>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="iso-notice mt-5 shadow-lg p-5 text-center text-white rounded-4">
        <h4 class="fw-bold mb-3 text-warning">ISO/IEC 17025:2017</h4>
        <p class="mb-0 fw-light opacity-90" style="max-width: 800px; margin: 0 auto;">
            <?php 
                echo $curr_lang == 'th' 
                ? 'ทุกขั้นตอนการปฏิบัติงานของเราอ้างอิงตามข้อกำหนดสากล เพื่อความถูกต้องและแม่นยำสูงสุดสำหรับเครื่องมือวัดของคุณ' 
                : 'All our operational procedures are based on international requirements to ensure maximum accuracy and precision for your measuring instruments.'; 
            ?>
        </p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var tabs = document.querySelectorAll('#serviceTabs button');
    tabs.forEach(function(tab) {
        tab.addEventListener('shown.bs.tab', function (e) {
            e.target.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>