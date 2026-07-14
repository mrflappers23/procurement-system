<?php
$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();
$suppliersList = $pdo->query("SELECT * FROM suppliers WHERE status='active' ORDER BY name")->fetchAll();
?>
<div class="overlay" id="modal-new-req">
  <div class="modal">
    <form action="actions/create_requisition.php" method="post">
      <div class="modal-head">
        <div class="modal-title">New purchase requisition</div>
        <button type="button" class="modal-close" onclick="closeModal('modal-new-req')">&times;</button>
      </div>
      <div class="modal-body">
        <div class="field-row">
          <div class="field">
            <label>Department</label>
            <select name="department_id" required>
              <?php foreach ($departments as $d): ?>
                <option value="<?= $d['department_id'] ?>" <?= ($me['department_id']==$d['department_id'])?'selected':'' ?>><?= e($d['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field"><label>Requested by</label><input type="text" value="<?= e($me['name']) ?>" disabled></div>
        </div>
        <div class="field"><label>Item or service description</label>
          <input type="text" name="item_description" required placeholder="e.g. 6 x Dell Latitude laptops for onboarding cohort">
        </div>
        <div class="field-row">
          <div class="field"><label>Quantity</label><input type="number" name="quantity" min="1" value="1" required></div>
          <div class="field"><label>Estimated amount (PHP)</label><input type="number" step="0.01" name="estimated_amount" required placeholder="0.00"></div>
        </div>
        <div class="field"><label>Preferred supplier (optional)</label>
          <select name="preferred_supplier_id">
            <option value="">No preference</option>
            <?php foreach ($suppliersList as $s): ?>
              <option value="<?= $s['supplier_id'] ?>"><?= e($s['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label>Justification / notes</label><textarea name="justification" rows="3" placeholder="Why is this purchase needed?"></textarea></div>
        <div class="banner ok" style="margin-bottom:0;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 2"/></svg>
          This requisition will route to a manager for approval before any purchase proceeds.
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-new-req')">Cancel</button>
        <button type="submit" class="btn btn-primary">Submit for approval</button>
      </div>
    </form>
  </div>
</div>
