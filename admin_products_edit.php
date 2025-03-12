<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';
require_once 'admin_products_db.php';

if (isset($_GET['edit_product_id'])) {
    $edit_product_id = intval($_GET['edit_product_id']);
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = :product_id");
    $stmt->bindParam(":product_id", $edit_product_id);
    $stmt->execute();
    $product_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<?php include 'layouts/header.php'; ?>
<div class="container mt-4">
    <h2 class="mb-3">แก้ไขสินค้า</h2>
    <form method="POST" action="admin_products_db.php" enctype="multipart/form-data">
        <input type="hidden" name="product_id" value="<?= $product_to_edit['id']; ?>">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">ชื่อสินค้า</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product_to_edit['name']); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">คำอธิบาย</label>
                <input type="text" name="description" class="form-control" value="<?= htmlspecialchars($product_to_edit['description']); ?>" required>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">ขนาด</label>
                <input type="text" name="size" class="form-control" value="<?= htmlspecialchars($product_to_edit['size']); ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">จำนวนในสต็อก</label>
                <input type="number" name="stock_quantity" class="form-control" value="<?= $product_to_edit['stock_quantity']; ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">ราคา</label>
                <input type="number" step="0.01" name="price" class="form-control" value="<?= $product_to_edit['price']; ?>" required>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">หมวดหมู่</label>
                <select name="category_id" class="form-control" required>
                    <option value="">-- เลือกหมวดหมู่ --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id']; ?>" <?= ($category['id'] == $product_to_edit['category_id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="formFile" class="form-label">อัปโหลดรูปภาพใหม่ (ถ้ามี)</label>
                <input class="form-control" type="file" id="formFile" name="image_file">
            </div>
        </div>
        <button type="submit" name="update_product" class="btn btn-warning w-100">อัปเดตสินค้า</button>
    </form>
</div>
<?php include 'layouts/footer.php'; ?>
