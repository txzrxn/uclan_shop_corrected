<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/json; charset=utf-8');

function json_response($status_code, $payload)
{
    http_response_code($status_code);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(405, ['success' => false, 'message' => 'Only POST requests are accepted.']);
}

if (!is_logged_in()) {
    json_response(401, ['success' => false, 'message' => 'Your login session has expired. Log in and try again.']);
}

$raw_body = file_get_contents('php://input');
$data = json_decode($raw_body, true);

if (!is_array($data)) {
    json_response(400, ['success' => false, 'message' => 'The order request was not valid JSON.']);
}

if (!valid_csrf_token($data['csrf_token'] ?? '')) {
    json_response(403, ['success' => false, 'message' => 'The order form session expired. Refresh the cart and try again.']);
}

$items = $data['items'] ?? [];
if (!is_array($items) || !$items || count($items) > 50) {
    json_response(422, ['success' => false, 'message' => 'The cart must contain between 1 and 50 products.']);
}

$quantities = [];
$valid_sizes = ['', 'S', 'M', 'L', 'XL', 'XXL'];
foreach ($items as $item) {
    $product_id = filter_var($item['product_id'] ?? null, FILTER_VALIDATE_INT);
    $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT);
    $size = strtoupper(trim((string) ($item['size'] ?? '')));

    if (!$product_id || !$quantity || $quantity < 1 || $quantity > 10) {
        json_response(422, ['success' => false, 'message' => 'A cart item has an invalid product ID or quantity.']);
    }

    if (!in_array($size, $valid_sizes, true)) {
        json_response(422, ['success' => false, 'message' => 'A cart item has an invalid clothing size.']);
    }

    // Group by product AND size so two sizes of the same product stay separate order lines.
    $line_key = $product_id . '|' . $size;
    if (!isset($quantities[$line_key])) {
        $quantities[$line_key] = 0;
    }
    $quantities[$line_key] += $quantity;

    if ($quantities[$line_key] > 10) {
        json_response(422, ['success' => false, 'message' => 'The maximum quantity for one product is 10.']);
    }
}

$discount_codes = [
    'LEGACY10' => 0.10,
    'UCLAN20' => 0.20,
    'BOGOF' => 0.50,
    'G21406018' => 0.25,
];

$discount_code = strtoupper(trim((string) ($data['discount_code'] ?? '')));
$discount_rate = 0;
if ($discount_code !== '') {
    if (!array_key_exists($discount_code, $discount_codes)) {
        json_response(422, ['success' => false, 'message' => 'The discount code is not valid.']);
    }
    $discount_rate = $discount_codes[$discount_code];
}

$stock_check = mysqli_query($connection, "SHOW COLUMNS FROM tbl_products LIKE 'product_stock'");
$has_stock_column = $stock_check && mysqli_num_rows($stock_check) > 0;
$stock_select = $has_stock_column ? ', product_stock' : '';

$product_query = mysqli_prepare(
    $connection,
    "SELECT product_id, product_title, product_price{$stock_select} FROM tbl_products WHERE product_id = ? LIMIT 1"
);

if (!$product_query) {
    error_log('Order product query prepare failed: ' . mysqli_error($connection));
    json_response(500, ['success' => false, 'message' => 'The product database could not be queried.']);
}

$order_items = [];
$subtotal = 0.0;

foreach ($quantities as $line_key => $quantity) {
    list($product_id, $size) = explode('|', (string) $line_key, 2);
    $product_id = (int) $product_id;
    $size = (string) $size;
    mysqli_stmt_bind_param($product_query, 'i', $product_id);
    mysqli_stmt_execute($product_query);
    $result = mysqli_stmt_get_result($product_query);
    $product = mysqli_fetch_assoc($result);

    if (!$product) {
        mysqli_stmt_close($product_query);
        json_response(422, ['success' => false, 'message' => 'A product in the cart no longer exists.']);
    }

    if ($has_stock_column && $product['product_stock'] === 'out-of-stock') {
        mysqli_stmt_close($product_query);
        json_response(422, ['success' => false, 'message' => $product['product_title'] . ' is out of stock.']);
    }

    $unit_price = (float) $product['product_price'];
    $line_total = round($unit_price * $quantity, 2);
    $subtotal += $line_total;

    $order_items[] = [
        'product_id' => (int) $product['product_id'],
        'title' => $product['product_title'] . ($size !== '' ? ' (Size ' . $size . ')' : ''),
        'quantity' => (int) $quantity,
        'unit_price' => $unit_price,
        'line_total' => $line_total,
        'size' => $size,
    ];
}
mysqli_stmt_close($product_query);

$subtotal = round($subtotal, 2);
$discount_amount = round($subtotal * $discount_rate, 2);
$total = round($subtotal - $discount_amount, 2);

$order_payload = json_encode([
    'items' => $order_items,
    'subtotal' => $subtotal,
    'discount_code' => $discount_code,
    'discount_amount' => $discount_amount,
    'total' => $total,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

if ($order_payload === false) {
    json_response(500, ['success' => false, 'message' => 'The order data could not be encoded.']);
}

$user_id = (int) $_SESSION['user_id'];
$insert = mysqli_prepare(
    $connection,
    'INSERT INTO tbl_orders (order_date, user_id, product_ids) VALUES (NOW(), ?, ?)'
);

if (!$insert) {
    error_log('Order insert prepare failed: ' . mysqli_error($connection));
    json_response(500, ['success' => false, 'message' => 'The order table is not available. Import the corrected SQL file.']);
}

mysqli_stmt_bind_param($insert, 'is', $user_id, $order_payload);
$saved = mysqli_stmt_execute($insert);
$order_id = mysqli_insert_id($connection);
mysqli_stmt_close($insert);

if (!$saved) {
    error_log('Order insert failed: ' . mysqli_error($connection));
    json_response(500, ['success' => false, 'message' => 'The order could not be saved to the database.']);
}

json_response(201, [
    'success' => true,
    'order_id' => (int) $order_id,
    'total' => $total,
]);
