<?php

require __DIR__ . '/includes/bootstrap.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['userID'])) {
    header('Location: login.php?redirect=' . urlencode('messages.php'));
    exit;
}

ensure_pastimes_schema($conn);

$userID   = (int) $_SESSION['userID'];
$username = $_SESSION['username'] ?? 'User';
$role     = $_SESSION['role'] ?? 'buyer';
$message  = '';
$error    = '';
$view     = $_GET['view'] ?? 'inbox';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $recipientType = $_POST['recipientType'] ?? '';
    $recipientUserID = isset($_POST['recipientUserID']) && $_POST['recipientUserID'] !== '' ? (int) $_POST['recipientUserID'] : null;
    $itemID = isset($_POST['itemID']) && $_POST['itemID'] !== '' ? (int) $_POST['itemID'] : null;
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['body'] ?? '');

    $allowedTypes = ['seller', 'admin', 'buyer'];
    if (!in_array($recipientType, $allowedTypes, true)) {
        $error = 'Please choose who you are messaging.';
    } elseif ($subject === '' || $body === '') {
        $error = 'Subject and message are required.';
    } elseif ($recipientType !== 'admin' && empty($recipientUserID)) {
        $error = 'Please choose a recipient.';
    } else {
        if ($recipientType === 'admin') {
            $stmt = $conn->prepare('INSERT INTO tblMessage (senderUserID, recipientType, itemID, subject, body) VALUES (?, ?, ?, ?, ?)');
            if ($stmt) {
                $itemParam = $itemID ?: null;
                $stmt->bind_param('isiss', $userID, $recipientType, $itemParam, $subject, $body);
            }
        } else {
            $stmt = $conn->prepare('INSERT INTO tblMessage (senderUserID, recipientType, recipientUserID, itemID, subject, body) VALUES (?, ?, ?, ?, ?, ?)');
            if ($stmt) {
                $itemParam = $itemID ?: null;
                $stmt->bind_param('isiiss', $userID, $recipientType, $recipientUserID, $itemParam, $subject, $body);
            }
        }

        if (!empty($stmt) && $stmt->execute()) {
            $stmt->close();
            header('Location: messages.php?view=sent&sent=1');
            exit;
        }
        $error = 'Unable to send your message.';
        if (!empty($stmt)) {
            $stmt->close();
        }
    }
    $view = 'compose';
}

if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    $readID = (int) $_GET['read'];
    $readStmt = $conn->prepare('UPDATE tblMessage SET isRead = 1 WHERE messageID = ? AND ((recipientType = ? AND recipientUserID = ?) OR (recipientType = ? AND recipientUserID = ?))');
    if ($readStmt) {
        $buyerType = 'buyer';
        $sellerType = 'seller';
        $readStmt->bind_param('isisi', $readID, $buyerType, $userID, $sellerType, $userID);
        $readStmt->execute();
        $readStmt->close();
    }
}

$compose = [
    'recipientType' => $_POST['recipientType'] ?? 'seller',
    'recipientUserID' => $_POST['recipientUserID'] ?? '',
    'itemID' => '',
    'subject' => $_POST['subject'] ?? '',
    'body' => $_POST['body'] ?? '',
    'productName' => '',
];

if (isset($_GET['item']) && is_numeric($_GET['item'])) {
    $enquiryItemID = (int) $_GET['item'];
    $itemStmt = $conn->prepare('SELECT c.itemID, c.itemName, c.sellerID, u.username AS sellerName FROM tblClothes c LEFT JOIN tblUser u ON c.sellerID = u.userID WHERE c.itemID = ?');
    if ($itemStmt) {
        $itemStmt->bind_param('i', $enquiryItemID);
        $itemStmt->execute();
        $enquiryItem = $itemStmt->get_result()->fetch_assoc();
        $itemStmt->close();
        if ($enquiryItem && !empty($enquiryItem['sellerID'])) {
            $compose['recipientType'] = 'seller';
            $compose['recipientUserID'] = (int) $enquiryItem['sellerID'];
            $compose['itemID'] = (int) $enquiryItem['itemID'];
            $compose['productName'] = $enquiryItem['itemName'];
            $compose['subject'] = 'Enquiry about: ' . $enquiryItem['itemName'];
            $compose['body'] = "Hi, I am interested in \"{$enquiryItem['itemName']}\". Could you tell me more about this item?\n\n";
            $view = 'compose';
        }
    }
}

