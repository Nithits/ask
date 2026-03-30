<?php 
// 1. เรียกใช้ระบบภาษาและ Header
include_once 'includes/language_setup.php'; 
include 'includes/header.php'; 

// ดึงภาษาปัจจุบัน
$curr_lang = $_SESSION['lang'] ?? 'th';
?>

<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<section class="about-hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <h1 class="hero-title mb-4" style="font-weight: 700; font-size: clamp(2.2rem, 5vw, 3.5rem);">
                    <?php echo $curr_lang == 'th' ? 'ห้องปฏิบัติการ<span>สอบเทียบ</span>' : 'Calibration <span>Laboratory</span>'; ?>
                </h1>
                <p class="text-muted mb-4" style="font-size: 1.1rem; line-height: 1.8;">
                    <?php 
                        echo $curr_lang == 'th' 
                        ? 'บริษัท เอ เอส เค คาลิเบรชั่น แอนด์เซอร์วิส จำกัด มุ่งมั่นสู่ความเป็นเลิศในด้านการสอบเทียบเครื่องมือวัดอุตสาหกรรม' 
                        : 'ASK Calibration & Service Co., Ltd. is committed to excellence in the field of industrial instrument calibration.'; 
                    ?>
                </p>
                <a href="services" class="btn btn-brand px-5 py-2 rounded-pill shadow-sm">
                    <?php echo $curr_lang == 'th' ? 'บริการของเรา' : 'Our Services'; ?>
                </a>
            </div>
            <div class="col-lg-6" data-aos="zoom-in">
                <div class="main-img-wrapper">
                    <div class="decoration-shape"></div>
                    <img src="https://images.unsplash.com/photo-1581092160562-40aa08e78837?q=80&w=1000" alt="About ASK Calibration">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="company-info-section py-5 bg-white">
    <div class="container py-lg-4">
        <h2 class="text-center mb-5 fw-bold" style="color: var(--brand-red);" data-aos="fade-up">
            <?php echo $curr_lang == 'th' ? 'บริษัท เอ เอส เค คาลิเบรชั่น แอนด์เซอร์วิส จำกัด' : 'ASK Calibration & Service Co., Ltd.'; ?>
        </h2>
        
        <div class="row g-4">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="info-card-modern shadow-sm">
                    <h3 class="fw-bold mb-3">
                        <?php echo $curr_lang == 'th' ? 'ประวัติและความเป็นมา' : 'Company History'; ?>
                    </h3>
                    <p class="text-muted">
                        <?php if($curr_lang == 'th'): ?>
                            บริษัทฯ ก่อตั้งขึ้นเมื่อวันที่ 16 กุมภาพันธ์ พ.ศ. 2561 ตั้งอยู่ที่จังหวัดสมุทรปราการ เราดำเนินธุรกิจด้านการให้บริการสอบเทียบเครื่องมือวัดในงานอุตสาหกรรม โดยมีความมุ่งมั่นในการดำเนินงานอย่างมืออาชีพ ด้วยบุคลากรที่มีความสามารถ เพื่อการให้บริการสอบเทียบที่มีคุณภาพ ถูกต้อง รวดเร็ว และเป็นไปตามมาตรฐานสากล ISO/IEC 17025
                        <?php else: ?>
                            Founded on February 16, 2018, located in Samut Prakan, we provide industrial calibration services with professional commitment. Our skilled personnel ensure high-quality, accurate, and fast calibration services in accordance with ISO/IEC 17025 international standards.
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <div class="col-lg-6" data-aos="fade-left">
                <div class="info-card-modern shadow-sm">
                    <h3 class="fw-bold mb-3">
                        <?php echo $curr_lang == 'th' ? 'บริการและคำปรึกษา' : 'Consultation & Support'; ?>
                    </h3>
                    <p class="text-muted">
                        <?php if($curr_lang == 'th'): ?>
                            เรายินดีที่จะให้คำปรึกษาปัญหาการใช้เครื่องมือวัดทั้งก่อนและหลังการขาย มุ่งมั่นที่จะพัฒนาคุณภาพการให้บริการและปรับปรุงระบบการบริหารงานอย่างต่อเนื่อง เพื่อสร้างความพึงพอใจสูงสุดให้กับลูกค้าภายใต้หลักการที่ว่า "คุณคือลูกค้าที่มีคุณค่าสำหรับเราเสมอ"
                        <?php else: ?>
                            We are happy to provide consultation on instrument usage both before and after-sales. We strive to continuously develop our service quality and management systems to ensure maximum customer satisfaction under our core principle: "You are always our valuable customer."
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="quote-section py-5 text-center bg-light" data-aos="zoom-in">
    <div class="container py-5">
        <div class="quote-icon mb-4">
            <i class="fas fa-quote-right"></i>
        </div>
        <h2 class="fw-bold text-danger mb-2" style="font-size: clamp(1.8rem, 4vw, 2.8rem);">
            You are always our valuable customer
        </h2>
        <h4 class="fw-normal text-danger">คุณคือลูกค้าที่มีคุณค่าสำหรับเราเสมอ</h4>
    </div>
</section>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 1000, once: true });
</script>

<?php include 'includes/footer.php'; ?>