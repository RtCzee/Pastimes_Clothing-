<?php
session_start();
include("data/DBConn.php");

$error = null;
$userData = null;
$checkoutSuccess = isset($_GET['checkout']) && $_GET['checkout'] === 'success';
$checkoutReference = isset($_GET['reference']) ? trim($_GET['reference']) : '';
$redirectAfter = isset($_GET['redirect']) ? trim($_GET['redirect']) : '';
if ($redirectAfter !== '' && !preg_match('/^[A-Za-z0-9_.?=&%-]+$/', $redirectAfter)) {
  $redirectAfter = '';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = isset($_POST['username']) ? trim($_POST['username']) : '';
  $email = isset($_POST['email']) ? trim($_POST['email']) : '';
  $password = isset($_POST['password']) ? $_POST['password'] : '';
  if ($redirectAfter === '' && isset($_POST['redirect'])) {
    $redirectAfter = trim($_POST['redirect']);
    if ($redirectAfter !== '' && !preg_match('/^[A-Za-z0-9_.?=&%-]+$/', $redirectAfter)) {
      $redirectAfter = '';
    }
  }

  // Validate that at least one identifier is provided
  if (empty($username) && empty($email)) {
    $error = "Please enter a username or email address.";
  } elseif (empty($password)) {
    $error = "Please enter a password.";
  } else {
    // Search by username or email
    if (!empty($username) && empty($email)) {
      $stmt = $conn->prepare("SELECT * FROM tblUser WHERE username = ?");
      $stmt->bind_param("s", $username);
    } elseif (!empty($email) && empty($username)) {
      $stmt = $conn->prepare("SELECT * FROM tblUser WHERE email = ?");
      $stmt->bind_param("s", $email);
    } else {
      // Both provided - search by either
      $stmt = $conn->prepare("SELECT * FROM tblUser WHERE username = ? OR email = ?");
      $stmt->bind_param("ss", $username, $email);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
      $error = "User not found. Please check your username or email.";
    } else {
      // Check password - supports both bcrypt and MD5 for legacy compatibility
      $passwordValid = false;
      if (strpos($user['password'], '$2y$') === 0) {
        $passwordValid = password_verify($password, $user['password']);
      } else {
        // Legacy MD5 support
        $passwordValid = (md5($password) === $user['password']);
      }

      if (!$passwordValid) {
        $error = "Incorrect password. Please try again.";
      } elseif ($user['status'] !== 'active') {
        $error = "Your account is not yet approved by an administrator. Please wait for verification.";
      } else {
        // Login successful
        $_SESSION['userID'] = $user['userID'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['fullName'] = $user['fullName'];
        $_SESSION['role'] = $user['role'] ?? 'buyer';
        $_SESSION['logged_in'] = true;

        if ($redirectAfter !== '') {
          header('Location: ' . $redirectAfter);
          exit;
        }

        $userData = $user;
      }
    }
  }
}
?>

<link rel="stylesheet" href="assets/css/styles.css">

<div class="auth-page">
  <div class="auth-card">
  <div class="auth-back"><a href="Home.php">← Back to Home</a></div>

  <?php if ($userData): ?>
    <!-- LOGIN SUCCESS - DISPLAY USER DATA -->
    <div class="auth-brand">Pastimes</div>
    <div class="auth-title" style="color: var(--accent);">✓ Login Successful</div>
    <div class="login-success-message" style="background: rgba(27, 94, 32, 0.1); padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; text-align: center;">
      <p style="font-size: 1.05rem; font-weight: 600; color: var(--foreground); margin: 0;">
        Thanks for logging in, <?= htmlspecialchars($userData['fullName']) ?>.
      </p>
    </div>
        
    <div class="login-success-message" style="background: rgba(76, 175, 80, 0.1); padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; text-align: center;">
      <p style="font-size: 1.1rem; font-weight: 600; color: var(--foreground);">
        User <?= htmlspecialchars($userData['fullName']) ?> is logged in
      </p>
    </div>

    <div class="user-data-section" style="margin-bottom: 1.5rem;">
      <h3 style="font-size: 0.95rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted); margin-bottom: 0.75rem;">Your Account Details</h3>
      <table style="width: 100%; border-collapse: collapse; font-size: 0.95rem;">
        <tr style="border-bottom: 1px solid var(--border);">
          <td style="padding: 0.75rem 0; font-weight: 600; color: var(--muted); width: 30%;">User ID</td>
          <td style="padding: 0.75rem 0; color: var(--foreground);"><?= htmlspecialchars($userData['userID']) ?></td>
        </tr>
        <tr style="border-bottom: 1px solid var(--border);">
          <td style="padding: 0.75rem 0; font-weight: 600; color: var(--muted);">Username</td>
          <td style="padding: 0.75rem 0; color: var(--foreground);"><?= htmlspecialchars($userData['username']) ?></td>
        </tr>
        <tr style="border-bottom: 1px solid var(--border);">
          <td style="padding: 0.75rem 0; font-weight: 600; color: var(--muted);">Full Name</td>
          <td style="padding: 0.75rem 0; color: var(--foreground);"><?= htmlspecialchars($userData['fullName']) ?></td>
        </tr>
        <tr style="border-bottom: 1px solid var(--border);">
          <td style="padding: 0.75rem 0; font-weight: 600; color: var(--muted);">Email</td>
          <td style="padding: 0.75rem 0; color: var(--foreground);"><?= htmlspecialchars($userData['email']) ?></td>
        </tr>
        <tr>
          <td style="padding: 0.75rem 0; font-weight: 600; color: var(--muted);">Account Status</td>
          <td style="padding: 0.75rem 0; color: var(--foreground);">
            <span style="display: inline-block; background: rgba(27, 94, 32, 0.14); color: #1b5e20; border: 1px solid rgba(27, 94, 32, 0.28); padding: 0.3rem 0.8rem; border-radius: 999px; font-weight: 700; letter-spacing: 0.02em; min-width: 4.5rem; text-align: center;">
              <?= ucfirst(htmlspecialchars($userData['status'])) ?>
            </span>
          </td>
        </tr>
      </table>
    </div>

    <div style="display: flex; gap: 0.75rem;">
      <a href="Home.php" class="auth-btn" style="flex: 1; text-align: center;">Continue to Home</a>
      <a href="Shop.php" class="auth-btn" style="flex: 1; text-align: center;">Browse Shop</a>
    </div>

    <div class="auth-footer-note">
      <a href="logout.php">Logout</a>
    </div>

  <?php else: ?>
    <!-- LOGIN FORM -->
    <div class="auth-brand">Pastimes</div>
    <div class="auth-title">Welcome Back</div>
    <div class="auth-sub">Login to your account</div>

    <?php if ($checkoutSuccess): ?>
      <p class="pending-note" style="background-color: rgba(27, 94, 32, 0.12); color: #1b5e20; padding: 0.75rem; border-radius: var(--radius); margin-bottom: 1rem; font-size: 0.95rem;">
        Thanks for your purchase<?= $checkoutReference !== '' ? ' • Reference: ' . htmlspecialchars($checkoutReference) : '' ?>. Please log in again to continue shopping.
      </p>
    <?php endif; ?>

    <?php if ($error): ?>
      <p class="pending-note" style="background-color: rgba(244, 67, 54, 0.15); color: #d32f2f; padding: 0.75rem; border-radius: var(--radius); margin-bottom: 1rem; font-size: 0.95rem;">
        <?= htmlspecialchars($error) ?>
      </p>
    <?php endif; ?>

    <form method="POST">
      <?php if ($redirectAfter !== ''): ?>
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectAfter) ?>">
      <?php endif; ?>
      <div class="form-group">
        <label class="form-label">Username</label>
        <input 
          class="form-input" 
          name="username" 
          type="text"
          minlength="3"
          value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
          placeholder="Enter your username"
        >
      </div>

      <div style="text-align: center; color: var(--muted); margin: 0.5rem 0; font-size: 0.9rem;">or</div>

      <div class="form-group">
        <label class="form-label">Email Address</label>
        <input 
          class="form-input" 
          name="email" 
          type="email"
          value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
          placeholder="Enter your email address"
        >
      </div>

      <div class="form-group password-wrap">
        <label class="form-label">Password</label>
        <input 
          class="form-input" 
          name="password" 
          type="password" 
          minlength="6"
          required
          placeholder="Enter your password"
        >
      </div>

      <button class="auth-btn" type="submit">Login</button>
    </form>

    <div class="auth-footer-note">
      No account? <a href="register.php">Register here</a>
    </div>
  <?php endif; ?>

  </div>
</div>