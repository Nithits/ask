<?php 
include 'includes/header.php'; 
require_once 'config/db.php';

// ดึงภาษาปัจจุบันจาก Session
$curr_lang = $lang ?? 'th';

// ดึงข้อมูลข่าวสารล่าสุด 3 รายการ
try {
    $stmt = $pdo->prepare("SELECT * FROM news ORDER BY published_date DESC, id DESC LIMIT 3");
    $stmt->execute();
    $latest_news = $stmt->fetchAll();
} catch (PDOException $e) {
    $latest_news = [];
}
?>

<section class="hero-banner">
    <div class="container">
        <div class="hero-text-box text-center text-lg-start">
            <h1 class="display-3 fw-bold mb-4">
                <?php echo $curr_lang == 'th' ? 'สอบเทียบเครื่องมือวัด<br><span style="color:var(--brand-orange)">มาตรฐานสากล</span>' : 'Professional <br><span style="color:var(--brand-orange)">Calibration</span> Services'; ?>
            </h1>
            <p class="lead mb-5 opacity-90 fw-light mx-auto mx-lg-0" style="max-width: 550px;">
                <?php echo $curr_lang == 'th' 
                    ? 'เรามุ่งเน้นความถูกต้อง แม่นยำ และการบริการที่รวดเร็ว เพื่อสนับสนุนทุกความสำเร็จของลูกค้าด้วยมาตรฐาน ISO/IEC 17025' 
                    : 'We focus on accuracy, precision, and rapid service to support our customers\' success with ISO/IEC 17025 standards.'; ?>
            </p>
            
            <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                <a href="services.php" class="btn btn-brand shadow d-inline-flex align-items-center justify-content-center" style="min-width: 160px; height: 50px;">
                    <?php echo $curr_lang == 'th' ? 'บริการของเรา' : 'Our Services'; ?>
                </a>
                <a href="contact.php" class="btn btn-outline-light rounded-pill px-4 fw-normal d-inline-flex align-items-center justify-content-center" style="min-width: 160px; height: 50px;">
                    <?php echo $curr_lang == 'th' ? 'ติดต่อเจ้าหน้าที่' : 'Contact Us'; ?>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="news-full-section">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-end mb-3">
            <h5 class="fw-bold m-0"><i class="fas fa-bullhorn text-danger me-2"></i> <?php echo $curr_lang == 'th' ? 'ข่าวสารล่าสุด' : 'Latest News'; ?></h5>
            <a href="news.php" class="text-danger small fw-normal text-decoration-none"><?php echo $curr_lang == 'th' ? 'ดูทั้งหมด' : 'View All'; ?> <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
    </div>
    <?php if (count($latest_news) > 0): ?>
    <div id="newsCarousel" class="carousel slide shadow-lg" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php foreach($latest_news as $index => $news): ?>
            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>" data-bs-interval="5000">
                <div class="carousel-item-custom">
                    <?php $news_img = !empty($news['image']) ? "assets/uploads/news_img/" . $news['image'] : "https://via.placeholder.com/1920x600?text=ASK+News"; ?>
                    <img src="<?php echo $news_img; ?>" class="w-100 h-100" style="object-fit: cover;">
                    <div class="news-overlay">
                        <div class="container text-center text-md-start">
                            <span class="badge bg-danger px-3 py-2 mb-3 rounded-pill fw-light"><?php echo date('d M Y', strtotime($news['published_date'])); ?></span>
                            <h2 class="display-5 fw-bold text-white mb-3"><?php echo htmlspecialchars($curr_lang == 'th' ? $news['title_th'] : $news['title_en']); ?></h2>
                            <a href="news_detail.php?id=<?php echo $news['id']; ?>" class="btn btn-warning px-4 fw-medium rounded-pill shadow-sm"><?php echo $curr_lang == 'th' ? 'อ่านรายละเอียด' : 'Read More'; ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#newsCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
        <button class="carousel-control-next" type="button" data-bs-target="#newsCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
    </div>
    <?php endif; ?>
</section>

