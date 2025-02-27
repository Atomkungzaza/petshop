<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

$user_id = $_SESSION['user_id'] ?? 0;
$coupon_id = isset($_POST['coupon_id']) ? intval($_POST['coupon_id']) : 0;

if ($user_id && $coupon_id) {
    // บันทึกคูปองที่เลือกลงในฐานข้อมูล
    $stmt = $conn->prepare("INSERT INTO user_coupons (user_id, coupon_id, created_at) VALUES (:user_id, :coupon_id, NOW())");
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->bindParam(":coupon_id", $coupon_id, PDO::PARAM_INT);
    $stmt->execute();
}

header("Location: products.php");
exit();
