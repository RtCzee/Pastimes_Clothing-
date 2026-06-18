<?php

// Shopping cart page - displays cart items and order summary
require __DIR__ . '/includes/bootstrap.php';

render_page_start([
    'title' => 'Cart | Pastimes',
    'page' => 'Cart',
]);
?>
<section class="section section--compact">
    <div class="container">
        <div class="cart-empty" data-cart-empty>
            <div class="empty-icon"><?= site_icon('shopping-bag', 'icon icon--large') ?></div>
            <h1>Your cart is empty</h1>
            <p>Looks like you have not added any items to your cart yet. Start shopping to fill it up!</p>
            <a class="button" href="Shop.php">Continue Shopping <?= site_icon('arrow-right', 'icon icon--small') ?></a>
        </div>

        <div class="cart-page is-hidden" data-cart-page>
            <div class="cart-page__top">
                <h1>Shopping Cart</h1>
                <button class="text-button" type="button" data-clear-cart>Clear Cart</button>
            </div>

            <div class="cart-layout">
                <div>
                    <div class="cart-items" data-cart-items></div>
                    <div class="cart-back-link">
                        <a href="Shop.php"><?= site_icon('arrow-right', 'icon icon--tiny icon--flip') ?> Continue Shopping</a>
                    </div>
                </div>

                <aside class="summary-box">
                    <h2>Order Summary</h2>
                    <div class="summary-box__rows">
                        <div><span>Subtotal</span><strong data-cart-subtotal>R0</strong></div>
                        <div><span>Shipping</span><strong data-cart-shipping>Free</strong></div>
                        <p class="summary-box__hint" data-cart-shipping-note>Free shipping on orders over R1,500</p>
                    </div>
                    <div class="summary-box__total">
                        <span>Total</span>
                        <strong data-cart-total>R0</strong>
                    </div>
                    <button class="button button--full" type="button" data-checkout-button>Proceed to Checkout</button>
                    <p class="summary-box__secure">Secure checkout powered by Stripe</p>
                </aside>
            </div>

            <div class="promo-box">
                <h3>Promo Code</h3>
                <form class="inline-form" data-simple-form>
                    <input class="field-input" type="text" placeholder="Enter code" aria-label="Promo code">
                    <button class="button button--outline button--small" type="submit">Apply</button>
                </form>
            </div>
        </div>
    </div>
</section>
<?php render_page_end(); ?>
