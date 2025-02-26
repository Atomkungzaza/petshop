<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แดชบอร์ดแอดมิน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { display: flex; }
        .sidebar { width: 250px; height: 100vh; background: #28a745; padding: 20px; color: white; }
        .sidebar a { color: white; display: block; padding: 10px; margin-bottom: 10px; text-decoration: none; background: rgba(255,255,255,0.1); border-radius: 5px; }
        .sidebar a:hover { background: rgba(255,255,255,0.3); }
        .content { flex-grow: 1; padding: 20px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h4>Admin Panel</h4>
    <a href="?page=members">ข้อมูลสมาชิก</a>
    <a href="?page=products">ข้อมูลสินค้า</a>
    <a href="?page=news">ข้อมูลข่าวสาร</a>
    <a href="?page=orders">ข้อมูลการสั่งซื้อ</a>
    <a href="?page=payments">ข้อมูลการชำระเงิน</a>
    <a href="?page=promotions">ข้อมูลโปรโมชัน</a>
    <a href="admin_index.php" class="btn btn-light mt-3">🔙 กลับหน้าหลัก</a>
</div>

<div class="content">
    <h2>แดชบอร์ดแอดมิน</h2>

    <?php
    $page = isset($_GET['page']) ? $_GET['page'] : 'members';
    include "admin_dashboard_db.php";
    ?>

    <h4 class="mt-4">เลือกการแสดงผล</h4>
    <button onclick="showTable()" class="btn btn-primary">ตาราง</button>
    <button onclick="showBarChart()" class="btn btn-success">กราฟแท่ง</button>
    <button onclick="showPieChart()" class="btn btn-warning">แผนภูมิวงกลม</button>

    <div id="tableView" class="mt-3"><?php echo $tableData; ?></div>

    <div id="chartView" class="mt-3" style="display: none;">
        <canvas id="chartCanvas"></canvas>
    </div>
</div>

<script>
    function showTable() {
        document.getElementById('tableView').style.display = 'block';
        document.getElementById('chartView').style.display = 'none';
    }

    function showBarChart() { loadChart('bar'); }
    function showPieChart() { loadChart('pie'); }

    function loadChart(type) {
        fetch('chart_data.php?page=<?= $page ?>')
        .then(response => response.json())
        .then(data => {
            document.getElementById('tableView').style.display = 'none';
            document.getElementById('chartView').style.display = 'block';

            new Chart(document.getElementById('chartCanvas').getContext('2d'), {
                type: type,
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'จำนวน',
                        data: data.values,
                        backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8']
                    }]
                }
            });
        });
    }
</script>

</body>
</html>
