<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';


$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

$category_stmt = $conn->prepare("SELECT id, name FROM categories WHERE id = :category_id");
$category_stmt->bindParam(":category_id", $category_id, PDO::PARAM_INT);
$category_stmt->execute();
$category = $category_stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    die("ไม่พบหมวดหมู่ที่คุณเลือก");
}

$product_stmt = $conn->prepare("
    SELECT id, name, description, size, stock_quantity, price, image_url 
    FROM products WHERE category_id = :category_id ORDER BY id ASC
");
$product_stmt->bindParam(":category_id", $category_id, PDO::PARAM_INT);
$product_stmt->execute();
$products = $product_stmt->fetchAll(PDO::FETCH_ASSOC);

// คำนวณราคาหลังส่วนลด
foreach ($products as $index => $product) {
    $promotion_stmt = $conn->prepare("
        SELECT p.discount_percentage FROM promotions p
        JOIN product_promotions pp ON p.id = pp.promotion_id
        WHERE pp.product_id = :product_id 
        AND p.start_date <= CURDATE() AND p.end_date >= CURDATE()
    ");
    $promotion_stmt->bindParam(":product_id", $product['id'], PDO::PARAM_INT);
    $promotion_stmt->execute();
    $promotion = $promotion_stmt->fetch(PDO::FETCH_ASSOC);

    $discounted_price = $product['price'];
    if ($promotion) {
        $discount_percentage = $promotion['discount_percentage'];
        $discounted_price = $product['price'] * (1 - $discount_percentage / 100);
    }

    $products[$index]['discounted_price'] = $discounted_price;
}
?>
