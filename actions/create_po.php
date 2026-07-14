<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';
require_role(['procurement_officer','admin']);

$requisitionId = !empty($_POST['requisition_id']) ? (int)$_POST['requisition_id'] : null;
$supplierId    = (int)($_POST['supplier_id'] ?? 0);
$quantity      = max(1, (int)($_POST['quantity'] ?? 1));
$unitPrice     = (float)($_POST['unit_price'] ?? 0);
$issueDate     = $_POST['issue_date'] ?: date('Y-m-d');
$expectedDate  = $_POST['expected_delivery_date'] ?: null;

if ($supplierId <= 0 || $unitPrice <= 0) {
    flash_set('Please choose a supplier and enter a valid unit price.', 'error');
    redirect('../purchase_orders.php');
}

// A requisition must be approved, and must not already have a PO, to be converted
if ($requisitionId) {
    $stmt = $pdo->prepare("SELECT r.status, po.po_id FROM requisitions r
                            LEFT JOIN purchase_orders po ON po.requisition_id = r.requisition_id
                            WHERE r.requisition_id = ?");
    $stmt->execute([$requisitionId]);
    $check = $stmt->fetch();
    if (!$check || $check['status'] !== 'approved') {
        flash_set('Only approved requisitions can proceed to a purchase order.', 'error');
        redirect('../purchase_orders.php');
    }
    if ($check['po_id']) {
        flash_set('That requisition already has a purchase order.', 'error');
        redirect('../purchase_orders.php');
    }
}

$total = $quantity * $unitPrice;
$code = generate_code($pdo, 'purchase_orders', 'po_code', 'PO');

$stmt = $pdo->prepare("INSERT INTO purchase_orders
    (po_code, requisition_id, supplier_id, quantity, unit_price, total_amount, issue_date, expected_delivery_date, status, created_by)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'sent', ?)");
$stmt->execute([$code, $requisitionId, $supplierId, $quantity, $unitPrice, $total, $issueDate, $expectedDate, current_user_id()]);

$supplierName = $pdo->query("SELECT name FROM suppliers WHERE supplier_id = " . (int)$supplierId)->fetchColumn();
log_activity($pdo, current_user_id(), 'po_sent', "$code sent to " . $supplierName . ($requisitionId ? ' (auto-generated from requisition)' : ' (manual PO)'));

flash_set("$code was created and sent to the supplier.");
redirect('../purchase_orders.php');
