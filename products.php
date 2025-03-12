<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$search_term = isset($_GET['search']) ? $_GET['search'] : ''; // Get search term from query string

// Fetch category
$category_stmt = $conn->prepare("SELECT id, name FROM categories WHERE id = :category_id");
$category_stmt->bindParam(":category_id", $category_id, PDO::PARAM_INT);
$category_stmt->execute();
$category = $category_stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    die("ไม่พบหมวดหมู่ที่คุณเลือก");
}

// Fetch products with optional search functionality
$product_query = "
    SELECT id, name, description, size, stock_quantity, price, image_url 
    FROM products 
    WHERE category_id = :category_id
";
if ($search_term) {
    $product_query .= " AND name LIKE :search_term"; // Add search filter
}

$product_query .= " ORDER BY id ASC";

$product_stmt = $conn->prepare($product_query);
$product_stmt->bindParam(":category_id", $category_id, PDO::PARAM_INT);

if ($search_term) {
    $search_term_like = "%" . $search_term . "%";
    $product_stmt->bindParam(":search_term", $search_term_like, PDO::PARAM_STR);
}

$product_stmt->execute();
$products = $product_stmt->fetchAll(PDO::FETCH_ASSOC);

// คำนวณราคาหลังส่วนลด
foreach ($products as $index => $product) {
    $promotion_stmt = $conn->prepare("
        SELECT p.discount_percentage FROM promotions p
        JOIN product_promotions pp ON p.id = pp.promotion_id
        WHERE pp.product_id = :product_id 
        AND p.start_date <= CURDATE() AND p.end_date >= CURDATE()
    ");
    $promotion_stmt->bindParam(":product_id", $product['id'], PDO::PARAM_INT);
    $promotion_stmt->execute();
    $promotion = $promotion_stmt->fetch(PDO::FETCH_ASSOC);

    $discounted_price = $product['price'];
    if ($promotion) {
        $discount_percentage = $promotion['discount_percentage'];
        $discounted_price = $product['price'] * (1 - $discount_percentage / 100);
    }

    $products[$index]['discounted_price'] = $discounted_price;

    // Shorten description to fit in one line (you can change the length as needed)
    $max_description_length = 100; // You can adjust this length
    if (strlen($product['description']) > $max_description_length) {
        $products[$index]['short_description'] = substr($product['description'], 0, $max_description_length) . '...';
    } else {
        $products[$index]['short_description'] = $product['description'];
    }
}
?>

<?php include 'layouts/header.php'; ?>

<div class="container mt-4">
    <h2>สินค้าทั้งหมดในหมวดหมู่ <?= htmlspecialchars($category['name']); ?></h2>

    <!-- Search form -->
    <form action="products.php" method="GET" class="mb-4">
        <input type="hidden" name="category_id" value="<?= $category_id; ?>">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="ค้นหาชื่อสินค้า" value="<?= htmlspecialchars($search_term); ?>">
            <button type="submit" class="btn btn-primary">ค้นหา</button>
        </div>
    </form>

    <div class="row">
        <?php if (empty($products)): ?>
            <p>ไม่พบสินค้าที่คุณค้นหา</p>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="uploads/products/<?= htmlspecialchars($product['image_url']); ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text"><?= nl2br(htmlspecialchars($product['short_description'])); ?></p>

                            <h5 class="text-success">
                                <?php if ($product['discounted_price'] < $product['price']): ?>
                                    <del>ราคา <?= number_format($product['price'], 2); ?> บาท</del> 
                                    ลดเหลือ <?= number_format($product['discounted_price'], 2); ?> บาท
                                <?php else: ?>
                                    <?= number_format($product['price'], 2); ?> บาท
                                <?php endif; ?>
                            </h5>

                            <p><strong>ขนาด:</strong> <?= htmlspecialchars($product['size']); ?></p>
                            <p><strong>จำนวนคงเหลือ:</strong> 
                                <?= ($product['stock_quantity'] > 0) ? $product['stock_quantity'] : '<span class="text-danger">สินค้าหมด</span>'; ?>
                            </p>

                            <?php if ($product['stock_quantity'] > 0): ?>
                                <form action="cart.php" method="POST">
                                    <input type="hidden" name="add" value="<?= $product['id']; ?>">
                                    <div class="input-group mb-3">
                                        <input type="number" name="quantity" class="form-control text-center" value="1" min="1" max="<?= $product['stock_quantity']; ?>">
                                        <button type="submit" class="btn btn-success">เพิ่มลงตะกร้า</button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-secondary" disabled>สินค้าหมด</button>
                            <?php endif; ?>

                            <a href="product_details.php?id=<?= $product['id']; ?>" class="btn btn-primary mt-3">ดูรายละเอียด</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'layouts/footer.php'; ?>
