<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

// ตรวจสอบค่าของ `page` และกำหนดชื่อหัวข้อที่เกี่ยวข้อง
$page = isset($_GET['page']) ? $_GET['page'] : 'members';
$pageTitleMap = [
    'members' => 'ข้อมูลสมาชิก',
    'products' => 'ข้อมูลสินค้า',
    'categories' => 'ข้อมูลประเภทสินค้า',
    'news' => 'ข้อมูลข่าวสาร',
    'orders' => 'ข้อมูลการสั่งซื้อ',
    'payments' => 'ข้อมูลการชำระเงิน',
    'promotions' => 'ข้อมูลโปรโมชัน',
    'revenue' => 'ข้อมูลรายสถิติการขาย'
];
$pageTitle = $pageTitleMap[$page] ?? 'Admin Dashboard';
?>

<?php include 'layouts/header.php'; ?> <!-- นำเข้า header -->

<style>
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 250px;
        height: 100vh;
        background: #198754;
        padding-top: 60px;
        color: white;
    }
    .sidebar .nav-link {
        color: white;
        padding: 10px;
        margin: 5px;
        border-radius: 5px;
    }
    .sidebar .nav-link:hover {
        background: rgba(255,255,255,0.2);
    }
    .content {
        margin-left: 250px;
        padding: 20px;
    }
</style>

<div class="sidebar">
    <h4 class="text-center">Admin Panel</h4>
    <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link" href="?page=members">ข้อมูลสมาชิก</a></li>
        <li class="nav-item"><a class="nav-link" href="?page=products">ข้อมูลสินค้า</a></li>
        <li class="nav-item"><a class="nav-link" href="?page=categories">ข้อมูลประเภทสินค้า</a></li>
        <li class="nav-item"><a class="nav-link" href="?page=news">ข้อมูลข่าวสาร</a></li>
        <li class="nav-item"><a class="nav-link" href="?page=orders">ข้อมูลการสั่งซื้อ</a></li>
        <li class="nav-item"><a class="nav-link" href="?page=payments">ข้อมูลการชำระเงิน</a></li>
        <li class="nav-item"><a class="nav-link" href="?page=promotions">ข้อมูลโปรโมชัน</a></li>
        <li class="nav-item"><a class="nav-link" href="?page=revenue">💰 ข้อมูลสถิติการขาย</a></li>
        <li class="nav-item mt-3"><a class="nav-link btn btn-light text-dark" href="admin_index.php">🔙 กลับหน้าหลัก</a></li>
    </ul>
</div>

<div class="content">
    <h2><?= htmlspecialchars($pageTitle) ?></h2> <!-- หัวของหน้าเป็นชื่อข้อมูล -->

    <?php include "admin_dashboard_db.php"; ?>
    
    <div id="tableView" class="mt-3"><?php echo $tableData; ?></div>

    <div id="chartView" class="mt-3" style="display: none;">
        <canvas id="chartCanvas"></canvas>
    </div>
</div>

</body>
</html>
