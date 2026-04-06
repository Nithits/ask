<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// เช็คว่าลูกค้าล็อกอินหรือยัง?
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    $_SESSION['error_msg'] = "กรุณาเข้าสู่ระบบ หรือสมัครสมาชิกก่อนเข้าใช้งานหน้านี้ครับ";
    header("Location: login.php");
    exit();
}

require_once 'config/db.php'; 
include_once 'includes/language_setup.php'; 
include 'includes/header.php'; 

$curr_lang = $_SESSION['lang'] ?? 'th';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// ------------------------------------------------------------------------
// 🛠️ ดึงข้อมูลรายการเครื่องมือ, ราคากลาง และ รูปภาพ (image)
// ------------------------------------------------------------------------
$standard_items = [];
try {
    $stmt = $pdo->prepare("SELECT id, title_th AS name_th, title_en AS name_en, price, image FROM services ORDER BY title_th ASC");
    $stmt->execute();
    $standard_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // ดัก Error เงียบๆ
}

$items_json = json_encode($standard_items);
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* ตกแต่ง Select2 ให้เข้ากับ Bootstrap 5 */
    .select2-container .select2-selection--single {
        height: auto;
        padding: 8px 12px;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        background-color: #f8f9fa;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        top: 50%;
        transform: translateY(-50%);
        right: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: normal;
        padding-left: 0;
    }
    .select-img-preview {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 6px;
        margin-right: 12px;
        border: 1px solid #dee2e6;
        background: #fff;
    }
    .select-icon-preview {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e9ecef;
        border-radius: 6px;
        margin-right: 12px;
        border: 1px solid #dee2e6;
        color: #adb5bd;
    }
    .select-item-wrapper {
        display: flex;
        align-items: center;
        font-weight: 500;
    }
