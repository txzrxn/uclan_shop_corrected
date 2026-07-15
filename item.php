<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$product = null;
$reviews = [];
$review_errors = [];
$review_title = '';
$review_desc = '';
$review_rating = '';

$stock_check = mysqli_query($connection, "SHOW COLUMNS FROM tbl_products LIKE 'product_stock'");
$has_stock_column = $stock_check && mysqli_num_rows($stock_check) > 0;
$stock_select = $has_stock_column
    ? 'product_stock'
    : "'good-stock' AS product_stock";

if ($product_id) {
    $sql = "SELECT product_id, product_title, product_desc, product_image, product_price, product_type, {$stock_select}
            FROM tbl_products
            WHERE product_id = ?
            LIMIT 1";
    $stmt = mysqli_prepare($connection, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $product_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result) ?: null;
        mysqli_stmt_close($stmt);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!$product) {
        $review_errors[] = 'The selected product does not exist.';
    } elseif (!is_logged_in()) {
        $review_errors[] = 'You must log in before submitting a review.';
    } elseif (!valid_csrf_token($_POST['csrf_token'] ?? '')) {
        $review_errors[] = 'The form session expired. Refresh the page and try again.';
    } else {
        $review_title = trim($_POST['review_title'] ?? '');
        $review_desc = trim($_POST['review_desc'] ?? '');
        $review_rating = trim($_POST['review_rating'] ?? '');

        if ($review_title === '' || text_length($review_title) > 120) {
            $review_errors[] = 'Enter a review title of no more than 120 characters.';
        }

        if ($review_desc === '' || text_length($review_desc) > 1500) {
            $review_errors[] = 'Enter a review of no more than 1500 characters.';
        }

        if (!in_array($review_rating, ['1', '2', '3', '4', '5'], true)) {
            $review_errors[] = 'Choose a rating from 1 to 5.';
        }

        if (!$review_errors) {
            $user_id = (int) $_SESSION['user_id'];
            $rating = (int) $review_rating;
            $insert = mysqli_prepare(
                $connection,
                'INSERT INTO tbl_reviews (user_id, product_id, review_title, review_desc, review_rating) VALUES (?, ?, ?, ?, ?)'
            );

            if ($insert) {
                mysqli_stmt_bind_param($insert, 'iissi', $user_id, $product_id, $review_title, $review_desc, $rating);
                $saved = mysqli_stmt_execute($insert);
                mysqli_stmt_close($insert);

                if ($saved) {
                    header('Location: item.php?id=' . $product_id . '&review=added#reviews');
                    exit;
                }
            }

            error_log('Review insert failed: ' . mysqli_error($connection));
            $review_errors[] = 'The review could not be saved. Check the tbl_reviews table and try again.';
        }
    }
}

if ($product) {
    $review_query = mysqli_prepare(
        $connection,
        'SELECT r.review_id, r.review_title, r.review_desc, r.review_rating, r.review_timestamp, u.full_name
         FROM tbl_reviews r
         INNER JOIN users u ON u.id = r.user_id
         WHERE r.product_id = ?
         ORDER BY r.review_timestamp DESC, r.review_id DESC'
    );

    if ($review_query) {
        mysqli_stmt_bind_param($review_query, 'i', $product_id);
        mysqli_stmt_execute($review_query);
        $review_result = mysqli_stmt_get_result($review_query);

        while ($review = mysqli_fetch_assoc($review_result)) {
            $reviews[] = $review;
        }

        mysqli_stmt_close($review_query);
    }
}

