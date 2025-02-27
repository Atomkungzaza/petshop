<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "กรุณาเข้าสู่ระบบก่อนทำการสั่งซื้อ";
    header("location: login.php");
    exit();
}

// ตรวจสอบว่ามีสินค้าในตะกร้าหรือไม่
if (empty($_SESSION['cart'])) {
    $_SESSION['error'] = "ไม่มีสินค้าในตะกร้า กรุณาเลือกสินค้าก่อน";
    header("location: cart.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$total_price = 0;
$order_items = [];

$ids = implode(',', array_keys($_SESSION['cart']));

try {
    // ดึงข้อมูลสินค้าพร้อมโปรโมชัน
    $stmt = $conn->prepare("
        SELECT p.*, 
               IFNULL(pr.discount_percentage, 0) AS discount_percentage 
        FROM products p
        LEFT JOIN product_promotions pp ON p.id = pp.product_id
        LEFT JOIN promotions pr ON pp.promotion_id = pr.id 
             AND CURDATE() BETWEEN pr.start_date AND pr.end_date
        WHERE p.id IN ($ids)
    ");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // คำนวณราคารวมและเตรียมรายการสินค้าในคำสั่งซื้อ
    foreach ($products as $product) {
        $product_id = $product['id'];
        $quantity = $_SESSION['cart'][$product_id];
        $original_price = $product['price'];
        $discount_percentage = isset($product['discount_percentage']) ? $product['discount_percentage'] : 0;
        $discount_amount = ($discount_percentage > 0) ? ($original_price * ($discount_percentage / 100)) : 0;
        $final_price = $original_price - $discount_amount;
        $subtotal = $final_price * $quantity;
        $total_price += $subtotal;
        $order_items[] = [
            'product_id' => $product_id,
            'quantity'   => $quantity,
            'price'      => $final_price
        ];
    }

    // บันทึกคำสั่งซื้อ (status เริ่มต้นเป็น 'pending')
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, status, created_at) VALUES (:user_id, :total_price, 'pending', NOW())");
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":total_price", $total_price);
    $stmt->execute();
    $order_id = $conn->lastInsertId();

    // บันทึกรายการสินค้าในคำสั่งซื้อ
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)");
    foreach ($order_items as $item) {
        $stmt->bindParam(":order_id", $order_id);
        $stmt->bindParam(":product_id", $item['product_id']);
        $stmt->bindParam(":quantity", $item['quantity']);
        $stmt->bindParam(":price", $item['price']);
        $stmt->execute();
    }

    // ลดจำนวนสินค้าในฐานข้อมูลหลังจากการสั่งซื้อ
    foreach ($order_items as $item) {
        $stmt = $conn->prepare("
            UPDATE products 
            SET stock_quantity = stock_quantity - :quantity
            WHERE id = :product_id
        ");
        $stmt->bindParam(":quantity", $item['quantity']);
        $stmt->bindParam(":product_id", $item['product_id']);
        $stmt->execute();
    }

    // ล้างตะกร้าหลังจากสั่งซื้อสำเร็จ
    unset($_SESSION['cart']);

    $_SESSION['success'] = "ทำรายการสั่งซื้อสำเร็จ! หมายเลขออเดอร์ของคุณคือ #" . $order_id;
    header("location: orders.php");
    exit();

} catch (PDOException $e) {
    $_SESSION['error'] = "เกิดข้อผิดพลาดในการสั่งซื้อ: " . $e->getMessage();
    header("location: cart.php");
    exit();
}
?>
