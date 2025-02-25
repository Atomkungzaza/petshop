<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

// เรียกไฟล์ products_db.php เพียงครั้งเดียว เพื่อดึงข้อมูล $products และ $category
require_once 'products_db.php';

include 'layouts/header.php';
?>

<div class="container mt-4">
    <!-- แจ้งเตือนข้อความ -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <h2 class="mb-3">หมวดหมู่: <?= htmlspecialchars($category['name']); ?></h2>
    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="uploads/products/<?= htmlspecialchars($product['image_url']); ?>" 
                         class="card-img-top" 
                         alt="<?= htmlspecialchars($product['name']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($product['name']); ?></h5>

                        <?php if ($product['discounted_price'] < $product['price']): ?>
                            <h5 class="card-title text-danger">
                                <del>ราคา <?= number_format($product['price'], 2); ?> บาท</del>
                            </h5>
                            <h5 class="card-title">
                                ลดเหลือ <?= number_format($product['discounted_price'], 2); ?> บาท
                            </h5>
                        <?php else: ?>
                            <h5 class="card-title">
                                ราคา <?= number_format($product['price'], 2); ?> บาท
                            </h5>
                        <?php endif; ?>

                        <p class="card-text">ขนาด: <?= htmlspecialchars($product['size']); ?></p>
                        
                        <!-- แสดงสถานะสินค้า -->
                        <p class="card-text">
                            จำนวนคงเหลือ: 
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <?= $product['stock_quantity']; ?>
                            <?php else: ?>
                                <span class="text-danger">สินค้าหมด</span>
                            <?php endif; ?>
                        </p>

                        <!-- ปุ่มเพิ่มลงตะกร้า (ซ่อนถ้าสินค้าหมด) -->
                        <?php if ($product['stock_quantity'] > 0): ?>
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

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'layouts/footer.php'; ?>
