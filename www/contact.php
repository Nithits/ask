<?php 
// 1. เรียกใช้ไฟล์ภาษาและ Header
include_once 'includes/language_setup.php'; 
include 'includes/header.php'; 

// ตรวจสอบภาษาปัจจุบัน
$curr_lang = $_SESSION['lang'] ?? 'th';
?>

<section class="contact-hero-clean">
    <div class="container">
        <h1 class="hero-title">
            <?php echo $curr_lang == 'th' ? 'ติดต่อเราได้<span>เสมอ</span>' : 'Always Stay <span>Connected</span>'; ?>
        </h1>
        <p class="mt-4 text-muted fw-bold mx-auto" style="max-width: 600px;">
    <p class="mt-4 text-muted font-weight: 500; mx-auto fs-5" style="max-width: 600px;">
    <?php echo $curr_lang == 'th' ? 'ยินดีให้คำปรึกษาและตอบข้อซักถามทุกรายละเอียดเกี่ยวกับการสอบเทียบเครื่องมือวัด' : 'We provide consultation and answer all questions regarding the comparison of measurement instruments.'; ?>
</p>
</section>

<section class="contact-main py-5 bg-light">
    <div class="container py-4">
        <div class="contact-card-wrap bg-white shadow-lg">
            <div class="row g-0">
                <div class="col-lg-5">
                    <div class="contact-info-side h-100">
                        <h3 class="fw-bold mb-5"><?php echo $curr_lang == 'th' ? 'ข้อมูลติดต่อ' : 'Contact Information'; ?></h3>
                        
                        <div class="info-item d-flex mb-4">
                            <div class="info-icon me-3 text-warning fs-4"><i class="fas fa-map-marker-alt"></i></div>
                            <div class="info-text">
                                <h5 class="mb-1"><?php echo $curr_lang == 'th' ? 'ที่ตั้งสำนักงาน' : 'Office Location'; ?></h5>
                                <p class="opacity-75 small">99/579 หมู่ที่ 1 ต.ศีรษะจรเข้น้อย อ.บางเสาธง จ.สมุทรปราการ 10570</p>
                            </div>
                        </div>

                        <div class="info-item d-flex mb-4">
                            <div class="info-icon me-3 text-warning fs-4"><i class="fas fa-phone-alt"></i></div>
                            <div class="info-text">
                                <h5 class="mb-1"><?php echo $curr_lang == 'th' ? 'สายด่วนบริการ' : 'Hotline'; ?></h5>
                                <p class="opacity-75">+66 87 013 8616</p>
                            </div>
                        </div>

                        <div class="info-item d-flex mb-4">
                            <div class="info-icon me-3 text-warning fs-4"><i class="fas fa-envelope"></i></div>
                            <div class="info-text">
                                <h5 class="mb-1"><?php echo $curr_lang == 'th' ? 'อีเมล' : 'Email Address'; ?></h5>
                                <p class="opacity-75">sale1_ask@askcalibration.com</p>
                            </div>
                        </div>

                        <div class="mt-5 pt-4 border-top border-secondary">
                            <p class="small opacity-50 mb-3 text-uppercase fw-light" style="letter-spacing: 2px;">Follow Us</p>
                            <div class="d-flex gap-2">
                                <a href="https://www.facebook.com/ASKcal579" target="_blank" class="text-white opacity-75 hover-opacity-100"><i class="fab fa-facebook-f fa-lg"></i></a>
                                <a href="https://page.line.me/537owswx" target="_blank" class="text-white opacity-75 ms-3"><i class="fab fa-line fa-lg"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="contact-form-side">
                        <h3 class="fw-bold mb-4"><?php echo $curr_lang == 'th' ? 'ส่งข้อความถึงเรา' : 'Send us a Message'; ?></h3>
                        <form action="process_contact.php" method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label"><?php echo $curr_lang == 'th' ? 'ชื่อ - นามสกุล *' : 'Full Name *'; ?></label>
                                    <input type="text" class="form-control" name="name" placeholder="<?php echo $curr_lang == 'th' ? 'กรุณากรอกชื่อ' : 'Enter your name'; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?php echo $curr_lang == 'th' ? 'อีเมล *' : 'Email Address *'; ?></label>
                                    <input type="email" class="form-control" name="email" placeholder="email@example.com" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?php echo $curr_lang == 'th' ? 'เบอร์โทรศัพท์' : 'Phone Number'; ?></label>
                                    <input type="tel" class="form-control" name="tel" placeholder="08X-XXX-XXXX">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?php echo $curr_lang == 'th' ? 'หัวข้อติดต่อ' : 'Subject'; ?></label>
                                    <input type="text" class="form-control" name="subject" placeholder="<?php echo $curr_lang == 'th' ? 'ระบุหัวข้อ' : 'Enter subject'; ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label"><?php echo $curr_lang == 'th' ? 'ข้อความรายละเอียด *' : 'Message Details *'; ?></label>
                                    <textarea class="form-control" name="message" rows="5" placeholder="<?php echo $curr_lang == 'th' ? 'พิมพ์ข้อความที่นี่...' : 'Type your message here...'; ?>" required></textarea>
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-send-message">
                                        <?php echo $curr_lang == 'th' ? 'ส่งข้อความ' : 'Send Message'; ?> <i class="fas fa-paper-plane ms-2"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="map-container mt-5 shadow-sm" style="border-radius: 25px; overflow: hidden; border: 8px solid #fff;">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3876.3216812852274!2d100.7905698758897!3d13.705021298457631!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x311d63c9d1ec7761%3A0x7f5e50a5daf242ae!2sASK%20Calibration%20%26%20Service!5e0!3m2!1sth!2sth!4v1710000000000!5m2!1sth!2sth" 
                width="100%" 
                height="450" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if(isset($_SESSION['status']) && $_SESSION['status'] != ''): ?>
<script>
    Swal.fire({
        title: "<?php echo ($_SESSION['status'] == 'success') ? ($curr_lang == 'th' ? 'สำเร็จ!' : 'Success!') : ($curr_lang == 'th' ? 'ผิดพลาด!' : 'Error!'); ?>",
        text: "<?php echo $_SESSION['message']; ?>",
        icon: "<?php echo $_SESSION['status']; ?>",
        confirmButtonText: "<?php echo $curr_lang == 'th' ? 'ตกลง' : 'OK'; ?>",
        confirmButtonColor: "#E31E24"
    });
</script>
<?php 
    unset($_SESSION['status']);
    unset($_SESSION['message']);
endif; 
?>

<?php include 'includes/footer.php'; ?>