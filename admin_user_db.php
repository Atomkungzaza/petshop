<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

// การจัดการการเพิ่ม, แก้ไข, หรือ ลบข้อมูลสมาชิก
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'delete') {
    // ลบสมาชิก
    $user_id = $_GET['id'];

    // ตรวจสอบการมีอยู่ของผู้ใช้
    $query = "SELECT * FROM users WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $delete_query = "DELETE FROM users WHERE id = :id";
        $stmt = $conn->prepare($delete_query);
        $stmt->execute([':id' => $user_id]);
        header('Location: admin_user.php');
        exit;
    } else {
        echo "ไม่พบข้อมูลสมาชิกที่ต้องการลบ";
    }
} elseif ($action === 'update') {
    // แก้ไขข้อมูลสมาชิก
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $user_id = $_POST['id'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);  // เข้ารหัสรหัสผ่านใหม่
        $full_name = $_POST['full_name'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $role = $_POST['role'];

        $query = "UPDATE users SET username = :username, email = :email, password = :password, full_name = :full_name, phone = :phone, address = :address, role = :role WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':id' => $user_id,
            ':username' => $username,
            ':email' => $email,
            ':password' => $password,
            ':full_name' => $full_name,
            ':phone' => $phone,
            ':address' => $address,
            ':role' => $role
        ]);
        header('Location: admin_user.php');
        exit;
    }
} elseif ($action === 'add') {
    // เพิ่มสมาชิกใหม่
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);  // เข้ารหัสรหัสผ่านใหม่
        $full_name = $_POST['full_name'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $role = $_POST['role'];

        $query = "INSERT INTO users (username, email, password, full_name, phone, address, role) VALUES (:username, :email, :password, :full_name, :phone, :address, :role)";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $password,
            ':full_name' => $full_name,
            ':phone' => $phone,
            ':address' => $address,
            ':role' => $role
        ]);
        header('Location: admin_user.php');
        exit;
    }
}
