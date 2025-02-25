<?php
// เริ่มการใช้งาน session ถ้ายังไม่ได้เริ่ม
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// รวมไฟล์เชื่อมต่อฐานข้อมูล
require_once 'config/db.php';

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
?>

<?php include 'layouts/header.php'; ?>

<div class="container mt-5">
    <h2>ข่าวสาร</h2>
    <?php if (empty($news)): ?>
        <p>ยังไม่มีข่าวสาร</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($news as $new): ?>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <img src="<?= $new['image_url']; ?>" class="card-img-top" alt="news image">
                        <div class="card-body">
                            <h5 class="card-title"><?= $new['title']; ?></h5>
                            <p class="card-text"><?= substr($new['content'], 0, 100); ?>...</p>
                            <p><strong>วันที่:</strong> <?= $new['created_at']; ?></p>
                            <a href="news_detail.php?id=<?= $new['id']; ?>" class="btn btn-primary">อ่านเพิ่มเติม</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'layouts/footer.php'; ?>
