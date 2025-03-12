<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'members';
$pageTitleMap = [
    'members' => 'ข้อมูลสมาชิก',
    'products' => 'ข้อมูลสินค้า',
    'categories' => 'ข้อมูลประเภทสินค้า',
    'news' => 'ข้อมูลข่าวสาร',
    'orders' => 'ข้อมูลการสั่งซื้อ',
    'payments' => 'ข้อมูลการชำระเงิน',
    'promotions' => 'ข้อมูลโปรโมชัน',
    'revenue' => 'ข้อมูลสถิติการขาย'
];
$pageTitle = $pageTitleMap[$page] ?? 'Admin Dashboard';

$limit = 30;
$pageNumber = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($pageNumber - 1) * $limit;

$queryMap = [
    'members' => "SELECT id, username, email FROM users LIMIT $limit OFFSET $offset",
    'products' => "SELECT id, name, price, stock_quantity FROM products LIMIT $limit OFFSET $offset",
   'categories' => "SELECT id, name, description FROM categories LIMIT $limit OFFSET $offset",
    'news' => "SELECT id, title, created_at FROM news LIMIT $limit OFFSET $offset",
    'orders' => "SELECT id, user_id, total_price, status FROM orders LIMIT $limit OFFSET $offset",
    'payments' => "SELECT id, order_id, amount, status FROM payments LIMIT $limit OFFSET $offset",
    'promotions' => "SELECT id, title, discount_percentage FROM promotions LIMIT $limit OFFSET $offset",
    'revenue' => "
        SELECT 
            p.id AS product_id, 
            p.name AS product_name, 
            SUM(oi.quantity) AS total_sold, 
            SUM(oi.quantity * oi.price) AS total_revenue
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        GROUP BY p.id, p.name
        ORDER BY total_revenue DESC
        LIMIT $limit OFFSET $offset
    "
];

$sql = $queryMap[$page];
$stmt = $conn->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tableData = "<table class='table table-striped'><tr>";
foreach (array_keys($rows[0]) as $col) {
    $tableData .= "<th>{$col}</th>";
}
$tableData .= "</tr>";

foreach ($rows as $row) {
    $tableData .= "<tr>";
    foreach ($row as $cell) {
        $tableData .= "<td>{$cell}</td>";
    }
    $tableData .= "</tr>";
}
$tableData .= "</table>";

// คำนวณรายได้รวมทุกสินค้า (เฉพาะหน้า revenue)
if ($page === 'revenue') {
    $sumSql = "SELECT SUM(oi.quantity * oi.price) AS total_revenue_all FROM order_items oi";
    $sumStmt = $conn->prepare($sumSql);
    $sumStmt->execute();
    $totalRevenueAll = $sumStmt->fetch(PDO::FETCH_ASSOC)['total_revenue_all'];
    $tableData .= "<h4 class='text-success mt-3'>💰 รายได้รวมทั้งหมด: " . number_format($totalRevenueAll, 2) . " บาท</h4>";
}

// ระบบแบ่งหน้า (pagination)
$countSqlMap = [
    'members' => "SELECT COUNT(*) AS count FROM users",
    'products' => "SELECT COUNT(*) AS count FROM products",
    'categories' => "SELECT COUNT(*) AS count FROM categories",
    'news' => "SELECT COUNT(*) AS count FROM news",
    'orders' => "SELECT COUNT(*) AS count FROM orders",
    'payments' => "SELECT COUNT(*) AS count FROM payments",
    'promotions' => "SELECT COUNT(*) AS count FROM promotions",
    'revenue' => "SELECT COUNT(DISTINCT product_id) AS count FROM order_items"
];

$countSql = $countSqlMap[$page];
$countStmt = $conn->prepare($countSql);
$countStmt->execute();
$totalRows = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
$totalPages = ceil($totalRows / $limit);

if ($totalPages > 1) {
    $tableData .= "<nav><ul class='pagination'>";
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i === $pageNumber ? 'active' : '';
        $tableData .= "<li class='page-item $active'><a class='page-link' href='?page=$page&p=$i'>$i</a></li>";
    }
    $tableData .= "</ul></nav>";
}
?>
