<?php
/**
 * ASK Calibration - Language Setup
 * จัดการระบบสลับภาษา TH/EN โดยใช้ Session
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. ตรวจสอบการเปลี่ยนภาษาผ่าน URL (เช่น ?lang=en)
if (isset($_GET['lang'])) {
    $allowed_langs = ['th', 'en'];
    $requested_lang = strtolower($_GET['lang']);
    
    if (in_array($requested_lang, $allowed_langs)) {
        $_SESSION['lang'] = $requested_lang;
    }
}

// 2. กำหนดภาษาเริ่มต้น (Default) เป็นไทย หากยังไม่ได้เลือก
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'th';
}

// 3. ดึงไฟล์ภาษามาใช้งาน โดยใช้ Absolute Path เพื่อป้องกัน Error ใน Docker
$current_lang = $_SESSION['lang'];
$base_dir = __DIR__; // อ้างอิงโฟลเดอร์ปัจจุบัน (includes)
$lang_file = $base_dir . "/lang/" . $current_lang . ".php";

// 4. ตรวจสอบไฟล์ และโหลดข้อมูล
if (file_exists($lang_file)) {
    require_once($lang_file);
} else {
    // Fallback: ถ้าหาไฟล์ที่เลือกไม่เจอ ให้ไปดึง th.php
    $fallback = $base_dir . "/lang/th.php";
    if (file_exists($fallback)) {
        require_once($fallback);
    } else {
        // กรณีเลวร้ายที่สุด (ไม่มีไฟล์เลย) ให้สร้าง Array เปล่าไว้ป้องกันหน้าเว็บพัง
        $lang = [];
    }
}

/**
 * ฟังก์ชันช่วยเหลือ (Helper Function) สำหรับเรียกใช้คำแปล
 * วิธีใช้: echo _l('home');
 */
function _l($key) {
    global $lang;
    return isset($lang[$key]) ? $lang[$key] : $key;
}