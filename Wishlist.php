<?php

require __DIR__ . '/includes/bootstrap.php';

render_page_start([
    'title' => 'Wishlist | Pastimes',
    'page' => 'Shop',
]);
?>
<section class="section section--compact">
    <div class="container">
        <div class="page-heading">
            <h1>Your Wishlist</h1>
            <p>Items you saved for later will appear here.</p>
        </div>

        <div class="product-grid product-grid--shop" data-wishlist-grid></div>

        <div class="empty-state is-hidden" data-wishlist-empty>
            <p>Your wishlist is empty.</p>
            <a class="button button--outline" href="Shop.php">Browse products</a>
        </div>
    </div>
</section>
<?php render_page_end(); ?>