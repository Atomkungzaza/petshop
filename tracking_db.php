<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $user_id = $_SESSION['user_id'];

    // เพิ่มข้อมูลการติดตาม
    $query = "INSERT INTO tracking (order_id, status, user_id, created_at) VALUES (:order_id, :status, :user_id, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    // อัปเดตสถานะในตาราง orders
    $updateQuery = "UPDATE orders SET status = :status WHERE id = :order_id";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bindParam(':status', $status);
    $updateStmt->bindParam(':order_id', $order_id);
    $updateStmt->execute();

    header("Location: tracking.php");
    exit();
}
?>