if (isset($_GET['to']) && $_GET['to'] === 'admin') {
    $compose['recipientType'] = 'admin';
    $compose['recipientUserID'] = '';
    $compose['subject'] = $_GET['subject'] ?? 'General enquiry';
    $view = 'compose';
}

if (isset($_GET['reply']) && is_numeric($_GET['reply'])) {
    $replyID = (int) $_GET['reply'];
    $replyStmt = $conn->prepare('SELECT * FROM tblMessage WHERE messageID = ?');
    if ($replyStmt) {
        $replyStmt->bind_param('i', $replyID);
        $replyStmt->execute();
        $original = $replyStmt->get_result()->fetch_assoc();
        $replyStmt->close();
        if ($original) {
            if ((int) $original['senderUserID'] === $userID) {
                $compose['recipientType'] = $original['recipientType'];
                $compose['recipientUserID'] = $original['recipientUserID'];
            } elseif ($original['recipientType'] === 'seller' && (int) $original['recipientUserID'] === $userID) {
                $compose['recipientType'] = 'buyer';
                $compose['recipientUserID'] = (int) $original['senderUserID'];
            } elseif ($original['recipientType'] === 'buyer' && (int) $original['recipientUserID'] === $userID) {
                $compose['recipientType'] = 'buyer';
                $compose['recipientUserID'] = (int) $original['senderUserID'];
            } elseif ($original['recipientType'] === 'admin' && !empty($original['senderUserID'])) {
                $compose['recipientType'] = 'buyer';
                $compose['recipientUserID'] = (int) $original['senderUserID'];
            }
            $compose['itemID'] = $original['itemID'] ?? '';
            $compose['subject'] = 'Re: ' . $original['subject'];
            $view = 'compose';
        }
    }
}

$inbox = [];
$inboxStmt = $conn->prepare("
    SELECT m.*, u.username AS senderUsername, u.fullName AS senderFullName, c.itemName
    FROM tblMessage m
    LEFT JOIN tblUser u ON m.senderUserID = u.userID
    LEFT JOIN tblClothes c ON m.itemID = c.itemID
    WHERE (m.recipientType = 'buyer' AND m.recipientUserID = ?)
       OR (m.recipientType = 'seller' AND m.recipientUserID = ?)
    ORDER BY m.createdAt DESC
");
if ($inboxStmt) {
    $inboxStmt->bind_param('ii', $userID, $userID);
    $inboxStmt->execute();
    $result = $inboxStmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $inbox[] = $row;
    }
    $inboxStmt->close();
}

$sent = [];
$sentStmt = $conn->prepare("
    SELECT m.*, c.itemName
    FROM tblMessage m
    LEFT JOIN tblClothes c ON m.itemID = c.itemID
    WHERE m.senderUserID = ?
    ORDER BY m.createdAt DESC
");
if ($sentStmt) {
    $sentStmt->bind_param('i', $userID);
    $sentStmt->execute();
    $result = $sentStmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $sent[] = $row;
    }
    $sentStmt->close();
}

$sellers = [];
$sellerList = $conn->query("SELECT userID, username, fullName FROM tblUser WHERE role = 'seller' AND status = 'active' ORDER BY username ASC");
if ($sellerList) {
    while ($row = $sellerList->fetch_assoc()) {
        $sellers[] = $row;
    }
}

