<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']); // ตรวจสอบหน้าปัจจุบัน
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest'; // ถ้ายังไม่ล็อกอินให้เป็น 'guest'
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ร้านขายสัตว์เลี้ยงสวยงาม</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>
    <?php if ($current_page !== 'login.php' && $current_page !== 'register.php'): ?>
        <header class="main-header">
            <h1 class="topic">ร้านขายสัตว์เลี้ยงสวยงาม</h1>
            <ul class="menu nav">
                <?php if ($role === 'admin'): ?>
                    <!-- ✅ เมนูสำหรับ Admin -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'admin_dashboard.php') ? 'active' : ''; ?>" href="/petshop/admin_dashboard.php">แดชบอร์ด</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'admin_products.php') ? 'active' : ''; ?>" href="/petshop/admin_products.php">จัดการสินค้า</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'admin_promotions.php') ? 'active' : ''; ?>" href="/petshop/admin_promotions.php">จัดการโปรโมชัน</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'admin_news.php') ? 'active' : ''; ?>" href="/petshop/admin_news.php">จัดการข่าวสาร</a>
                    </li>
                    <!-- เมนูติดตามสินค้า สำหรับแอดมิน -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'admin_tracking.php') ? 'active' : ''; ?>" href="/petshop/admin_tracking.php">ติดตามสินค้า</a>
                    </li>
                <?php elseif ($role === 'customer'): ?>
                    <!-- ✅ เมนูสำหรับ Customer -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'index.php') ? 'active' : ''; ?>" href="/petshop/index.php">หน้าแรก</a>
                    </li>
                    <!-- เมนูสัตว์เลี้ยง -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'products.php' && isset($_GET['category_id']) && $_GET['category_id'] == 1) ? 'active' : ''; ?>" href="products.php?category_id=1">สัตว์เลี้ยง</a>
                    </li>
                    <!-- เมนูอาหาร/ยา -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'products.php' && isset($_GET['category_id']) && $_GET['category_id'] == 2) ? 'active' : ''; ?>" href="products.php?category_id=2">อาหาร/ยา</a>
                    </li>
                    <!-- เมนูอุปกรณ์ -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'products.php' && isset($_GET['category_id']) && $_GET['category_id'] == 3) ? 'active' : ''; ?>" href="products.php?category_id=3">อุปกรณ์</a>
                    </li>
                    <!-- เมนูข่าวสารสำหรับ Customer -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'news.php') ? 'active' : ''; ?>" href="/petshop/news.php">ข่าวสาร</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'cart.php') ? 'active' : ''; ?>" href="/petshop/cart.php">ตะกร้า</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'orders.php') ? 'active' : ''; ?>" href="/petshop/orders.php">คำสั่งซื้อ</a>
                    </li>
                    <!-- เมนูติดตามสินค้า สำหรับลูกค้า -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'tracking.php') ? 'active' : ''; ?>" href="/petshop/tracking.php">ติดตามสินค้า</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'contact.php') ? 'active' : ''; ?>" href="/petshop/contact.php">ติดต่อ</a>
                    </li>
                <?php else: ?>
                    <!-- ✅ เมนูสำหรับผู้เยี่ยมชม (Guest) -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'index.php') ? 'active' : ''; ?>" href="/petshop/index.php">หน้าแรก</a>
                    </li>
                    <!-- เมนูสัตว์เลี้ยง -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'products.php' && isset($_GET['category_id']) && $_GET['category_id'] == 1) ? 'active' : ''; ?>" href="products.php?category_id=1">สัตว์เลี้ยง</a>
                    </li>
                    <!-- เมนูอาหาร/ยา -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'products.php' && isset($_GET['category_id']) && $_GET['category_id'] == 2) ? 'active' : ''; ?>" href="products.php?category_id=2">อาหาร/ยา</a>
                    </li>
                    <!-- เมนูอุปกรณ์ -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'products.php' && isset($_GET['category_id']) && $_GET['category_id'] == 3) ? 'active' : ''; ?>" href="products.php?category_id=3">อุปกรณ์</a>
                    </li>
                    <!-- เมนูข่าวสารสำหรับ Guest -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'news.php') ? 'active' : ''; ?>" href="/petshop/news.php">ข่าวสาร</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'contact.php') ? 'active' : ''; ?>" href="/petshop/contact.php">ติดต่อ</a>
                    </li>
                <?php endif; ?>

                <?php if (isset($_SESSION['username'])): ?>
                    <!-- ✅ ถ้าผู้ใช้ล็อกอินแล้ว -->
                    <li class="nav-item ms-4">
                        <span class="nav-link text-white fw-bold">👤 <?= $_SESSION['username']; ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-danger text-white px-3 py-1" href="/petshop/logout.php">ออกจากระบบ</a>
                    </li>
                <?php else: ?>
                    <!-- ✅ ถ้ายังไม่ได้ล็อกอิน -->
                    <li class="nav-item ms-4">
                        <a class="nav-link <?= ($current_page == 'login.php') ? 'active' : ''; ?>" href="/petshop/login.php">เข้าสู่ระบบ</a>
                    </li>
                <?php endif; ?>
            </ul>
        </header>
    <?php endif; ?>
</body>
</html>
