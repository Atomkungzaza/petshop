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

// ดึงหมวดหมู่จากฐานข้อมูล
$category_stmt = $conn->prepare("SELECT * FROM categories");
$category_stmt->execute();
$categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);

// ฟังก์ชันอัปโหลดรูปภาพ
function uploadImage($file)
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo "❌ ข้อผิดพลาดการอัปโหลดไฟล์: " . $file['error'];
        return null;
    }
    $target_dir = "uploads/products/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    if (!is_writable($target_dir)) {
        echo "⛔ ไม่มีสิทธิ์เขียนไฟล์ไปที่ $target_dir";
        return null;
    }
    $file_name = time() . "_" . basename($file["name"]);
    $target_file = $target_dir . $file_name;
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $file_name;
    } else {
        echo "❌ move_uploaded_file() ล้มเหลว!";
    }
    return null;
}

// เพิ่มสินค้าใหม่
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $size = trim($_POST['size']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $image_file = $_FILES['image_file'];
    if (empty($name) || empty($description) || empty($size) || empty($stock_quantity) || empty($price) || empty($category_id) || empty($image_file)) {
        $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } else {
        $image_url = uploadImage($image_file);
        if ($image_url) {
            try {
                $stmt = $conn->prepare("INSERT INTO products (name, description, size, stock_quantity, price, category_id, image_url) 
                                        VALUES (:name, :description, :size, :stock_quantity, :price, :category_id, :image_url)");
                $stmt->bindParam(":name", $name);
                $stmt->bindParam(":description", $description);
                $stmt->bindParam(":size", $size);
                $stmt->bindParam(":stock_quantity", $stock_quantity);
                $stmt->bindParam(":price", $price);
                $stmt->bindParam(":category_id", $category_id);
                $stmt->bindParam(":image_url", $image_url);
                $stmt->execute();
                $_SESSION['success'] = "เพิ่มสินค้าสำเร็จ!";
            } catch (PDOException $e) {
                $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = "อัปโหลดรูปภาพไม่สำเร็จ";
        }
    }
    header("location: admin_products.php");
    exit();
}

// ลบสินค้า
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_product'])) {
    $product_id = intval($_POST['product_id']);
    try {
        $stmt = $conn->prepare("DELETE FROM products WHERE id = :product_id");
        $stmt->bindParam(":product_id", $product_id);
        $stmt->execute();
        $_SESSION['success'] = "ลบสินค้าสำเร็จ!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
    header("location: admin_products.php");
    exit();
}

// ดึงรายการสินค้าทั้งหมด
$stmt = $conn->prepare("SELECT products.*, categories.name AS category_name FROM products
                        LEFT JOIN categories ON products.category_id = categories.id");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
// อัปเดตข้อมูลสินค้า
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product'])) {
    $product_id = intval($_POST['product_id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $size = trim($_POST['size']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $image_file = $_FILES['image_file'];

    if (empty($name) || empty($description) || empty($size) || empty($stock_quantity) || empty($price) || empty($category_id)) {
        $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } else {
        $image_url = null;
        if (!empty($image_file['name'])) {
            $image_url = uploadImage($image_file);
        } else {
            $stmt = $conn->prepare("SELECT image_url FROM products WHERE id = :product_id");
            $stmt->bindParam(":product_id", $product_id);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            $image_url = $product['image_url']; // ใช้รูปเดิมถ้าไม่ได้อัปโหลดใหม่
        }

        try {
            $stmt = $conn->prepare("UPDATE products SET name = :name, description = :description, size = :size, 
                                    stock_quantity = :stock_quantity, price = :price, category_id = :category_id, 
                                    image_url = :image_url WHERE id = :product_id");
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":description", $description);
            $stmt->bindParam(":size", $size);
            $stmt->bindParam(":stock_quantity", $stock_quantity);
            $stmt->bindParam(":price", $price);
            $stmt->bindParam(":category_id", $category_id);
            $stmt->bindParam(":image_url", $image_url);
            $stmt->bindParam(":product_id", $product_id);
            $stmt->execute();
            $_SESSION['success'] = "อัปเดตสินค้าสำเร็จ!";
        } catch (PDOException $e) {
            $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
    header("location: admin_products.php");
    exit();
}

