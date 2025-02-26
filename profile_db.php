<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // แก้ไขข้อมูลโปรไฟล์
    if (isset($_POST['update_profile'])) {
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];

        // ตรวจสอบและอัพเดทข้อมูลในฐานข้อมูล
        $stmt = $conn->prepare("UPDATE users SET full_name = :full_name, email = :email, phone = :phone, address = :address WHERE id = :user_id");
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        header("Location: profile.php");
        exit;
    }

    // อัพโหลดรูปโปรไฟล์
    if (isset($_POST['upload_image']) && isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $file_name = $_FILES['profile_image']['name'];
        $file_tmp = $_FILES['profile_image']['tmp_name'];
        $file_path = 'uploads/profile_images/' . $file_name;

        // ย้ายไฟล์ที่อัพโหลดไปยังที่เก็บ
        move_uploaded_file($file_tmp, $file_path);

        // อัพเดทรูปโปรไฟล์ในฐานข้อมูล
        $stmt = $conn->prepare("UPDATE users SET image_url = :image_url WHERE id = :user_id");
        $stmt->bindParam(':image_url', $file_path);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        header("Location: profile.php");
        exit;
    }

    // ลบบัญชี
    if (isset($_POST['delete_account'])) {
        // ลบบัญชีผู้ใช้
        $stmt = $conn->prepare("DELETE FROM users WHERE id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        // ลบข้อมูลที่เกี่ยวข้องกับผู้ใช้
        // คุณอาจต้องลบข้อมูลที่เกี่ยวข้อง เช่น คำสั่งซื้อ, ตะกร้า ฯลฯ
        // $stmt = $conn->prepare("DELETE FROM orders WHERE user_id = :user_id");
        // $stmt->bindParam(':user_id', $user_id);
        // $stmt->execute();

        // ลบ session
        session_destroy();

        header("Location: login.php");
        exit;
    }
}
