<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

require_once 'products_db.php'; // ✅ นำเข้าไฟล์ประมวลผล
?>

<?php include 'layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="uploads/products/<?= htmlspecialchars($product['image_url']); ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($product['name']); ?></h5>
                        <h5 class="card-title">ราคา <?= number_format($product['price'], 2); ?> บาท</h5>
                        <p class="card-text"><?= htmlspecialchars($product['size']); ?></p>
                        <p class="card-text">จำนวน <?= $product['stock_quantity']; ?> ชิ้น</p>
                        <!-- ✅ ปุ่มไปหน้า product details -->
                        <a href="product_details.php?id=<?= $product['id']; ?>" class="btn btn-primary w-100">รายละเอียดสินค้า</a>

                        <?php if ($product['stock_quantity'] > 0): ?>
                        <?php else: ?>
                            <p class="text-danger mt-2">สินค้าหมด</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'layouts/footer.php'; ?>
