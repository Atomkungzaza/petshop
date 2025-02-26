<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

// ตรวจสอบสิทธิ์ว่าเป็น admin หรือไม่
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แดชบอร์ดผู้ดูแล</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #e9f5e9;
        }
        .sidebar {
            background-color: #2d6a4f;
            color: white;
            min-height: 100vh;
            padding-top: 20px;
        }
        .sidebar a {
            color: white;
            display: block;
            padding: 10px;
            text-decoration: none;
            border-bottom: 1px solid #fff;
        }
        .sidebar a:hover {
            background-color: #40916c;
        }
        .content {
            padding: 20px;
        }
        .chart-container {
            width: 100%;
            max-width: 800px;
            margin: auto;
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 d-none d-md-block sidebar">
            <h4 class="text-center">เมนูแอดมิน</h4>
            <a href="?page=members">ข้อมูลสมาชิก</a>
            <a href="?page=products">ข้อมูลสินค้า</a>
            <a href="?page=news">ข้อมูลข่าวสาร</a>
            <a href="?page=orders">ข้อมูลการสั่งซื้อ</a>
            <a href="?page=payments">ข้อมูลการชำระเงิน</a>
            <a href="?page=promotions">ข้อมูลโปรโมชัน</a>
            <a href="admin_index.php" class="btn btn-warning w-100 mt-3">🔙 กลับหน้าแรก</a>
        </nav>

        <!-- Main Content -->
        <main class="col-md-10 content">
            <?php
            $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

            echo "<h2 class='mb-4 text-success'>แดชบอร์ดแอดมิน</h2>";

            if ($page == 'members') {
                include 'admin_dashboard_db.php?type=members';
            } elseif ($page == 'products') {
                include 'admin_dashboard_db.php?type=products';
            } elseif ($page == 'news') {
                include 'admin_dashboard_db.php?type=news';
            } elseif ($page == 'orders') {
                include 'admin_dashboard_db.php?type=orders';
            } elseif ($page == 'payments') {
                include 'admin_dashboard_db.php?type=payments';
            } elseif ($page == 'promotions') {
                include 'admin_dashboard_db.php?type=promotions';
            } else {
                echo "<p>เลือกเมนูเพื่อดูข้อมูล</p>";
            }
            ?>
        </main>
    </div>
</div>

</body>
</html>
