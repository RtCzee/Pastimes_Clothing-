<?php
session_start();
require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json');

ensure_pastimes_schema($conn);

if (!isset($_SESSION['userID']) || ($_SESSION['role'] ?? 'buyer') === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Please log in before checking out.',
        'redirectUrl' => 'login.php?checkout=required',
    ]);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
$items = isset($payload['items']) && is_array($payload['items']) ? $payload['items'] : [];

if (empty($items)) {
    echo json_encode([
        'success' => false,
        'message' => 'Your cart is empty.',
    ]);
    exit;
}

$normalizedItems = [];
$subtotal = 0.0;

foreach ($items as $item) {
    $productId = isset($item['id']) ? (string) $item['id'] : '';
    $quantity = isset($item['quantity']) ? (int) $item['quantity'] : 0;

    if ($productId === '' || $quantity < 1) {
        continue;
    }

    $product = find_product($productId);
    if (!$product) {
        continue;
    }

    $lineTotal = (float) $product['price'] * $quantity;
    $subtotal += $lineTotal;
    $normalizedItems[] = [
        'id' => $productId,
        'name' => $product['name'],
        'quantity' => $quantity,
        'price' => (float) $product['price'],
    ];
}

if (empty($normalizedItems)) {
    echo json_encode([
        'success' => false,
        'message' => 'No valid items were found in your cart.',
    ]);
    exit;
}

$shipping = $subtotal >= 1500 ? 0.0 : 150.0;
$total = $subtotal + $shipping;
$reference = sprintf('CHK-%s-%s', date('YmdHis'), strtoupper(bin2hex(random_bytes(3))));
$userID = (int) $_SESSION['userID'];
$cartSnapshot = json_encode($normalizedItems, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$itemCount = array_sum(array_map(static function ($item) {
    return (int) $item['quantity'];
}, $normalizedItems));

$stmt = $conn->prepare("INSERT INTO tblCheckout (checkoutReference, userID, itemCount, subtotal, shipping, total, cartSnapshot, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'completed')");
if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Unable to create checkout record: ' . $conn->error,
    ]);
    exit;
}

$stmt->bind_param("siiddds", $reference, $userID, $itemCount, $subtotal, $shipping, $total, $cartSnapshot);
if (!$stmt->execute()) {
    $checkoutError = $stmt->error;
    $stmt->close();
    echo json_encode([
        'success' => false,
        'message' => 'Unable to save checkout: ' . $checkoutError,
    ]);
    exit;
}
$stmt->close();

$orderStmt = $conn->prepare("INSERT INTO tblAorder (checkoutReference, userID, itemID, quantity) VALUES (?, ?, ?, ?)");
if ($orderStmt) {
    foreach ($normalizedItems as $item) {
        $product = find_product($item['id']);
        $itemID = isset($product['db_item_id']) ? (int) $product['db_item_id'] : (int) $item['id'];
        $quantity = (int) $item['quantity'];
        $orderStmt->bind_param("siii", $reference, $userID, $itemID, $quantity);
        $orderStmt->execute();
    }
    $orderStmt->close();
}

echo json_encode([
    'success' => true,
    'reference' => $reference,
    'redirectUrl' => 'login.php?checkout=success&reference=' . urlencode($reference),
]);