<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/functions.php';
require_login();
$me = current_user($pdo);

$filter = $_GET['status'] ?? 'all';
$allowed = ['all','pending','approved','rejected'];
if (!in_array($filter, $allowed, true)) $filter = 'all';

$sql = "SELECT r.*, d.name AS department_name, u.name AS requester_name
        FROM requisitions r
        JOIN departments d ON d.department_id = r.department_id
        JOIN users u ON u.user_id = r.requested_by";
$params = [];
if ($filter !== 'all') { $sql .= " WHERE r.status = ?"; $params[] = $filter; }
$sql .= " ORDER BY r.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requisitions = $stmt->fetchAll();

$counts = $pdo->query("SELECT status, COUNT(*) c FROM requisitions GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$totalCount = array_sum($counts);

$active = 'requisitions';
$pageEyebrow = 'Procure-to-Pay';
$pageTitle = 'Purchase Requisitions';
$mainBtn = ['label' => 'New Requisition', 'modal' => 'modal-new-req'];
require __DIR__ . '/includes/header.php';
?>

<div class="filter-row">
  <a class="chip <?= $filter==='all'?'active':'' ?>" href="?status=all">All (<?= $totalCount ?>)</a>
  <a class="chip <?= $filter==='pending'?'active':'' ?>" href="?status=pending">Pending (<?= $counts['pending'] ?? 0 ?>)</a>
  <a class="chip <?= $filter==='approved'?'active':'' ?>" href="?status=approved">Approved (<?= $counts['approved'] ?? 0 ?>)</a>
  <a class="chip <?= $filter==='rejected'?'active':'' ?>" href="?status=rejected">Rejected (<?= $counts['rejected'] ?? 0 ?>)</a>
  <div class="filter-spacer"></div>
  <span class="mono" style="font-size:12px; color:var(--text-faint);">Only approved requisitions may proceed to a purchase order</span>
</div>

<div class="panel">
  <?php if ($requisitions): ?>
  <table>
    <thead><tr><th>Requisition</th><th>Requested by</th><th>Department</th><th>Description</th><th>Amount</th><th>Submitted</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($requisitions as $r): ?>
      <tr class="row-link" onclick="location.href='requisition_view.php?id=<?= $r['requisition_id'] ?>'">
        <td class="mono"><?= e($r['req_code']) ?></td>
        <td><?= e($r['requester_name']) ?></td>
        <td><?= e($r['department_name']) ?></td>
        <td class="cell-primary"><?= e($r['item_description']) ?></td>
        <td class="mono"><?= peso($r['estimated_amount']) ?></td>
        <td class="mono"><?= e(date('M j', strtotime($r['created_at']))) ?></td>
        <td><?= stamp($r['status']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
    <div class="empty"><div class="empty-title">No requisitions here</div>Try a different filter, or create a new one.</div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/modal_new_requisition.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
