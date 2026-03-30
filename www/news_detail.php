<?php 
// 1. เริ่มต้นระบบ Config และ Logic (ต้องอยู่ก่อน Output เสมอ)
require_once 'config/db.php';
include_once 'includes/language_setup.php'; 

// เปิด Output Buffering
ob_start();

// ดึงภาษาปัจจุบัน
$curr_lang = $_SESSION['lang'] ?? 'th';

// 2. ตรวจสอบ ID และดึงข้อมูลจาก Database
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM news WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $news = $stmt->fetch();

        if (!$news) {
            include 'includes/header.php';
            echo "
            <div class='container my-5 py-5 text-center' style='min-height: 50vh;'>
                <div class='opacity-25 mb-4'><i class='fas fa-search fa-5x'></i></div>
                <h3 class='text-muted mb-4'>" . ($curr_lang == 'th' ? 'ไม่พบข่าวสารที่คุณต้องการ' : 'News article not found.') . "</h3>
                <a href='news' class='btn btn-danger rounded-pill px-5 py-2' style='background:linear-gradient(135deg, #E31E24 0%, #F58220 100%); border:none;'>
                    " . ($curr_lang == 'th' ? 'กลับไปหน้ารวมข่าวสาร' : 'Back to News') . "
                </a>
            </div>";
            include 'includes/footer.php';
            exit();
        }
    } catch (PDOException $e) {
        die("Error fetching news: " . $e->getMessage());
    }
} else {
    header("Location: news");
    exit();
}

// 3. เตรียมตัวแปรสำหรับแสดงผล
$title = ($curr_lang == 'th') ? $news['title_th'] : ($news['title_en'] ?? $news['title_th']);
$content = ($curr_lang == 'th') ? $news['content_th'] : ($news['content_en'] ?? $news['content_th']);
$date = !empty($news['published_date']) ? $news['published_date'] : $news['created_at'];
$image = !empty($news['image']) ? "assets/uploads/news_img/" . $news['image'] : "https://via.placeholder.com/1200x600?text=ASK+Calibration+News";

// ลิงก์สำหรับ Social Share
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

// 4. เรียก Header มาแสดงผล
include 'includes/header.php'; 
?>

<div class="container my-5 pt-4 news-detail-container">
    
    <nav aria-label="breadcrumb" class="mb-5">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="index"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="news"><?php echo $curr_lang == 'th' ? 'ข่าวสารและกิจกรรม' : 'News & Updates'; ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars(mb_strimwidth($title, 0, 45, "...")); ?></li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            
            <div class="text-center mb-5">
                <div class="date-badge-detail mb-4">
                    <i class="far fa-calendar-alt me-2"></i> <?php echo date('d F Y', strtotime($date)); ?>
                </div>
                <h1 class="fw-bold text-dark mb-4" style="line-height: 1.3; font-size: clamp(1.8rem, 4vw, 2.8rem);">
                    <?php echo htmlspecialchars($title); ?>
                </h1>
                <div class="mx-auto" style="height: 5px; width: 80px; background: var(--brand-gradient); border-radius: 10px;"></div>
            </div>

            <div class="featured-img-container">
                <img src="<?php echo $image; ?>" class="featured-img" alt="News Image">
            </div>

            <article class="content-body">
                <?php 
                    // ใช้ nl2br เพื่อแสดงการขึ้นบรรทัดใหม่ และ htmlspecialchars เพื่อความปลอดภัย
                    echo nl2br(htmlspecialchars($content)); 
                ?>
            </article>

            <div class="share-box text-center shadow-sm">
                <h6 class="text-muted text-uppercase mb-3" style="letter-spacing: 2px; font-size: 0.8rem;">
                    <?php echo $curr_lang == 'th' ? 'แชร์ข่าวสารนี้' : 'Share this update'; ?>
                </h6>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($actual_link); ?>" target="_blank" class="btn-share btn-fb shadow-sm">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://social-plugins.line.me/lineit/share?url=<?php echo urlencode($actual_link); ?>" target="_blank" class="btn-share btn-line shadow-sm">
                    <i class="fab fa-line"></i>
                </a>
            </div>

            <div class="mt-5 pt-5 border-top text-center">
                <a href="news" class="btn-back shadow-sm">
                    <i class="fas fa-arrow-left me-2"></i> <?php echo $curr_lang == 'th' ? 'กลับไปหน้ารวมข่าวสาร' : 'Back to News'; ?>
                </a>
            </div>

        </div>
    </div>
</div>

<?php 
include 'includes/footer.php'; 
// จบการทำงานของ Output Buffering
ob_end_flush();
?>