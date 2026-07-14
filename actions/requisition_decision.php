<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';
require_role(['manager','admin']);

$id       = (int)($_POST['requisition_id'] ?? 0);
$decision = $_POST['decision'] ?? '';
$notes    = trim($_POST['decision_notes'] ?? '');

if (!in_array($decision, ['approved','rejected'], true)) {
    flash_set('Invalid decision.', 'error');
    redirect("../requisition_view.php?id=$id");
}

$stmt = $pdo->prepare("SELECT * FROM requisitions WHERE requisition_id = ?");
$stmt->execute([$id]);
$req = $stmt->fetch();
if (!$req || $req['status'] !== 'pending') {
    flash_set('This requisition has already been decided.', 'error');
    redirect("../requisition_view.php?id=$id");
}

$stmt = $pdo->prepare("UPDATE requisitions SET status = ?, decided_by = ?, decided_at = NOW(), decision_notes = ? WHERE requisition_id = ?");
$stmt->execute([$decision, current_user_id(), $notes, $id]);

log_activity($pdo, current_user_id(), $decision === 'approved' ? 'approve' : 'reject',
    "{$req['req_code']} {$decision} — {$req['item_description']}");

flash_set("{$req['req_code']} was $decision.");
redirect("../requisition_view.php?id=$id");
