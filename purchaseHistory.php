<?php

require __DIR__ . '/includes/bootstrap.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

ensure_pastimes_schema($conn);

$userID = (int) $_SESSION['userID'];
$orders = [];
$grandTotal = 0.0;

$stmt = $conn->prepare('SELECT checkoutReference, itemCount, subtotal, shipping, total, cartSnapshot, createdAt FROM tblCheckout WHERE userID = ? ORDER BY createdAt DESC');
if ($stmt) {
    $stmt->bind_param('i', $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['items'] = json_decode($row['cartSnapshot'] ?? '[]', true);
        if (!is_array($row['items'])) {
            $row['items'] = [];
        }
        $orders[] = $row;
        $grandTotal += (float) $row['total'];
    }
    $stmt->close();
}

render_page_start([
    'title' => 'Purchase History | Pastimes',
    'page' => 'PurchaseHistory',
]);
?>
<section class="section section--compact">
    <div class="container">
        <div class="page-heading">
            <h1>Purchase History</h1>
            <p>A report of all your completed orders on Pastimes.</p>
        </div>

        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <div class="empty-icon"><?= site_icon('shopping-bag', 'icon icon--large') ?></div>
                <h2>No purchases yet</h2>
                <p>When you complete checkout, your orders will appear here.</p>
                <a class="button" href="Shop.php">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="purchase-report">
                <?php foreach ($orders as $order): ?>
                    <article class="purchase-card">
                        <div class="purchase-card__head">
                            <div>
                                <strong><?= h($order['checkoutReference']) ?></strong>
                                <p class="purchase-card__date"><?= h(date('d M Y, H:i', strtotime($order['createdAt']))) ?></p>
                            </div>
                            <div class="purchase-card__total"><?= h(format_price((int) $order['total'])) ?></div>
                        </div>
                        <div class="purchase-card__meta">
                            <span><?= (int) $order['itemCount'] ?> item<?= (int) $order['itemCount'] === 1 ? '' : 's' ?></span>
                            <span>Subtotal: <?= h(format_price((int) $order['subtotal'])) ?></span>
                            <span>Shipping: <?= (float) $order['shipping'] <= 0 ? 'Free' : h(format_price((int) $order['shipping'])) ?></span>
                        </div>
                        <?php if (!empty($order['items'])): ?>
                            <ul class="purchase-card__items">
                                <?php foreach ($order['items'] as $line): ?>
                                    <li>
                                        <?= h($line['name'] ?? 'Item') ?>
                                        &times; <?= (int) ($line['quantity'] ?? 1) ?>
                                        <span><?= h(format_price((int) ($line['price'] ?? 0) * (int) ($line['quantity'] ?? 1))) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>

            <aside class="summary-box purchase-report__total">
                <h2>Report Total</h2>
                <div class="summary-box__rows">
                    <div><span>Orders</span><strong><?= count($orders) ?></strong></div>
                </div>
                <div class="summary-box__total">
                    <span>Total of all purchases</span>
                    <strong><?= h(format_price((int) round($grandTotal))) ?></strong>
                </div>
            </aside>
        <?php endif; ?>
    </div>
</section>
<?php render_page_end(); ?>
