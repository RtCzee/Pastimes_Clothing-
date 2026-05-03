<?php
session_start();
include("data/DBConn.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM tblUser WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && $user['status'] == 'active' && password_verify($password, $user['password'])) {

        $_SESSION['userID'] = $user['userID'];
        $_SESSION['username'] = $user['username'];

        header("Location: Home.php");
        exit;

    } else {
        $error = "Invalid login or account not approved.";
    }
}
?>


 <link rel="stylesheet" href="assets/css/styles.css">


<div class="auth-page">
  <div class="auth-card">

    <div class="auth-back"><a href="Home.php">← Back to Home</a></div>

    <div class="auth-brand">Pastimes</div>
    <div class="auth-title">Welcome Back</div>
    <div class="auth-sub">Login to your account</div>

    <?php if (isset($error)) echo "<p class='pending-note'>$error</p>"; ?>

    <form method="POST">

      <div class="form-group">
        <label class="form-label">Email</label>
        <input class="form-input" name="email" type="email" required>
      </div>

      <div class="form-group password-wrap">
        <label class="form-label">Password</label>
        <input class="form-input" name="password" type="password" required>
      </div>

      <button class="auth-btn" type="submit">Login</button>

    </form>

    <div class="auth-footer-note">
      No account? <a href="register.php">Register</a>
    </div>

  </div>
</div>