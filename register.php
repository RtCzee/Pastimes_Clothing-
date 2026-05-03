<?php
include("data/DBConn.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST['username'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO tblUser (username, fullName, email, password, status)
        VALUES (?, ?, ?, ?, 'pending')
    ");

    $stmt->bind_param("ssss", $username, $fullname, $email, $password);
    $stmt->execute();

    $message = "Account created. Waiting for admin approval.";
}
?>

 <link rel="stylesheet" href="assets/css/styles.css">


<div class="auth-page">
  <div class="auth-card">

    <div class="auth-brand">Pastimes</div>
    <div class="auth-title">Create Account</div>
    <div class="auth-sub">Join the sustainable fashion movement</div>

    <?php if (isset($message)) echo "<p class='pending-note'><strong>Success:</strong> $message</p>"; ?>

    <form method="POST">

      <div class="form-group">
        <label class="form-label">Username</label>
        <input class="form-input" name="username" required>
      </div>

      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input class="form-input" name="fullname" required>
      </div>

      <div class="form-group">
        <label class="form-label">Email</label>
        <input class="form-input" name="email" type="email" required>
      </div>

      <div class="form-group password-wrap">
        <label class="form-label">Password</label>
        <input class="form-input" name="password" type="password" required>
      </div>

      <button class="auth-btn" type="submit">Register</button>

    </form>

    <div class="auth-footer-note">
      Already have an account? <a href="login.php">Login</a>
    </div>

  </div>
</div>