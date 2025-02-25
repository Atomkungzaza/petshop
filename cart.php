<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

// ตรวจสอบว่ามีสินค้าในตะกร้า
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ลบสินค้าออกจากตะกร้า
if (isset($_GET['remove'])) {
    $product_id = intval($_GET['remove']);
    unset($_SESSION['cart'][$product_id]);
    $_SESSION['success'] = "ลบสินค้าเรียบร้อย!";
    header("location: cart.php");
    exit();
}

// เพิ่มสินค้าเข้า $_SESSION['cart'] ด้วย POST (แบบฟอร์มใน products.php)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    $product_id = intval($_POST['add']);
    $quantity = intval($_POST['quantity']);
    if ($quantity < 1) {
        $quantity = 1;
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

// ดึงข้อมูลสินค้าในตะกร้า
$cart_items = [];
$total_price = 0;
if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    try {
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
        foreach ($products as $product) {
            $product_id = $product['id'];
            $quantity = $_SESSION['cart'][$product_id];
            $original_price = $product['price'];
            $discount_percentage = $product['discount_percentage'] ?? 0;
            $discount_amount = ($discount_percentage > 0) ? ($original_price * ($discount_percentage / 100)) : 0;
            $final_price = $original_price - $discount_amount;
            $subtotal = $final_price * $quantity;
            $total_price += $subtotal;
            $cart_items[] = [
                'id' => $product_id,
                'name' => $product['name'],
                'original_price' => $original_price,
                'discount_percentage' => $discount_percentage,
                'final_price' => $final_price,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
                'stock_quantity' => $product['stock_quantity']
            ];
        }
    } catch (PDOException $e) {
        echo "SQL Error: " . $e->getMessage();
        exit();
    }
}
?>
<?php include 'layouts/header.php'; ?>
<div class="container mt-4">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($cart_items)): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>สินค้า</th>
                    <th>ราคาปกติ</th>
                    <th>ส่วนลด</th>
                    <th>ราคาหลังหักส่วนลด</th>
                    <th>จำนวนที่สั่ง</th>
                    <th>ราคารวม</th>
                    <th>สถานะสินค้า</th>
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
                        <td><?= $item['quantity']; ?></td>
                        <td><?= number_format($item['subtotal'], 2); ?> บาท</td>
                        <td>
                            <?php if ($item['stock_quantity'] <= 0): ?>
                                <span class="text-danger">สินค้าหมด</span>
                            <?php else: ?>
                                <span class="text-success">มีสินค้า</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="cart.php?remove=<?= $item['id']; ?>" class="btn btn-danger btn-sm">ลบ</a>
                        </td>
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
