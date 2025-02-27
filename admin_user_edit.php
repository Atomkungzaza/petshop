<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/db.php';

// ตรวจสอบสิทธิ์การเข้าถึงหน้า Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// ตรวจสอบ ID ของสมาชิกที่ต้องการแก้ไข
if (!isset($_GET['id'])) {
    header('Location: admin_user.php');
    exit;
}

$user_id = $_GET['id'];
$query = "SELECT * FROM users WHERE id = :id";
$stmt = $conn->prepare($query);
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "ไม่พบข้อมูลผู้ใช้";
    exit;
}
?>

<?php include 'layouts/header.php'; ?> <!-- ใช้ path ที่ถูกต้อง -->
<div class="container mt-4">
    <h2 class="mb-3">แก้ไขข้อมูลสมาชิก</h2>

<body>


    <div class="container mt-4">
        <form action="admin_user_db.php?action=update" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $user['id']; ?>">
            <div class="mb-3">
                <label for="username" class="form-label">ชื่อผู้ใช้</label>
                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">อีเมล</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="full_name" class="form-label">ชื่อ-นามสกุล</label>
                <input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">ที่อยู่</label>
                <textarea class="form-control" id="address" name="address" required><?= htmlspecialchars($user['address']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">บทบาท</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="user" <?= $user['role'] == 'user' ? 'selected' : ''; ?>>ผู้ใช้ทั่วไป</option>
                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : ''; ?>>ผู้ดูแลระบบ</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">อัพโหลดรูปโปรไฟล์</label>
                <input type="file" class="form-control" id="image" name="image">
                <?php if (!empty($user['image'])): ?>
                    <img src="uploads/<?= $user['image']; ?>" alt="Profile Image" class="img-fluid mt-3" style="width: 50px; height: 50px;">
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">อัปเดตข้อมูล</button>
        </form>
    </div>
</body>
<?php include 'layouts/footer.php'; ?>

</html>