<section class="py-5 bg-white">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="section-title-red display-6"><?php echo $curr_lang == 'th' ? 'ทำไมต้องเลือกเรา?' : 'Why Choose Us?'; ?></h2>
            <div class="mx-auto bg-brand-gradient mt-3" style="height: 4px; width: 80px; border-radius: 2px;"></div>
            <p class="text-muted mt-4 fw-light" style="font-size: 1.1rem;">
                <?php echo $curr_lang == 'th' ? 'มุ่งมั่นส่งมอบบริการที่มีคุณภาพด้วยมาตรฐานสากลและทีมงานมืออาชีพ' : 'Committed to delivering quality services with international standards and a professional team.'; ?>
            </p>
        </div>

        <div class="row g-4">
            <?php 
            $features = [
                'th' => [
                    ['i'=>'fa-users-cog', 't'=>'ทีมงานมืออาชีพ', 'd'=>'ผู้เชี่ยวชาญผ่านการฝึกอบรม มีประสบการณ์จริงในหลายสาขา'],
                    ['i'=>'fa-thumbs-up', 't'=>'มีคุณภาพ', 'd'=>'ใช้เครื่องมืออ้างอิงความแม่นยำสูง และกระบวนการที่เป็นมาตรฐาน'],
                    ['i'=>'fa-award', 't'=>'ได้มาตรฐาน', 'd'=>'ปฏิบัติตามมาตรฐาน ISO/IEC 17025 สากลอย่างเคร่งครัด'],
                    ['i'=>'fa-bolt', 't'=>'รวดเร็ว', 'd'=>'บริหารจัดการเวลาอย่างใส่ใจ เพื่อบริการที่รวดเร็วและแม่นยำ']
                ],
                'en' => [
                    ['i'=>'fa-users-cog', 't'=>'Professional Team', 'd'=>'Trained experts with real-world experience in multiple fields.'],
                    ['i'=>'fa-thumbs-up', 't'=>'Quality Focused', 'd'=>'Using high-precision reference tools and standardized processes.'],
                    ['i'=>'fa-award', 't'=>'Standardized', 'd'=>'Strictly adhering to international ISO/IEC 17025 standards.'],
                    ['i'=>'fa-bolt', 't'=>'Fast Service', 'd'=>'Attentive time management for rapid and accurate service.']
                ]
            ];
            foreach($features[$curr_lang] as $f): ?>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card border-0 shadow-sm p-4 h-100 bg-white">
                    <div class="icon-box-modern mb-4">
                        <i class="fas <?php echo $f['i']; ?>"></i>
                    </div>
                    <h5 class="fw-bold mb-3" style="color: var(--brand-red-dark);"><?php echo $f['t']; ?></h5>
                    <p class="text-muted small mb-0 fw-light" style="line-height: 1.7;"><?php echo $f['d']; ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5 bg-light overflow-hidden">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="img-frame">
                    <img src="assets/images/favicon.png" class="img-fluid rounded-4 shadow-sm" alt="Calibration Services" style="background:#fff; padding: 20px;">
                </div>
            </div>
            <div class="col-lg-6 ps-lg-5 text-start">
                <h6 class="text-danger fw-bold mb-2" style="letter-spacing: 2px;">OUR EXPERTISE</h6>
                <h2 class="display-5 fw-bold mb-4">SCOPE OF SERVICES</h2>
                <div class="service-list mt-4">
                    <?php 
                    $services = [
                        'th' => [
                            ['t' => 'บริการให้คำปรึกษา', 'd' => 'ด้านการดูแลและการใช้งานเครื่องมือวัดอุตสาหกรรมโดยผู้เชี่ยวชาญ'],
                            ['t' => 'ให้บริการสอบเทียบ', 'd' => 'ครอบคลุมทั้งภายในและภายนอกห้องปฏิบัติการ (On-site Service)'],
                            ['t' => 'บุคลากรมืออาชีพ', 'd' => 'ให้บริการโดยช่างเทคนิคที่มีความชำนาญและประสบการณ์เฉพาะสาขา']
                        ],
                        'en' => [
                            ['t' => 'Consulting Services', 'd' => 'Expert advice on maintenance and usage of industrial measuring tools.'],
                            ['t' => 'Calibration Services', 'd' => 'Comprehensive in-lab and on-site calibration services.'],
                            ['t' => 'Professional Personnel', 'd' => 'Provided by skilled technicians with specialized field experience.']
                        ]
                    ];
                    foreach($services[$curr_lang] as $s): ?>
                    <div class="d-flex mb-4 hover-translate">
                        <i class="fas fa-star text-danger fa-2x me-4 mt-1"></i>
                        <div><h4 class="fw-bold mb-1"><?php echo $s['t']; ?></h4><p class="text-muted mb-0 fw-light"><?php echo $s['d']; ?></p></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5" style="background-color: var(--brand-dark); color: white;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-5 mb-4 mb-lg-0 text-center text-lg-start">
                <h2 class="fw-bold display-5 mb-3" style="color: var(--brand-red);">
                    <?php echo $curr_lang == 'th' ? 'ห้องปฏิบัติการสอบเทียบ' : 'Calibration Laboratory'; ?>
                </h2>
                <div class="bg-brand-orange" style="height: 4px; width: 80px; border-radius: 2px;"></div>
            </div>
            <div class="col-lg-7 border-start border-secondary ps-lg-5">
                <p class="lead mb-0 opacity-75 fw-light" style="line-height: 1.9; color: white !important;">
                    <?php echo $curr_lang == 'th' 
                        ? 'เรามีความมุ่งมั่นในการปฏิบัติงานอย่างมืออาชีพ พร้อมด้วยความสามารถ เพื่อการบริการสอบเทียบที่ดี มีคุณภาพ ตอบสนองต่อความต้องการของลูกค้าด้วยผลที่มีความถูกต้องแม่นยำ เป็นไปตามมาตรฐานสากล' 
                        : 'We are committed to professional operation with high competency to provide quality calibration services that meet customer needs with accurate results following international standards.'; ?>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="section-title-red display-6"><?php echo $curr_lang == 'th' ? 'ขอบเขตเครื่องมือที่ให้บริการ' : 'Our Calibration Scope'; ?></h2>
            <p class="text-muted mt-3 fw-light"><?php echo $curr_lang == 'th' ? 'ยกระดับมาตรฐานอุตสาหกรรม ด้วยบริการสอบเทียบที่แม่นยำที่สุด' : 'Elevating industrial standards with the most precise calibration services.'; ?></p>
        </div>
        <div class="row g-4">
            <?php
            $labs = [
                ['n' => 'Chemical', 'i' => 'fa-flask'],
                ['n' => 'Dimension', 'i' => 'fa-ruler-combined'],
                ['n' => 'Electrical', 'i' => 'fa-bolt'],
                ['n' => 'Force and Mass', 'i' => 'fa-weight-hanging'],
                ['n' => 'Pressure', 'i' => 'fa-tachometer-alt'],
                ['n' => 'Temperature', 'i' => 'fa-temperature-high'],
                ['n' => 'Torque', 'i' => 'fa-wrench'],
            ];
            foreach($labs as $l): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="lab-grid-item shadow-sm p-4 rounded-4 text-center">
                    <div class="lab-icon-red mb-3">
                        <i class="fas <?php echo $l['i']; ?>"></i>
                    </div>
                    <h6 class="fw-bold mb-1"><?php echo $l['n']; ?></h6>
                    <small class="text-muted text-uppercase small"><?php echo $curr_lang == 'th' ? 'ห้องปฏิบัติการ' : 'Laboratory'; ?></small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5 pt-3">
            <a href="services.php" class="btn btn-brand rounded-pill px-5 py-3 fw-bold">
                <?php echo $curr_lang == 'th' ? 'ดูขอบข่ายการบริการทั้งหมด' : 'View All Scope of Services'; ?> <i class="fas fa-external-link-alt ms-2"></i>
            </a>
        </div>
    </div>
</section>

<section class="py-5 text-white" style="background: linear-gradient(135deg, #004d40 0%, #009688 100%);">
    <div class="container text-center py-5">
        <h4 class="fw-bold mb-3" style="color: #ffc107; letter-spacing: 2px;">ISO/IEC 17025:2017</h4>
        <h1 class="display-4 fw-bold mb-4 text-white"><?php echo $curr_lang == 'th' ? 'ทุกขั้นตอนการปฏิบัติงานอ้างอิงตามข้อกำหนดสากล' : 'All Procedures Follow International Standards'; ?></h1>
        <div class="mx-auto" style="height: 2px; width: 100px; background: rgba(255,255,255,0.3);"></div>
        <p class="mt-4 opacity-90 lead fw-light text-white" style="max-width: 800px; margin-left: auto; margin-right: auto;">
            <?php echo $curr_lang == 'th' 
                ? 'สร้างความเชื่อมั่นในผลการวัด ด้วยกระบวนการที่ตรวจสอบได้และแม่นยำ เพื่อมาตรฐานสูงสุดของภาคอุตสาหกรรม' 
                : 'Building confidence in measurement results with traceable and accurate processes for the highest industrial standards.'; ?>
        </p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>