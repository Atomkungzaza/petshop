<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'], $_POST['status'], $_POST['user_id'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $user_id = $_POST['user_id']; // รับค่า user_id จากฟอร์มแอดมิน

    // ตรวจสอบว่าผู้ใช้มีอยู่ในระบบจริงหรือไม่
    $userCheck = $conn->prepare("SELECT id FROM users WHERE id = :user_id");
    $userCheck->bindParam(':user_id', $user_id);
    $userCheck->execute();

    if ($userCheck->rowCount() > 0) {
        $query = "INSERT INTO tracking (order_id, status, user_id) VALUES (:order_id, :status, :user_id)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        header("Location: admin_tracking.php");
        exit();
    } else {
        echo "<script>alert('ไม่พบผู้ใช้นี้ในระบบ'); window.location.href='admin_tracking.php';</script>";
        exit();
    }
}
?>
