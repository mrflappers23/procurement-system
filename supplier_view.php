<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/functions.php';
require_login();
$me = current_user($pdo);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE supplier_id = ?");
$stmt->execute([$id]);
$s = $stmt->fetch();
if (!$s) { http_response_code(404); die('Supplier not found.'); }

$ratingRow = $pdo->prepare("SELECT AVG(delivery_score) d, AVG(quality_score) q, AVG(cost_score) c, COUNT(*) n FROM supplier_ratings WHERE supplier_id = ?");
$ratingRow->execute([$id]);
$avg = $ratingRow->fetch();

$history = $pdo->prepare("SELECT po_code, total_amount, status, issue_date FROM purchase_orders WHERE supplier_id = ? ORDER BY issue_date DESC");
$history->execute([$id]);
$history = $history->fetchAll();

$active = 'suppliers';
$pageEyebrow = 'Procure-to-Pay';
$pageTitle = $s['name'];
$mainBtn = null;
require __DIR__ . '/includes/header.php';
?>

<div class="section-head">
  <div>
    <div class="section-title"><?= e($s['name']) ?></div>
    <div class="section-desc"><?= e($s['category']) ?> · <?= e(ucfirst($s['status'])) ?><?= $s['contract_start'] ? ' · Supplier since ' . e(date('Y', strtotime($s['contract_start']))) : '' ?></div>
  </div>
  <a href="purchase_orders.php" class="btn btn-primary">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
    Create purchase order
  </a>
</div>

<div class="grid-2">
  <div>
    <div class="panel" style="padding:20px 22px; margin-bottom:20px;">
      <div class="section-title" style="font-size:14px; margin-bottom:14px;">Performance rating <?= $avg['n'] ? '(' . (int)$avg['n'] . ' review' . ($avg['n']>1?'s':'') . ')' : '(no reviews yet)' ?></div>
      <?php foreach ([['Delivery time', $avg['d']], ['Quality', $avg['q']], ['Cost competitiveness', $avg['c']]] as [$label, $val]): $val = (float)($val ?? 0); ?>
      <div class="rating-bar-wrap">
        <div class="rating-bar-label"><span><?= e($label) ?></span><span class="mono"><?= round($val) ?>%</span></div>
        <div class="rating-bar-track"><div class="rating-bar-fill" style="width:<?= max(2,$val) ?>%"></div></div>
      </div>
      <?php endforeach; ?>

      <div class="section-title" style="font-size:14px; margin:22px 0 12px;">Contract &amp; terms</div>
      <div class="match-row"><span class="k">Payment terms</span><span class="v"><?= e($s['payment_terms']) ?></span></div>
      <div class="match-row"><span class="k">Contract start</span><span class="v"><?= $s['contract_start'] ? e(date('M j, Y', strtotime($s['contract_start']))) : '—' ?></span></div>
      <div class="match-row"><span class="k">Contract end</span><span class="v"><?= $s['contract_end'] ? e(date('M j, Y', strtotime($s['contract_end']))) : '—' ?></span></div>
      <div class="match-row"><span class="k">Contact</span><span class="v"><?= e($s['contact_name'] ?: '—') ?></span></div>
      <div class="match-row"><span class="k">Email</span><span class="v"><?= e($s['email'] ?: '—') ?></span></div>
      <div class="match-row"><span class="k">Phone</span><span class="v"><?= e($s['phone'] ?: '—') ?></span></div>
    </div>

    <?php if (has_role(['procurement_officer','admin'])): ?>
    <div class="panel" style="padding:20px 22px;">
      <div class="section-title" style="font-size:14px; margin-bottom:14px;">Submit a performance rating</div>
      <form action="actions/rate_supplier.php" method="post">
        <input type="hidden" name="supplier_id" value="<?= $s['supplier_id'] ?>">
        <div class="field-row">
          <div class="field"><label>Delivery time (0-100)</label><input type="number" name="delivery_score" min="0" max="100" required></div>
          <div class="field"><label>Quality (0-100)</label><input type="number" name="quality_score" min="0" max="100" required></div>
        </div>
        <div class="field-row">
          <div class="field"><label>Cost (0-100)</label><input type="number" name="cost_score" min="0" max="100" required></div>
          <div class="field"><label>Notes</label><input type="text" name="notes" placeholder="Optional"></div>
        </div>
        <button type="submit" class="btn btn-primary">Submit rating</button>
      </form>
    </div>
    <?php endif; ?>
  </div>

  <div class="panel" style="padding:20px 22px;">
    <div class="section-title" style="font-size:14px; margin-bottom:12px;">Purchase history</div>
    <?php if ($history): ?>
      <?php foreach ($history as $h): ?>
      <div style="display:flex; justify-content:space-between; align-items:center; font-size:12.5px; padding:9px 0; border-bottom:1px dashed var(--line);">
        <span><?= e($h['po_code']) ?> <span class="cell-sub" style="margin-left:6px;"><?= e(date('M j, Y', strtotime($h['issue_date']))) ?></span></span>
        <span style="display:flex; align-items:center; gap:10px;"><span class="mono"><?= peso($h['total_amount']) ?></span><?= stamp($h['status']) ?></span>
      </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="cell-sub">No purchase orders with this supplier yet.</div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
