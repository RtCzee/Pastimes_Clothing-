<?php

require __DIR__ . '/includes/bootstrap.php';

render_page_start([
    'title' => 'Wishlist | Pastimes',
    'page' => 'Wishlist',
]);
?>
<section class="section section--compact">
    <div class="container">
        <div class="empty-state" data-wishlist-empty>
            <div class="empty-icon"><?= site_icon('heart', 'icon icon--large') ?></div>
            <h1>Your wishlist is empty</h1>
            <p>Save items you love from the shop and they will appear here.</p>
            <a class="button" href="Shop.php">Browse Products <?= site_icon('arrow-right', 'icon icon--small') ?></a>
        </div>

        <div class="is-hidden" data-wishlist-page>
            <div class="page-heading">
                <h1>Your Wishlist</h1>
                <p>Items you saved for later.</p>
            </div>
            <div class="product-grid product-grid--shop" data-wishlist-grid></div>
        </div>
    </div>
</section>
<?php render_page_end(); ?>
