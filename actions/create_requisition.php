<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';
require_login();

$departmentId = (int)($_POST['department_id'] ?? 0);
$catalogueId  = (int)($_POST['catalogue_id'] ?? 0);
$quantity     = max(1, (int)($_POST['quantity'] ?? 1));
$justification = trim($_POST['justification'] ?? '');

if ($departmentId <= 0 || $catalogueId <= 0) {

    flash_set(
        'Please select a supplier and product.',
        'error'
    );

    redirect('../requisitions.php');

}

$stmt = $pdo->prepare("
    SELECT
        catalogue_id,
        supplier_id,
        product_name,
        unit_price
    FROM supplier_catalogue
    WHERE catalogue_id = ?
");

$stmt->execute([$catalogueId]);

$product = $stmt->fetch();

if (!$product) {

    flash_set(
        'Selected product no longer exists.',
        'error'
    );

    redirect('../requisitions.php');

}

$itemDesc = $product['product_name'];

$preferredSupId = $product['supplier_id'];

$amount = $quantity * $product['unit_price'];

$code = generate_code($pdo, 'requisitions', 'req_code', 'REQ');

$stmt = $pdo->prepare("
    INSERT INTO requisitions
    (
        req_code,
        department_id,
        requested_by,
        item_description,
        quantity,
        estimated_amount,
        preferred_supplier_id,
        catalogue_id,
        justification,
        status
    )
    VALUES
    (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending'
    )
");

$stmt->execute([
    $code,
    $departmentId,
    current_user_id(),
    $itemDesc,
    $quantity,
    $amount,
    $preferredSupId,
    $catalogueId,
    $justification
]);

log_activity($pdo, current_user_id(), 'submit', "$code submitted for approval — $itemDesc");
flash_set("$code was submitted and is now pending manager approval.");
redirect('../requisitions.php');
