<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

// ตรวจสอบว่ามี id ส่งมา
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$product_id) {
    die("ไม่พบสินค้าที่คุณเลือก");
}

// ดึงข้อมูลสินค้าจากฐานข้อมูล
$product_stmt = $conn->prepare("
    SELECT id, name, description, size, stock_quantity, price, image_url 
    FROM products 
    WHERE id = :product_id
");
$product_stmt->bindParam(":product_id", $product_id, PDO::PARAM_INT);
$product_stmt->execute();
$product = $product_stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("ไม่พบสินค้าที่คุณเลือก");
}

// ดึงโปรโมชั่นสินค้า
$promotion_stmt = $conn->prepare("
    SELECT p.discount_percentage 
    FROM promotions p
    JOIN product_promotions pp ON p.id = pp.promotion_id
    WHERE pp.product_id = :product_id 
      AND p.start_date <= CURDATE() 
      AND p.end_date >= CURDATE()
");
$promotion_stmt->bindParam(":product_id", $product_id, PDO::PARAM_INT);
$promotion_stmt->execute();
$promotion = $promotion_stmt->fetch(PDO::FETCH_ASSOC);

// คำนวณราคาหลังส่วนลด
$discounted_price = $product['price'];
if ($promotion) {
    $discount_percentage = $promotion['discount_percentage'];
    $discounted_price = $product['price'] * (1 - $discount_percentage / 100);
}

include 'layouts/header.php';
?>

<div class="container mt-4">
    <h2><?= htmlspecialchars($product['name']); ?></h2>
    <div class="row">
        <div class="col-md-6">
            <img src="uploads/products/<?= htmlspecialchars($product['image_url']); ?>" class="img-fluid" alt="<?= htmlspecialchars($product['name']); ?>">
        </div>
        <div class="col-md-6">
            <h4>รายละเอียดสินค้า</h4>
            <p><?= nl2br(htmlspecialchars($product['description'])); ?></p>

            <h4>ราคา</h4>
            <?php if ($discounted_price < $product['price']): ?>
                <h5 class="text-danger">
                    <del>ราคา <?= number_format($product['price'], 2); ?> บาท</del>
                </h5>
                <h5>ลดเหลือ <?= number_format($discounted_price, 2); ?> บาท</h5>
            <?php else: ?>
                <h5><?= number_format($product['price'], 2); ?> บาท</h5>
            <?php endif; ?>

            <p><strong>ขนาด:</strong> <?= htmlspecialchars($product['size']); ?></p>
            <p><strong>จำนวนคงเหลือ:</strong> 
                <?= ($product['stock_quantity'] > 0) ? $product['stock_quantity'] : '<span class="text-danger">สินค้าหมด</span>'; ?>
            </p>

            <?php if ($product['stock_quantity'] > 0): ?>
                <!-- ฟอร์มเพิ่มลงตะกร้า -->
                <form action="cart.php" method="POST">
                    <input type="hidden" name="add" value="<?= $product['id']; ?>">
                    <div class="input-group mb-3">
                        <input type="number" 
                               name="quantity" 
                               class="form-control text-center" 
                               value="1" 
                               min="1" 
                               max="<?= $product['stock_quantity']; ?>">
                        <button type="submit" class="btn btn-success">เพิ่มลงตะกร้า</button>
                    </div>
                </form>
            <?php else: ?>
                <button class="btn btn-secondary" disabled>สินค้าหมด</button>
            <?php endif; ?>

            <a href="products.php?category_id=<?= $product['id']; ?>" class="btn btn-primary mt-3">ย้อนกลับ</a>
        </div>
    </div>
</div>

<?php include 'layouts/footer.php'; ?>
