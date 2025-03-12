<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<?php include 'layouts/header.php'; ?>

<div class="bg-light d-flex justify-content-center align-items-center vh-100">
    <form action="reset_password_db.php" method="POST">
        <div class="card shadow-lg p-4 bg-login text-white" style="width: 400px;">
            <h2 class="text-center">รีเซ็ตรหัสผ่าน</h2>
            <p class="text-center">กรุณากรอกอีเมลของคุณ</p>

            <?php if (isset($_SESSION['error'])) { ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php } ?>
            <?php if (isset($_SESSION['success'])) { ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php } ?>

            <div class="mb-3">
                <label for="email" class="form-label">อีเมล</label>
                <input type="email" name="email" class="form-control" placeholder="กรอกอีเมลของคุณ" required>
            </div>

            <button type="submit" name="request_reset" class="btn btn-dark w-100">ดำเนินการต่อ</button>

            <div class="text-center mt-3">
                <a href="login.php" class="btn btn-secondary btn-home">← กลับไปเข้าสู่ระบบ</a>
            </div>
        </div>
    </form>
</div>

<?php include 'layouts/footer.php'; ?>
