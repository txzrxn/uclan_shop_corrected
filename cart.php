<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$csrf_token = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="View your cart and save a completed order to the UCLan shop database.">
    <title>UCLan Legacy Shop | Cart</title>
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
                <li><a href="cart.php" class="cart-nav-link active" aria-current="page">&#128722; Cart <span class="cart-count-badge" id="cartBadge">0</span></a></li>
                <li><a href="account.php" class="user-greeting-link">Hi, <?php echo h($_SESSION['user_name']); ?></a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
    <div class="page-title-strip">
        <h1>Your Shopping Cart</h1>
        <p>Review the items and save the completed order to MySQL.</p>
    </div>

    <nav aria-label="Breadcrumb navigation">
        <p class="breadcrumb"><a href="index.php">Home</a><span aria-hidden="true">/</span><span aria-current="page">Cart</span></p>
    </nav>

    <div id="orderSuccess" class="success-message" role="status" aria-live="polite" hidden></div>
    <div id="orderError" class="form-errors" role="alert" hidden></div>

    <div id="cartContent" hidden>
        <div class="cart-layout">
            <section class="cart-items-section" aria-labelledby="cartItemsHeading">
                <h2 id="cartItemsHeading">Items in Your Cart</h2>
                <div id="cartItemsList"></div>
                <button class="btn btn-danger btn-sm" id="emptyCartButton" type="button">Empty Cart</button>
            </section>

            <section class="cart-summary-section" aria-labelledby="summaryHeading">
                <h2 id="summaryHeading">Order Summary</h2>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="summarySubtotal">£0.00</span>
                </div>
                <div class="summary-row discount-row" id="discountRow" hidden>
                    <span id="discountLabel">Discount</span>
                    <span id="discountAmount">-£0.00</span>
                </div>
                <div class="summary-row total-row">
                    <span>Total</span>
                    <span id="summaryTotal">£0.00</span>
                </div>

                <div class="discount-block">
                    <label for="discountCodeInput">Discount Code</label>
                    <div class="discount-form">
                        <input type="text" id="discountCodeInput" class="discount-input" placeholder="Enter code" maxlength="20">
                        <button class="btn btn-primary btn-sm" id="applyDiscountButton" type="button">Apply</button>
                    </div>
                    <p class="discount-msg" id="discountMsg" aria-live="polite"></p>
                    <details>
                        <summary>View available codes</summary>
                        <ul>
                            <li><strong>LEGACY10</strong>: 10% off</li>
                            <li><strong>UCLAN20</strong>: 20% off</li>
                            <li><strong>BOGOF</strong>: 50% off</li>
                            <li><strong>G21406018</strong>: 25% off</li>
                        </ul>
                    </details>
                </div>

                <div class="checkout-actions">
                    <a href="products.php" class="btn btn-secondary">Continue Shopping</a>
                    <button class="btn btn-primary" id="checkoutButton" type="button">Place Order</button>
                </div>
                <p class="checkout-note">This assessment project records the order in the database. It does not process a real payment.</p>
            </section>
        </div>
    </div>

    <div id="emptyCartMsg" hidden>
        <div class="empty-cart-msg">
            <div class="icon" aria-hidden="true">&#128722;</div>
            <h2>Your cart is empty</h2>
            <p>Add a product before placing an order.</p>
            <a href="products.php" class="btn btn-primary">Browse Products</a>
        </div>
    </div>
</main>

<footer>
    <p>&copy; 2026 UCLan Legacy Shop, University of Lancashire Student Union | <a href="index.php">Home</a> | <a href="products.php">Products</a> | <a href="cart.php">Cart</a></p>
</footer>

<div class="toast" id="toast" role="status" aria-live="polite"></div>

