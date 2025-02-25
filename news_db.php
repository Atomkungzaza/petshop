<?php
// เริ่มการใช้งาน session ถ้ายังไม่ได้เริ่ม
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// รวมไฟล์เชื่อมต่อฐานข้อมูล
require_once 'config/db.php';

try {
    // คำสั่ง SQL สำหรับดึงข้อมูลข่าวสารทั้งหมดจากฐานข้อมูล
    $sql = "SELECT * FROM news ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql); // ใช้ $conn แทน $pdo
    $stmt->execute();

    // เก็บผลลัพธ์ทั้งหมดในตัวแปร $news
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: " . $e->getMessage();
}
?>
