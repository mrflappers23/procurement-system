<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/functions.php';
require_login();
$me = current_user($pdo);

$filter = $_GET['status'] ?? 'all';
$allowed = ['all','sent','confirmed','delivered','cancelled'];
if (!in_array($filter, $allowed, true)) $filter = 'all';

$sql = "SELECT po.*, s.name AS supplier_name, r.req_code
        FROM purchase_orders po
        JOIN suppliers s ON s.supplier_id = po.supplier_id
        LEFT JOIN requisitions r ON r.requisition_id = po.requisition_id";
$params = [];
if ($filter !== 'all') { $sql .= " WHERE po.status = ?"; $params[] = $filter; }
$sql .= " ORDER BY po.issue_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$counts = $pdo->query("SELECT status, COUNT(*) c FROM purchase_orders GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$totalCount = array_sum($counts);

// approved requisitions that don't have a PO yet (eligible for auto-generation)
$eligibleReqs = $pdo->query("
    SELECT
        r.requisition_id,
        r.req_code,
        r.catalogue_id,
        r.item_description,
        r.quantity,
        r.estimated_amount,
        r.preferred_supplier_id,
        c.product_name,
        c.unit,
        c.unit_price
    FROM requisitions r
    LEFT JOIN supplier_catalogue c
        ON r.catalogue_id = c.catalogue_id
    LEFT JOIN purchase_orders po
        ON po.requisition_id = r.requisition_id
    WHERE
        r.status='approved'
        AND po.po_id IS NULL
    ORDER BY r.req_code
")->fetchAll();
$suppliersList = $pdo->query("SELECT * FROM suppliers WHERE status='active' ORDER BY name")->fetchAll();

$active = 'orders';
$pageEyebrow = 'Procure-to-Pay';
$pageTitle = 'Purchase Orders';
$mainBtn = has_role(['procurement_officer','admin']) ? ['label' => 'Create Purchase Order', 'modal' => 'modal-new-po'] : null;
require __DIR__ . '/includes/header.php';
?>

<div class="filter-row">
  <a class="chip <?= $filter==='all'?'active':'' ?>" href="?status=all">All (<?= $totalCount ?>)</a>
  <a class="chip <?= $filter==='sent'?'active':'' ?>" href="?status=sent">Sent (<?= $counts['sent'] ?? 0 ?>)</a>
  <a class="chip <?= $filter==='confirmed'?'active':'' ?>" href="?status=confirmed">Confirmed (<?= $counts['confirmed'] ?? 0 ?>)</a>
  <a class="chip <?= $filter==='delivered'?'active':'' ?>" href="?status=delivered">Delivered (<?= $counts['delivered'] ?? 0 ?>)</a>
  <a class="chip <?= $filter==='cancelled'?'active':'' ?>" href="?status=cancelled">Cancelled (<?= $counts['cancelled'] ?? 0 ?>)</a>
</div>

<div class="panel">
  <?php if ($orders): ?>
  <table>
    <thead><tr><th>PO Number</th><th>Supplier</th><th>Linked requisition</th><th>Issue date</th><th>Amount</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($orders as $po): ?>
      <tr>
        <td class="mono cell-primary"><?= e($po['po_code']) ?></td>
        <td><?= e($po['supplier_name']) ?></td>
        <td class="mono"><?= e($po['req_code'] ?? '—') ?></td>
        <td class="mono"><?= e(date('M j', strtotime($po['issue_date']))) ?></td>
        <td class="mono"><?= peso($po['total_amount']) ?></td>
        <td><?= stamp($po['status']) ?></td>
        <td><a class="btn btn-ghost btn-sm" href="po_view.php?id=<?= $po['po_id'] ?>">View</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
    <div class="empty"><div class="empty-title">No purchase orders here</div>Try a different filter.</div>
  <?php endif; ?>
</div>

<div class="overlay" id="modal-new-po">
  <div class="modal">
    <form action="actions/create_po.php" method="post">
      <div class="modal-head">
        <div class="modal-title">Create purchase order</div>
        <button type="button" class="modal-close" onclick="closeModal('modal-new-po')">&times;</button>
      </div>
      <div class="modal-body">
        <div class="field">
          <label>Generate from an approved requisition (optional)</label>
          <select name="requisition_id" id="req-select" onchange="fillFromReq(this)">
            <option value="">— Manual purchase order —</option>
            <?php foreach ($eligibleReqs as $r): ?>
              <option
              value="<?= $r['requisition_id'] ?>"
              data-catalogue="<?= $r['catalogue_id'] ?>"
              data-product="<?= e($r['product_name']) ?>"
              data-unit="<?= e($r['unit']) ?>"
              data-price="<?= $r['unit_price'] ?>"
              data-qty="<?= $r['quantity'] ?>"
              data-supplier="<?= $r['preferred_supplier_id'] ?>">

              <?= e($r['req_code']) ?>
              —
              <?= e($r['product_name']) ?>
              (<?= $r['quantity'] ?> <?= e($r['unit']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field-row">

    <div class="field">

        <label>Supplier</label>

        <select
            id="supplier-select"
            name="supplier_id"
            required>

            <option value="">Select Supplier</option>

            <?php foreach($suppliersList as $supplier): ?>

                <option value="<?= $supplier['supplier_id'] ?>">

                    <?= e($supplier['name']) ?>

                </option>

            <?php endforeach; ?>

        </select>

    </div>

    <div class="field">

    <label>Product</label>

    <select
        id="product-select"
        name="catalogue_id"
        required>

        <option value="">
            Select Supplier First
        </option>

    </select>

</div>

</div>
        <div class="field-row">

    <div class="field">

        <label>Quantity</label>

        <input
            id="qty-input"
            type="number"
            name="quantity"
            value="1"
            min="1"
            required>

    </div>

    <div class="field">

    <label>Unit price (PHP)</label>

    <input
        type="number"
        step="0.01"
        name="unit_price"
        id="unit-price-input"
        readonly>

  </div>

</div>

<div class="field">

    <label>Total</label>

    <input
        id="total-input"
        readonly>

</div>
        <div class="field-row">
          <div class="field"><label>Issue date</label><input type="date" name="issue_date" value="<?= date('Y-m-d') ?>" required></div>
          <div class="field"><label>Expected delivery</label><input type="date" name="expected_delivery_date"></div>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-new-po')">Cancel</button>
        <button type="submit" class="btn btn-primary">Create &amp; send to supplier</button>
      </div>
    </form>
  </div>
</div>

<script>
const catalogue = <?= json_encode(
    $pdo->query("
        SELECT
            catalogue_id,
            supplier_id,
            product_name,
            unit,
            unit_price
        FROM supplier_catalogue
        ORDER BY product_name
    ")->fetchAll()
) ?>;

const supplierSelect = document.getElementById('supplier-select');
const productSelect  = document.getElementById('product-select');
const reqSelect      = document.getElementById('req-select');

const qtyInput       = document.getElementById('qty-input');
const unitPriceInput = document.getElementById('unit-price-input');
const totalInput     = document.getElementById('total-input');

function refreshProducts(selectedCatalogue = null){

    const supplierId = supplierSelect.value;

    productSelect.innerHTML =
        '<option value="">Select Product</option>';

    unitPriceInput.value = '';
    totalInput.value = '';

    if(!supplierId) return;

    catalogue
        .filter(item => item.supplier_id == supplierId)
        .forEach(item => {

            const option = document.createElement('option');

            option.value = item.catalogue_id;
            option.dataset.price = item.unit_price;

            option.textContent =
                item.product_name +
                " (" +
                item.unit +
                ")";

            if(selectedCatalogue &&
               item.catalogue_id == selectedCatalogue){
                option.selected = true;
            }

            productSelect.appendChild(option);

        });

    if(selectedCatalogue){
        updatePrice();
    }

}

function updatePrice(){

    const option =
        productSelect.options[productSelect.selectedIndex];

    if(!option || !option.dataset.price){

        unitPriceInput.value = '';
        updateTotal();
        return;

    }

    unitPriceInput.value =
        parseFloat(option.dataset.price).toFixed(2);

    updateTotal();

}

function updateTotal(){

    const qty =
        parseInt(qtyInput.value) || 0;

    const price =
        parseFloat(unitPriceInput.value) || 0;

    totalInput.value =
        (qty * price).toFixed(2);

}

function fillFromReq(sel){

    const option = sel.options[sel.selectedIndex];

    if(!option.value){

        supplierSelect.value = '';

        refreshProducts();

        qtyInput.value = 1;

        return;

    }

    supplierSelect.value = option.dataset.supplier;

    refreshProducts(option.dataset.catalogue);

    qtyInput.value = option.dataset.qty;

    updateTotal();

}

supplierSelect.addEventListener('change', function(){

    refreshProducts();

});

productSelect.addEventListener('change', function(){

    updatePrice();

});

qtyInput.addEventListener('input', function(){

    updateTotal();

});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
