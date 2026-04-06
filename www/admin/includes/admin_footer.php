<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// 1. ตั้งค่าเสียงแจ้งเตือน (แนะนำให้หาไฟล์เสียง mp3 สั้นๆ เช่น เสียงแชทเฟสบุ๊ค มาใส่ไว้ในโฟลเดอร์ assets/sounds/)
// ถ้านายยังไม่มีไฟล์เสียง ให้ใช้ลิงก์เสียงตัวอย่างนี้ไปก่อนได้ครับ
// ใช้เสียง MP3 ป๊อปอัปใสๆ แทน
const notifSound = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');

// 2. ตั้งค่ารูปแบบป็อปอัป (Toast) ให้เด้งมุมขวาบนแบบไม่บังหน้าจอ
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 5000, // โชว์ 5 วินาทีแล้วหายไปเอง
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

// 3. ฟังก์ชันแอบเช็คข้อมูลหลังบ้าน
function checkAdminAlerts() {
    fetch('action/check_alerts.php')
    .then(response => response.json())
    .then(data => {
        if(data && data.length > 0) {
            // ถ้ามีแจ้งเตือนใหม่ ให้วนลูปโชว์ทีละอัน
            data.forEach(alert => {
                // เล่นเสียง!
                notifSound.play().catch(error => {
                    console.log("เบราว์เซอร์บล็อกเสียงอัตโนมัติ ต้องคลิกที่หน้าเว็บก่อน 1 ครั้งถึงจะมีเสียงครับ");
                });
                
                // เด้งป็อปอัป!
                Toast.fire({
                    icon: 'info',
                    title: alert.title,
                    text: alert.message
                });
            });
        }
    })
    .catch(error => console.error('Error fetching alerts:', error));
}

// 4. สั่งให้ฟังก์ชันเช็คข้อมูล ทำงานทุกๆ 3 วินาที (3000 มิลลิวินาที)
setInterval(checkAdminAlerts, 3000);
</script>