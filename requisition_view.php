<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/functions.php';
require_login();
$me = current_user($pdo);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT r.*, d.name AS department_name, u.name AS requester_name,
                        s.name AS preferred_supplier_name, decider.name AS decider_name
                        FROM requisitions r
                        JOIN departments d ON d.department_id = r.department_id
                        JOIN users u ON u.user_id = r.requested_by
                        LEFT JOIN suppliers s ON s.supplier_id = r.preferred_supplier_id
                        LEFT JOIN users decider ON decider.user_id = r.decided_by
                        WHERE r.requisition_id = ?");
$stmt->execute([$id]);
$req = $stmt->fetch();
if (!$req) { http_response_code(404); die('Requisition not found.'); }

// has a PO already been created from this requisition?
$poStmt = $pdo->prepare("SELECT po_code, status FROM purchase_orders WHERE requisition_id = ? LIMIT 1");
$poStmt->execute([$id]);
$linkedPO = $poStmt->fetch();

$active = 'requisitions';
$pageEyebrow = 'Procure-to-Pay';
$pageTitle = $req['req_code'];
$mainBtn = null;
require __DIR__ . '/includes/header.php';

// step states for the flow diagram
$steps = ['Submitted','Manager review','PO issued','Received & matched'];
$stepState = 1; // index of current active/furthest step (0-based)
if ($req['status'] === 'approved') $stepState = 2;
if ($req['status'] === 'rejected') $stepState = 1;
if ($linkedPO) $stepState = 3;
?>

<div class="section-head">
  <div>
    <div class="section-title"><?= e($req['req_code']) ?> — <?= e($req['item_description']) ?></div>
    <div class="section-desc">Requested by <?= e($req['requester_name']) ?> · <?= e($req['department_name']) ?> · <?= e(date('M j, Y', strtotime($req['created_at']))) ?></div>
  </div>
  <?= stamp($req['status']) ?>
</div>

<div class="panel" style="padding:22px 24px; margin-bottom:20px;">
  <?php if ($req['status'] !== 'rejected'): ?>
  <div class="flow">
    <?php foreach ($steps as $i => $label):
      $cls = $i < $stepState ? 'done' : ($i === $stepState ? 'active' : '');
    ?>
      <div class="flow-step <?= $cls ?>"><div class="flow-line"></div><div class="flow-dot"><?= $i < $stepState ? '&#10003;' : $i+1 ?></div><div class="flow-label"><?= e($label) ?></div></div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
    <div class="banner warn" style="margin-bottom:0;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01M10.3 3.9 2.6 18a1.5 1.5 0 0 0 1.3 2.2h16.2a1.5 1.5 0 0 0 1.3-2.2L13.7 3.9a1.5 1.5 0 0 0-2.6 0Z"/></svg>
      This requisition was rejected<?= $req['decider_name'] ? ' by ' . e($req['decider_name']) : '' ?> and cannot proceed to a purchase order.
    </div>
  <?php endif; ?>

  <div class="match-row" style="border-bottom:1px solid var(--line-soft); padding:10px 0; margin-top:<?= $req['status']!=='rejected'?'18px':'0' ?>;"><span class="k">Amount requested</span><span class="v" style="font-size:14px;"><?= peso($req['estimated_amount']) ?></span></div>
  <div class="match-row" style="border-bottom:1px solid var(--line-soft); padding:10px 0;"><span class="k">Quantity</span><span class="v"><?= (int)$req['quantity'] ?></span></div>
  <div class="match-row" style="border-bottom:1px solid var(--line-soft); padding:10px 0;"><span class="k">Preferred supplier</span><span class="v"><?= e($req['preferred_supplier_name'] ?? 'None specified') ?></span></div>
  <?php if ($req['decided_at']): ?>
  <div class="match-row" style="padding:10px 0;"><span class="k">Decision</span><span class="v"><?= e(ucfirst($req['status'])) ?> by <?= e($req['decider_name']) ?> on <?= e(date('M j, Y', strtotime($req['decided_at']))) ?></span></div>
  <?php endif; ?>

  <?php if ($req['justification']): ?>
  <div style="margin-top:16px;">
    <label style="display:block; font-size:12px; font-weight:600; color:var(--ink-soft); margin-bottom:6px;">Justification</label>
    <div style="font-size:13.5px; color:var(--text-soft); line-height:1.5;"><?= nl2br(e($req['justification'])) ?></div>
  </div>
  <?php endif; ?>
  <?php if ($req['decision_notes']): ?>
  <div style="margin-top:16px;">
    <label style="display:block; font-size:12px; font-weight:600; color:var(--ink-soft); margin-bottom:6px;">Decision notes</label>
    <div style="font-size:13.5px; color:var(--text-soft); line-height:1.5;"><?= nl2br(e($req['decision_notes'])) ?></div>
  </div>
  <?php endif; ?>

  <?php if ($linkedPO): ?>
  <div class="banner ok" style="margin-top:18px; margin-bottom:0;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 2"/></svg>
    Linked to purchase order <b>&nbsp;<?= e($linkedPO['po_code']) ?>&nbsp;</b> (<?= e(ucfirst($linkedPO['status'])) ?>). <a href="purchase_orders.php" style="text-decoration:underline; margin-left:6px;">View purchase orders</a>
  </div>
  <?php endif; ?>
</div>

<?php if ($req['status'] === 'pending' && has_role(['manager','admin'])): ?>
<div class="panel" style="padding:20px 24px;">
  <div class="section-title" style="font-size:15px; margin-bottom:14px;">Manager decision</div>
  <form action="actions/requisition_decision.php" method="post">
    <input type="hidden" name="requisition_id" value="<?= $req['requisition_id'] ?>">
    <div class="field"><label>Decision notes (optional)</label><textarea name="decision_notes" rows="2" placeholder="Add any context for this decision"></textarea></div>
    <div style="display:flex; justify-content:flex-end; gap:10px;">
      <button type="submit" name="decision" value="rejected" class="btn btn-danger-ghost" onclick="return confirmAction('Reject this requisition?')">Reject</button>
      <button type="submit" name="decision" value="approved" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 13l4 4L19 7"/></svg>
        Approve
      </button>
    </div>
  </form>
</div>
<?php elseif ($req['status'] === 'pending'): ?>
  <div class="banner ok" style="margin-bottom:0;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 2"/></svg>
    This requisition is waiting on a manager or admin to approve or reject it.
  </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
