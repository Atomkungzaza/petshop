<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

if (isset($_POST['update_password'])) {
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (!isset($_SESSION['reset_email'])) {
        $_SESSION['error'] = "เกิดข้อผิดพลาด กรุณาลองใหม่";
        header("location: login.php");
        exit();
    }

    $email = $_SESSION['reset_email'];

    if (empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "กรุณากรอกรหัสผ่านทั้งสองช่อง";
    } elseif ($password !== $confirm_password) {
        $_SESSION['error'] = "รหัสผ่านไม่ตรงกัน";
    } elseif (strlen($password) < 6) {
        $_SESSION['error'] = "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $conn->prepare("UPDATE users SET password = :password WHERE email = :email");
            $stmt->bindParam(":password", $hashed_password);
            $stmt->bindParam(":email", $email);
            $stmt->execute();

            unset($_SESSION['reset_email']);
            $_SESSION['success'] = "รีเซ็ตรหัสผ่านสำเร็จ! กรุณาเข้าสู่ระบบ";
            header("location: login.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }

    header("location: set_new_password.php");
    exit();
}
