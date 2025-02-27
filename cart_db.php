<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

require_once 'config/db.php';

// ✅ เช็คการล็อกอินก่อนเพิ่มสินค้า
if (isset($_GET['add'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "กรุณาเข้าสู่ระบบก่อนเพิ่มสินค้าในตะกร้า";
        header("location: login.php");
        exit();
    }

    $product_id = intval($_GET['add']);

    // เช็คจำนวนสินค้าคงเหลือในฐานข้อมูล
    $stmt = $conn->prepare("SELECT stock_quantity FROM products WHERE id = :product_id");
    $stmt->bindParam(":product_id", $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product && $product['stock_quantity'] > 0) {
        if (isset($_SESSION['cart'][$product_id])) {
            // ตรวจสอบจำนวนสินค้าที่มีในตะกร้าและในสต็อก
            if ($_SESSION['cart'][$product_id] < $product['stock_quantity']) {
                $_SESSION['cart'][$product_id]++;
            } else {
                $_SESSION['error'] = "จำนวนสินค้าในตะกร้ามีไม่เพียงพอ";
            }
        } else {
            $_SESSION['cart'][$product_id] = 1;
        }

        $_SESSION['success'] = "เพิ่มสินค้าลงตะกร้าเรียบร้อย!";
    } else {
        $_SESSION['error'] = "สินค้านี้หมดแล้ว";
    }

    header("location: cart.php");
    exit();
}

// ✅ ตรวจสอบว่าตะกร้าสินค้า ($_SESSION['cart']) มีอยู่หรือไม่
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ✅ ลบสินค้าออกจาก $_SESSION['cart']
if (isset($_GET['remove'])) {
    $product_id = intval($_GET['remove']);

    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }

    $_SESSION['success'] = "ลบสินค้าจากตะกร้าเรียบร้อย!";
    header("location: cart.php");
    exit();
}
?>
