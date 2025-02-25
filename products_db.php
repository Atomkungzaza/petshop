<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

// 1) ตรวจสอบว่ามี category_id ส่งมาหรือไม่
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

// 2) ดึงชื่อหมวดหมู่ (เช็คเฉพาะฟิลด์ name, id)
$category_stmt = $conn->prepare("
    SELECT id, name 
    FROM categories 
    WHERE id = :category_id
");
$category_stmt->bindParam(":category_id", $category_id, PDO::PARAM_INT);
$category_stmt->execute();
$category = $category_stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    die("ไม่พบหมวดหมู่ที่คุณเลือก");
}

// 3) ดึงสินค้าตามหมวดหมู่ (รวมสินค้าที่หมดสต็อก)
$product_stmt = $conn->prepare("
    SELECT 
        id, 
        name, 
        description,
        size,
        stock_quantity,
        price,
        image_url,
        CASE 
            WHEN stock_quantity = 0 THEN 1 
            ELSE 0 
        END AS is_out_of_stock
    FROM products 
    WHERE category_id = :category_id 
    ORDER BY id ASC
");
$product_stmt->bindParam(":category_id", $category_id, PDO::PARAM_INT);
$product_stmt->execute();
$products = $product_stmt->fetchAll(PDO::FETCH_ASSOC);

// 4) ดึงโปรโมชั่นสำหรับแต่ละสินค้า
foreach ($products as $index => $product) {
    $promotion_stmt = $conn->prepare("
        SELECT p.discount_percentage 
        FROM promotions p
        JOIN product_promotions pp ON p.id = pp.promotion_id
        WHERE pp.product_id = :product_id 
          AND p.start_date <= CURDATE() 
          AND p.end_date >= CURDATE()
    ");
    $promotion_stmt->bindParam(":product_id", $product['id'], PDO::PARAM_INT);
    $promotion_stmt->execute();
    $promotion = $promotion_stmt->fetch(PDO::FETCH_ASSOC);

    // คำนวณราคาหลังส่วนลด
    $discounted_price = $product['price'];
    if ($promotion) {
        $discount_percentage = $promotion['discount_percentage'];
        $discounted_price = $product['price'] * (1 - $discount_percentage / 100);
    }

    // อัปเดตข้อมูลใน array
    $products[$index]['discounted_price'] = $discounted_price;
}
?>
