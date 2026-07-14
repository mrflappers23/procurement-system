<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';
require_role(['procurement_officer','admin']);

$poId = (int)($_POST['po_id'] ?? 0);

$stmt = $pdo->prepare("SELECT im.*, po.po_code FROM invoice_matching im
                        JOIN purchase_orders po ON po.po_id = im.po_id
                        WHERE im.po_id = ?");
$stmt->execute([$poId]);
$m = $stmt->fetch();

if (!$m || $m['match_status'] !== 'matched') {
    flash_set('Payment can only be approved once the 3-way match is validated.', 'error');
    redirect('../goods_receipt.php');
}

$pdo->prepare("UPDATE invoice_matching SET payment_approved = 1, payment_approved_by = ?, payment_approved_at = NOW() WHERE po_id = ?")
    ->execute([current_user_id(), $poId]);

log_activity($pdo, current_user_id(), 'payment', "Payment approved for {$m['po_code']}");
flash_set("Payment approved for {$m['po_code']}.");
redirect('../goods_receipt.php');
