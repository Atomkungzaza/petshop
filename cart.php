<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

require_once 'cart_db.php'; // ✅ นำเข้าไฟล์ประมวลผล
?>

<?php include 'layouts/header.php'; ?>

<div class="container mt-4">

    <!-- ✅ แสดงข้อความแจ้งเตือน -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'];
                                            unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (!empty($cart_items)): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>สินค้า</th>
                    <th>ราคาปกติ</th>
                    <th>ส่วนลด</th>
                    <th>ราคาหลังหักส่วนลด</th>
                    <th>จำนวน</th>
                    <th>ราคารวม</th>
                    <th>ลบ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']); ?></td>
                        <td><?= number_format($item['original_price'], 2); ?> บาท</td>
                        <td><?= $item['discount_percentage']; ?>%</td>
                        <td><?= number_format($item['final_price'], 2); ?> บาท</td>
                        <td>
                            <!-- เพิ่ม-ลดจำนวนสินค้าในตะกร้า -->
                            <form action="cart.php" method="POST" class="form-inline">
                                <input type="hidden" name="product_id" value="<?= $item['id']; ?>">
                                <button type="submit" name="action" value="decrease" class="btn btn-sm btn-secondary">-</button>
                                <input type="number" name="quantity" value="<?= $item['quantity']; ?>" min="1" max="<?= $item['stock_quantity']; ?>" class="form-control w-25 mx-2">
                                <button type="submit" name="action" value="increase" class="btn btn-sm btn-primary">+</button>
                            </form>
                        </td>
                        <td><?= number_format($item['subtotal'], 2); ?> บาท</td>
                        <td><a href="cart.php?remove=<?= $item['id']; ?>" class="btn btn-danger btn-sm">ลบ</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3 class="text-end">ราคารวมทั้งหมด: <?= number_format($total_price, 2); ?> บาท</h3>
        <a href="process_checkout.php" class="btn btn-primary w-100">สั่งซื้อสินค้า</a>
    <?php else: ?>
        <p class="text-center">ตะกร้าของคุณว่างเปล่า</p>
    <?php endif; ?>
</div>

<?php include 'layouts/footer.php'; ?>
