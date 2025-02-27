<!-- โค้ดในไฟล์ index.php -->

<?php
include 'layouts/header.php';

// เริ่มการใช้งาน session ถ้ายังไม่ได้เริ่ม
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// รวมไฟล์เชื่อมต่อฐานข้อมูล
require_once 'config/db.php';

// ดึงข้อมูลข่าวทั้งหมดจากฐานข้อมูล
try {
    $sql = "SELECT * FROM news ORDER BY created_at DESC LIMIT 10"; // จำกัดแค่  ข่าวล่าสุด
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: " . $e->getMessage();
}
?>

<div class="container mt-4">
    <h2>ยินดีต้อนรับ!</h2>
    <p>ที่นี่คุณจะพบกับสัตว์เลี้ยงหลากหลายชนิด รวมถึงอาหาร, ยา และอุปกรณ์ต่าง ๆ ที่คุณต้องการ</p>
    <img src="assets/images/logo.png" >

    <!-- ช่องแสดงข่าว -->
    <div class="news-section mt-5">
        <h3>ข่าวสารล่าสุด</h3>
        <div class="row">
            <?php foreach ($news as $item): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="<?= $item['image_url']; ?>" class="card-img-top" alt="news image">
                        <div class="card-body">
                            <h5 class="card-title"><?= $item['title']; ?></h5>
                            <p class="card-text"><?= substr($item['content'], 0, 100); ?>...</p>
                            <a href="news_detail.php?id=<?= $item['id']; ?>" class="btn btn-primary">อ่านเพิ่มเติม</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
include 'layouts/footer.php';
?>