<script>
(function () {
    const csrfToken = <?php echo json_encode($csrf_token, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
    const discountCodes = {
        LEGACY10: 0.10,
        UCLAN20: 0.20,
        BOGOF: 0.50,
        G21406018: 0.25
    };

    let appliedDiscount = 0;
    let appliedCode = '';

    const cartContent = document.getElementById('cartContent');
    const emptyCartMsg = document.getElementById('emptyCartMsg');
    const cartItemsList = document.getElementById('cartItemsList');
    const checkoutButton = document.getElementById('checkoutButton');
    const orderSuccess = document.getElementById('orderSuccess');
    const orderError = document.getElementById('orderError');

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
            const stored = JSON.parse(localStorage.getItem('uclanCart'));
            return Array.isArray(stored) ? stored : [];
        } catch (error) {
            localStorage.removeItem('uclanCart');
            return [];
        }
    }

    function saveCart(cart) {
        localStorage.setItem('uclanCart', JSON.stringify(cart));
    }

    function showToast(message) {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.classList.add('show');
        window.setTimeout(function () { toast.classList.remove('show'); }, 3000);
    }

    function updateCartBadge(cart) {
        const total = cart.reduce(function (sum, item) {
            return sum + (Number.parseInt(item.qty, 10) || 0);
        }, 0);
        document.getElementById('cartBadge').textContent = total;
    }

    function makeTextElement(tag, className, text) {
        const element = document.createElement(tag);
        if (className) element.className = className;
        element.textContent = text;
        return element;
    }

    function renderCart() {
        const cart = readCart();
        cartItemsList.replaceChildren();
        orderError.hidden = true;

        if (cart.length === 0) {
            cartContent.hidden = true;
            emptyCartMsg.hidden = false;
            updateCartBadge(cart);
            return;
        }

        cartContent.hidden = false;
        emptyCartMsg.hidden = true;

        cart.forEach(function (item, index) {
            const quantity = Number.parseInt(item.qty, 10) || 1;
            const price = Number(item.price) || 0;
            const productId = Number.parseInt(item.productId, 10);

            const wrapper = document.createElement('div');
            wrapper.className = 'cart-item';
            wrapper.dataset.index = String(index);

            const image = document.createElement('img');
            image.src = String(item.image || 'images/logo.png');
            image.alt = String(item.name || 'Cart product');
            wrapper.appendChild(image);

            const info = document.createElement('div');
            info.className = 'cart-item-info';
            info.appendChild(makeTextElement('h4', '', String(item.name || 'Product')));
            info.appendChild(makeTextElement('p', '', 'Price: £' + price.toFixed(2) + ' each'));
            info.appendChild(makeTextElement('p', 'cart-item-price', 'Line total: £' + (price * quantity).toFixed(2)));

            if (Number.isInteger(productId) && productId > 0) {
                const link = document.createElement('a');
                link.href = 'item.php?id=' + encodeURIComponent(productId);
                link.textContent = 'View item';
                info.appendChild(link);
            } else {
                info.appendChild(makeTextElement('p', 'legacy-cart-warning', 'Re-add this item before checkout because it came from the older cart format.'));
            }
            wrapper.appendChild(info);

            const controls = document.createElement('div');
            controls.className = 'cart-item-controls';

            const label = document.createElement('label');
            label.htmlFor = 'qty_' + index;
            label.textContent = 'Qty:';
            controls.appendChild(label);

            const input = document.createElement('input');
            input.type = 'number';
            input.id = 'qty_' + index;
            input.className = 'cart-qty-input';
            input.value = String(quantity);
            input.min = '1';
            input.max = '10';
            input.addEventListener('change', function () {
                updateQuantity(index, input.value);
            });
            controls.appendChild(input);

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'btn btn-danger btn-sm';
            removeButton.textContent = 'Remove';
            removeButton.addEventListener('click', function () {
                removeItem(index);
            });
            controls.appendChild(removeButton);

            wrapper.appendChild(controls);
            cartItemsList.appendChild(wrapper);
        });

        updateSummary(cart);
        updateCartBadge(cart);
    }

    function updateQuantity(index, rawQuantity) {
        const quantity = Number.parseInt(rawQuantity, 10);
        const cart = readCart();

        if (!Number.isInteger(quantity) || quantity < 1 || quantity > 10 || !cart[index]) {
            showToast('Quantity must be between 1 and 10.');
            renderCart();
            return;
        }

        cart[index].qty = quantity;
        saveCart(cart);
        renderCart();
    }

    function removeItem(index) {
        const cart = readCart();
        if (!cart[index]) return;
        const removedName = String(cart[index].name || 'Item');
        cart.splice(index, 1);
        saveCart(cart);
        renderCart();
        showToast(removedName + ' removed.');
    }

    function emptyCart() {
        if (window.confirm('Remove all items from the cart?')) {
            localStorage.removeItem('uclanCart');
            appliedDiscount = 0;
            appliedCode = '';
            renderCart();
            showToast('Cart emptied.');
        }
    }

    function applyDiscount() {
        const input = document.getElementById('discountCodeInput');
        const message = document.getElementById('discountMsg');
        const code = input.value.trim().toUpperCase();

        if (!Object.prototype.hasOwnProperty.call(discountCodes, code)) {
            appliedDiscount = 0;
            appliedCode = '';
            message.className = 'discount-msg error';
            message.textContent = code ? 'Invalid discount code.' : 'Enter a discount code.';
            updateSummary(readCart());
            return;
        }

        appliedDiscount = discountCodes[code];
        appliedCode = code;
        message.className = 'discount-msg success';
        message.textContent = code + ' applied: ' + Math.round(appliedDiscount * 100) + '% off.';
        updateSummary(readCart());
    }

    function updateSummary(cart) {
        const subtotal = cart.reduce(function (sum, item) {
            return sum + ((Number(item.price) || 0) * (Number.parseInt(item.qty, 10) || 0));
        }, 0);
        const discountAmount = subtotal * appliedDiscount;
        const total = subtotal - discountAmount;

        document.getElementById('summarySubtotal').textContent = '£' + subtotal.toFixed(2);
        document.getElementById('summaryTotal').textContent = '£' + total.toFixed(2);

        const discountRow = document.getElementById('discountRow');
        if (appliedDiscount > 0) {
            discountRow.hidden = false;
            document.getElementById('discountLabel').textContent = 'Discount (' + appliedCode + ')';
            document.getElementById('discountAmount').textContent = '-£' + discountAmount.toFixed(2);
        } else {
            discountRow.hidden = true;
        }
    }

    async function placeOrder() {
        const cart = readCart();
        orderError.hidden = true;
        orderSuccess.hidden = true;

        if (cart.length === 0) {
            showToast('Your cart is empty.');
            return;
        }

        const invalidItem = cart.some(function (item) {
            const productId = Number.parseInt(item.productId, 10);
            const quantity = Number.parseInt(item.qty, 10);
            return !Number.isInteger(productId) || productId < 1 || !Number.isInteger(quantity) || quantity < 1 || quantity > 10;
        });

        if (invalidItem) {
            orderError.textContent = 'At least one item uses the old cart format. Remove it and add it again from the corrected Products page.';
            orderError.hidden = false;
            return;
        }

        checkoutButton.disabled = true;
        checkoutButton.textContent = 'Saving Order...';

        try {
            const response = await fetch('place_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    csrf_token: csrfToken,
                    discount_code: appliedCode,
                    items: cart.map(function (item) {
                        return {
                            product_id: Number.parseInt(item.productId, 10),
                            quantity: Number.parseInt(item.qty, 10)
                        };
                    })
                })
            });

            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'The order could not be saved.');
            }

            localStorage.removeItem('uclanCart');
            appliedDiscount = 0;
            appliedCode = '';
            orderSuccess.innerHTML = '';
            orderSuccess.appendChild(document.createTextNode('Order #' + data.order_id + ' was saved. Database total: £' + Number(data.total).toFixed(2) + '. '));
            const accountLink = document.createElement('a');
            accountLink.href = 'account.php';
            accountLink.textContent = 'View order history';
            orderSuccess.appendChild(accountLink);
            orderSuccess.hidden = false;
            renderCart();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (error) {
            orderError.textContent = error.message;
            orderError.hidden = false;
        } finally {
            checkoutButton.disabled = false;
            checkoutButton.textContent = 'Place Order';
        }
    }

    document.getElementById('emptyCartButton').addEventListener('click', emptyCart);
    document.getElementById('applyDiscountButton').addEventListener('click', applyDiscount);
    checkoutButton.addEventListener('click', placeOrder);
    document.getElementById('discountCodeInput').addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            applyDiscount();
        }
    });

    renderCart();
}());
</script>
</body>
</html>
