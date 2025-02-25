<!-- โค้ดในไฟล์ register_db.php -->

<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

if (isset($_POST['register'])) {
    // ✅ รับค่าจากฟอร์ม พร้อมล้างข้อมูลเพื่อป้องกัน XSS
    $username = trim(htmlspecialchars($_POST['username']));
    $email = trim(htmlspecialchars($_POST['email']));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // ✅ ตรวจสอบว่ามีการกรอกข้อมูลครบถ้วน
    if (empty($username)) {
        $_SESSION['error'] = 'กรุณากรอกชื่อผู้ใช้งาน';
    } elseif (empty($email)) {
        $_SESSION['error'] = 'กรุณากรอกอีเมล';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'รูปแบบอีเมลไม่ถูกต้อง';
    } elseif (empty($password)) {
        $_SESSION['error'] = 'กรุณากรอกรหัสผ่าน';
    } elseif (strlen($password) < 6) {
        $_SESSION['error'] = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
    } elseif (empty($confirm_password)) {
        $_SESSION['error'] = 'กรุณากรอกยืนยันรหัสผ่าน';
    } elseif ($password !== $confirm_password) {
        $_SESSION['error'] = 'รหัสผ่านไม่ตรงกัน';
    }

    // ✅ หากมี error ให้ redirect กลับไปที่ register.php
    if (isset($_SESSION['error'])) {
        header("location: register.php");
        exit();
    }

    try {
        // ✅ ตรวจสอบว่าอีเมลนี้มีอยู่ในระบบหรือไม่
        $check_email = $conn->prepare("SELECT email FROM users WHERE email = :email");
        $check_email->bindParam(":email", $email);
        $check_email->execute();

        if ($check_email->rowCount() > 0) {
            $_SESSION['warning'] = "มีอีเมลนี้อยู่ในระบบแล้ว <a href='login.php'>คลิกที่นี่</a>";
            header("location: register.php");
            exit();
        }

        // ✅ แฮชรหัสผ่าน
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // ✅ บันทึกข้อมูลลงฐานข้อมูล
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $passwordHash);
        $stmt->execute();

        // ✅ แจ้งเตือนว่าสมัครสมาชิกสำเร็จ
        $_SESSION['success'] = "สมัครสมาชิกเรียบร้อย! <a href='login.php' class='alert-link'>เข้าสู่ระบบ</a>";
        header("location: register.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการสมัครสมาชิก: " . $e->getMessage();
        header("location: register.php");
        exit();
    }
}
