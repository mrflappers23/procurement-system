<?php
/**
 * ONE-TIME SETUP SCRIPT
 * -------------------------------------------------------------------
 * Run this once in your browser after importing schema.sql, e.g.:
 *   http://localhost/procurement-system/database/seed_users.php
 *
 * It sets a working bcrypt password hash for every seeded user.
 * All seeded users share the password: password123
 *
 * Delete this file (or block it) after running it once.
 * -------------------------------------------------------------------
 */

require __DIR__ . '/../config/db.php';

$defaultPassword = 'password123';
$hash = password_hash($defaultPassword, PASSWORD_BCRYPT);

$stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE password_hash = 'PENDING'");
$stmt->execute([$hash]);

$count = $stmt->rowCount();

echo "<h2>Ledgerway setup</h2>";
if ($count > 0) {
    echo "<p>Updated <b>$count</b> user account(s) with password: <code>$defaultPassword</code></p>";
} else {
    echo "<p>No pending accounts found — passwords may already be set.</p>";
}

$users = $pdo->query("SELECT name, email, role FROM users")->fetchAll();
echo "<table border='1' cellpadding='6' style='border-collapse:collapse;font-family:sans-serif;font-size:13px;'>";
echo "<tr><th>Name</th><th>Email</th><th>Role</th></tr>";
foreach ($users as $u) {
    echo "<tr><td>{$u['name']}</td><td>{$u['email']}</td><td>{$u['role']}</td></tr>";
}
echo "</table>";
echo "<p style='margin-top:16px;font-family:sans-serif;'>You can now log in at <a href='../login.php'>login.php</a>. Please delete this file once setup is complete.</p>";
