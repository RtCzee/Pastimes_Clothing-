<?php
session_start();
require_once 'data/DBConn.php';
require_once 'includes/schema.php';

if (!isset($_SESSION['adminID'])) {
    header('Location: adminLogin.php');
    exit;
}

ensure_pastimes_schema($conn);

$adminID = (int) $_SESSION['adminID'];
$message = '';
$error   = '';
$view    = $_GET['view'] ?? 'inbox';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $recipientUserID = isset($_POST['recipientUserID']) ? (int) $_POST['recipientUserID'] : 0;
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['body'] ?? '');

    if ($recipientUserID <= 0 || $subject === '' || $body === '') {
        $error = 'Recipient, subject, and message are required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO tblMessage (senderAdminID, recipientType, recipientUserID, subject, body) VALUES (?, 'buyer', ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param('iiss', $adminID, $recipientUserID, $subject, $body);
            if ($stmt->execute()) {
                $stmt->close();
                header('Location: adminMessages.php?view=sent&sent=1');
                exit;
            }
            $error = 'Unable to send reply.';
            $stmt->close();
        }
    }
    $view = 'compose';
}

$inbox = [];
$inboxResult = $conn->query("
    SELECT m.*, u.username AS senderUsername, u.fullName AS senderFullName, c.itemName
    FROM tblMessage m
    LEFT JOIN tblUser u ON m.senderUserID = u.userID
    LEFT JOIN tblClothes c ON m.itemID = c.itemID
    WHERE m.recipientType = 'admin'
    ORDER BY m.createdAt DESC
");
if ($inboxResult) {
    while ($row = $inboxResult->fetch_assoc()) {
        $inbox[] = $row;
    }
}

$sent = [];
$sentResult = $conn->query("
    SELECT m.*, u.username AS recipientUsername
    FROM tblMessage m
    LEFT JOIN tblUser u ON m.recipientUserID = u.userID
    WHERE m.senderAdminID IS NOT NULL
    ORDER BY m.createdAt DESC
");
if ($sentResult) {
    while ($row = $sentResult->fetch_assoc()) {
        $sent[] = $row;
    }
}

$compose = [
    'recipientUserID' => '',
    'subject' => '',
    'body' => '',
];

if (isset($_GET['reply']) && is_numeric($_GET['reply'])) {
    $replyID = (int) $_GET['reply'];
    $replyStmt = $conn->prepare('SELECT * FROM tblMessage WHERE messageID = ? AND recipientType = ?');
    if ($replyStmt) {
        $adminType = 'admin';
        $replyStmt->bind_param('is', $replyID, $adminType);
        $replyStmt->execute();
        $original = $replyStmt->get_result()->fetch_assoc();
        $replyStmt->close();
        if ($original && !empty($original['senderUserID'])) {
            $compose['recipientUserID'] = (int) $original['senderUserID'];
            $compose['subject'] = 'Re: ' . $original['subject'];
            $view = 'compose';

            $markRead = $conn->prepare('UPDATE tblMessage SET isRead = 1 WHERE messageID = ?');
            if ($markRead) {
                $markRead->bind_param('i', $replyID);
                $markRead->execute();
                $markRead->close();
            }
        }
    }
}

$viewMessage = null;
if ($view === 'read' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $viewID = (int) $_GET['id'];
    $viewStmt = $conn->prepare("
        SELECT m.*, u.username AS senderUsername, u.fullName AS senderFullName, c.itemName
        FROM tblMessage m
        LEFT JOIN tblUser u ON m.senderUserID = u.userID
        LEFT JOIN tblClothes c ON m.itemID = c.itemID
        WHERE m.messageID = ? AND m.recipientType = 'admin'
    ");
    if ($viewStmt) {
        $viewStmt->bind_param('i', $viewID);
        $viewStmt->execute();
        $viewMessage = $viewStmt->get_result()->fetch_assoc();
        $viewStmt->close();
        if ($viewMessage) {
            $markRead = $conn->prepare('UPDATE tblMessage SET isRead = 1 WHERE messageID = ?');
            if ($markRead) {
                $markRead->bind_param('i', $viewID);
                $markRead->execute();
                $markRead->close();
            }
        }
    }
}
?>
<link rel="stylesheet" href="assets/css/styles.css">

<div class="auth-page">
    <div class="auth-card admin-dashboard-card" style="max-width:980px;">
        <div class="auth-back"><a href="adminDashboard.php">&larr; Back to Admin Dashboard</a></div>

        <div class="admin-dashboard-header">
            <div>
                <div class="admin-badge">ADMIN MESSAGES</div>
                <h2 class="auth-title" style="text-align:left; margin-top:0.9rem;">Customer Messages</h2>
                <div class="auth-sub" style="text-align:left; margin-bottom:0;">Read enquiries and reply to buyers.</div>
            </div>
            <div class="admin-dashboard-actions">
                <a href="adminItems.php" class="admin-secondary-link">Manage Items</a>
                <a href="logout.php" class="admin-secondary-link">Logout Admin</a>
            </div>
        </div>

        <div class="message-tabs" style="margin-bottom:1rem;">
            <a class="message-tabs__link<?= $view === 'inbox' ? ' is-active' : '' ?>" href="adminMessages.php?view=inbox">Inbox</a>
            <a class="message-tabs__link<?= $view === 'sent' ? ' is-active' : '' ?>" href="adminMessages.php?view=sent">Sent</a>
            <?php if ($view === 'compose'): ?>
                <span class="message-tabs__link is-active">Reply</span>
            <?php endif; ?>
        </div>

        <?php if (!empty($error)): ?>
            <div class="dash-msg dash-msg--err" style="margin-bottom:1rem;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['sent'])): ?>
            <div class="dash-msg dash-msg--ok" style="margin-bottom:1rem;">Reply sent successfully.</div>
        <?php endif; ?>

        <?php if ($view === 'compose'): ?>
            <div class="admin-section">
                <h3 class="admin-section-title">Reply to Buyer</h3>
                <form method="POST" class="admin-item-form">
                    <input type="hidden" name="send_message" value="1">
                    <label class="admin-form-label">Buyer user ID</label>
                    <input class="admin-form-input" type="number" name="recipientUserID" required value="<?= htmlspecialchars((string) $compose['recipientUserID']) ?>">
                    <label class="admin-form-label">Subject</label>
                    <input class="admin-form-input" type="text" name="subject" required value="<?= htmlspecialchars($compose['subject']) ?>">
                    <label class="admin-form-label">Message</label>
                    <textarea class="admin-form-input" name="body" rows="6" required><?= htmlspecialchars($compose['body']) ?></textarea>
                    <button type="submit" class="button" style="margin-top:0.75rem;">Send Reply</button>
                </form>
            </div>
        <?php elseif ($view === 'read' && $viewMessage): ?>
            <div class="admin-section">
                <a href="adminMessages.php?view=inbox">&larr; Back to inbox</a>
                <h3 class="admin-section-title" style="margin-top:1rem;"><?= htmlspecialchars($viewMessage['subject']) ?></h3>
                <p style="font-size:0.88rem;color:var(--muted);">
                    From: <?= htmlspecialchars($viewMessage['senderUsername'] ?? 'Unknown') ?>
                    (<?= htmlspecialchars($viewMessage['senderFullName'] ?? '') ?>)
                    &middot; <?= htmlspecialchars(date('d M Y, H:i', strtotime($viewMessage['createdAt']))) ?>
                </p>
                <?php if (!empty($viewMessage['itemName'])): ?>
                    <p style="font-size:0.88rem;color:var(--muted);">Product: <?= htmlspecialchars($viewMessage['itemName']) ?></p>
                <?php endif; ?>
                <div style="margin:1rem 0;padding:1rem;background:var(--secondary);border-radius:var(--radius);white-space:pre-wrap;"><?= htmlspecialchars($viewMessage['body']) ?></div>
                <a class="admin-action-link admin-action-link--approve" href="adminMessages.php?reply=<?= (int) $viewMessage['messageID'] ?>">Reply</a>
            </div>
        <?php elseif ($view === 'sent'): ?>
            <div class="admin-section">
                <h3 class="admin-section-title">Sent Replies</h3>
                <?php if (empty($sent)): ?>
                    <p class="pending-note">No replies sent yet.</p>
                <?php else: ?>
                    <div class="admin-user-list">
                        <?php foreach ($sent as $row): ?>
                            <div class="admin-user-card">
                                <div>
                                    <div class="admin-user-name"><?= htmlspecialchars($row['subject']) ?></div>
                                    <div class="admin-user-email">To: <?= htmlspecialchars($row['recipientUsername'] ?? 'Buyer #' . $row['recipientUserID']) ?></div>
                                    <div style="font-size:0.8rem;color:var(--muted);margin-top:0.3rem;"><?= htmlspecialchars(date('d M Y, H:i', strtotime($row['createdAt']))) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="admin-section">
                <h3 class="admin-section-title">Inbox</h3>
                <?php if (empty($inbox)): ?>
                    <p class="pending-note">No messages yet.</p>
                <?php else: ?>
                    <div class="admin-user-list">
                        <?php foreach ($inbox as $row): ?>
                            <div class="admin-user-card<?= empty($row['isRead']) ? ' admin-user-card--pending' : '' ?>">
                                <div>
                                    <div class="admin-user-name"><?= htmlspecialchars($row['subject']) ?></div>
                                    <div class="admin-user-email">
                                        From: <?= htmlspecialchars($row['senderUsername'] ?? 'User') ?>
                                        <?php if (!empty($row['itemName'])): ?> &middot; Re: <?= htmlspecialchars($row['itemName']) ?><?php endif; ?>
                                    </div>
                                    <div style="font-size:0.8rem;color:var(--muted);margin-top:0.3rem;"><?= htmlspecialchars(date('d M Y, H:i', strtotime($row['createdAt']))) ?></div>
                                </div>
                                <div class="admin-user-actions">
                                    <a href="adminMessages.php?view=read&id=<?= (int) $row['messageID'] ?>" class="admin-action-link admin-action-link--approve">Open</a>
                                    <a href="adminMessages.php?reply=<?= (int) $row['messageID'] ?>" class="admin-action-link">Reply</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .dash-msg--ok { background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:0.85rem 1rem;border-radius:var(--radius); }
    .dash-msg--err { background:#fdf0f0;border:1px solid #f5c6c6;color:#a33;padding:0.85rem 1rem;border-radius:var(--radius); }
</style>
