<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

$type = trim($_GET['type'] ?? '');
$valid_types = ['UCLan Hoodie', 'UCLan Logo Tshirt', 'UCLan Logo Jumper'];
if (!in_array($type, $valid_types, true)) {
    $type = '';
}

$search = trim($_GET['search'] ?? '');
// Additional research: synonym expansion lets one search term match spelling
// variations, e.g. "tshirt", "t shirts", and "tee" all match "T-shirt".
// https://www.php.net/manual/en/function.str-ireplace.php
function expand_search_terms($search)
{
    // Longer variants are listed first so "t shirts" is replaced before "t shirt".
    $synonym_groups = [
        'T-shirt' => ['t-shirts', 'tshirts', 't shirts', 'tee shirts', 'tee shirt', 'tshirt', 't shirt', 'tees', 'tee'],
        'Hoodie'  => ['hoodies', 'hoodys', 'hoody', 'hooded tops', 'hooded top'],
        'Jumper'  => ['jumpers', 'sweatshirts', 'sweatshirt', 'sweaters', 'sweater', 'pullovers', 'pullover'],
    ];

    $terms = [$search];
    $needle = strtolower($search);

    foreach ($synonym_groups as $canonical => $variants) {
        foreach ($variants as $variant) {
            if (strpos($needle, $variant) !== false) {
                $terms[] = trim(str_ireplace($variant, $canonical, $search));
                break;
            }
        }
    }

    return array_values(array_unique($terms));
}

$products = [];
$query_error = '';

$stock_check = mysqli_query($connection, "SHOW COLUMNS FROM tbl_products LIKE 'product_stock'");
$has_stock_column = $stock_check && mysqli_num_rows($stock_check) > 0;
$stock_select = $has_stock_column
    ? 'product_stock'
    : "'good-stock' AS product_stock";

$sql = "SELECT product_id, product_title, product_desc, product_image, product_price, product_type, {$stock_select}
        FROM tbl_products WHERE 1=1";
$params = [];
$types_str = '';

if ($type !== '') {
    $sql .= " AND product_type = ?";
    $params[] = $type;
    $types_str .= 's';
}

if ($search !== '') {
    // Every expanded synonym is joined with OR, still inside a prepared statement.
    $search_terms = expand_search_terms($search);
    $search_clauses = [];
    foreach ($search_terms as $term) {
        $search_clauses[] = "(product_title LIKE ? OR product_desc LIKE ?)";
        $like = '%' . $term . '%';
        $params[] = $like;
        $params[] = $like;
        $types_str .= 'ss';
    }
    $sql .= " AND (" . implode(" OR ", $search_clauses) . ")";
}

$sql .= " ORDER BY product_type, product_title";

$stmt = mysqli_prepare($connection, $sql);
if ($stmt) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types_str, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = false;
}

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    if ($stmt) {
        mysqli_stmt_close($stmt);
    }
} else {
    $query_error = 'Products could not be loaded. Confirm that uclan_shop_corrected.sql or database_fix.sql has been imported.';
    error_log('Product query failed: ' . mysqli_error($connection));
}

$page_heading = $type === '' ? 'All Legacy Products' : $type;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Browse UCLan legacy merchandise from the shop database.">
    <title>UCLan Legacy Shop | Products</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="header-inner">
    <a href="index.php" class="site-logo" aria-label="UCLan Student Union Shop Home">
                <img src="images/logo_reverse.png" alt="University of Lancashire logo" class="header-logo-img">
                <div class="logo-text"></div>
                <span class="site-title">Legacy Shop</span>
            </a>
           
        <button class="hamburger" id="hamburgerBtn" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="primaryNav">&#9776;</button>
        <nav aria-label="Primary navigation" id="primaryNav">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php" class="active" aria-current="page">Products</a></li>
                <li><a href="cart.php" class="cart-nav-link" aria-label="Shopping cart">&#128722; Cart <span class="cart-count-badge" id="cartBadge">0</span></a></li>
                <?php if (is_logged_in()): ?>
                    <li><a href="account.php" class="user-greeting-link">Hi, <?php echo h($_SESSION['user_name']); ?></a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

