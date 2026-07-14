<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';
require_login();

$departmentId   = (int)($_POST['department_id'] ?? 0);
$itemDesc       = trim($_POST['item_description'] ?? '');
$quantity       = max(1, (int)($_POST['quantity'] ?? 1));
$amount         = (float)($_POST['estimated_amount'] ?? 0);
$preferredSupId = !empty($_POST['preferred_supplier_id']) ? (int)$_POST['preferred_supplier_id'] : null;
$justification  = trim($_POST['justification'] ?? '');

if ($departmentId <= 0 || $itemDesc === '' || $amount <= 0) {
    flash_set('Please fill in all required fields for the requisition.', 'error');
    redirect('../requisitions.php');
}

$code = generate_code($pdo, 'requisitions', 'req_code', 'REQ');

$stmt = $pdo->prepare("INSERT INTO requisitions
    (req_code, department_id, requested_by, item_description, quantity, estimated_amount, preferred_supplier_id, justification, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
$stmt->execute([$code, $departmentId, current_user_id(), $itemDesc, $quantity, $amount, $preferredSupId, $justification]);

log_activity($pdo, current_user_id(), 'submit', "$code submitted for approval — $itemDesc");
flash_set("$code was submitted and is now pending manager approval.");
redirect('../requisitions.php');
