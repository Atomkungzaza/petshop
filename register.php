<!-- โค้ดในไฟล์ register.php -->

<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<?php include 'layouts/header.php'; ?>


<!-- ✅ ใช้ Bootstrap ทำให้ฟอร์มอยู่กึ่งกลาง -->
<div class="d-flex vh-100 justify-content-center align-items-center">
    <form method="POST" action="register_db.php">
        <div class="register-container p-4 shadow rounded bg-white">
            <h2 class="text-center">สมัครสมาชิก</h2>
            <p class="text-center text-muted">กรุณากรอกข้อมูลเพื่อสร้างบัญชี</p>

            <!-- แสดง Error หรือ Success -->
            <?php if (isset($_SESSION['error'])) { ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error'];
                                                unset($_SESSION['error']); ?></div>
            <?php } ?>
            <?php if (isset($_SESSION['success'])) { ?>
                <div class="alert alert-success"><?php echo $_SESSION['success'];
                                                    unset($_SESSION['success']); ?></div>
            <?php } ?>
            <?php if (isset($_SESSION['warning'])) { ?>
                <div class="alert alert-danger"><?php echo $_SESSION['warning'];
                                                unset($_SESSION['warning']); ?></div>
            <?php } ?>


            <div class="mb-3 text-start">
                <label for="username" class="form-label">ชื่อผู้ใช้งาน</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="กรอกชื่อผู้ใช้งาน" required>
            </div>

            <div class="mb-3 text-start">
                <label for="email" class="form-label">อีเมล</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="กรอกอีเมล" required>
            </div>

            <div class="mb-3 text-start">
                <label for="password" class="form-label">รหัสผ่าน</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="กรอกรหัสผ่าน" required>
            </div>

            <div class="mb-3 text-start">
                <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="ยืนยันรหัสผ่าน" required>
            </div>

            <button class="btn btn-success w-100" type="submit" name="register">สมัครสมาชิก</button>

            <div class="text-center mt-3">
                <p>มีบัญชีแล้วใช่ไหม? <a href="login.php" class="text-primary">เข้าสู่ระบบ</a></p>
            </div>

            

            <!-- ✅ ปุ่มกลับสู่หน้าหลัก -->
            <div class="text-center mt-3">
                <a href="index.php" class="btn btn-outline-success">← กลับสู่หน้าหลัก</a>
            </div>
        </div>
    </form>
</div>
<?php include 'layouts/footer.php'; ?>