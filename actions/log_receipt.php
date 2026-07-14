<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';
require_role(['procurement_officer','admin']);

$poId = (int)($_POST['po_id'] ?? 0);
$qty  = max(1, (int)($_POST['quantity_received'] ?? 0));
$date = $_POST['received_date'] ?: date('Y-m-d');
$notes = trim($_POST['condition_notes'] ?? '') ?: null;

$stmt = $pdo->prepare("SELECT po_code FROM purchase_orders WHERE po_id = ? AND status = 'delivered'");
$stmt->execute([$poId]);
$po = $stmt->fetch();
if (!$po) {
    flash_set('This purchase order is not eligible for a goods receipt.', 'error');
    redirect('../goods_receipt.php');
}

$pdo->prepare("INSERT INTO goods_receipts (po_id, received_by, received_date, quantity_received, condition_notes) VALUES (?, ?, ?, ?, ?)")
    ->execute([$poId, current_user_id(), $date, $qty, $notes]);
$receiptId = (int)$pdo->lastInsertId();

$matchStmt = $pdo->prepare("SELECT matching_id FROM invoice_matching WHERE po_id = ?");
$matchStmt->execute([$poId]);
if ($matchStmt->fetch()) {
    $pdo->prepare("UPDATE invoice_matching SET receipt_id = ?, match_status = 'pending' WHERE po_id = ?")->execute([$receiptId, $poId]);
} else {
    $pdo->prepare("INSERT INTO invoice_matching (po_id, receipt_id, match_status) VALUES (?, ?, 'pending')")->execute([$poId, $receiptId]);
}

log_activity($pdo, current_user_id(), 'receipt', "Goods receipt logged for {$po['po_code']} ($qty received)");
flash_set("Goods receipt logged for {$po['po_code']}.");
redirect('../goods_receipt.php');
