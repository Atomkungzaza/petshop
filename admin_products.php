<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';
require_once 'admin_products_db.php';
?>
<?php include 'layouts/header.php'; ?>
<div class="container mt-4">
    <h2 class="mb-3">เพิ่มสินค้าใหม่</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <!-- ฟอร์มเพิ่มสินค้า -->
    <form method="POST" action="admin_products_db.php" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">ชื่อสินค้า</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">คำอธิบาย</label>
                <input type="text" name="description" class="form-control" required>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">ขนาด</label>
                <input type="text" name="size" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">จำนวนในสต็อก</label>
                <input type="number" name="stock_quantity" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">ราคา</label>
                <input type="number" step="0.01" name="price" class="form-control" required>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">หมวดหมู่</label>
                <select name="category_id" class="form-control" required>
                    <option value="">-- เลือกหมวดหมู่ --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id']; ?>"><?= htmlspecialchars($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="formFile" class="form-label">เพิ่มรูปภาพ</label>
                <input class="form-control" type="file" id="formFile" name="image_file" required>
            </div>
        </div>
        <button type="submit" name="add_product" class="btn btn-success w-100">เพิ่มสินค้า</button>
    </form>

    <hr class="my-4">
    <h2 class="mb-3">รายการสินค้า</h2>
    <!-- ตารางแสดงสินค้า -->
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>ชื่อสินค้า</th>
                <th>คำอธิบาย</th>
                <th>ขนาด</th>
                <th>จำนวน</th>
                <th>ราคา</th>
                <th>หมวดหมู่</th>
                <th>รูปภาพ</th>
                <th>แก้ไข</th>
                <th>ลบ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $index => $product): ?>
                <tr>
                    <td><?= $index + 1; ?></td>
                    <td class="text-start"><?= htmlspecialchars($product['name']); ?></td>
                    <td class="text-start"><?= htmlspecialchars($product['description']); ?></td>
                    <td><?= htmlspecialchars($product['size']); ?></td>
                    <td><?= $product['stock_quantity']; ?></td>
                    <td><?= number_format($product['price'], 2); ?> บาท</td>
                    <td><?= htmlspecialchars($product['category_name']); ?></td>
                    <td>
                        <img src="uploads/products/<?= htmlspecialchars($product['image_url']); ?>" alt="รูปสินค้า" width="50">
                    </td>
                    <td>
                        <form method="GET" action="admin_products.php">
                            <input type="hidden" name="edit_product_id" value="<?= $product['id']; ?>">
                            <button type="submit" class="btn btn-primary btn-sm">แก้ไข</button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" action="admin_products_db.php" onsubmit="return confirm('คุณต้องการลบสินค้านี้ใช่หรือไม่?');">
                            <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                            <button type="submit" name="delete_product" class="btn btn-danger btn-sm">ลบ</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- แสดงฟอร์มแก้ไขเมื่อกดปุ่ม "แก้ไข" -->
    <?php if (isset($_GET['edit_product_id'])): ?>
        <?php
        $edit_product_id = intval($_GET['edit_product_id']);
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = :product_id");
        $stmt->bindParam(":product_id", $edit_product_id);
        $stmt->execute();
        $product_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>
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
    <?php endif; ?>

</div>
<?php include 'layouts/footer.php'; ?>
