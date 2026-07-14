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

$catalogueStmt = $pdo->prepare("
    SELECT *
    FROM supplier_catalogue
    WHERE supplier_id = ?
    ORDER BY product_name
");

$catalogueStmt->execute([$id]);

$catalogue = $catalogueStmt->fetchAll();

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

  <div>

    <!-- Supplier Catalogue -->
    <div class="panel" style="padding:20px 22px; margin-bottom:20px;">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">

    <div class="section-title" style="font-size:14px;">
        Supplier Catalogue
    </div>

    <?php if (has_role(['procurement_officer','admin'])): ?>

        <button
            type="button"
            class="btn btn-primary"
            onclick="openModal('modal-add-product')">

            + Add Product

        </button>

    <?php endif; ?>

</div>

        <?php if ($catalogue): ?>

            <table style="width:100%; border-collapse:collapse; font-size:13px;">

                <thead>

                    <tr>

                        <th>Product</th>

                        <th>Description</th>

                        <th>Unit</th>

                        <th style="text-align:right;">Price</th>

                        <th style="text-align:center;">Actions</th>

                    </tr>

                    </thead>

                <tbody>

                <?php foreach ($catalogue as $item): ?>

                    <tr style="border-bottom:1px dashed var(--line);">

                    <td style="padding:10px 0;">
                        <strong><?= e($item['product_name']) ?></strong>
                    </td>

                    <td>
                        <?= e($item['description'] ?: '—') ?>
                    </td>

                    <td>
                        <?= e($item['unit']) ?>
                    </td>

                    <td style="text-align:right;" class="mono">
                        <?= peso($item['unit_price']) ?>
                    </td>

                    <td style="text-align:center; white-space:nowrap; display:flex; justify-content:center; gap:6px;">

                      <button
                          type="button"
                          class="btn btn-ghost"
                          title="Edit Product"
                          onclick='editCatalogueItem(
                              <?= $item["catalogue_id"] ?>,
                              <?= json_encode($item["product_name"]) ?>,
                              <?= json_encode($item["description"]) ?>,
                              <?= json_encode($item["unit"]) ?>,
                              <?= json_encode($item["unit_price"]) ?>
                          )'>

                          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                              stroke="currentColor" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round">

                              <path d="M12 20h9"/>
                              <path d="M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4 12.5-12.5z"/>

                          </svg>

                      </button>

                      <a
                          href="actions/delete_catalogue_item.php?id=<?= $item['catalogue_id'] ?>"
                          class="btn btn-ghost"
                          title="Delete Product"
                          onclick="return confirm('Delete this product?');">

                          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                              stroke="currentColor" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round">

                              <polyline points="3 6 5 6 21 6"/>
                              <path d="M19 6l-1 14H6L5 6"/>
                              <path d="M10 11v6"/>
                              <path d="M14 11v6"/>
                              <path d="M9 6V4h6v2"/>

                          </svg>

                      </a>

                  </td>

                </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        <?php else: ?>

            <div class="cell-sub">

                No catalogue items added yet.

            </div>

        <?php endif; ?>

    </div>

    <!-- Purchase History -->

    <div class="panel" style="padding:20px 22px;">

        <div class="section-title" style="font-size:14px; margin-bottom:12px;">

            Purchase History

        </div>

        <?php if ($history): ?>

            <?php foreach ($history as $h): ?>

            <div style="display:flex; justify-content:space-between; align-items:center; font-size:12.5px; padding:9px 0; border-bottom:1px dashed var(--line);">

                <span>

                    <?= e($h['po_code']) ?>

                    <span class="cell-sub" style="margin-left:6px;">

                        <?= e(date('M j, Y', strtotime($h['issue_date']))) ?>

                    </span>

                </span>

                <span style="display:flex; align-items:center; gap:10px;">

                    <span class="mono">

                        <?= peso($h['total_amount']) ?>

                    </span>

                    <?= stamp($h['status']) ?>

                </span>

            </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="cell-sub">

                No purchase orders with this supplier yet.

            </div>

        <?php endif; ?>

    </div>

</div>
</div>

<div class="overlay" id="modal-add-product">

    <div class="modal">

        <form action="actions/add_catalogue_item.php" method="post">

            <input
                type="hidden"
                name="supplier_id"
                value="<?= $s['supplier_id'] ?>">

            <div class="modal-head">

                <div class="modal-title">

                    Add Catalogue Item

                </div>

                <button
                    type="button"
                    class="modal-close"
                    onclick="closeModal('modal-add-product')">

                    &times;

                </button>

            </div>

            <div class="modal-body">

                <div class="field">

                    <label>Product Name</label>

                    <input
                        type="text"
                        name="product_name"
                        required>

                </div>

                <div class="field">

                    <label>Description</label>

                    <input
                        type="text"
                        name="description">

                </div>

                <div class="field-row">

                    <div class="field">

                        <label>Unit</label>

                        <input
                            type="text"
                            name="unit"
                            required>

                    </div>

                    <div class="field">

                        <label>Unit Price</label>

                        <input
                            type="number"
                            name="unit_price"
                            step="0.01"
                            min="0"
                            required>

                    </div>

                </div>

            </div>

            <div class="modal-foot">

                <button
                    type="button"
                    class="btn btn-ghost"
                    onclick="closeModal('modal-add-product')">

                    Cancel

                </button>

                <button
                    class="btn btn-primary"
                    type="submit">

                    Save Product

                </button>

            </div>

        </form>

    </div>

</div>

<div class="overlay" id="modal-edit-product">

    <div class="modal">

        <form action="actions/update_catalogue_item.php" method="post">

            <input
                type="hidden"
                name="catalogue_id"
                id="edit_catalogue_id">

            <input
                type="hidden"
                name="supplier_id"
                value="<?= $s['supplier_id'] ?>">

            <div class="modal-head">

                <div class="modal-title">

                    Edit Catalogue Item

                </div>

                <button
                    type="button"
                    class="modal-close"
                    onclick="closeModal('modal-edit-product')">

                    &times;

                </button>

            </div>

            <div class="modal-body">

                <div class="field">

                    <label>Product Name</label>

                    <input
                        type="text"
                        id="edit_product_name"
                        name="product_name"
                        required>

                </div>

                <div class="field">

                    <label>Description</label>

                    <input
                        type="text"
                        id="edit_description"
                        name="description">

                </div>

                <div class="field-row">

                    <div class="field">

                        <label>Unit</label>

                        <input
                            type="text"
                            id="edit_unit"
                            name="unit"
                            required>

                    </div>

                    <div class="field">

                        <label>Price</label>

                        <input
                            type="number"
                            id="edit_price"
                            name="unit_price"
                            step="0.01"
                            min="0"
                            required>

                    </div>

                </div>

            </div>

            <div class="modal-foot">

                <button
                    type="button"
                    class="btn btn-ghost"
                    onclick="closeModal('modal-edit-product')">

                    Cancel

                </button>

                <button
                    class="btn btn-primary"
                    type="submit">

                    Save Changes

                </button>

            </div>

        </form>

    </div>

</div>

<script>

function editCatalogueItem(id,name,description,unit,price){

    document.getElementById('edit_catalogue_id').value=id;

    document.getElementById('edit_product_name').value=name;

    document.getElementById('edit_description').value=description;

    document.getElementById('edit_unit').value=unit;

    document.getElementById('edit_price').value=price;

    openModal('modal-edit-product');

}

</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