$viewMessage = null;
if ($view === 'read' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $viewID = (int) $_GET['id'];
    $viewStmt = $conn->prepare("
        SELECT m.*, u.username AS senderUsername, c.itemName
        FROM tblMessage m
        LEFT JOIN tblUser u ON m.senderUserID = u.userID
        LEFT JOIN tblClothes c ON m.itemID = c.itemID
        WHERE m.messageID = ?
          AND (
            m.senderUserID = ?
            OR (m.recipientType = 'buyer' AND m.recipientUserID = ?)
            OR (m.recipientType = 'seller' AND m.recipientUserID = ?)
          )
    ");
    if ($viewStmt) {
        $viewStmt->bind_param('iiii', $viewID, $userID, $userID, $userID);
        $viewStmt->execute();
        $viewMessage = $viewStmt->get_result()->fetch_assoc();
        $viewStmt->close();
        if ($viewMessage && ((int) $viewMessage['senderUserID'] !== $userID)) {
            $markRead = $conn->prepare('UPDATE tblMessage SET isRead = 1 WHERE messageID = ?');
            if ($markRead) {
                $markRead->bind_param('i', $viewID);
                $markRead->execute();
                $markRead->close();
            }
        }
    }
}

render_page_start([
    'title' => 'Messages | Pastimes',
    'page' => 'Messages',
]);
?>
<section class="section section--compact">
    <div class="container container--narrow-wide">
        <div class="page-heading">
            <h1>Messages</h1>
            <p>Contact sellers about products or reach the Pastimes admin team.</p>
        </div>

        <div class="message-tabs">
            <a class="message-tabs__link<?= $view === 'inbox' ? ' is-active' : '' ?>" href="messages.php?view=inbox">Inbox</a>
            <a class="message-tabs__link<?= $view === 'sent' ? ' is-active' : '' ?>" href="messages.php?view=sent">Sent</a>
            <a class="message-tabs__link<?= $view === 'compose' ? ' is-active' : '' ?>" href="messages.php?view=compose">Compose</a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="dash-msg dash-msg--ok"><?= h($message) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="dash-msg dash-msg--err"><?= h($error) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['sent'])): ?>
            <div class="dash-msg dash-msg--ok">Your message was sent successfully.</div>
        <?php endif; ?>

        <?php if ($view === 'compose'): ?>
            <div class="message-panel">
                <h2>Compose Message</h2>
                <?php if (!empty($compose['productName'])): ?>
                    <p class="message-product-banner">Product enquiry: <strong><?= h($compose['productName']) ?></strong></p>
                <?php endif; ?>
                <form method="POST" class="stack-form">
                    <input type="hidden" name="send_message" value="1">
                    <?php if (!empty($compose['itemID'])): ?>
                        <input type="hidden" name="itemID" value="<?= (int) $compose['itemID'] ?>">
                    <?php endif; ?>

                    <?php if (!empty($compose['itemID']) && $compose['recipientType'] === 'seller' && !empty($compose['recipientUserID'])): ?>
                        <input type="hidden" name="recipientType" value="seller">
                        <input type="hidden" name="recipientUserID" value="<?= (int) $compose['recipientUserID'] ?>">
                    <?php elseif ($compose['recipientType'] === 'buyer' && !empty($compose['recipientUserID'])): ?>
                        <input type="hidden" name="recipientType" value="buyer">
                        <input type="hidden" name="recipientUserID" value="<?= (int) $compose['recipientUserID'] ?>">
                        <p class="message-meta">Replying to buyer account #<?= (int) $compose['recipientUserID'] ?></p>
                    <?php else: ?>
                        <label>
                            <span>Send to</span>
                            <select class="field-select" name="recipientType" id="recipientType" required>
                                <option value="seller"<?= $compose['recipientType'] === 'seller' ? ' selected' : '' ?>>Seller</option>
                                <option value="admin"<?= $compose['recipientType'] === 'admin' ? ' selected' : '' ?>>Admin</option>
                            </select>
                        </label>

                        <label id="sellerPicker"<?= $compose['recipientType'] !== 'seller' ? ' class="is-hidden"' : '' ?>>
                            <span>Seller</span>
                            <select class="field-select" name="recipientUserID" id="recipientUserIDSeller">
                                <option value="">Select seller</option>
                                <?php foreach ($sellers as $seller): ?>
                                    <option value="<?= (int) $seller['userID'] ?>"<?= (string) $compose['recipientUserID'] === (string) $seller['userID'] ? ' selected' : '' ?>>
                                        <?= h($seller['username']) ?> (<?= h($seller['fullName']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    <?php endif; ?>

                    <label>
                        <span>Subject</span>
                        <input class="field-input" type="text" name="subject" required value="<?= h($compose['subject']) ?>">
                    </label>
                    <label>
                        <span>Message</span>
                        <textarea class="field-textarea" name="body" rows="6" required placeholder="Write your message..."><?= h($compose['body']) ?></textarea>
                    </label>
                    <button class="button" type="submit">Send Message</button>
                </form>
            </div>
            <script>
                (function () {
                    const typeSelect = document.getElementById('recipientType');
                    const sellerPicker = document.getElementById('sellerPicker');
                    if (!typeSelect || !sellerPicker) return;
                    typeSelect.addEventListener('change', function () {
                        sellerPicker.classList.toggle('is-hidden', this.value !== 'seller');
                    });
                })();
            </script>
        <?php elseif ($view === 'read' && $viewMessage): ?>
            <div class="message-panel">
                <a class="back-link" href="messages.php?view=inbox">&larr; Back to inbox</a>
                <h2><?= h($viewMessage['subject']) ?></h2>
                <p class="message-meta">
                    From: <?= h($viewMessage['senderUsername'] ?? 'Admin') ?>
                    &middot; <?= h(date('d M Y, H:i', strtotime($viewMessage['createdAt']))) ?>
                    <?php if (!empty($viewMessage['itemName'])): ?>
                        &middot; Product: <?= h($viewMessage['itemName']) ?>
                    <?php endif; ?>
                </p>
                <div class="message-body"><?= nl2br(h($viewMessage['body'])) ?></div>
                <a class="button button--outline" href="messages.php?reply=<?= (int) $viewMessage['messageID'] ?>">Reply</a>
            </div>
        <?php elseif ($view === 'sent'): ?>
            <div class="message-panel">
                <h2>Sent Messages</h2>
                <?php if (empty($sent)): ?>
                    <p class="review-note">You have not sent any messages yet.</p>
                <?php else: ?>
                    <div class="message-list">
                        <?php foreach ($sent as $row): ?>
                            <article class="message-item">
                                <div>
                                    <strong><?= h($row['subject']) ?></strong>
                                    <p>To: <?= h(ucfirst($row['recipientType'])) ?><?= !empty($row['itemName']) ? ' &middot; ' . h($row['itemName']) : '' ?></p>
                                    <p class="message-meta"><?= h(date('d M Y, H:i', strtotime($row['createdAt']))) ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="message-panel">
                <h2>Inbox</h2>
                <?php if (empty($inbox)): ?>
                    <p class="review-note">No messages in your inbox yet.</p>
                    <a class="button button--outline" href="messages.php?view=compose">Send a message</a>
                <?php else: ?>
                    <div class="message-list">
                        <?php foreach ($inbox as $row): ?>
                            <article class="message-item<?= empty($row['isRead']) ? ' message-item--unread' : '' ?>">
                                <div>
                                    <strong><?= h($row['subject']) ?></strong>
                                    <p>From: <?= h($row['senderUsername'] ?? 'Admin') ?><?= !empty($row['itemName']) ? ' &middot; ' . h($row['itemName']) : '' ?></p>
                                    <p class="message-meta"><?= h(date('d M Y, H:i', strtotime($row['createdAt']))) ?></p>
                                </div>
                                <div class="message-item__actions">
                                    <a class="button button--outline button--small" href="messages.php?view=read&id=<?= (int) $row['messageID'] ?>">Open</a>
                                    <a class="button button--small" href="messages.php?reply=<?= (int) $row['messageID'] ?>">Reply</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php render_page_end(); ?>
