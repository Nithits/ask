<?php 
// 1. เรียกใช้ระบบภาษาและ Header
include_once 'includes/language_setup.php'; 
include 'includes/header.php'; 

// ดึงภาษาปัจจุบัน
$curr_lang = $_SESSION['lang'] ?? 'th';
$status = isset($_GET['status']) ? $_GET['status'] : '';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="quote-page">
    <div class="quote-hero">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3 animate__animated animate__fadeInDown">
                <?php echo $curr_lang == 'th' ? 'ขอใบเสนอราคา' : 'Request a quote'; ?>
            </h1>
            <p class="lead opacity-90 fw-bold">
                <?php echo $curr_lang == 'th' ? 'บริการสอบเทียบเครื่องมือวัดมาตรฐานสากล รวดเร็ว แม่นยำ และเป็นมืออาชีพ' : 'Professional calibration services. Fast, accurate, and reliable.'; ?>
            </p>
        </div>
    </div>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="quote-card p-4 p-md-5">
                    <form action="action/submit_quote.php" method="POST" enctype="multipart/form-data">
                        
                        <div class="form-section-title">
                            <i class="fas fa-user-tie me-2"></i> <?php echo $curr_lang == 'th' ? 'ข้อมูลผู้ติดต่อ' : 'Contact Information'; ?>
                        </div>
                        
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label"><?php echo $curr_lang == 'th' ? 'ชื่อบริษัท / องค์กร' : 'Company Name'; ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="company_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?php echo $curr_lang == 'th' ? 'ชื่อผู้ติดต่อ' : 'Contact Person'; ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="contact_person" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?php echo $curr_lang == 'th' ? 'อีเมล' : 'Email Address'; ?> <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" placeholder="example@company.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?php echo $curr_lang == 'th' ? 'เบอร์โทรศัพท์' : 'Phone Number'; ?> <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="phone" placeholder="08X-XXX-XXXX" required>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label"><?php echo $curr_lang == 'th' ? 'หมายเหตุ / รายละเอียดเพิ่มเติม' : 'Remarks / Additional Details'; ?></label>
                                <textarea class="form-control" name="message" rows="4" placeholder="<?php echo $curr_lang == 'th' ? 'ระบุรายละเอียดเพิ่มเติม หรือข้อกำหนดพิเศษ (ถ้ามี)' : 'Enter more details or special requirements (if any)'; ?>"></textarea>
                            </div>
                        </div>

                        <div class="form-section-title">
                            <i class="fas fa-file-medical me-2"></i> <?php echo $curr_lang == 'th' ? 'รายการเครื่องมือ' : 'Equipment Details'; ?>
                        </div>

                        <div class="mb-4">
                            <label class="form-label"><?php echo $curr_lang == 'th' ? 'แนบไฟล์รายการเครื่องมือ (Equipment List)' : 'Upload Equipment List (File)'; ?> <span class="text-danger">*</span></label>
                            <div class="file-upload-wrapper position-relative" id="uploadArea">
                                <i class="fas fa-cloud-upload-alt mb-3" style="font-size: 2.5rem; color: var(--q-orange);"></i>
                                <h5 class="mb-1"><?php echo $curr_lang == 'th' ? 'คลิกเพื่อเลือกไฟล์ หรือลากไฟล์มาวางที่นี่' : 'Click to select or drag and drop file here'; ?></h5>
                                <p class="small text-muted mb-0" id="fileName">
                                    <?php echo $curr_lang == 'th' ? 'รองรับไฟล์ .pdf, .xls, .xlsx, .doc, .docx (สูงสุด 5MB)' : 'Supports .pdf, .xls, .xlsx, .doc, .docx (Max 5MB)'; ?>
                                </p>
                                <input type="file" name="equipment_file" id="fileInput" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" accept=".pdf,.doc,.docx,.xls,.xlsx" style="cursor: pointer;" required>
                            </div>
                        </div>

                        <div class="text-center mt-5">
                            <button type="submit" class="btn btn-submit-quote px-5 py-3">
                                <?php echo $curr_lang == 'th' ? 'ส่งข้อมูลขอใบเสนอราคา' : 'Submit Request'; ?> <i class="fas fa-paper-plane ms-2"></i>
                            </button>
                            <p class="small text-muted mt-3 fw-light">
                                <?php echo $curr_lang == 'th' ? 'เราจะดำเนินการจัดทำใบเสนอราคาและติดต่อกลับภายใน 24 ชม.' : 'We will process your request and contact you within 24 hours.'; ?>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. แสดงชื่อไฟล์ทันทีที่เลือก (คงเดิม)
    document.getElementById('fileInput').onchange = function() {
        if(this.files[0]) {
            var name = this.files[0].name;
            var label = "<?php echo $curr_lang == 'th' ? 'ไฟล์ที่เลือก: ' : 'Selected file: '; ?>";
            document.getElementById('fileName').innerHTML = label + "<strong class='text-danger'>" + name + "</strong>";
            document.getElementById('uploadArea').style.borderColor = "#F58220";
            document.getElementById('uploadArea').style.background = "#fffcf9";
        }
    };

    // 2. แสดง SweetAlert และล้าง URL ทันที
    document.addEventListener('DOMContentLoaded', function() {
        const status = '<?php echo $status; ?>';
        const isTh = '<?php echo $curr_lang; ?>' === 'th';

        if (status) {
            // --- ส่วนที่เพิ่ม: ล้าง ?status=... ออกจาก URL บนแถบบราวเซอร์ ---
            if (window.history.replaceState) {
                // สร้าง URL ใหม่ที่ไม่มี query string
                const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({path: cleanUrl}, '', cleanUrl);
            }
            // -------------------------------------------------------

            if (status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: isTh ? 'ส่งข้อมูลสำเร็จ!' : 'Submitted Successfully!',
                    text: isTh ? 'เราได้รับข้อมูลของคุณแล้ว เจ้าหน้าที่จะติดต่อกลับโดยเร็วที่สุด' : 'We have received your request. Our staff will contact you shortly.',
                    confirmButtonColor: '#E31E24',
                    confirmButtonText: isTh ? 'ตกลง' : 'OK'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: isTh ? 'เกิดข้อผิดพลาด' : 'An Error Occurred',
                    text: isTh ? 'กรุณาตรวจสอบความถูกต้องของข้อมูลหรือขนาดไฟล์ และลองใหม่อีกครั้ง' : 'Please check your information or file size and try again.',
                    confirmButtonColor: '#E31E24',
                    confirmButtonText: isTh ? 'ลองอีกครั้ง' : 'Try Again'
                });
            }
        }
    });
</script>
<?php include 'includes/footer.php'; ?>