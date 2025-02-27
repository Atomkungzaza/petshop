<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// ดึงข้อมูลผู้ใช้จากฐานข้อมูล
$user_id = $_SESSION['user_id']; // สมมติว่าเก็บ user_id ใน session
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

include 'layouts/header.php';
?>

<div class="container mt-5">
    <h2>โปรไฟล์ของคุณ</h2>
    <div class="row">
        <div class="col-md-4">
            <!-- รูปโปรไฟล์ -->
            <div class="profile-img">
                <?php if (!empty($user['image'])): ?>
                    <img src="uploads/<?= $user['image']; ?>" alt="Profile Image" class="img-fluid">
                <?php else: ?>
                    <div class="profile-img-placeholder">
                        <i class="bi bi-person-circle" style="font-size: 5rem;"></i>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-8">
            <!-- ข้อมูลผู้ใช้ -->
            <form action="profile_db.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="full_name" class="form-label">ชื่อเต็ม</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">อีเมล</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">หมายเลขโทรศัพท์</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">ที่อยู่</label>
                    <textarea class="form-control" id="address" name="address" rows="3" required><?= htmlspecialchars($user['address']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">อัพโหลดรูปโปรไฟล์</label>
                    <input type="file" class="form-control" id="image" name="image">
                </div>
                <button type="submit" class="btn btn-primary" name="update_profile">อัปเดตข้อมูล</button>
                <a href="#" class="btn btn-danger ms-3" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">ลบบัญชี</a>
            </form>
        </div>
    </div>
</div>

<!-- Modal ยืนยันการลบบัญชี -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAccountModalLabel">ยืนยันการลบบัญชี</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                คุณแน่ใจหรือไม่ว่าต้องการลบบัญชีของคุณ? การลบจะไม่สามารถกู้คืนได้
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <a href="profile_db.php?action=delete_account" class="btn btn-danger">ลบบัญชี</a>
            </div>
        </div>
    </div>
</div>

<?php include 'layouts/footer.php'; ?>
