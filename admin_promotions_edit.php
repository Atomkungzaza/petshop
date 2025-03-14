<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

// ตรวจสอบว่ามีค่าที่ส่งมาหรือไม่
if (!isset($_GET['edit']) || empty($_GET['edit'])) {
    $_SESSION['error'] = "ไม่พบโปรโมชั่นที่ต้องการแก้ไข";
    header("Location: admin_promotions.php");
    exit();
}

$promotion_id = intval($_GET['edit']);

// ดึงข้อมูลโปรโมชั่นจากฐานข้อมูล
$stmt = $conn->prepare("SELECT p.*, pr.product_id 
                        FROM promotions p
                        LEFT JOIN product_promotions pr ON p.id = pr.promotion_id 
                        WHERE p.id = ?");
$stmt->execute([$promotion_id]);
$promotion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$promotion) {
    $_SESSION['error'] = "ไม่พบโปรโมชั่นที่ต้องการแก้ไข";
    header("Location: admin_promotions.php");
    exit();
}

// ดึงรายการสินค้าสำหรับ dropdown
$stmt = $conn->prepare("SELECT * FROM products ORDER BY name ASC");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'layouts/header.php'; ?>

<div class="container mt-4">
    <h2 class="mb-3">แก้ไขโปรโมชั่น</h2>

    <!-- แสดงข้อความแจ้งเตือน -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <form method="POST" action="admin_promotion_db.php">
        <input type="hidden" name="promotion_id" value="<?= $promotion['id']; ?>">
        <div class="mb-3">
            <label class="form-label">ชื่อโปรโมชั่น</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($promotion['title']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">รายละเอียดโปรโมชั่น</label>
            <input type="text" name="description" class="form-control" value="<?= htmlspecialchars($promotion['description']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">สินค้าที่ลดราคา</label>
            <select name="product_ids" class="form-control" required>
                <option value="">-- เลือกสินค้าที่จะลดราคา --</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= $product['id']; ?>" <?= $product['id'] == $promotion['product_id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($product['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">ส่วนลด (%)</label>
            <input type="number" name="discount_percentage" step="0.01" class="form-control" value="<?= $promotion['discount_percentage']; ?>" required>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">เริ่มวันที่</label>
                <input type="date" name="start_date" class="form-control" value="<?= $promotion['start_date']; ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">หมดวันที่</label>
                <input type="date" name="end_date" class="form-control" value="<?= $promotion['end_date']; ?>" required>
            </div>
        </div>
        <button type="submit" name="edit_promotion" class="btn btn-primary w-100">บันทึกการแก้ไข</button>
        <a href="admin_promotions.php" class="btn btn-secondary w-100 mt-2">กลับไปหน้าหลัก</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'layouts/footer.php'; ?>
