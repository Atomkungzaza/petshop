<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';
require_once 'admin_promotion_db.php'; // นำเข้าไฟล์ประมวลผลโปรโมชั่น

// ดึงรายการสินค้าจากตาราง products
try {
    $stmt = $conn->prepare("SELECT * FROM products ORDER BY name ASC");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "เกิดข้อผิดพลาดในการดึงข้อมูลสินค้า: " . $e->getMessage();
    $products = [];
}

// ดึงรายการโปรโมชั่น
$stmt = $conn->prepare("SELECT p.*, pr.product_id, pt.name AS product_name 
                        FROM promotions p 
                        LEFT JOIN product_promotions pr ON p.id = pr.promotion_id 
                        LEFT JOIN products pt ON pr.product_id = pt.id
                        ORDER BY p.id DESC");
$stmt->execute();
$promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!isset($promotions) || !is_array($promotions)) {
    $promotions = [];
}
?>

<?php include 'layouts/header.php'; ?>

<div class="container mt-4">
    <h2 class="mb-3">เพิ่มโปรโมชั่นใหม่</h2>
    
    <!-- แสดงข้อความแจ้งเตือน -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <!-- ฟอร์มเพิ่มโปรโมชั่น -->
    <form method="POST" action="admin_promotion_db.php" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">ชื่อโปรโมชั่น</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">รายละเอียดโปรโมชัน</label>
            <input type="text" name="description" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">สินค้าที่ลดราคา</label>
            <select name="product_ids" class="form-control" required>
                <option value="">-- เลือกสินค้าที่จะลดราคา --</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= $product['id']; ?>"><?= htmlspecialchars($product['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">ส่วนลด (%)</label>
            <input type="number" name="discount_percentage" step="0.01" class="form-control" required>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">เริ่มวันที่</label>
                <input type="date" name="start_date" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">หมดวันที่</label>
                <input type="date" name="end_date" class="form-control" required>
            </div>
        </div>
        <button type="submit" name="add_promotion" class="btn btn-success w-100">เพิ่มโปรโมชั่น</button>
    </form>

    <hr class="my-4">

    <h2 class="mb-3">ค้นหาโปรโมชั่น</h2>
    <input type="text" id="search_promotion" class="form-control mb-3" placeholder="🔍 ค้นหาตาม ID หรือชื่อโปรโมชั่น">

    <h2 class="mb-3">รายการโปรโมชั่น</h2>

    <!-- ตารางแสดงโปรโมชั่น -->
    <table class="table table-bordered table-hover" id="promotion_table">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>ชื่อโปรโมชั่น</th>
                <th>รายละเอียด</th>
                <th>สินค้าที่ลดราคา</th>
                <th>ส่วนลด (%)</th>
                <th>เริ่มวันที่</th>
                <th>หมดวันที่</th>
                <th>แก้ไข</th>
                <th>ลบ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($promotions as $index => $promotion): ?>
                <tr>
                    <td><?= $index + 1; ?></td>
                    <td class="text-start"><?= htmlspecialchars($promotion['title']); ?></td>
                    <td class="text-start"><?= htmlspecialchars($promotion['description']); ?></td>
                    <td class="text-start"><?= htmlspecialchars($promotion['product_name']); ?></td>
                    <td><?= number_format($promotion['discount_percentage'], 2); ?>%</td>
                    <td><?= htmlspecialchars($promotion['start_date']); ?></td>
                    <td><?= htmlspecialchars($promotion['end_date']); ?></td>
                    <td>
                        <a href="admin_promotions_edit.php?edit=<?= $promotion['id']; ?>" class="btn btn-warning btn-sm">แก้ไข</a>
                    </td>
                    <td>
                        <form method="POST" action="admin_promotion_db.php" onsubmit="return confirm('คุณต้องการลบโปรโมชั่นนี้ใช่หรือไม่?');">
                            <input type="hidden" name="promotion_id" value="<?= $promotion['id']; ?>">
                            <button type="submit" name="delete_promotion" class="btn btn-danger btn-sm">ลบ</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

<script>
document.getElementById("search_promotion").addEventListener("keyup", function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("#promotion_table tbody tr");

    rows.forEach(row => {
        let id = row.cells[0].textContent.toLowerCase();
        let name = row.cells[1].textContent.toLowerCase();
        
        if (id.includes(filter) || name.includes(filter)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'layouts/footer.php'; ?>
