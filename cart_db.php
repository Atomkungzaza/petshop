<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

// ตรวจสอบการล็อกอินก่อนเพิ่มสินค้า
if (isset($_GET['add'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "กรุณาเข้าสู่ระบบก่อนเพิ่มสินค้าในตะกร้า";
        header("location: login.php");
        exit();
    }
    $product_id = intval($_GET['add']);
    $quantity = isset($_GET['quantity']) ? max(1, intval($_GET['quantity'])) : 1;
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    $_SESSION['success'] = "เพิ่มสินค้าลงตะกร้าเรียบร้อย!";
    header("location: cart.php");
    exit();
}

// ลบสินค้าจากตะกร้า (ถ้ามีการเรียกผ่าน GET)
if (isset($_GET['remove'])) {
    $product_id = intval($_GET['remove']);
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        $_SESSION['success'] = "ลบสินค้าจากตะกร้าเรียบร้อย!";
    }
    header("location: cart.php");
    exit();
}
?>
