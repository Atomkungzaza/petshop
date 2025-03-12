<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

if (isset($_POST['request_reset'])) {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $_SESSION['error'] = 'กรุณากรอกอีเมล';
        header("location: reset_password.php");
        exit();
    }

    try {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['reset_email'] = $email;
            $_SESSION['success'] = "กรุณาตั้งค่ารหัสผ่านใหม่";
            header("location: set_new_password.php");
        } else {
            $_SESSION['error'] = "ไม่พบอีเมลนี้ในระบบ";
            header("location: reset_password.php");
        }
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        header("location: reset_password.php");
        exit();
    }
}