<main>
    <div class="page-title-strip">
        <h1><?php echo h($page_heading); ?></h1>
        <p>Product details and prices are loaded directly from MySQL.</p>
    </div>

    <nav aria-label="Breadcrumb navigation">
        <p class="breadcrumb"><a href="index.php">Home</a><span aria-hidden="true">/</span><span aria-current="page">Products</span></p>
    </nav>

    <nav class="category-links" aria-label="Product categories">
        <a href="products.php"<?php echo $type === '' ? ' class="active"' : ''; ?>>All</a>
        <a href="products.php?type=<?php echo rawurlencode('UCLan Logo Tshirt'); ?>"<?php echo $type === 'UCLan Logo Tshirt' ? ' class="active"' : ''; ?>>T-shirts</a>
        <a href="products.php?type=<?php echo rawurlencode('UCLan Logo Jumper'); ?>"<?php echo $type === 'UCLan Logo Jumper' ? ' class="active"' : ''; ?>>Jumpers</a>
        <a href="products.php?type=<?php echo rawurlencode('UCLan Hoodie'); ?>"<?php echo $type === 'UCLan Hoodie' ? ' class="active"' : ''; ?>>Hoodies</a>
    </nav>
     <form method="get" action="products.php" class="search-bar" role="search" aria-label="Search products">
        <?php if ($type !== ''): ?>
            <input type="hidden" name="type" value="<?php echo h($type); ?>">
        <?php endif; ?>
        <label for="search" class="visually-hidden">Search by product name or colour </label>
        <input type="text" id="search" name="search" value="<?php echo h($search); ?>" placeholder="Search by product name or colour..." style="padding:0.45rem 0.9rem; border-radius:20px; border:2px solid var(--uclan-blue); min-width:200px;">
        <button type="submit" class="btn btn-primary btn-sm">Search</button>
        <?php if ($search !== ''): ?>
            <a href="products.php<?php echo $type !== '' ? '?type=' . rawurlencode($type) : ''; ?>" class="btn btn-secondary btn-sm">Reset</a>
        <?php endif; ?>
    </form>
<br>
    <section aria-labelledby="filterHeading">
        <div class="filter-bar" role="group" aria-labelledby="filterHeading">
            <span id="filterHeading"><strong>Filter by stock:</strong></span>
            <button type="button" data-filter="all" aria-pressed="true" class="active">All Items</button>
            <button type="button" data-filter="in-stock" aria-pressed="false">In Stock</button>
            <button type="button" data-filter="last-few" aria-pressed="false">Last Few</button>
            <button type="button" data-filter="out-of-stock" aria-pressed="false">Out of Stock</button>
            
        </div>
    </section>
 <p id="resultsCount" aria-live="polite" class="results-count"></p>

    <?php if ($query_error !== ''): ?>
        <div class="form-errors" role="alert"><p><?php echo h($query_error); ?></p></div>
    <?php elseif (!$products): ?>
        <p class="empty-state">No products were found for this category.</p>
    <?php else: ?>
        <section aria-labelledby="productsHeading">
            <h2 id="productsHeading" class="section-heading">Available Products</h2>
            <div class="products-grid" id="productsGrid">
                <?php foreach ($products as $product): ?>
                    <?php
                    $stock = $product['product_stock'];
                    $is_out = $stock === 'out-of-stock';
                    ?>
                    <!-- <article class="product-card<?php echo $is_out ? ' out-of-stock' : ''; ?>" data-stock="<?php echo h($stock); ?>">
                        <img src="<?php echo h($product['product_image']); ?>" alt="<?php echo h($product['product_title']); ?>">
                        <div class="product-card-body">
                            <h3><?php echo h($product['product_title']); ?></h3>
                            <p class="product-colour"><?php echo h($product['product_type']); ?></p>
                            <span class="stock-badge <?php echo h(stock_badge_class($stock)); ?>"><?php echo h(stock_label($stock)); ?></span>
                            <p class="product-price"><?php echo h(format_price($product['product_price'])); ?></p>
                        </div>
                        <div class="product-card-footer">
                            <?php if ($is_out): ?>
                                <span class="btn btn-sm product-disabled" aria-disabled="true">Out of Stock</span>
                            <?php else: ?>
                                <a href="item.php?id=<?php echo (int) $product['product_id']; ?>" class="btn btn-primary btn-sm view-btn">View Details</a>
                            <?php endif; ?>
                        </div>
                    </article> -->
                    <?php
