<?php

// Product detail page - shows individual product info and related items
require __DIR__ . '/includes/bootstrap.php';

$productId = isset($_GET['id']) ? $_GET['id'] : '';
$product = find_product($productId);

if ($product === null) {
    http_response_code(404);
    render_page_start([
        'title' => 'Product Not Found | Pastimes',
        'page' => 'Shop',
    ]);
    ?>
    <section class="section">
        <div class="container container--narrow center-text">
            <h1>Product not found</h1>
            <p>The item you were looking for is no longer available.</p>
            <a class="button" href="Shop.php">Back to Shop</a>
        </div>
    </section>
    <?php
    render_page_end();
    return;
}

$relatedProducts = array_slice(array_values(array_filter(site_products(), static function ($item) use ($product) {
    return $item['category'] === $product['category'] && $item['id'] !== $product['id'];
})), 0, 4);

render_page_start([
    'title' => $product['name'] . ' | Pastimes',
    'page' => 'Shop',
]);
?>
<section class="section section--compact">
    <div class="container">
        <a class="back-link" href="Shop.php"><?= site_icon('chevron-left', 'icon icon--tiny') ?> Back to Shop</a>

        <div class="product-detail">
            <div class="product-gallery">
                <div class="product-gallery__main">
                    <img
                        src="<?= h(isset($product['images'][0]) ? $product['images'][0] : $product['image']) ?>"
                        alt="<?= h($product['name']) ?>"
                        data-product-main-image
                    >
                    <?php if (!empty($product['new'])): ?>
                        <span class="product-card__badge product-card__badge--dark product-card__badge--large">New Arrival</span>
                    <?php endif; ?>
                    <?php if (!empty($product['originalPrice'])): ?>
                        <span class="product-card__badge product-card__badge--light product-card__badge--right product-card__badge--large">Sale</span>
                    <?php endif; ?>
                </div>
                <?php if (count($product['images']) > 1): ?>
                    <div class="product-thumbs">
                        <?php foreach ($product['images'] as $index => $image): ?>
                            <button
                                class="product-thumbs__button<?= $index === 0 ? ' is-active' : '' ?>"
                                type="button"
                                data-product-thumb
                                data-image-src="<?= h($image) ?>"
                                data-image-alt="<?= h($product['name'] . ' ' . ($index + 1)) ?>"
                            >
                                <img src="<?= h($image) ?>" alt="<?= h($product['name'] . ' ' . ($index + 1)) ?>">
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="product-info">
                <div class="product-info__top">
                    <p class="product-info__category"><?= h($product['category']) ?></p>
                    <h1><?= h($product['name']) ?></h1>
                    <div class="product-info__pricing">
                        <span><?= h(format_price((int) $product['price'])) ?></span>
                        <?php if (!empty($product['originalPrice'])): ?>
                            <span class="product-info__pricing-old"><?= h(format_price((int) $product['originalPrice'])) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="product-info__details">
                    <div><span>Size:</span><strong><?= h($product['size']) ?></strong></div>
                    <div><span>Condition:</span><strong class="pill"><?= h($product['condition']) ?></strong></div>
                </div>

                <div class="product-info__description">
                    <h2>Description</h2>
                    <p><?= h($product['description']) ?></p>
                </div>

                <button class="button button--full" type="button" data-product-add="<?= h($product['id']) ?>">
                    <span data-product-add-label>Add to Cart</span>
                </button>

                <div class="product-info__features">
                    <div><?= site_icon('truck', 'icon icon--small') ?><span>Free shipping on orders over R1,500</span></div>
                    <div><?= site_icon('refresh-cw', 'icon icon--small') ?><span>Easy returns within 14 days</span></div>
                    <div><?= site_icon('shield', 'icon icon--small') ?><span>Authenticity guaranteed</span></div>
                </div>
            </div>
        </div>

        <?php if ($relatedProducts !== []): ?>
            <section class="related-section">
                <div class="section-heading">
                    <h2>You May Also Like</h2>
                </div>
                <div class="product-grid product-grid--four">
                    <?php foreach ($relatedProducts as $relatedProduct): ?>
                        <?php render_product_card($relatedProduct); ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</section>
<?php render_page_end(); ?>