$average_rating = 0;
if ($reviews) {
    $rating_total = 0;
    foreach ($reviews as $review) {
        $rating_total += (int) $review['review_rating'];
    }
    $average_rating = $rating_total / count($reviews);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="View a UCLan product, add it to the cart, and read customer reviews.">
    <title><?php echo $product ? h($product['product_title']) . ' | UCLan Legacy Shop' : 'Product Not Found | UCLan Legacy Shop'; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="header-inner">
        <a href="index.php" class="logo-link" aria-label="UCLan Legacy Shop Home">
            <img src="images/logo_reverse.png" alt="University of Lancashire logo">
            <span class="site-title">Legacy Shop</span>
        </a>
        <button class="hamburger" id="hamburgerBtn" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="primaryNav">&#9776;</button>
        <nav aria-label="Primary navigation" id="primaryNav">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
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
    <nav aria-label="Breadcrumb navigation">
        <p class="breadcrumb">
            <a href="index.php">Home</a><span aria-hidden="true">/</span>
            <a href="products.php">Products</a><span aria-hidden="true">/</span>
            <span aria-current="page"><?php echo $product ? h($product['product_title']) : 'Not Found'; ?></span>
        </p>
    </nav>

    <a href="products.php" class="back-link">&#8592; Back to Products</a>
    

    <?php if (!$product): ?>
        <div class="form-errors" role="alert">
            <p>No valid product was selected. Use a product link from the Products page.</p>
        </div>
    <?php else: ?>
        <section class="item-container" aria-labelledby="itemName">
            <figure class="item-image-wrapper">
                <img src="<?php echo h($product['product_image']); ?>" alt="<?php echo h($product['product_title']); ?>">
            </figure>

            <article class="item-details">
                <h1 id="itemName"><?php echo h($product['product_title']); ?></h1>
                <p class="item-colour"><?php echo h($product['product_type']); ?></p>
                <p class="item-price"><?php echo h(format_price($product['product_price'])); ?></p>
                <p class="item-stock">
                    <span class="stock-badge <?php echo h(stock_badge_class($product['product_stock'])); ?>"><?php echo h(stock_label($product['product_stock'])); ?></span>
                </p>
                <p class="item-desc"><?php echo h($product['product_desc']); ?></p>

                <?php if ($reviews): ?>
                    <p class="rating-summary"><strong><?php echo number_format($average_rating, 1); ?>/5</strong> from <?php echo count($reviews); ?> review<?php echo count($reviews) === 1 ? '' : 's'; ?>.</p>
                <?php else: ?>
                    <p class="rating-summary">No reviews yet.</p>
                <?php endif; ?>

                <!-- <div class="qty-row">
                    <label for="qtyInput">Quantity:</label>
                    <input type="number" id="qtyInput" class="qty-input" value="1" min="1" max="10" <?php echo $product['product_stock'] === 'out-of-stock' ? 'disabled' : ''; ?>>
                </div> -->

                    <div class="qty-row">
                    <label for="qtyInput">Quantity:</label>
                    <div class="qty-stepper">
                        <button type="button" class="qty-btn" id="qtyDecrease" aria-label="Decrease quantity" <?php echo $product['product_stock'] === 'out-of-stock' ? 'disabled' : ''; ?>>&minus;</button>
                        <input type="number" id="qtyInput" class="qty-input" value="1" min="1" max="10" readonly <?php echo $product['product_stock'] === 'out-of-stock' ? 'disabled' : ''; ?>>
                        <button type="button" class="qty-btn" id="qtyIncrease" aria-label="Increase quantity" <?php echo $product['product_stock'] === 'out-of-stock' ? 'disabled' : ''; ?>>+</button>
                    </div>
                </div>
            
                <button class="btn btn-primary" id="addToCartBtn" type="button" <?php echo $product['product_stock'] === 'out-of-stock' ? 'disabled' : ''; ?>>
                    <?php echo $product['product_stock'] === 'out-of-stock' ? 'Out of Stock' : 'Add to Cart'; ?>
                </button>

                <div class="add-confirm-msg" id="confirmMsg" role="status" aria-live="polite">Item added to your cart.</div>
                <a href="cart.php" class="btn btn-secondary" id="viewCartBtn" style="display:none;">View Cart</a>
            </article>
        </section>

        <section class="reviews-section" id="reviews" aria-labelledby="reviewsHeading">
            <div class="reviews-header">
                <h2 id="reviewsHeading">Product Reviews</h2>
                <?php if ($reviews): ?>
                    <span><?php echo number_format($average_rating, 1); ?>/5 average</span>
                <?php endif; ?>
            </div>
             <div class="review-list">
                <?php if (!$reviews): ?>
                    <p class="empty-state">Be the first person to review this product.</p>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <article class="review-card">
                            <div class="review-card-heading">
                                <h3><?php echo h($review['review_title']); ?></h3>
                                <span class="review-rating" aria-label="<?php echo (int) $review['review_rating']; ?> out of 5 stars"><?php echo str_repeat('★', (int) $review['review_rating']); ?><?php echo str_repeat('☆', 5 - (int) $review['review_rating']); ?></span>
                            </div>
                            <p><?php echo nl2br(h($review['review_desc'])); ?></p>
                            <p class="review-meta">By <?php echo h($review['full_name']); ?> on <?php echo h(date('j F Y', strtotime($review['review_timestamp']))); ?></p>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <br>

            <?php if (isset($_GET['review']) && $_GET['review'] === 'added'): ?>
                <div class="success-message" role="status">Your review was saved successfully.</div>
            <?php endif; ?>

            <?php if ($review_errors): ?>
                <div class="form-errors" role="alert">
                    <?php foreach ($review_errors as $error): ?>
                        <p><?php echo h($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (is_logged_in()): ?>
                <form class="review-form" method="post" action="item.php?id=<?php echo (int) $product_id; ?>#reviews">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="submit_review" value="1">

                    <label for="review_rating">Rating</label>
                    <select id="review_rating" name="review_rating" required>
                        <option value="">Choose a rating</option>
                        <?php for ($rating = 5; $rating >= 1; $rating--): ?>
                            <option value="<?php echo $rating; ?>"<?php echo $review_rating === (string) $rating ? ' selected' : ''; ?>><?php echo $rating; ?> out of 5</option>
                        <?php endfor; ?>
                    </select>

                    <label for="review_title">Review title</label>
                    <input type="text" id="review_title" name="review_title" maxlength="120" value="<?php echo h($review_title); ?>" required>

                    <label for="review_desc">Review</label>
                    <textarea id="review_desc" name="review_desc" rows="5" maxlength="1500" required><?php echo h($review_desc); ?></textarea>

                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </form>
            <?php else: ?>
                <p class="login-prompt"><a href="login.php?next=<?php echo rawurlencode('item.php?id=' . $product_id . '#reviews'); ?>">Log in</a> to submit a review.</p>
            <?php endif; ?>

           
        </section>
    <?php endif; ?>
</main>

<footer>
    <p>&copy; 2026 UCLan Legacy Shop, University of Lancashire Student Union | <a href="index.php">Home</a> | <a href="products.php">Products</a> | <a href="cart.php">Cart</a></p>
</footer>

<div class="toast" id="toast" role="status" aria-live="polite"></div>

<?php if ($product): ?>
<script>
const product = <?php echo json_encode([
    'productId' => (int) $product['product_id'],
    'name' => $product['product_title'],
    'price' => (float) $product['product_price'],
    'image' => $product['product_image'],
    'stock' => $product['product_stock'],
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
</script>
<?php endif; ?>
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

    const qtyInput = document.getElementById('qtyInput');
    const qtyDecrease = document.getElementById('qtyDecrease');
    const qtyIncrease = document.getElementById('qtyIncrease');

    if (qtyInput && qtyDecrease && qtyIncrease) {
        qtyDecrease.addEventListener('click', function () {
            const current = Number.parseInt(qtyInput.value, 10) || 1;
            if (current > 1) qtyInput.value = current - 1;
        });

        qtyIncrease.addEventListener('click', function () {
            const current = Number.parseInt(qtyInput.value, 10) || 1;
            if (current < 10) qtyInput.value = current + 1;
        });
    }

    function readCart() {
        try {
            const stored = JSON.parse(localStorage.getItem('uclanCart'));
            return Array.isArray(stored) ? stored : [];
        } catch (error) {
            return [];
        }
    }

    function saveCart(cart) {
        localStorage.setItem('uclanCart', JSON.stringify(cart));
    }

    function updateCartBadge() {
        const total = readCart().reduce(function (sum, item) {
            return sum + (Number.parseInt(item.qty, 10) || 0);
        }, 0);
        const badge = document.getElementById('cartBadge');
        if (badge) badge.textContent = total;
    }

    function showToast(message) {
        const toast = document.getElementById('toast');
        if (!toast) return;
        toast.textContent = message;
        toast.classList.add('show');
        window.setTimeout(function () { toast.classList.remove('show'); }, 3000);
    }

    const addButton = document.getElementById('addToCartBtn');
    if (addButton && typeof product !== 'undefined' && product.stock !== 'out-of-stock') {
        addButton.addEventListener('click', function () {
            const quantityInput = document.getElementById('qtyInput');
            const quantity = Number.parseInt(quantityInput.value, 10);

            if (!Number.isInteger(quantity) || quantity < 1 || quantity > 10) {
                showToast('Choose a quantity between 1 and 10.');
                return;
            }

            const cart = readCart();
            const existing = cart.find(function (item) {
                return Number(item.productId) === product.productId;
            });

            if (existing) {
                existing.qty = Math.min(10, (Number.parseInt(existing.qty, 10) || 0) + quantity);
            } else {
                cart.push({
                    productId: product.productId,
                    name: product.name,
                    price: product.price,
                    image: product.image,
                    qty: quantity
                });
            }

            saveCart(cart);
            document.getElementById('confirmMsg').style.display = 'block';
            document.getElementById('viewCartBtn').style.display = 'inline-block';
            updateCartBadge();
            showToast('Product added to cart.');
        });
    }

    updateCartBadge();
}());
</script>
</body>
</html>
