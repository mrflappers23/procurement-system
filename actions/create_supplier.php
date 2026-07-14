<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';
require_role(['procurement_officer','admin']);

$name = trim($_POST['name'] ?? '');
$category = trim($_POST['category'] ?? '');
if ($name === '' || $category === '') {
    flash_set('Supplier name and category are required.', 'error');
    redirect('../suppliers.php');
}

$stmt = $pdo->prepare("INSERT INTO suppliers
    (name, category, contact_name, email, phone, address, payment_terms, contract_start, contract_end, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
$stmt->execute([
    $name, $category,
    trim($_POST['contact_name'] ?? '') ?: null,
    trim($_POST['email'] ?? '') ?: null,
    trim($_POST['phone'] ?? '') ?: null,
    trim($_POST['address'] ?? '') ?: null,
    $_POST['payment_terms'] ?? 'Net 30',
    $_POST['contract_start'] ?: null,
    $_POST['contract_end'] ?: null,
]);

log_activity($pdo, current_user_id(), 'supplier_add', "New supplier added — $name");
flash_set("$name was added to the supplier directory.");
redirect('../suppliers.php');
