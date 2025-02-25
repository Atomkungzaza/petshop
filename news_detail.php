<?php
// เริ่มการใช้งาน session ถ้ายังไม่ได้เริ่ม
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// รวมไฟล์เชื่อมต่อฐานข้อมูล
require_once 'config/db.php';

// ตรวจสอบว่ามีการส่งค่า id มาใน URL หรือไม่
if (isset($_GET['id'])) {
    $news_id = $_GET['id'];

    try {
        // คำสั่ง SQL สำหรับดึงข้อมูลข่าวสารตาม id ที่ส่งมา
        $sql = "SELECT * FROM news WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $news_id, PDO::PARAM_INT);
        $stmt->execute();

        // เก็บผลลัพธ์ในตัวแปร $news
        $news = $stmt->fetch(PDO::FETCH_ASSOC);

        // ตรวจสอบว่ามีข้อมูลข่าวสารหรือไม่
        if (!$news) {
            echo "ไม่พบข้อมูลข่าวสารที่คุณต้องการ";
            exit;
        }

    } catch (PDOException $e) {
        echo "เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: " . $e->getMessage();
    }
} else {
    echo "ไม่พบ ID ของข่าวสาร";
    exit;
}
?>

<?php include 'layouts/header.php'; ?>

<div class="container mt-5">
    <h2><?= $news['title']; ?></h2>
    <p><strong>วันที่:</strong> <?= $news['created_at']; ?></p>
    <img src="<?= $news['image_url']; ?>" class="img-fluid" alt="news image">
    <p class="mt-4"><?= $news['content']; ?></p>
    <a href="news.php" class="btn btn-secondary">กลับไปยังหน้าข่าวสาร</a>
</div>

<?php include 'layouts/footer.php'; ?>
