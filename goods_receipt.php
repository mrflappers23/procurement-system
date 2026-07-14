<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/functions.php';
require_login();
$me = current_user($pdo);

$rows = $pdo->query("
    SELECT po.po_id, po.po_code, po.quantity, po.unit_price, po.total_amount, po.issue_date,
           s.name AS supplier_name,
           im.matching_id, im.match_status, im.discrepancy_notes, im.payment_approved,
           gr.receipt_id, gr.quantity_received, gr.received_date, gr.condition_notes,
           inv.invoice_id, inv.invoice_number, inv.quantity_billed, inv.unit_price AS inv_unit_price, inv.total_amount AS inv_total, inv.invoice_date
    FROM purchase_orders po
    JOIN suppliers s ON s.supplier_id = po.supplier_id
    LEFT JOIN invoice_matching im ON im.po_id = po.po_id
    LEFT JOIN goods_receipts gr ON gr.receipt_id = im.receipt_id
    LEFT JOIN invoices inv ON inv.invoice_id = im.invoice_id
    WHERE po.status = 'delivered'
    ORDER BY po.issue_date DESC
")->fetchAll();

$mismatchCount = 0;
foreach ($rows as $r) if ($r['match_status'] === 'mismatch' && !$r['payment_approved']) $mismatchCount++;

$active = 'matching';
$pageEyebrow = 'Procure-to-Pay';
$pageTitle = 'Goods Receipt & Invoice Matching';
$mainBtn = null;
require __DIR__ . '/includes/header.php';
?>

<?php if ($mismatchCount > 0): ?>
<div class="banner warn">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01M10.3 3.9 2.6 18a1.5 1.5 0 0 0 1.3 2.2h16.2a1.5 1.5 0 0 0 1.3-2.2L13.7 3.9a1.5 1.5 0 0 0-2.6 0Z"/></svg>
  <?= $mismatchCount ?> purchase order<?= $mismatchCount>1?'s':'' ?> <?= $mismatchCount>1?'have':'has' ?> mismatches between the delivery receipt and invoice. Payment is on hold until resolved.
</div>
<?php else: ?>
<div class="banner ok">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 2"/></svg>
  No unresolved mismatches right now.
</div>
<?php endif; ?>

<?php if (!$rows): ?>
  <div class="panel"><div class="empty"><div class="empty-title">Nothing to match yet</div>Purchase orders will show up here once their status is "Delivered".</div></div>
<?php endif; ?>

<?php foreach ($rows as $r):
  $canManage = has_role(['procurement_officer','admin']);
  $status = $r['match_status'] ?? 'pending';
?>
<div class="section-head" style="margin-top:26px;">
  <div>
    <div class="section-title">3-way match — <?= e($r['po_code']) ?></div>
    <div class="section-desc"><?= e($r['supplier_name']) ?> · Qty <?= (int)$r['quantity'] ?> · <?= peso($r['total_amount']) ?></div>
  </div>
  <?php if ($r['payment_approved']): ?>
    <span class="stamp approved">Payment approved</span>
  <?php else: ?>
    <?= stamp($status === 'pending' ? 'pending' : $status) ?>
  <?php endif; ?>
</div>

<div class="match-grid">
  <div class="match-doc ok">
    <div class="match-doc-head"><svg viewBox="0 0 24 24" fill="none" stroke="var(--slate)" stroke-width="2"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/></svg> Purchase Order</div>
    <div class="match-row"><span class="k">PO number</span><span class="v"><?= e($r['po_code']) ?></span></div>
    <div class="match-row"><span class="k">Qty ordered</span><span class="v"><?= (int)$r['quantity'] ?></span></div>
    <div class="match-row"><span class="k">Unit price</span><span class="v"><?= peso($r['unit_price']) ?></span></div>
    <div class="match-row"><span class="k">Total</span><span class="v"><?= peso($r['total_amount']) ?></span></div>
  </div>

  <?php if ($r['receipt_id']): ?>
  <div class="match-doc ok">
    <div class="match-doc-head"><svg viewBox="0 0 24 24" fill="none" stroke="var(--teal)" stroke-width="2"><path d="M3 7l9-4 9 4-9 4-9-4Z"/><path d="M3 7v10l9 4 9-4V7"/></svg> Delivery Receipt</div>
    <div class="match-row"><span class="k">Received</span><span class="v"><?= (int)$r['quantity_received'] ?></span></div>
    <div class="match-row"><span class="k">Condition</span><span class="v"><?= e($r['condition_notes'] ?: '—') ?></span></div>
    <div class="match-row"><span class="k">Date</span><span class="v"><?= e(date('M j', strtotime($r['received_date']))) ?></span></div>
  </div>
  <?php else: ?>
  <div class="match-doc empty">
    <div>
      <div style="margin-bottom:10px;">No delivery receipt logged yet</div>
      <?php if ($canManage): ?><button class="btn btn-ghost btn-sm" onclick="openModal('modal-receipt-<?= $r['po_id'] ?>')">Log receipt</button><?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($r['invoice_id']): ?>
  <div class="match-doc <?= $status==='mismatch' ? 'flag' : 'ok' ?>">
    <div class="match-doc-head"><svg viewBox="0 0 24 24" fill="none" stroke="<?= $status==='mismatch' ? 'var(--red)' : 'var(--green)' ?>" stroke-width="2"><path d="M6 2h9l3 3v17H6z"/><path d="M9 9h6M9 13h6M9 17h3"/></svg> Supplier Invoice</div>
    <div class="match-row <?= $r['quantity_billed'] != $r['quantity'] ? 'diff' : '' ?>"><span class="k">Qty billed</span><span class="v"><?= (int)$r['quantity_billed'] ?></span></div>
    <div class="match-row <?= abs($r['inv_total']-$r['total_amount'])>0.01 ? 'diff' : '' ?>"><span class="k">Total</span><span class="v"><?= peso($r['inv_total']) ?></span></div>
    <div class="match-row"><span class="k">Invoice #</span><span class="v"><?= e($r['invoice_number']) ?></span></div>
  </div>
  <?php else: ?>
  <div class="match-doc empty">
    <div>
      <div style="margin-bottom:10px;">No invoice logged yet</div>
      <?php if ($canManage): ?><button class="btn btn-ghost btn-sm" onclick="openModal('modal-invoice-<?= $r['po_id'] ?>')">Log invoice</button><?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<div class="panel" style="padding:16px 20px; margin-bottom:8px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
  <div style="font-size:13px; color:var(--text-soft);">
    <?php if ($r['payment_approved']): ?>
      Payment has been approved for this order.
    <?php elseif ($status === 'mismatch'): ?>
      <?= e($r['discrepancy_notes'] ?: 'A discrepancy was found between the documents.') ?>
    <?php elseif ($status === 'matched'): ?>
      All three documents agree on quantity and amount. This transaction is validated and ready for payment.
    <?php elseif (!$r['receipt_id'] || !$r['invoice_id']): ?>
      Log both the delivery receipt and the supplier invoice to run the 3-way match.
    <?php else: ?>
      Documents are logged — run the match to validate this transaction.
    <?php endif; ?>
  </div>
  <div style="display:flex; gap:10px;">
    <?php if ($canManage && $r['receipt_id'] && $r['invoice_id'] && !$r['payment_approved']): ?>
      <form action="actions/run_matching.php" method="post" style="display:inline;">
        <input type="hidden" name="po_id" value="<?= $r['po_id'] ?>">
        <button class="btn btn-ghost">Run 3-way match</button>
      </form>
    <?php endif; ?>
    <?php if ($canManage && $status === 'matched' && !$r['payment_approved']): ?>
      <form action="actions/approve_payment.php" method="post" style="display:inline;">
        <input type="hidden" name="po_id" value="<?= $r['po_id'] ?>">
        <button class="btn btn-primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 13l4 4L19 7"/></svg>
          Approve payment
        </button>
      </form>
    <?php endif; ?>
  </div>
</div>

<!-- Log receipt modal -->
<div class="overlay" id="modal-receipt-<?= $r['po_id'] ?>">
  <div class="modal">
    <form action="actions/log_receipt.php" method="post">
      <input type="hidden" name="po_id" value="<?= $r['po_id'] ?>">
      <div class="modal-head"><div class="modal-title">Log goods receipt — <?= e($r['po_code']) ?></div>
        <button type="button" class="modal-close" onclick="closeModal('modal-receipt-<?= $r['po_id'] ?>')">&times;</button></div>
      <div class="modal-body">
        <div class="field-row">
          <div class="field"><label>Quantity received</label><input type="number" name="quantity_received" min="1" value="<?= (int)$r['quantity'] ?>" required></div>
          <div class="field"><label>Date received</label><input type="date" name="received_date" value="<?= date('Y-m-d') ?>" required></div>
        </div>
        <div class="field"><label>Condition notes</label><input type="text" name="condition_notes" placeholder="e.g. Good, minor box damage, etc."></div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-receipt-<?= $r['po_id'] ?>')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save receipt</button>
      </div>
    </form>
  </div>
</div>

<!-- Log invoice modal -->
<div class="overlay" id="modal-invoice-<?= $r['po_id'] ?>">
  <div class="modal">
    <form action="actions/log_invoice.php" method="post">
      <input type="hidden" name="po_id" value="<?= $r['po_id'] ?>">
      <div class="modal-head"><div class="modal-title">Log supplier invoice — <?= e($r['po_code']) ?></div>
        <button type="button" class="modal-close" onclick="closeModal('modal-invoice-<?= $r['po_id'] ?>')">&times;</button></div>
      <div class="modal-body">
        <div class="field-row">
          <div class="field"><label>Invoice number</label><input type="text" name="invoice_number" required></div>
          <div class="field"><label>Invoice date</label><input type="date" name="invoice_date" value="<?= date('Y-m-d') ?>" required></div>
        </div>
        <div class="field-row">
          <div class="field"><label>Quantity billed</label><input type="number" name="quantity_billed" min="1" value="<?= (int)$r['quantity'] ?>" required></div>
          <div class="field"><label>Unit price (PHP)</label><input type="number" step="0.01" name="unit_price" value="<?= $r['unit_price'] ?>" required></div>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-invoice-<?= $r['po_id'] ?>')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save invoice</button>
      </div>
    </form>
  </div>
</div>

<?php endforeach; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
