<?php 
// 1. เรียกใช้ระบบภาษาและ Header
include_once 'includes/language_setup.php'; 
include 'includes/header.php'; 
require_once 'config/db.php';

// ดึงภาษาปัจจุบัน
$curr_lang = $_SESSION['lang'] ?? 'th';

// ดึงข้อมูลข่าวสาร กรองเฉพาะที่ยังไม่หมดอายุ
try {
    $stmt = $pdo->prepare("SELECT * FROM news 
                           WHERE end_date IS NULL OR end_date >= CURDATE() 
                           ORDER BY published_date DESC, id DESC");
    $stmt->execute();
    $news_items = $stmt->fetchAll();
} catch (PDOException $e) {
    $news_items = [];
}
?>

<section class="news-hero-clean">
    <div class="container">
        
        <h1 class="hero-title">
            <?php echo $curr_lang == 'th' ? 'ข่าวสารและ<span>กิจกรรม</span>' : 'News & <span>Updates</span>'; ?>
        </h1>
        
        <div class="title-underline-modern"></div>
        
        <p class="mt-4 mx-auto fs-5" style="max-width: 600px; font-weight: 500; line-height: 1.6;">
            <?php echo $curr_lang == 'th' 
                ? 'ติดตามความเคลื่อนไหว ข้อมูลทางเทคนิค และกิจกรรมดีๆ เพื่อยกระดับมาตรฐานอุตสาหกรรมไปพร้อมกับเรา' 
                : 'Stay updated with our latest insights, technical advances, and corporate activities from ASK Calibration.'; ?>
        </p>
    </div>
</section>

<section class="news-section py-5">
    <div class="container">
        <div class="row g-4">
            <?php if (count($news_items) > 0): ?>
                <?php foreach ($news_items as $news): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="news-card-clean shadow-sm">
                            <div class="news-img-box">
                                <?php $news_img = !empty($news['image']) ? "assets/uploads/news_img/" . $news['image'] : "https://via.placeholder.com/600x400?text=ASK+News"; ?>
                                <img src="<?php echo $news_img; ?>" alt="News Image">
                                <div class="news-date-tag">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    <?php echo date('d M Y', strtotime($news['published_date'])); ?>
                                </div>
                            </div>
                            
                            <div class="news-body p-4 d-flex flex-column flex-grow-1">
                                <h3 class="news-title" style="font-size: 1.2rem; font-weight: 600; min-height: 3.4rem;">
                                    <?php echo htmlspecialchars($curr_lang == 'th' ? $news['title_th'] : ($news['title_en'] ?? $news['title_th'])); ?>
                                </h3>
                                <p class="news-excerpt text-muted mb-4" style="font-size: 0.9rem; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?php 
                                        $content = $curr_lang == 'th' ? $news['content_th'] : ($news['content_en'] ?? $news['content_th']);
                                        echo strip_tags($content); 
                                    ?>
                                </p>
                                <div class="mt-auto">
                                    <a href="news_detail?id=<?php echo $news['id']; ?>" class="btn-read-more text-danger text-decoration-none fw-bold">
                                        <?php echo $curr_lang == 'th' ? 'อ่านรายละเอียด' : 'Read More'; ?>
                                        <i class="fas fa-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="opacity-25 mb-4">
                        <i class="far fa-newspaper fa-5x"></i>
                    </div>
                    <h4 class="text-muted fw-light">
                        <?php echo $curr_lang == 'th' ? 'ยังไม่มีข้อมูลข่าวสารในขณะนี้' : 'No news updates available at the moment.'; ?>
                    </h4>
                    <a href="index" class="btn btn-outline-danger rounded-pill mt-4 px-4">
                        <?php echo $curr_lang == 'th' ? 'กลับหน้าแรก' : 'Back to Home'; ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>