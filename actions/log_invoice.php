<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';
require_role(['procurement_officer','admin']);

$poId = (int)($_POST['po_id'] ?? 0);
$invoiceNumber = trim($_POST['invoice_number'] ?? '');
$invoiceDate = $_POST['invoice_date'] ?: date('Y-m-d');
$qty = max(1, (int)($_POST['quantity_billed'] ?? 0));
$unitPrice = (float)($_POST['unit_price'] ?? 0);
$total = $qty * $unitPrice;

$stmt = $pdo->prepare("SELECT po_code, supplier_id FROM purchase_orders WHERE po_id = ? AND status = 'delivered'");
$stmt->execute([$poId]);
$po = $stmt->fetch();
if (!$po || $invoiceNumber === '' || $unitPrice <= 0) {
    flash_set('Please provide a valid invoice for an eligible delivered purchase order.', 'error');
    redirect('../goods_receipt.php');
}

$pdo->prepare("INSERT INTO invoices (po_id, supplier_id, invoice_number, invoice_date, quantity_billed, unit_price, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?)")
    ->execute([$poId, $po['supplier_id'], $invoiceNumber, $invoiceDate, $qty, $unitPrice, $total]);
$invoiceId = (int)$pdo->lastInsertId();

$matchStmt = $pdo->prepare("SELECT matching_id FROM invoice_matching WHERE po_id = ?");
$matchStmt->execute([$poId]);
if ($matchStmt->fetch()) {
    $pdo->prepare("UPDATE invoice_matching SET invoice_id = ?, match_status = 'pending' WHERE po_id = ?")->execute([$invoiceId, $poId]);
} else {
    $pdo->prepare("INSERT INTO invoice_matching (po_id, invoice_id, match_status) VALUES (?, ?, 'pending')")->execute([$poId, $invoiceId]);
}

log_activity($pdo, current_user_id(), 'invoice', "Invoice $invoiceNumber logged for {$po['po_code']}");
flash_set("Invoice logged for {$po['po_code']}.");
redirect('../goods_receipt.php');
