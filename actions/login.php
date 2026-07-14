<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    redirect('../login.php?error=' . urlencode('Please enter your email and password.'));
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || $user['password_hash'] === 'PENDING' || !password_verify($password, $user['password_hash'])) {
    redirect('../login.php?error=' . urlencode('Invalid email or password. Did you run database/seed_users.php yet?'));
}

$_SESSION['user_id'] = $user['user_id'];
$_SESSION['role']    = $user['role'];
$_SESSION['name']    = $user['name'];

redirect('../index.php');
