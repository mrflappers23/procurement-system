<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';
require_role(['procurement_officer','admin']);

$supplierId = (int)($_POST['supplier_id'] ?? 0);
$delivery = (float)($_POST['delivery_score'] ?? -1);
$quality  = (float)($_POST['quality_score'] ?? -1);
$cost     = (float)($_POST['cost_score'] ?? -1);
$notes    = trim($_POST['notes'] ?? '');

foreach ([$delivery, $quality, $cost] as $v) {
    if ($v < 0 || $v > 100) {
        flash_set('Scores must be between 0 and 100.', 'error');
        redirect("../supplier_view.php?id=$supplierId");
    }
}

$stmt = $pdo->prepare("INSERT INTO supplier_ratings (supplier_id, delivery_score, quality_score, cost_score, notes, rated_by) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$supplierId, $delivery, $quality, $cost, $notes ?: null, current_user_id()]);

log_activity($pdo, current_user_id(), 'supplier_rate', "New performance rating submitted for supplier #$supplierId");
flash_set('Rating submitted — averages updated.');
redirect("../supplier_view.php?id=$supplierId");
