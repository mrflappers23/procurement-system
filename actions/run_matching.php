<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';
require_role(['procurement_officer','admin']);

$poId = (int)($_POST['po_id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT po.po_code, po.quantity AS po_qty, po.total_amount AS po_total,
           gr.quantity_received, inv.quantity_billed, inv.total_amount AS inv_total, inv.invoice_number
    FROM purchase_orders po
    JOIN invoice_matching im ON im.po_id = po.po_id
    LEFT JOIN goods_receipts gr ON gr.po_id = po.po_id
    LEFT JOIN invoices inv ON inv.po_id = po.po_id
    WHERE po.po_id = ?
");
$stmt->execute([$poId]);
$row = $stmt->fetch();

if (!$row || $row['quantity_received'] === null || $row['quantity_billed'] === null) {
    flash_set('Both a goods receipt and an invoice must be logged before matching.', 'error');
    redirect('../goods_receipt.php');
}

$issues = [];
if ((int)$row['quantity_received'] !== (int)$row['po_qty']) {
    $issues[] = "Quantity received ({$row['quantity_received']}) does not match quantity ordered ({$row['po_qty']}).";
}
if ((int)$row['quantity_billed'] !== (int)$row['po_qty']) {
    $issues[] = "Quantity billed ({$row['quantity_billed']}) does not match quantity ordered ({$row['po_qty']}).";
}
$diff = round((float)$row['inv_total'] - (float)$row['po_total'], 2);
if (abs($diff) > 0.01) {
    $direction = $diff > 0 ? 'higher' : 'lower';
    $issues[] = 'Invoice total is ' . peso(abs($diff)) . " $direction than the purchase order (" . peso($row['inv_total']) . ' vs ' . peso($row['po_total']) . ' at the same quantity).';
}

$status = $issues ? 'mismatch' : 'matched';
$notes = $issues ? implode(' ', $issues) : null;

$pdo->prepare("UPDATE invoice_matching SET match_status = ?, discrepancy_notes = ?, matched_by = ?, matched_at = NOW() WHERE po_id = ?")
    ->execute([$status, $notes, current_user_id(), $poId]);

log_activity($pdo, current_user_id(),
    $status === 'matched' ? 'match' : 'flag',
    $status === 'matched'
        ? "{$row['po_code']} 3-way matched & cleared for payment"
        : "{$row['po_code']} invoice amount mismatch flagged"
);

flash_set($status === 'matched'
    ? "{$row['po_code']} matched — ready for payment approval."
    : "{$row['po_code']} has a mismatch — payment is on hold.", $status === 'matched' ? 'ok' : 'error');
redirect('../goods_receipt.php');
