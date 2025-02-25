<?php
// เริ่มการใช้งาน session ถ้ายังไม่ได้เริ่ม
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// รวมไฟล์เชื่อมต่อฐานข้อมูล
require_once 'config/db.php';

// ตรวจสอบการรับค่าจากฟอร์มการเพิ่มข่าว
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับข้อมูลจากฟอร์ม
    $title = $_POST['title'];
    $content = $_POST['content'];
    
    // การจัดการไฟล์ภาพ
    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $upload_dir = 'uploads/news_images/';
    $image_path = $upload_dir . uniqid() . '-' . basename($image);

    // ตรวจสอบว่าโฟลเดอร์ปลายทางมีอยู่หรือไม่
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // ย้ายไฟล์ภาพไปยังโฟลเดอร์ที่กำหนด
    if (move_uploaded_file($image_tmp, $image_path)) {
        try {
            // คำสั่ง SQL สำหรับเพิ่มข้อมูลข่าวสาร
            $sql = "INSERT INTO news (title, content, image_url, created_at) VALUES (:title, :content, :image_url, NOW())";
            $stmt = $conn->prepare($sql); // ใช้ $conn แทน $pdo

            // Bind ค่าต่างๆ
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':image_url', $image_path);

            // Execute คำสั่ง SQL
            $stmt->execute();

            // รีไดเรคไปยังหน้าแสดงผลข่าวสาร
            header("Location: admin_news.php");
            exit();
        } catch (PDOException $e) {
            echo "เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: " . $e->getMessage();
        }
    } else {
        echo "การอัพโหลดรูปภาพไม่สำเร็จ.";
    }
}
?>
