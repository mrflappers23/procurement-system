<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
$error = $_GET['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ledgerway · Log in</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,600;0,9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="login-wrap">
    <div class="login-card">
      <div class="login-brand">
        <div class="brand-mark" style="color:#16213E;">L</div>
        <div>
          <div style="font-family:'Fraunces',serif; font-weight:700; font-size:19px; color:var(--ink);">Ledgerway</div>
          <div style="font-size:11px; color:var(--text-faint); text-transform:uppercase; letter-spacing:.08em;">Procurement Management</div>
        </div>
      </div>

      <?php if ($error): ?>
        <div class="banner warn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01M10.3 3.9 2.6 18a1.5 1.5 0 0 0 1.3 2.2h16.2a1.5 1.5 0 0 0 1.3-2.2L13.7 3.9a1.5 1.5 0 0 0-2.6 0Z"/></svg>
          <?= ($error) ?>
        </div>
      <?php endif; ?>

      <form action="actions/login.php" method="post">
        <div class="field">
          <label>Email</label>
          <input type="email" name="email" required autofocus value="mara@ledgerway.test">
        </div>
        <div class="field" style="margin-bottom:8px;">
          <label>Password</label>
          <input type="password" name="password" required value="password123">
        </div>
        <button class="btn btn-primary" type="submit" style="width:100%; justify-content:center; margin-top:10px;">Log in</button>
      </form>

      <div class="login-hint">
        Seeded accounts (password: <b>password123</b>):<br>
        mara@ledgerway.test — admin ·
        reyes@ledgerway.test — manager ·
        aquino@ledgerway.test — employee ·
        ong@ledgerway.test — procurement officer
      </div>
    </div>
  </div>
</body>
</html>
