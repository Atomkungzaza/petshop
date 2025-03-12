<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['reset_email'])) {
    header("location: login.php");
    exit();
}
?>

<?php include 'layouts/header.php'; ?>

<div class="bg-light d-flex justify-content-center align-items-center vh-100">
    <form action="update_password.php" method="POST">
        <div class="card shadow-lg p-4 bg-login text-white" style="width: 400px;">
            <h2 class="text-center">ตั้งค่ารหัสผ่านใหม่</h2>

            <?php if (isset($_SESSION['error'])) { ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php } ?>
            <?php if (isset($_SESSION['success'])) { ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php } ?>

            <div class="mb-3">
                <label for="password" class="form-label">รหัสผ่านใหม่</label>
                <input type="password" name="password" class="form-control" placeholder="กรอกรหัสผ่านใหม่" required>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="ยืนยันรหัสผ่าน" required>
            </div>

            <button type="submit" name="update_password" class="btn btn-dark w-100">บันทึก</button>
        </div>
    </form>
</div>

<?php include 'layouts/footer.php'; ?>
