<?php
$departments = $pdo->query("
    SELECT *
    FROM departments
    ORDER BY name
")->fetchAll();

$suppliersList = $pdo->query("
    SELECT *
    FROM suppliers
    WHERE status='active'
    ORDER BY name
")->fetchAll();

$catalogue = $pdo->query("
    SELECT
        catalogue_id,
        supplier_id,
        product_name,
        unit,
        unit_price
    FROM supplier_catalogue
    ORDER BY product_name
")->fetchAll();
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
        <div class="field-row">

    <div class="field">

        <label>Supplier</label>

        <select
            id="supplier-select"
            name="preferred_supplier_id"
            required>

            <option value="">
                Select Supplier
            </option>

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
            id="qty"
            type="number"
            name="quantity"
            min="1"
            value="1"
            required>

    </div>

    <div class="field">

        <label>Unit Price</label>

        <input
            id="unit-price"
            type="text"
            readonly>

    </div>

</div>

<div class="field">

    <label>Estimated Total</label>

    <input
        id="estimated-total"
        name="estimated_amount"
        readonly>

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

<script>

const catalogue = <?= json_encode($catalogue) ?>;

const supplierSelect = document.getElementById('supplier-select');
const productSelect = document.getElementById('product-select');
const qtyInput = document.getElementById('qty');
const unitPriceInput = document.getElementById('unit-price');
const totalInput = document.getElementById('estimated-total');

function refreshProducts() {

    const supplierId = supplierSelect.value;

    productSelect.innerHTML =
        '<option value="">Select Product</option>';

    unitPriceInput.value = '';
    totalInput.value = '';

    if (!supplierId) return;

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

            productSelect.appendChild(option);

        });

}

function updatePrice() {

    const option =
        productSelect.options[productSelect.selectedIndex];

    if (!option || !option.dataset.price) {

        unitPriceInput.value = '';
        totalInput.value = '';
        return;

    }

    const price = parseFloat(option.dataset.price);

    unitPriceInput.value =
        price.toFixed(2);

    updateTotal();

}

function updateTotal() {

    const price =
        parseFloat(unitPriceInput.value) || 0;

    const qty =
        parseInt(qtyInput.value) || 0;

    totalInput.value =
        (price * qty).toFixed(2);

}

supplierSelect.addEventListener(
    'change',
    refreshProducts
);

productSelect.addEventListener(
    'change',
    updatePrice
);

qtyInput.addEventListener(
    'input',
    updateTotal
);

</script>
