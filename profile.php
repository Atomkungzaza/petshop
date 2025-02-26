<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';
$user_id = $_SESSION['user_id']; // ตรวจสอบว่า user_id มาอย่างถูกต้อง
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // ถ้าไม่มีผู้ใช้งานในฐานข้อมูล
    echo "User not found.";
    exit;
}

?>

<?php include 'layouts/header.php'; ?>

<div class="container">
    <h2>โปรไฟล์ของคุณ</h2>
    <div class="row">
        <!-- รูปโปรไฟล์ -->
        <div class="col-md-4">
            <?php if ($user['image_url']): ?>
                <img src="<?= $user['image_url'] ?>" alt="Profile Image" class="img-fluid rounded-circle">
            <?php else: ?>
                <div class="profile-image-placeholder">
                    <i class="fas fa-user-circle fa-5x"></i> <!-- ใช้ไอคอนสำหรับโปรไฟล์ที่ไม่มีรูป -->
                </div>
            <?php endif; ?>
            <form action="profile_db.php" method="post" enctype="multipart/form-data">
                <input type="file" name="profile_image" accept="image/*" class="form-control mt-3">
                <button type="submit" name="upload_image" class="btn btn-primary mt-2">อัพโหลดรูปโปรไฟล์</button>
            </form>
        </div>
        <!-- ข้อมูลผู้ใช้ -->
        <div class="col-md-8">
            <form action="profile_db.php" method="post">
                <div class="form-group">
                    <label for="full_name">ชื่อ-นามสกุล</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?= $user['full_name'] ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">อีเมล</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= $user['email'] ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">เบอร์โทรศัพท์</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?= $user['phone'] ?>" required>
                </div>
                <div class="form-group">
                    <label for="address">ที่อยู่</label>
                    <textarea class="form-control" id="address" name="address" required><?= $user['address'] ?></textarea>
                </div>
                <button type="submit" name="update_profile" class="btn btn-success">บันทึกการเปลี่ยนแปลง</button>
            </form>
            <form action="profile_db.php" method="post" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบบัญชีของคุณ?')">
                <button type="submit" name="delete_account" class="btn btn-danger mt-3">ลบบัญชี</button>
            </form>
        </div>
    </div>
</div>

<?php include 'layouts/footer.php'; ?>

