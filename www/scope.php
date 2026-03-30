<?php
// 1. เรียกใช้ระบบภาษาและ Header
include_once 'includes/language_setup.php';
require_once __DIR__ . '/config/db.php';
include __DIR__ . '/includes/header.php';

$curr_lang = $_SESSION['lang'] ?? 'th';

if (!isset($pdo) || !$pdo) {
    die('Database connection failed.');
}

// ดึงข้อมูลหมวดหมู่ (ดึงข้อมูลทั้งหมดที่มีสถานะ active)
try {
    $stmtCategories = $pdo->query("
        SELECT *
        FROM scope_categories
        WHERE is_active = 1
        ORDER BY sort_order ASC, id ASC
    ");
    $categories = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}
?>

<section class="scope-banner">
    <div class="container animate__animated animate__fadeIn">
        
        <h1 class="display-4 mb-2">
            <?php echo $curr_lang == 'th' ? 'ขอบข่ายการให้บริการสอบเทียบ' : 'CALIBRATION SCOPE'; ?>
        </h1>
        
        <div class="title-underline-modern"></div>
        
        <p class="lead mt-3">
            <?php echo $curr_lang == 'th' 
                ? 'ให้บริการโดยบุคลากรที่มีความชำนาญ และมีประสบการณ์เฉพาะด้าน' 
                : 'Delivered by highly skilled and experienced calibration professionals'; ?>
        </p>
    </div>
</section>

<section class="scope-page-section py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-11">

                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $cat): ?>
                        <?php
                        try {
                            $stmtItems = $pdo->prepare("SELECT * FROM scope_items WHERE category_id = ? AND is_active = 1 ORDER BY sort_order ASC, id ASC");
                            $stmtItems->execute([$cat['id']]);
                            $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
                        } catch (PDOException $e) { $items = []; }
                        ?>

                        <div class="scope-table-block bg-white animate__animated animate__fadeIn">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle scope-table mb-0">
                                    <thead>
                                        <tr class="scope-category-title">
                                            <th colspan="3">
                                                <?php echo htmlspecialchars($curr_lang == 'th' ? ($cat['name_th'] ?? $cat['name_en']) : ($cat['name_en'] ?? $cat['name_th'])); ?>
                                            </th>
                                        </tr>
                                        <tr class="scope-column-head">
                                            <th style="width: 45%;"><?php echo $curr_lang == 'th' ? 'เครื่องมือ / รายการ' : 'Instrument / Items'; ?></th>
                                            <th style="width: 25%;"><?php echo $curr_lang == 'th' ? 'เอกสารอ้างอิง' : 'Reference Method'; ?></th>
                                            <th style="width: 30%;"><?php echo $curr_lang == 'th' ? 'ช่วงการวัด' : 'Calibration Range'; ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($items)): ?>
                                            <?php foreach ($items as $item): ?>
                                                <?php if (!empty($item['is_group_header'])): ?>
                                                    <tr class="scope-group-row">
                                                        <td colspan="3" class="fw-bold px-3" style="color: var(--brand-red);">
                                                            <i class="fas fa-caret-right me-2"></i><?php echo htmlspecialchars($item['instrument']); ?>
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <tr>
                                                        <td class="ps-4"><?php echo htmlspecialchars($item['instrument'] ?? ''); ?></td>
                                                        <td class="text-center"><?php echo htmlspecialchars($item['reference'] ?? '-'); ?></td>
                                                        <td class="text-center fw-medium"><?php echo htmlspecialchars($item['range_value'] ?? '-'); ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php  /* <div class="scope-download-box mt-5 p-5 bg-white rounded-4 shadow-sm text-center">
                    <h4 class="fw-bold mb-4"><?php echo $curr_lang == 'th' ? 'ต้องการข้อมูลขอบข่ายฉบับเต็ม?' : 'Need the full accreditation scope?'; ?></h4>
                    <a href="assets/downloads/full-scope.pdf" class="scope-download-btn" target="_blank">
                        <i class="fas fa-file-pdf me-2"></i>
                        <?php echo $curr_lang == 'th' ? 'ดาวน์โหลดเอกสาร PDF' : 'Download Full Scope (PDF)'; ?>
                    </a>
                 </div> */ ?>
<div class="text-center mt-5">
    <div style="max-width: 800px; margin: 0 auto; line-height: 1.8;">
        <?php if ($curr_lang == 'th'): ?>
            <strong style="font-size: 1.2rem; display: block; margin-bottom: 5px; color: #333;">เครื่องมือวัด</strong>
            <span style="color: #666;">
                มัลติมิเตอร์, แคลมป์มิเตอร์, เคาน์เตอร์อเนกประสงค์, เครื่องวัดความต้านทาน, เคาน์เตอร์, นาฬิกาจับเวลา, ตัวจับเวลา, มิเตอร์วัดแสง, ตัวควบคุมอุณหภูมิ, เครื่องสอบเทียบอเนกประสงค์, ออสซิโลสโคป, เครื่องทดสอบฉนวน, แหล่งจ่ายไฟ, เครื่องทดสอบความทนทาน, เครื่องวัดกำลังไฟฟ้า, อัลตราโซนิก, เครื่องวัดเสียง, เครื่องวัดความสว่าง, เครื่องทดสอบอีนาเมล, อื่นๆ
            </span>
        <?php else: ?>
            <strong style="font-size: 1.2rem; display: block; margin-bottom: 5px; color: #333;">Instrument</strong>
            <span style="color: #666;">
                Multimeter, Clamp meter, Universal counter, Resistance meter, Counter, Stop watch, Timer, Panal meter, Temperature controller, Multifunction calibrator, Oscilo scope, Insulation tester, Power supply, withstanding tester, Power meter, Ultrasonic, Sound meter, Lux meter, Enamel tester, other
            </span>
        <?php endif; ?>
    </div>
</div>

                <div class="scope-iso-notice mt-5 text-center shadow-lg">
                    <div class="position-relative">
                        <h4 class="text-warning mb-3">ISO/IEC 17025:2017</h4>
                        <p class="lead opacity-75">
                            <?php echo $curr_lang == 'th'
                                ? 'ทุกขั้นตอนการปฏิบัติงานของเราอ้างอิงตามข้อกำหนดสากล เพื่อความถูกต้องและแม่นยำสูงสุดสำหรับเครื่องมือวัดของคุณ'
                                : 'All of our operations follow international requirements to ensure the highest accuracy and reliability for your measuring instruments.'; ?>
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>