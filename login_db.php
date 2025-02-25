<!-- โค้ดในไฟล์ login_db.php -->

<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

if (isset($_POST['login'])) {
    // ✅ รับค่าจากฟอร์ม พร้อมล้างข้อมูลเพื่อป้องกัน XSS
    $user_input = trim(htmlspecialchars($_POST['user_input'])); // สามารถเป็น username หรือ email ได้
    $password = trim($_POST['password']);

    // ✅ ตรวจสอบว่ามีการกรอกข้อมูลครบถ้วน
    if (empty($user_input)) {
        $_SESSION['error'] = 'กรุณากรอกชื่อผู้ใช้งานหรืออีเมล';
    } elseif (empty($password)) {
        $_SESSION['error'] = 'กรุณากรอกรหัสผ่าน';
    }

    // ✅ หากมี error ให้ redirect กลับไปที่ login.php
    if (isset($_SESSION['error'])) {
        header("location: login.php");
        exit();
    }

    try {
        // ✅ ค้นหาผู้ใช้โดยใช้ username หรือ email
        $check_user = $conn->prepare("SELECT * FROM users WHERE username = :user_input OR email = :user_input");
        $check_user->bindParam(":user_input", $user_input);
        $check_user->execute();
        $user = $check_user->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];  // ✅ เก็บ role ไว้ใน session

            $_SESSION['success'] = "เข้าสู่ระบบสำเร็จ!";

            // ✅ เช็ค role และเปลี่ยนเส้นทาง
            if ($user['role'] === 'admin') {
                header("location: admin_index.php");  // ✅ ไปหน้า admin
            } else {
                header("location: index.php");  // ✅ ไปหน้า user ทั่วไป
            }
            exit();
        } else {
            $_SESSION['error'] = "ไม่พบชื่อผู้ใช้งานหรืออีเมลนี้ในระบบ";
        }

        header("location: login.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการเข้าสู่ระบบ: " . $e->getMessage();
        header("location: login.php");
        exit();
    }
}