$cardTag = $is_out ? 'article' : 'a';
$cardHref = $is_out ? '' : ' href="item.php?id=' . (int) $product['product_id'] . '"';
$cardClass = 'product-card' . ($is_out ? ' out-of-stock' : ' product-card-clickable');
?>
<<?php echo $cardTag . $cardHref; ?> class="<?php echo $cardClass; ?>" data-stock="<?php echo h($stock); ?>">
    <img src="<?php echo h($product['product_image']); ?>" alt="<?php echo h($product['product_title']); ?>">
    <div class="product-card-body">
        <h3><?php echo h($product['product_title']); ?></h3>
        <p class="product-colour"><?php echo h($product['product_type']); ?></p>
        <span class="stock-badge <?php echo h(stock_badge_class($stock)); ?>"><?php echo h(stock_label($stock)); ?></span>
        <p class="product-price"><?php echo h(format_price($product['product_price'])); ?></p>
    </div>
    <div class="product-card-footer">
        <?php if ($is_out): ?>
            <span class="btn btn-sm product-disabled" aria-disabled="true">Out of Stock</span>
        <?php else: ?>
            <span class="btn btn-primary btn-sm view-btn" aria-hidden="true">View Details</span>
        <?php endif; ?>
    </div>
</<?php echo $cardTag; ?>>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</main>

<footer>
    <p>&copy; 2026 UCLan Legacy Shop, University of Lancashire Student Union | <a href="index.php">Home</a> | <a href="products.php">Products</a> | <a href="cart.php">Cart</a></p>
</footer>

<script>
(function () {
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const primaryNav = document.getElementById('primaryNav');

    if (hamburgerBtn && primaryNav) {
        hamburgerBtn.addEventListener('click', function () {
            const isOpen = primaryNav.classList.toggle('open');
            hamburgerBtn.setAttribute('aria-expanded', isOpen.toString());
        });
    }

    function readCart() {
        try {
            const value = JSON.parse(localStorage.getItem('uclanCart'));
            return Array.isArray(value) ? value : [];
        } catch (error) {
            return [];
        }
    }

    function updateCartBadge() {
        const total = readCart().reduce(function (sum, item) {
            return sum + (Number.parseInt(item.qty, 10) || 0);
        }, 0);
        const badge = document.getElementById('cartBadge');
        if (badge) badge.textContent = total;
    }

    const cards = Array.from(document.querySelectorAll('.product-card'));
    const count = document.getElementById('resultsCount');
    const buttons = Array.from(document.querySelectorAll('[data-filter]'));

    function applyFilter(filter) {
        let visible = 0;
        cards.forEach(function (card) {
            const stock = card.dataset.stock;
            const matches = filter === 'all'
                || (filter === 'in-stock' && (stock === 'good-stock' || stock === 'last-few'))
                || stock === filter;
            card.classList.toggle('hidden', !matches);
            if (matches) visible++;
        });

        if (count) count.textContent = 'Showing ' + visible + ' of ' + cards.length + ' products.';

        buttons.forEach(function (button) {
            const selected = button.dataset.filter === filter;
            button.classList.toggle('active', selected);
            button.setAttribute('aria-pressed', selected.toString());
        });
    }

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            applyFilter(button.dataset.filter);
        });
    });

    applyFilter('all');
    updateCartBadge();
}());
</script>
</body>
</html>
