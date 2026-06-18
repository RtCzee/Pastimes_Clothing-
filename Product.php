<?php

// Product detail page - shows individual product info and related items
require __DIR__ . '/includes/bootstrap.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

$reviewMessage = '';
$reviewError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['userID'])) {
        $reviewError = 'Please log in to leave a review.';
    } else {
        $rating = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
        $comment = trim($_POST['comment'] ?? '');

        if ($rating < 1 || $rating > 5) {
            $reviewError = 'Please choose a rating from 1 to 5.';
        } elseif ($comment === '') {
            $reviewError = 'Please write a short review.';
        } else {
            $tableCheck = $conn->query("SHOW TABLES LIKE 'tblReview'");
            if (!$tableCheck || $tableCheck->num_rows === 0) {
                $reviewError = 'Reviews are not set up yet. Run the database setup script first.';
            } else {
                $username = $_SESSION['username'] ?? 'Guest';
                $stmt = $conn->prepare('INSERT INTO tblReview (productId, userID, username, rating, comment) VALUES (?, ?, ?, ?, ?)');
                if ($stmt) {
                    $userID = (int) $_SESSION['userID'];
                    $stmt->bind_param('sisis', $productId, $userID, $username, $rating, $comment);
                    if ($stmt->execute()) {
                        $stmt->close();
                        header('Location: Product.php?id=' . urlencode($productId) . '&review=posted');
                        exit;
                    }
                    $stmt->close();
                    $reviewError = 'Unable to save your review right now.';
                } else {
                    $reviewError = 'Unable to prepare the review form.';
                }
            }
        }
    }
}

$reviews = [];
$averageRating = 0;
$reviewCount = 0;
$reviewTableCheck = $conn->query("SHOW TABLES LIKE 'tblReview'");
if ($reviewTableCheck && $reviewTableCheck->num_rows > 0) {
    $stmt = $conn->prepare('SELECT rating, comment, username, createdAt FROM tblReview WHERE productId = ? ORDER BY createdAt DESC');
    if ($stmt) {
        $stmt->bind_param('s', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }
        $stmt->close();
    }

    $stmt = $conn->prepare('SELECT AVG(rating) AS avgRating, COUNT(*) AS reviewCount FROM tblReview WHERE productId = ?');
    if ($stmt) {
        $stmt->bind_param('s', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $summary = $result->fetch_assoc();
        $averageRating = isset($summary['avgRating']) ? (float) $summary['avgRating'] : 0;
        $reviewCount = isset($summary['reviewCount']) ? (int) $summary['reviewCount'] : 0;
        $stmt->close();
    }
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

                <button class="button button--outline button--full" type="button" data-wishlist-toggle="<?= h($product['id']) ?>" data-product-name="<?= h($product['name']) ?>" data-wishlist-label-save="Save to Wishlist">
                    <?= site_icon('heart', 'icon icon--small') ?>
                    <span data-wishlist-label>Save to Wishlist</span>
                </button>

                <?php if (!empty($product['db_item_id']) && !empty($product['sellerID'])): ?>
                    <?php if (isset($_SESSION['userID'])): ?>
                        <a class="button button--ghost button--full" href="messages.php?item=<?= (int) $product['db_item_id'] ?>">
                            <?= site_icon('mail', 'icon icon--small') ?> Enquire about this item
                        </a>
                    <?php else: ?>
                        <a class="button button--ghost button--full" href="login.php?redirect=<?= urlencode('messages.php?item=' . (int) $product['db_item_id']) ?>">
                            <?= site_icon('mail', 'icon icon--small') ?> Log in to enquire
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if (isset($_SESSION['userID'])): ?>
                        <a class="button button--ghost button--full" href="messages.php?to=admin&amp;subject=<?= urlencode('Question about: ' . $product['name']) ?>">
                            <?= site_icon('mail', 'icon icon--small') ?> Message admin about this item
                        </a>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="alert-card">
                    <div class="alert-card__head">
                        <strong>Price Drop Alert</strong>
                        <span class="pill" data-price-alert-status>Inactive</span>
                    </div>
                    <p>Save a target price. If the current price is at or below that amount, the alert status changes here.</p>
                    <form class="inline-form" data-price-alert-form>
                        <input class="field-input" type="number" min="1" step="1" value="<?= h((string) ((int) $product['price'])) ?>" data-price-alert-target>
                        <button class="button button--outline button--small" type="submit" data-price-alert-save="<?= h($product['id']) ?>">Save Alert</button>
                    </form>
                </div>

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

        <section class="related-section">
            <div class="section-heading">
                <h2>Ratings & Reviews</h2>
            </div>

            <div class="review-summary">
                <strong><?= $reviewCount > 0 ? number_format($averageRating, 1) . ' / 5' : 'No reviews yet' ?></strong>
                <span><?= $reviewCount ?> review<?= $reviewCount === 1 ? '' : 's' ?></span>
            </div>

            <?php if ($reviewMessage !== ''): ?>
                <div class="dash-msg dash-msg--ok"><?= h($reviewMessage) ?></div>
            <?php endif; ?>
            <?php if ($reviewError !== ''): ?>
                <div class="dash-msg dash-msg--err"><?= h($reviewError) ?></div>
            <?php endif; ?>

            <?php if (isset($_GET['review']) && $_GET['review'] === 'posted'): ?>
                <div class="dash-msg dash-msg--ok">Thanks for your review. It has been posted.</div>
            <?php endif; ?>

            <?php if (isset($_SESSION['userID'])): ?>
                <form class="review-form" method="POST">
                    <input type="hidden" name="submit_review" value="1">
                    <label>
                        <span>Rating</span>
                        <select class="field-select" name="rating" required>
                            <option value="">Choose rating</option>
                            <option value="5">5 - Excellent</option>
                            <option value="4">4 - Very Good</option>
                            <option value="3">3 - Good</option>
                            <option value="2">2 - Fair</option>
                            <option value="1">1 - Poor</option>
                        </select>
                    </label>
                    <label>
                        <span>Comment</span>
                        <textarea class="field-textarea" name="comment" rows="4" required placeholder="Tell other shoppers what you think about this item"></textarea>
                    </label>
                    <button class="button" type="submit">Post Review</button>
                </form>
            <?php else: ?>
                <p class="review-note">Log in to add a review.</p>
            <?php endif; ?>

            <div class="review-list">
                <?php if (empty($reviews)): ?>
                    <p class="review-note">No reviews have been posted for this item yet.</p>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <article class="review-item">
                            <div class="review-item__top">
                                <strong><?= h($review['username']) ?></strong>
                                <span class="pill"><?= (int) $review['rating'] ?>/5</span>
                            </div>
                            <p><?= h($review['comment']) ?></p>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</section>
<?php render_page_end(); ?>
