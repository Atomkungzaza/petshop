

<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

// ตรวจสอบว่าผู้ใช้เป็นแอดมินหรือไม่
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("location: index.php");
    exit();
}

// เพิ่มโปรโมชั่นใหม่
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_promotion'])) {
    // รับค่าและ trim ข้อมูล
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    // รับค่า product_ids (ในที่นี้เป็น single select)
    $product_id = intval($_POST['product_ids']);
    $discount_percentage = floatval($_POST['discount_percentage']);
    $start_date = trim($_POST['start_date']);
    $end_date = trim($_POST['end_date']);

    // ตรวจสอบความถูกต้องของข้อมูล
    if (empty($title) || empty($description) || empty($product_id) || empty($discount_percentage) || empty($start_date) || empty($end_date)) {
        $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบถ้วน";
        header("location: admin_promotions.php");
        exit();
    }
    if ($discount_percentage < 0 || $discount_percentage > 100) {
        $_SESSION['error'] = "กรุณาระบุส่วนลดให้อยู่ในช่วง 0-100%";
        header("location: admin_promotions.php");
        exit();
    }
    if ($start_date >= $end_date) {
        $_SESSION['error'] = "วันที่เริ่มต้นต้องน้อยกว่าวันที่สิ้นสุด";
        header("location: admin_promotions.php");
        exit();
    }

    try {
        // เริ่ม transaction เพื่อให้การบันทึกทั้งสองตารางเป็น atomically
        $conn->beginTransaction();

        // บันทึกข้อมูลโปรโมชั่นลงในตาราง promotions
        $stmt = $conn->prepare("INSERT INTO promotions (title, description, discount_percentage, start_date, end_date, created_at) 
                                 VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$title, $description, $discount_percentage, $start_date, $end_date]);

        // รับ promotion_id ที่ถูกสร้างขึ้น
        $promotion_id = $conn->lastInsertId();

        // บันทึกความสัมพันธ์ในตาราง product_promotions
        $stmt = $conn->prepare("INSERT INTO product_promotions (product_id, promotion_id) VALUES (?, ?)");
        $stmt->execute([$product_id, $promotion_id]);

        $conn->commit();
        $_SESSION['success'] = "เพิ่มโปรโมชั่นสำเร็จ!";
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
    header("location: admin_promotions.php");
    exit();
}

// ลบโปรโมชั่น
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_promotion'])) {
    $promotion_id = intval($_POST['promotion_id']);

    try {
        // เริ่ม transaction
        $conn->beginTransaction();

        // ลบข้อมูลความสัมพันธ์ในตาราง product_promotions ก่อน
        $stmt = $conn->prepare("DELETE FROM product_promotions WHERE promotion_id = ?");
        $stmt->execute([$promotion_id]);

        // ลบโปรโมชั่นจากตาราง promotions
        $stmt = $conn->prepare("DELETE FROM promotions WHERE id = ?");
        $stmt->execute([$promotion_id]);

        $conn->commit();
        $_SESSION['success'] = "ลบโปรโมชั่นสำเร็จ!";
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
    header("location: admin_promotions.php");
    exit();
}
// แก้ไขโปรโมชั่น
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_promotion'])) {
    $promotion_id = intval($_POST['promotion_id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $product_id = intval($_POST['product_ids']);
    $discount_percentage = floatval($_POST['discount_percentage']);
    $start_date = trim($_POST['start_date']);
    $end_date = trim($_POST['end_date']);

    // ตรวจสอบความถูกต้องของข้อมูล
    if (empty($title) || empty($description) || empty($product_id) || empty($discount_percentage) || empty($start_date) || empty($end_date)) {
        $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบถ้วน";
        header("location: admin_promotions.php");
        exit();
    }
    if ($discount_percentage < 0 || $discount_percentage > 100) {
        $_SESSION['error'] = "กรุณาระบุส่วนลดให้อยู่ในช่วง 0-100%";
        header("location: admin_promotions.php");
        exit();
    }
    if ($start_date >= $end_date) {
        $_SESSION['error'] = "วันที่เริ่มต้นต้องน้อยกว่าวันที่สิ้นสุด";
        header("location: admin_promotions.php");
        exit();
    }

    try {
        // เริ่ม transaction
        $conn->beginTransaction();

        // อัปเดตข้อมูลโปรโมชั่น
        $stmt = $conn->prepare("UPDATE promotions SET title = ?, description = ?, discount_percentage = ?, start_date = ?, end_date = ? WHERE id = ?");
        $stmt->execute([$title, $description, $discount_percentage, $start_date, $end_date, $promotion_id]);

        // อัปเดตความสัมพันธ์ในตาราง product_promotions
        $stmt = $conn->prepare("UPDATE product_promotions SET product_id = ? WHERE promotion_id = ?");
        $stmt->execute([$product_id, $promotion_id]);

        $conn->commit();
        $_SESSION['success'] = "แก้ไขโปรโมชั่นสำเร็จ!";
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
    header("location: admin_promotions.php");
    exit();
}

?>