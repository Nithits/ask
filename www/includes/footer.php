<?php
// ตรวจสอบภาษาปัจจุบัน (Fallback เป็น 'th')
$f_lang = $_SESSION['lang'] ?? 'th';
?>

<section class="qc-bar">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-4">
                <h2 class="qc-title">
                    <?php echo $f_lang == 'th' ? 'ช่องทางติดต่อที่รวดเร็ว' : 'Quick Contact'; ?>
                </h2>
            </div>
            <div class="col-lg-5">
                <p class="qc-desc">
                    <?php echo $f_lang == 'th' 
                        ? 'บริการสอบเทียบมาตรฐานสากล โดยบุคลากรที่มีความชำนาญ ติดตามผลงานหรือติดต่อเราได้หลายช่องทาง' 
                        : 'International standard calibration services by experts. Follow our updates or contact us through various channels.'; ?>
                </p>
            </div>
            <div class="col-lg-3 text-lg-end qc-social">
                <a href="https://www.facebook.com/ASKcal579" target="_blank" title="Facebook" class="btn-facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="https://page.line.me/537owswx" target="_blank" title="Line Official" class="btn-line"><i class="fab fa-line"></i></a>
            </div>
        </div>
    </div>
</section>

<footer class="main-footer">
    <div class="container">
        <div class="row gy-5">
            <div class="col-lg-4">
                <img src="assets/images/favicon.png" class="f-logo" alt="ASK Calibration Logo">
              
            </div>

            <div class="col-lg-4">
                <span class="f-h"><?php echo $f_lang == 'th' ? 'ข้อมูลการติดต่อ' : 'Contact Information'; ?></span>
                <ul class="f-links mb-4">
                    <li><i class="fas fa-phone-alt"></i> 087 013 8616</li>
                    <li><i class="fas fa-envelope-open-text"></i> Apisit_wan@askcalibration.com</li>
                    <li><i class="fas fa-headset"></i> Sale1_ask@askcalibration.com</li>
                </ul>
                
                <div class="d-flex gap-3 f-social-wrap">
                    <a href="https://page.line.me/537owswx" target="_blank" title="Line Official">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/4/41/LINE_logo.svg" class="f-social-icon" alt="Line Logo">
                    </a>
                    <a href="https://www.facebook.com/ASKcal579" target="_blank" title="Facebook">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/0/05/Facebook_Logo_%282019%29.png" class="f-social-icon" alt="Facebook Logo">
                    </a>
                </div>
            </div>

            <div class="col-lg-4">
                <span class="f-h"><?php echo $f_lang == 'th' ? 'สำนักงานใหญ่' : 'Head Office'; ?></span>
                <ul class="f-links">
                    <li>
                        <i class="fas fa-map-marker-alt"></i>
                        <span>
                            <?php echo $f_lang == 'th' 
                                ? 'บริษัท เอ เอส เค คาลิเบรชั่น แอนด์ เซอร์วิส จำกัด<br>99/579 หมู่ 1 ต.ศีรษะจรเข้น้อย อ.บางเสาธง จ.สมุทรปราการ 10570' 
                                : 'ASK Calibration and Service Co., Ltd.<br>99/579 Moo 1, Sisa Chorakhe Noi, Bang Sao Thong, Samut Prakan 10570'; ?>
                            <br><small class="mt-2 d-block opacity-75"><?php echo $f_lang == 'th' ? 'เลขประจำตัวผู้เสียภาษี' : 'Tax ID'; ?>: 0115561005048</small>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</footer>

<div class="copyright-bar">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8 text-center text-md-start mb-3 mb-md-0">
                &copy; 2026 <?php echo $f_lang == 'th' ? 'บริษัท เอ เอส เค คาลิเบรชั่น แอนด์ เซอร์วิส จำกัด' : 'ASK Calibration and Service Co., Ltd.'; ?>. 
                <?php echo $f_lang == 'th' ? 'สงวนสิทธิ์ทุกประการ' : 'All Rights Reserved.'; ?>
            </div>
            <div class="col-md-4 text-center text-md-end">
                <span id="back-to-top" class="btn-up" title="Back to top">
                    <i class="fas fa-chevron-up"></i>
                </span>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Smooth Scroll to top
    document.getElementById('back-to-top').onclick = () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };
</script>