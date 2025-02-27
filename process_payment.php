<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';


// ✅ ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "กรุณาเข้าสู่ระบบก่อนทำการสั่งซื้อ";
    header("location: login.php");
    exit();
}

// ✅ ตรวจสอบข้อมูลที่ส่งมาจากฟอร์มชำระเงิน
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order_id'], $_POST['total_price'])) {
    $order_id = intval($_POST['order_id']);
    $user_id = $_SESSION['user_id'];
    $amount = floatval($_POST['total_price']);
    $status = 'paid';

    try {
        $conn->beginTransaction();

        // ✅ บันทึกการชำระเงิน (ไม่มี payment_method)
        $stmt = $conn->prepare("INSERT INTO payments (order_id, user_id, amount, status, created_at) 
                                VALUES (:order_id, :user_id, :amount, :status, NOW())");
        $stmt->bindParam(":order_id", $order_id);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":amount", $amount);
        $stmt->bindParam(":status", $status);
        $stmt->execute();

        // ✅ อัปเดตสถานะ Order เป็น 'paid'
        $stmt = $conn->prepare("UPDATE orders SET status = :status WHERE id = :order_id");
        $stmt->bindParam(":order_id", $order_id);
        $stmt->bindParam(":status", $status);
        $stmt->execute();

        // ✅ ลดจำนวนสินค้าในฐานข้อมูลตามที่สั่งซื้อ
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            // ตรวจสอบจำนวนสินค้าที่จะลดในฐานข้อมูล
            $stmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - :quantity WHERE id = :product_id");
            $stmt->bindParam(":quantity", $quantity, PDO::PARAM_INT);
            $stmt->bindParam(":product_id", $product_id, PDO::PARAM_INT);
            $stmt->execute();
        }

        // ✅ เคลียร์ตะกร้าสินค้า
        unset($_SESSION['cart']);

        $conn->commit();
        $_SESSION['success'] = "ชำระเงินสำเร็จ!";
        header("location: orders.php");
        exit();
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการชำระเงิน: " . $e->getMessage();
        header("location: invoice.php?order_id=$order_id");
        exit();
    }
} else {
    $_SESSION['error'] = "ข้อมูลไม่ถูกต้อง";
    header("location: orders.php");
    exit();
}
?>
