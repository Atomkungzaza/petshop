<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $coupon_id = intval($_POST['coupon_id']);
    $user_id = $_SESSION['user_id'];

    // เช็คว่า user ใช้คูปองนี้ไปแล้วหรือยัง
    $check_stmt = $conn->prepare("
        SELECT COUNT(*) FROM user_coupons 
        WHERE user_id = :user_id AND coupon_id = :coupon_id
    ");
    $check_stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $check_stmt->bindParam(":coupon_id", $coupon_id, PDO::PARAM_INT);
    $check_stmt->execute();
    $count = $check_stmt->fetchColumn();

    if ($count > 0) {
        $_SESSION['error'] = "คุณใช้คูปองนี้ไปแล้ว";
    } else {
        // เพิ่มคูปองให้กับผู้ใช้
        $insert_stmt = $conn->prepare("
            INSERT INTO user_coupons (user_id, coupon_id) 
            VALUES (:user_id, :coupon_id)
        ");
        $insert_stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $insert_stmt->bindParam(":coupon_id", $coupon_id, PDO::PARAM_INT);
        $insert_stmt->execute();

        $_SESSION['success'] = "คุณใช้คูปองสำเร็จ!";
    }

    header("Location: products.php");
    exit();
}
?>
