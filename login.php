<!-- โค้ดในไฟล์ login.php -->

<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<?php include 'layouts/header.php'; ?>

<div class="bg-light d-flex justify-content-center align-items-center vh-100">
    <form action="login_db.php" method="POST">
        <div class="card shadow-lg p-4 bg-login text-white" style="width: 400px;">
            <h2 class="text-center">ยินดีต้อนรับ</h2>
            <p class="text-center">กรุณาเข้าสู่ระบบเพื่อดำเนินการต่อ</p>

            <!-- แสดง Error หรือ Success -->
            <?php if (isset($_SESSION['error'])) { ?>
                <div class="alert alert-danger"><?= $_SESSION['error'];
                                                unset($_SESSION['error']); ?></div>
            <?php } ?>
            <?php if (isset($_SESSION['success'])) { ?>
                <div class="alert alert-success"><?= $_SESSION['success'];
                                                    unset($_SESSION['success']); ?></div>
            <?php } ?>
            <?php if (isset($_SESSION['warning'])) { ?>
                <div class="alert alert-warning"><?= $_SESSION['warning'];
                                                    unset($_SESSION['warning']); ?></div>
            <?php } ?>

            <!-- ฟอร์มล็อกอิน -->
            <div class="mb-3">
                <label for="user_input" class="form-label">ชื่อผู้ใช้งาน/อีเมล</label>
                <input type="text" name="user_input" class="form-control" placeholder="กรอกชื่อผู้ใช้งาน/อีเมล" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">รหัสผ่าน</label>
                <input type="password" name="password" class="form-control" placeholder="กรอกรหัสผ่าน" required>
            </div>

            <button type="submit" name="login" class="btn btn-dark w-100">เข้าสู่ระบบ</button>

            <!-- ลิงก์สร้างบัญชี -->
            <div class="text-center mt-3">
                <p>หากไม่มีบัญชี <a href="register.php" class="text-gold">สร้างบัญชี</a></p>
            </div>

            <!-- ปุ่มกลับสู่หน้าหลัก -->
            <div class="text-center mt-3">
                <a href="index.php" class="btn btn-secondary btn-home">← กลับสู่หน้าหลัก</a>
            </div>
        </div>
    </form>
</div>
<?php include 'layouts/footer.php'; ?>