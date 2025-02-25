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
    $stmt = $conn->prepare($sql); // ใช้ $conn แทน $pdo
    $stmt->execute();

    // เก็บผลลัพธ์ทั้งหมดในตัวแปร $news
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: " . $e->getMessage();
}

// ตรวจสอบการรับค่าจากฟอร์มการเพิ่มข่าว
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับข้อมูลจากฟอร์ม
    $title = $_POST['title'];
    $content = $_POST['content'];
    
    // การจัดการไฟล์ภาพ
    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $upload_dir = 'uploads/news_images/';
    $image_path = $upload_dir . uniqid() . '-' . basename($image);

    // ตรวจสอบว่าโฟลเดอร์ปลายทางมีอยู่หรือไม่
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // ย้ายไฟล์ภาพไปยังโฟลเดอร์ที่กำหนด
    if (move_uploaded_file($image_tmp, $image_path)) {
        try {
            // คำสั่ง SQL สำหรับเพิ่มข้อมูลข่าวสาร
            $sql = "INSERT INTO news (title, content, image_url, created_at) VALUES (:title, :content, :image_url, NOW())";
            $stmt = $conn->prepare($sql); // ใช้ $conn แทน $pdo

            // Bind ค่าต่างๆ
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':image_url', $image_path);

            // Execute คำสั่ง SQL
            $stmt->execute();

            // รีไดเรคไปยังหน้าแสดงผลข่าวสาร
            header("Location: admin_news.php");
            exit();
        } catch (PDOException $e) {
            echo "เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: " . $e->getMessage();
        }
    } else {
        echo "การอัพโหลดรูปภาพไม่สำเร็จ.";
    }
}
?>

<?php include 'layouts/header.php'; ?>

<div class="container mt-5">
    <h2>จัดการข่าวสาร</h2>

    <!-- ฟอร์มเพิ่มข่าวสารใหม่ -->
    <form action="admin_news.php" method="POST" enctype="multipart/form-data">
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
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'layouts/footer.php'; ?>
