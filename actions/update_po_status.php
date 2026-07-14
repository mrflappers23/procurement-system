<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';
require_role(['procurement_officer','admin']);

$poId = (int)($_POST['po_id'] ?? 0);
$newStatus = $_POST['status'] ?? '';

$validTransitions = [
    'sent' => ['confirmed','cancelled'],
    'confirmed' => ['delivered','cancelled'],
];

$stmt = $pdo->prepare("SELECT * FROM purchase_orders WHERE po_id = ?");
$stmt->execute([$poId]);
$po = $stmt->fetch();

if (!$po || !isset($validTransitions[$po['status']]) || !in_array($newStatus, $validTransitions[$po['status']], true)) {
    flash_set('That status change is not allowed.', 'error');
    redirect("../po_view.php?id=$poId");
}

$pdo->prepare("UPDATE purchase_orders SET status = ? WHERE po_id = ?")->execute([$newStatus, $poId]);

// once delivered, open a pending 3-way match record so it shows up in Goods Receipt & Matching
if ($newStatus === 'delivered') {
    $exists = $pdo->prepare("SELECT matching_id FROM invoice_matching WHERE po_id = ?");
    $exists->execute([$poId]);
    if (!$exists->fetch()) {
        $pdo->prepare("INSERT INTO invoice_matching (po_id, match_status) VALUES (?, 'pending')")->execute([$poId]);
    }
}

log_activity($pdo, current_user_id(), 'po_status', "{$po['po_code']} status changed to $newStatus");
flash_set("{$po['po_code']} is now $newStatus.");
redirect("../po_view.php?id=$poId");
