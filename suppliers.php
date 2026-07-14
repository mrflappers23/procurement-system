<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/functions.php';
require_login();
$me = current_user($pdo);

$suppliers = $pdo->query("
    SELECT s.*,
           COALESCE(AVG(r.delivery_score),0) avg_delivery,
           COALESCE(AVG(r.quality_score),0) avg_quality,
           COALESCE(AVG(r.cost_score),0) avg_cost,
           (SELECT COUNT(*) FROM purchase_orders po WHERE po.supplier_id = s.supplier_id) order_count
    FROM suppliers s
    LEFT JOIN supplier_ratings r ON r.supplier_id = s.supplier_id
    GROUP BY s.supplier_id
    ORDER BY s.name
")->fetchAll();

$logoColors = ['#3B5BA6,#6E8FD6', '#8A5C15,#C9973F', '#2E6F6E,#57A6A4', '#5B5FA6,#8286D6', '#AD3B41,#D97A7F', '#3F7D5C,#6FAE87'];

$active = 'suppliers';
$pageEyebrow = 'Procure-to-Pay';
$pageTitle = 'Supplier Directory';
$mainBtn = has_role(['procurement_officer','admin']) ? ['label' => 'Add Supplier', 'modal' => 'modal-new-supplier'] : null;
require __DIR__ . '/includes/header.php';
?>

<div class="supplier-grid">
  <?php foreach ($suppliers as $i => $s):
      $overall = ($s['avg_delivery'] + $s['avg_quality'] + $s['avg_cost']) / 3;
      $gradient = $logoColors[$i % count($logoColors)];
      $initials = strtoupper(substr(preg_replace('/[^A-Za-z ]/','',$s['name']),0,1) . (strpos($s['name'],' ') ? substr($s['name'], strpos($s['name'],' ')+1, 1) : ''));
  ?>
  <div class="supplier-card linked" onclick="location.href='supplier_view.php?id=<?= $s['supplier_id'] ?>'">
    <div class="supplier-top">
      <div>
        <div class="supplier-logo" style="background:linear-gradient(135deg,<?= $gradient ?>)"><?= e($initials) ?></div>
        <div class="supplier-name" style="margin-top:8px;"><?= e($s['name']) ?></div>
        <div class="supplier-cat"><?= e($s['category']) ?></div>
      </div>
      <?= stars($overall) ?>
    </div>
    <div class="supplier-meta">
      <div class="meta-block"><div class="meta-label">Orders</div><div class="meta-val"><?= (int)$s['order_count'] ?></div></div>
      <div class="meta-block"><div class="meta-label">On-time</div><div class="meta-val"><?= round($s['avg_delivery']) ?>%</div></div>
      <div class="meta-block"><div class="meta-label">Terms</div><div class="meta-val"><?= e($s['payment_terms']) ?></div></div>
    </div>
  </div>
  <?php endforeach; ?>
  <?php if (!$suppliers): ?><div class="empty"><div class="empty-title">No suppliers yet</div>Add your first supplier to get started.</div><?php endif; ?>
</div>

<div class="overlay" id="modal-new-supplier">
  <div class="modal">
    <form action="actions/create_supplier.php" method="post">
      <div class="modal-head">
        <div class="modal-title">Add supplier</div>
        <button type="button" class="modal-close" onclick="closeModal('modal-new-supplier')">&times;</button>
      </div>
      <div class="modal-body">
        <div class="field-row">
          <div class="field"><label>Supplier name</label><input type="text" name="name" required></div>
          <div class="field"><label>Category</label><input type="text" name="category" required placeholder="e.g. IT & Electronics"></div>
        </div>
        <div class="field-row">
          <div class="field"><label>Contact name</label><input type="text" name="contact_name"></div>
          <div class="field"><label>Email</label><input type="email" name="email"></div>
        </div>
        <div class="field-row">
          <div class="field"><label>Phone</label><input type="text" name="phone"></div>
          <div class="field"><label>Payment terms</label>
            <select name="payment_terms">
              <option>Net 15</option><option selected>Net 30</option><option>Net 45</option><option>Net 60</option>
            </select>
          </div>
        </div>
        <div class="field"><label>Address</label><input type="text" name="address"></div>
        <div class="field-row">
          <div class="field"><label>Contract start</label><input type="date" name="contract_start"></div>
          <div class="field"><label>Contract end</label><input type="date" name="contract_end"></div>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-new-supplier')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save supplier</button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
