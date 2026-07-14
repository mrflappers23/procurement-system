<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/functions.php';
require_login();
$me = current_user($pdo);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT po.*, s.name AS supplier_name, s.supplier_id, r.req_code
                        FROM purchase_orders po
                        JOIN suppliers s ON s.supplier_id = po.supplier_id
                        LEFT JOIN requisitions r ON r.requisition_id = po.requisition_id
                        WHERE po.po_id = ?");
$stmt->execute([$id]);
$po = $stmt->fetch();
if (!$po) { http_response_code(404); die('Purchase order not found.'); }

$active = 'orders';
$pageEyebrow = 'Procure-to-Pay';
$pageTitle = $po['po_code'];
$mainBtn = null;
require __DIR__ . '/includes/header.php';

$steps = ['Generated','Sent','Confirmed','Delivered'];
$stepIndex = ['sent'=>1,'confirmed'=>2,'delivered'=>3,'cancelled'=>1][$po['status']] ?? 1;
?>

<div class="section-head">
  <div>
    <div class="section-title"><?= e($po['po_code']) ?></div>
    <div class="section-desc"><?= e($po['supplier_name']) ?> <?= $po['req_code'] ? '· linked to ' . e($po['req_code']) : '· manual purchase order' ?></div>
  </div>
  <?= stamp($po['status']) ?>
</div>

<div class="panel" style="padding:22px 24px; margin-bottom:20px;">
  <?php if ($po['status'] !== 'cancelled'): ?>
  <div class="flow">
    <?php foreach ($steps as $i => $label): $cls = $i < $stepIndex ? 'done' : ($i === $stepIndex ? 'active' : ''); ?>
      <div class="flow-step <?= $cls ?>"><div class="flow-line"></div><div class="flow-dot"><?= $i < $stepIndex ? '&#10003;' : $i+1 ?></div><div class="flow-label"><?= e($label) ?></div></div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
    <div class="banner warn" style="margin-bottom:0;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01M10.3 3.9 2.6 18a1.5 1.5 0 0 0 1.3 2.2h16.2a1.5 1.5 0 0 0 1.3-2.2L13.7 3.9a1.5 1.5 0 0 0-2.6 0Z"/></svg>
      This purchase order was cancelled.
    </div>
  <?php endif; ?>

  <div class="match-row" style="border-bottom:1px solid var(--line-soft); padding:10px 0; margin-top:18px;"><span class="k">Quantity</span><span class="v"><?= (int)$po['quantity'] ?></span></div>
  <div class="match-row" style="border-bottom:1px solid var(--line-soft); padding:10px 0;"><span class="k">Unit price</span><span class="v"><?= peso($po['unit_price']) ?></span></div>
  <div class="match-row" style="border-bottom:1px solid var(--line-soft); padding:10px 0;"><span class="k">Total</span><span class="v" style="font-size:14px;"><?= peso($po['total_amount']) ?></span></div>
  <div class="match-row" style="border-bottom:1px solid var(--line-soft); padding:10px 0;"><span class="k">Issue date</span><span class="v"><?= e(date('M j, Y', strtotime($po['issue_date']))) ?></span></div>
  <div class="match-row" style="padding:10px 0;"><span class="k">Expected delivery</span><span class="v"><?= $po['expected_delivery_date'] ? e(date('M j, Y', strtotime($po['expected_delivery_date']))) : '—' ?></span></div>
</div>

<?php if (has_role(['procurement_officer','admin']) && !in_array($po['status'], ['cancelled'])): ?>
<div class="panel" style="padding:18px 24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
  <div style="font-size:13px; color:var(--text-soft);">Update the status of this purchase order as it progresses.</div>
  <form action="actions/update_po_status.php" method="post" style="display:flex; gap:10px;">
    <input type="hidden" name="po_id" value="<?= $po['po_id'] ?>">
    <?php if ($po['status'] === 'sent'): ?>
      <button class="btn btn-danger-ghost" name="status" value="cancelled" onclick="return confirmAction('Cancel this purchase order?')">Cancel PO</button>
      <button class="btn btn-primary" name="status" value="confirmed">Mark confirmed</button>
    <?php elseif ($po['status'] === 'confirmed'): ?>
      <button class="btn btn-danger-ghost" name="status" value="cancelled" onclick="return confirmAction('Cancel this purchase order?')">Cancel PO</button>
      <button class="btn btn-primary" name="status" value="delivered">Mark delivered</button>
    <?php elseif ($po['status'] === 'delivered'): ?>
      <a class="btn btn-primary" href="goods_receipt.php">Go to goods receipt &amp; matching</a>
    <?php endif; ?>
  </form>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