</style>

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
                                <input type="text" class="form-control" name="company_name" value="<?php echo htmlspecialchars($_SESSION['user_company'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?php echo $curr_lang == 'th' ? 'ชื่อผู้ติดต่อ' : 'Contact Person'; ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="contact_person" value="<?php echo htmlspecialchars($_SESSION['user_fullname'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?php echo $curr_lang == 'th' ? 'อีเมล' : 'Email Address'; ?> <span class="text-danger">*</span></label>
                                <<input type="email" class="form-control" name="email" value="<?php echo $_SESSION['user_email'] ?? ''; ?>" readonly required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?php echo $curr_lang == 'th' ? 'เบอร์โทรศัพท์' : 'Phone Number'; ?> <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="phone" required>
                            </div>
                        </div>

                        <div class="form-section-title d-flex justify-content-between align-items-center">
                            <div><i class="fas fa-tools me-2"></i> <?php echo $curr_lang == 'th' ? 'เลือกรายการเครื่องมือ' : 'Select Instruments'; ?></div>
                            <button type="button" class="btn btn-sm btn-outline-primary fw-bold" onclick="addRow()">
                                <i class="fas fa-plus"></i> <?php echo $curr_lang == 'th' ? 'เพิ่มรายการ' : 'Add Item'; ?>
                            </button>
                        </div>

                        <div class="table-responsive mb-4" style="overflow: visible;"> <table class="table table-bordered align-middle" id="itemsTable">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th width="45%"><?php echo $curr_lang == 'th' ? 'รายการเครื่องมือ' : 'Instrument'; ?></th>
                                        <th width="15%"><?php echo $curr_lang == 'th' ? 'ราคา/หน่วย' : 'Unit Price'; ?></th>
                                        <th width="15%"><?php echo $curr_lang == 'th' ? 'จำนวน' : 'Qty'; ?></th>
                                        <th width="15%"><?php echo $curr_lang == 'th' ? 'รวม' : 'Total'; ?></th>
                                        <th width="10%"><i class="fas fa-cog"></i></th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody">
                                    </tbody>
                                <tfoot>
                                    <tr class="bg-light">
                                        <td colspan="3" class="text-end fw-bold fs-5"><?php echo $curr_lang == 'th' ? 'ราคาประเมินเบื้องต้น (Estimated Total) :' : 'Estimated Total :'; ?></td>
                                        <td class="text-end fw-bold fs-5 text-danger"><span id="grandTotal">0.00</span></td>
                                        <td class="text-center fw-bold">THB</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="form-section-title mt-5">
                            <i class="fas fa-file-medical me-2"></i> <?php echo $curr_lang == 'th' ? 'แนบไฟล์รายการเพิ่มเติม (ถ้ามี)' : 'Upload Additional List (Optional)'; ?>
                        </div>

                        <div class="mb-4">
                            <div class="file-upload-wrapper position-relative" id="uploadArea">
                                <i class="fas fa-cloud-upload-alt mb-3" style="font-size: 2.5rem; color: var(--q-orange);"></i>
                                
                                <h5 class="mb-1">
                                    <?php echo $curr_lang == 'th' ? 'คลิกเพื่อเลือกไฟล์ หรือลากไฟล์มาวางที่นี่ <span class="text-secondary">(ไม่บังคับ)</span>' : 'Click to select or drop file here <span class="text-secondary">(Optional)</span>'; ?>
                                </h5>
                                
                                <p class="small text-muted mb-0" id="fileName">
                                    <?php echo $curr_lang == 'th' ? 'รองรับไฟล์ .pdf, .xls, .xlsx, .doc, .docx' : 'Supports .pdf, .xls, .xlsx, .doc, .docx'; ?>
                                </p>
                                
                                <input type="file" name="equipment_file" id="fileInput" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" accept=".pdf,.doc,.docx,.xls,.xlsx" style="cursor: pointer;">
                            </div>
                        </div>
                        
                        <div class="col-12 mb-5">
                            <label class="form-label"><?php echo $curr_lang == 'th' ? 'หมายเหตุ / รายละเอียดเพิ่มเติม' : 'Remarks / Additional Details'; ?></label>
                            <textarea class="form-control" name="message" rows="3"></textarea>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-warning px-5 py-3 fw-bold rounded-pill shadow-sm fs-5">
                                <?php echo $curr_lang == 'th' ? 'ส่งข้อมูลขอใบเสนอราคา' : 'Submit Request'; ?> <i class="fas fa-paper-plane ms-2"></i>
                            </button>
                            <p class="small text-muted mt-3 fw-light">
                                <?php echo $curr_lang == 'th' ? '*ราคาข้างต้นเป็นการประเมินเบื้องต้นเท่านั้น เราจะติดต่อกลับเพื่อยืนยันราคาที่แน่นอน' : '*This is an estimated price. We will contact you for a formal quotation.'; ?>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const itemsData = <?php echo $items_json; ?>;
    const isTh = '<?php echo $curr_lang; ?>' === 'th';

    // ฟังก์ชันสร้างตัวเลือก (Option)
    function getOptionsHtml() {
        let options = `<option value=""></option>`; // ปล่อยว่างไว้ให้ Select2 จัดการ Placeholder
        
        if(itemsData && itemsData.length > 0) {
            itemsData.forEach(item => {
                const name = isTh ? item.name_th : item.name_en;
                const img = item.image ? item.image : ''; 
                options += `<option value="${item.id}" data-price="${item.price}" data-image="${img}">${name}</option>`;
            });
        }
        return options;
    }

    // ฟังก์ชันตกแต่งรายการใน Select2 (สร้าง HTML โชว์รูป)
    function formatInstrument(state) {
        if (!state.id) { return state.text; } // ถ้าเป็น Placeholder ไม่ต้องโชว์รูป
        
        const imageUrl = state.element.getAttribute('data-image');
        const baseUrl = "assets/uploads/services/";
        
        // ถ้ามีรูปโชว์รูป ถ้าไม่มีโชว์ไอคอนกล้องจุลทรรศน์
        const imageHtml = (imageUrl && imageUrl !== '') 
            ? `<img src="${baseUrl}${imageUrl}" class="select-img-preview" onerror="this.src='assets/images/default.png'" />` 
            : `<div class="select-icon-preview"><i class="fas fa-microscope"></i></div>`;
            
        return $(`<div class="select-item-wrapper">${imageHtml} <span>${state.text}</span></div>`);
    }

    // ฟังก์ชันเพิ่มแถวใหม่
    function addRow() {
        const tbody = document.getElementById('tableBody');
        const tr = document.createElement('tr');
        
        tr.innerHTML = `
            <td>
                <select class="form-select item-select" name="item_id[]" required style="width: 100%;">
                    ${getOptionsHtml()}
                </select>
            </td>
            <td>
                <input type="number" class="form-control text-end item-price bg-white" name="item_price[]" value="0" readonly>
            </td>
            <td>
                <input type="number" class="form-control text-center item-qty" name="item_qty[]" value="1" min="1" required onchange="updateRow(this)" onkeyup="updateRow(this)">
            </td>
            <td>
                <input type="text" class="form-control text-end item-total bg-white" value="0.00" readonly>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);

        // เปิดใช้งาน Select2 บนแถวที่เพิ่งสร้างใหม่
        $(tr).find('.item-select').select2({
            templateResult: formatInstrument,      // ตอนกดกาง Dropdown
            templateSelection: formatInstrument,   // ตอนที่เลือกเสร็จแล้ว
            placeholder: isTh ? '-- เลือกรายการเครื่องมือ --' : '-- Select Instrument --',
            allowClear: true
        }).on('change', function() {
            updateRow(this); // เมื่อเปลี่ยนสินค้า ให้คำนวณราคาใหม่
        });
    }

    // ฟังก์ชันอัปเดตราคา
    function updateRow(element) {
        const row = element.closest('tr');
        // ใช้ jQuery ค้นหา select
        const select = $(row).find('.item-select')[0];
        const priceInput = row.querySelector('.item-price');
        const qtyInput = row.querySelector('.item-qty');
        const totalInput = row.querySelector('.item-total');
        
        const selectedOption = select.options[select.selectedIndex];
        
        const price = selectedOption ? parseFloat(selectedOption.getAttribute('data-price') || 0) : 0;
        const qty = parseInt(qtyInput.value) || 0;
        const total = price * qty;

        priceInput.value = price;
        totalInput.value = total.toFixed(2);

        calculateGrandTotal(); 
    }

    function removeRow(btn) {
        btn.closest('tr').remove();
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let grandTotal = 0;
        const totalInputs = document.querySelectorAll('.item-total');
        totalInputs.forEach(input => {
            grandTotal += parseFloat(input.value) || 0;
        });
        document.getElementById('grandTotal').innerText = grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    // อัปโหลดไฟล์
    document.getElementById('fileInput').onchange = function() {
        if(this.files[0]) {
            var name = this.files[0].name;
            var label = isTh ? 'ไฟล์ที่เลือก: ' : 'Selected file: ';
            document.getElementById('fileName').innerHTML = label + "<strong class='text-danger'>" + name + "</strong>";
            document.getElementById('uploadArea').style.borderColor = "#F58220";
            document.getElementById('uploadArea').style.background = "#fffcf9";
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        addRow();

        const status = '<?php echo $status; ?>';
        if (status) {
            if (window.history.replaceState) {
                const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({path: cleanUrl}, '', cleanUrl);
            }

            if (status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: isTh ? 'ส่งข้อมูลสำเร็จ!' : 'Submitted Successfully!',
                    text: isTh ? 'เราได้รับข้อมูลของคุณแล้ว พร้อมราคาประเมินเบื้องต้น เจ้าหน้าที่จะติดต่อกลับเพื่อยืนยันอีกครั้ง' : 'We have received your request and preliminary estimation. Our staff will contact you shortly.',
                    confirmButtonColor: '#E31E24',
                    confirmButtonText: isTh ? 'ตกลง' : 'OK'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: isTh ? 'เกิดข้อผิดพลาด' : 'An Error Occurred',
                    text: isTh ? 'กรุณาตรวจสอบความถูกต้องของข้อมูล และลองใหม่อีกครั้ง' : 'Please check your information and try again.',
                    confirmButtonColor: '#E31E24',
                    confirmButtonText: isTh ? 'ลองอีกครั้ง' : 'Try Again'
                });
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>