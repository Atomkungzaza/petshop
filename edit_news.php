<?php
// เริ่ม session ถ้ายังไม่ได้เริ่ม
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// รวมไฟล์เชื่อมต่อฐานข้อมูล
require_once 'config/db.php';

// ตรวจสอบว่ามีการส่งค่า id มาหรือไม่
if (!isset($_GET['id'])) {
    header("Location: admin_news.php");
    exit();
}

$news_id = $_GET['id'];
$news = null;

try {
    // ดึงข้อมูลข่าวสารจากฐานข้อมูล
    $sql = "SELECT * FROM news WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $news_id, PDO::PARAM_INT);
    $stmt->execute();
    $news = $stmt->fetch(PDO::FETCH_ASSOC);

    // ถ้าไม่มีข่าวนี้ในฐานข้อมูล ให้ redirect กลับ
    if (!$news) {
        header("Location: admin_news.php");
        exit();
    }
} catch (PDOException $e) {
    echo "เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage();
    exit();
}

// ตรวจสอบการส่งฟอร์มเพื่ออัปเดตข่าวสาร
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $image_path = $news['image_url']; // เก็บ path เดิมเผื่อไม่มีการอัพโหลดใหม่

    // ตรวจสอบว่ามีการอัปโหลดรูปภาพใหม่หรือไม่
    if (!empty($_FILES['image']['name'])) {
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
            // ลบรูปภาพเก่าออก
            if (file_exists($news['image_url'])) {
                unlink($news['image_url']);
            }
        } else {
            echo "การอัพโหลดรูปภาพไม่สำเร็จ.";
            exit();
        }
    }

    try {
        // อัปเดตข่าวสารในฐานข้อมูล
        $sql = "UPDATE news SET title = :title, content = :content, image_url = :image_url WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':image_url', $image_path);
        $stmt->bindParam(':id', $news_id, PDO::PARAM_INT);
        $stmt->execute();

        // รีไดเรคกลับไปที่หน้า admin_news.php
        header("Location: admin_news.php");
        exit();
    } catch (PDOException $e) {
        echo "เกิดข้อผิดพลาดในการอัปเดตข่าว: " . $e->getMessage();
        exit();
    }
}
?>

<?php include 'layouts/header.php'; ?>

<div class="container mt-5">
    <h2>แก้ไขข่าวสาร</h2>

    <form action="edit_news.php?id=<?= $news_id; ?>" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="title" class="form-label">หัวข้อข่าวสาร</label>
            <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($news['title']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="content" class="form-label">เนื้อหาข่าวสาร</label>
            <textarea class="form-control" id="content" name="content" rows="3" required><?= htmlspecialchars($news['content']); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">อัพโหลดรูปภาพใหม่ (ถ้าต้องการเปลี่ยน)</label>
            <input type="file" class="form-control" id="image" name="image">
            <div class="mt-2">
                <img src="<?= $news['image_url']; ?>" alt="Current Image" width="150">
            </div>
        </div>
        <button type="submit" class="btn btn-success">อัปเดตข่าวสาร</button>
        <a href="admin_news.php" class="btn btn-secondary">ยกเลิก</a>
    </form>
</div>

<?php include 'layouts/footer.php'; ?>