<?php
// เริ่มการใช้งาน session ถ้ายังไม่ได้เริ่ม
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// รวมไฟล์เชื่อมต่อฐานข้อมูล
require_once 'config/db.php';

// ตัวแปรสำหรับการเก็บข้อมูลข่าวสาร
$news = [];

try {
    // คำสั่ง SQL สำหรับดึงข้อมูลข่าวสารทั้งหมดจากฐานข้อมูล
    $sql = "SELECT * FROM news ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // เก็บผลลัพธ์ทั้งหมดในตัวแปร $news
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: " . $e->getMessage();
}
?>

<?php include 'layouts/header.php'; ?>

<div class="container mt-5">
    <h2>จัดการข่าวสาร</h2>

    <!-- ฟอร์มเพิ่มข่าวสารใหม่ -->
    <form action="admin_news_db.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="title" class="form-label">หัวข้อข่าวสาร</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="mb-3">
            <label for="content" class="form-label">เนื้อหาข่าวสาร</label>
            <textarea class="form-control" id="content" name="content" rows="3" required></textarea>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">เลือกภาพ</label>
            <input type="file" class="form-control" id="image" name="image" required>
        </div>
        <button type="submit" class="btn btn-primary">เพิ่มข่าวสาร</button>
    </form>

    <h3 class="mt-5">ข่าวสารทั้งหมด</h3>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>#</th>
                <th>หัวข้อ</th>
                <th>เนื้อหา</th>
                <th>รูปภาพ</th>
                <th>วันที่</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($news as $new): ?>
                <tr>
                    <td><?= $new['id']; ?></td>
                    <td><?= $new['title']; ?></td>
                    <td><?= substr($new['content'], 0, 50); ?>...</td>
                    <td><img src="<?= $new['image_url']; ?>" alt="image" width="100"></td>
                    <td><?= $new['created_at']; ?></td>
                    <td>
                        <!-- ปุ่มแก้ไข (ตัวอย่างลิงก์ไปยังหน้า edit_news.php) -->
                        <a href="edit_news.php?id=<?= $new['id']; ?>" class="btn btn-sm btn-warning">แก้ไข</a>
                        <!-- ปุ่มลบ (ลิงก์ไปยัง admin_news_db.php?action=delete&id=...) -->
                        <a href="admin_news_db.php?action=delete&id=<?= $new['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบข่าวนี้?');">ลบ</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'layouts/footer.php'; ?>
