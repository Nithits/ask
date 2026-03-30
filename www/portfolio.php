<?php 
// 1. เรียกใช้ระบบภาษาและ Header
include_once 'includes/language_setup.php'; 
include 'includes/header.php'; 
require_once 'config/db.php';

$curr_lang = $_SESSION['lang'] ?? 'th';

// 2. ดึงข้อมูลหมวดหมู่ (ใช้ตารางใหม่ portfolio_categories)
try {
    $stmt_cat = $pdo->query("SELECT category_key, category_name FROM portfolio_categories ORDER BY id ASC");
    $categories = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}

// 3. ดึงข้อมูลผลงาน (ใช้ตารางใหม่ portfolio_items)
try {
    $stmt_port = $pdo->query("SELECT * FROM portfolio_items ORDER BY id DESC");
    $portfolios = $stmt_port->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $portfolios = [];
}
?>

<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css"/>

<section class="portfolio-hero">
    <div class="container">
        <h1 class="animate__animated animate__fadeInDown">
            <?php echo $curr_lang == 'th' ? 'ผลงานของบริษัท' : 'OUR PORTFOLIO'; ?>
        </h1>

        <div class="title-underline-modern"></div>

    </div>
</section>

<section class="portfolio-section pb-5 bg-light">
    <div class="container">
        
        <div class="portfolio-filters">
            <button class="filter-btn active" data-filter="all">
                <?php echo $curr_lang == 'th' ? 'ทั้งหมด (All)' : 'All Services'; ?>
            </button>
            <?php foreach($categories as $cat): ?>
                <button class="filter-btn" data-filter="<?php echo htmlspecialchars($cat['category_key']); ?>">
                    <?php echo htmlspecialchars($cat['category_name']); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="row g-4" id="portfolio-grid">
            <?php if (!empty($portfolios)): ?>
                <?php foreach($portfolios as $item): 
                    $title = ($curr_lang == 'th') ? $item['title_th'] : ($item['title_en'] ?? $item['title_th']);
                    $img_src = !empty($item['image']) ? "assets/uploads/portfolio/" . $item['image'] : "https://via.placeholder.com/600x400?text=No+Image";
                ?>
                    <div class="col-12 col-md-6 col-lg-4 portfolio-item" data-category="<?php echo htmlspecialchars($item['category_key']); ?>">
                        <div class="portfolio-card">
                            
                            <a href="<?php echo $img_src; ?>" data-fancybox="gallery" data-caption="<?php echo htmlspecialchars($title); ?>" class="portfolio-img-box text-decoration-none d-block position-relative">
                                
                                <img src="<?php echo $img_src; ?>" alt="<?php echo htmlspecialchars($title); ?>" loading="lazy" class="img-fluid w-100">
                                
                                <div class="portfolio-overlay">
                                    <i class="fas fa-search-plus"></i>
                                    <span><?php echo htmlspecialchars($title); ?></span>
                                </div>
                            </a>

                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5 my-5">
                    <i class="fas fa-images fa-4x text-muted opacity-25 mb-3"></i>
                    <h5 class="text-muted fw-light">
                        <?php echo $curr_lang == 'th' ? 'กำลังอัปเดตข้อมูลผลงาน' : 'Portfolio is being updated.'; ?>
                    </h5>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<section class="iso-banner-section">
    <div class="container">
        <div class="iso-card">
            <div class="iso-watermark">17025</div>
            
            <div class="iso-content">
                <h3>ISO/IEC 17025:2017</h3>
                <p>
                    <?php echo $curr_lang == 'th' 
                        ? 'ทุกขั้นตอนการปฏิบัติงานของเราอ้างอิงตามข้อกำหนดสากล เพื่อความถูกต้องและแม่นยำสูงสุดสำหรับเครื่องมือวัดของคุณ' 
                        : 'All our operational procedures adhere to international standards to ensure the highest accuracy and precision for your measuring instruments.'; ?>
                </p>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. เริ่มใช้งาน Fancybox
    Fancybox.bind('[data-fancybox="gallery"]', {
        // เปิดใช้งานการเลื่อนรูปภาพแบบลูป
        loop: true,
        // แสดงปุ่ม Thumbnail ด้านล่าง (ถ้าต้องการปิด ให้ตั้งค่าเป็น false)
        Thumbs: {
            autoStart: true,
        }
    });

    // 2. ระบบ Filter (คัดกรองหมวดหมู่)
    const filterBtns = document.querySelectorAll('.filter-btn');
    const portfolioItems = document.querySelectorAll('.portfolio-item');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // สลับสถานะ Active ให้ปุ่มที่ถูกคลิก
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const filterValue = this.getAttribute('data-filter');

            portfolioItems.forEach(item => {
                if (filterValue === 'all' || item.getAttribute('data-category') === filterValue) {
                    item.style.display = 'block';
                    // อนิเมชัน Fade in เบาๆ
                    item.animate([
                        { opacity: 0, transform: 'scale(0.95)' },
                        { opacity: 1, transform: 'scale(1)' }
                    ], { duration: 300, fill: 'forwards' });
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>