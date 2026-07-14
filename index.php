<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/functions.php';
require_login();
$me = current_user($pdo);

$pendingApprovals = (int)$pdo->query("SELECT COUNT(*) FROM requisitions WHERE status='pending'")->fetchColumn();
$activePOs        = (int)$pdo->query("SELECT COUNT(*) FROM purchase_orders WHERE status IN ('sent','confirmed','delivered')")->fetchColumn();
$committedAmount  = (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM purchase_orders WHERE status IN ('sent','confirmed','delivered')")->fetchColumn();
$mismatches       = (int)$pdo->query("SELECT COUNT(*) FROM invoice_matching WHERE match_status='mismatch' AND payment_approved=0")->fetchColumn();
$onTimeAvg        = $pdo->query("SELECT AVG(delivery_score) FROM supplier_ratings")->fetchColumn();
$onTimeAvg        = $onTimeAvg !== null ? round($onTimeAvg) : 0;

$pendingList = $pdo->query("
    SELECT r.*, d.name AS department_name
    FROM requisitions r JOIN departments d ON d.department_id = r.department_id
    WHERE r.status = 'pending'
    ORDER BY r.created_at ASC LIMIT 5
")->fetchAll();

$activity = $pdo->query("
    SELECT a.*, u.name AS user_name
    FROM activity_log a LEFT JOIN users u ON u.user_id = a.user_id
    ORDER BY a.created_at DESC LIMIT 8
")->fetchAll();

$active = 'dashboard';
$pageEyebrow = 'Overview';
$pageTitle = 'Good day, ' . explode(' ', $me['name'])[0];
$mainBtn = has_role(['employee','manager','procurement_officer']) ? ['label' => 'New Requisition', 'modal' => 'modal-new-req'] : null;
require __DIR__ . '/includes/header.php';
?>

<div class="kpi-row">
  <div class="kpi-card" style="--accent:var(--amber)">
    <div class="kpi-label">Pending Approvals</div>
    <div class="kpi-value"><?= $pendingApprovals ?></div>
    <div class="kpi-delta warn">Awaiting manager decision</div>
  </div>
  <div class="kpi-card" style="--accent:var(--slate)">
    <div class="kpi-label">Active Purchase Orders</div>
    <div class="kpi-value"><?= $activePOs ?></div>
    <div class="kpi-delta"><?= peso($committedAmount) ?> committed</div>
  </div>
  <div class="kpi-card" style="--accent:var(--red)">
    <div class="kpi-label">Flagged Mismatches</div>
    <div class="kpi-value"><?= $mismatches ?></div>
    <div class="kpi-delta warn">Blocking payment</div>
  </div>
  <div class="kpi-card" style="--accent:var(--green)">
    <div class="kpi-label">Avg. Supplier On-Time Score</div>
    <div class="kpi-value"><?= $onTimeAvg ?>%</div>
    <div class="kpi-delta up">Across all rated suppliers</div>
  </div>
</div>

<div class="grid-2">
  <div class="panel">
    <div style="padding:18px 20px 4px;">
      <div class="section-title" style="font-size:15px;">Requisitions awaiting approval</div>
    </div>
    <?php if ($pendingList): ?>
    <table>
      <thead><tr><th>Requisition</th><th>Department</th><th>Item</th><th>Amount</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($pendingList as $r): ?>
        <tr class="row-link" onclick="location.href='requisition_view.php?id=<?= $r['requisition_id'] ?>'">
          <td class="mono"><?= e($r['req_code']) ?></td>
          <td><?= e($r['department_name']) ?></td>
          <td class="cell-primary"><?= e($r['item_description']) ?></td>
          <td class="mono"><?= peso($r['estimated_amount']) ?></td>
          <td><?= stamp($r['status']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
      <div class="empty"><div class="empty-title">All caught up</div>No requisitions are waiting for approval.</div>
    <?php endif; ?>
  </div>

  <div class="panel">
    <div style="padding:18px 20px 4px;"><div class="section-title" style="font-size:15px;">Activity</div></div>
    <div style="padding:6px 20px 20px; display:flex; flex-direction:column; gap:16px; margin-top:8px;">
      <?php foreach ($activity as $a): ?>
        <div style="display:flex; gap:10px;">
          <span class="stamp <?= e($a['action']==='flag' ? 'mismatch' : ($a['action']==='match' ? 'matched' : ($a['action']==='approve' ? 'approved' : 'sent'))) ?> flat" style="padding:5px 6px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
          </span>
          <div>
            <div style="font-size:13px;"><?= e($a['description']) ?></div>
            <div class="cell-sub"><?= e(date('M j, g:ia', strtotime($a['created_at']))) ?><?= $a['user_name'] ? ' · ' . e($a['user_name']) : '' ?></div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (!$activity): ?><div class="cell-sub">No activity yet.</div><?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/modal_new_requisition.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
