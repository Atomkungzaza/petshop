<!-- โค้ดในไฟล์ order_db.php -->

<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

// ✅ ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "กรุณาเข้าสู่ระบบก่อน";
    header("location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ ดึงข้อมูลคำสั่งซื้อของผู้ใช้
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>