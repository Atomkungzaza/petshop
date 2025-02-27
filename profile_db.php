<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    // ตรวจสอบการอัปโหลดไฟล์ภาพ
    $image = null;
    if ($_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $image_name = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
        $image_new_name = uniqid() . '.' . $image_ext;
        move_uploaded_file($image_tmp, 'uploads/' . $image_new_name);
        $image = $image_new_name;
    }

    // อัปเดตข้อมูลผู้ใช้
    $sql = "UPDATE users SET full_name = :full_name, email = :email, phone = :phone, address = :address, image = :image WHERE id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':image', $image);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    header('Location: profile.php');
    exit();
}

// ลบบัญชี
if (isset($_GET['action']) && $_GET['action'] === 'delete_account') {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    session_destroy();
    header('Location: index.php');
    exit();
}
