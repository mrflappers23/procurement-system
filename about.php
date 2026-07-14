<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/functions.php';
require_login();
$me = current_user($pdo);

$active = 'about';
$pageEyebrow = 'Info';
$pageTitle = 'About the Developers';
$mainBtn = null;
require __DIR__ . '/includes/header.php';
?>

<div class="section-head">
  <div>
    <div class="section-title" style="font-size:22px;">About the developers</div>
    <div class="section-desc">The team behind Ledgerway, a Procurement Management System built with PHP, MySQL, and a ledger-inspired interface.</div>
  </div>
</div>

<div class="panel" style="padding:22px 24px; margin-bottom:22px; display:flex; gap:20px; align-items:flex-start; flex-wrap:wrap;">
  <div class="supplier-logo" style="width:52px;height:52px;font-size:19px;background:linear-gradient(135deg, var(--brass), #E0A94A); flex-shrink:0;">L</div>
  <div>
    <div class="section-title" style="font-size:16px; margin-bottom:6px;">The project</div>
    <div style="font-size:13.5px; color:var(--text-soft); line-height:1.6; max-width:640px;">
      Ledgerway reimagines procurement as a paper trail you can trust — every requisition, order, and invoice carries a visible status from submission to payment. The system covers four core workflows: requisitions and approval, supplier management, purchase order tracking, and 3-way invoice matching, backed by a MySQL database and a PHP/PDO backend.
    </div>
  </div>
</div>

<div class="section-title" style="font-size:15px; margin-bottom:12px;">Team</div>
<div class="supplier-grid" style="margin-bottom:24px;">
  <div class="supplier-card">
    <div class="supplier-top">
      <div>
        <div class="supplier-logo" style="background:linear-gradient(135deg,#5B5FA6,#8286D6)">AR</div>
        <div class="supplier-name" style="margin-top:8px;">Alex Reyes</div>
        <div class="supplier-cat">Product &amp; UX Design</div>
      </div>
    </div>
    <div style="font-size:12.5px; color:var(--text-soft); line-height:1.5; margin-top:10px;">
      Led the workflow mapping for requisitions and approvals, and designed the stamp-based status system.
    </div>
  </div>

  <div class="supplier-card">
    <div class="supplier-top">
      <div>
        <div class="supplier-logo" style="background:linear-gradient(135deg,#2E6F6E,#57A6A4)">JT</div>
        <div class="supplier-name" style="margin-top:8px;">Jamie Tan</div>
        <div class="supplier-cat">Backend Engineering</div>
      </div>
    </div>
    <div style="font-size:12.5px; color:var(--text-soft); line-height:1.5; margin-top:10px;">
      Built the PHP/MySQL backend — authentication, the approval workflow, and the 3-way matching engine.
    </div>
  </div>

  <div class="supplier-card">
    <div class="supplier-top">
      <div>
        <div class="supplier-logo" style="background:linear-gradient(135deg,#8A5C15,#C9973F)">SD</div>
        <div class="supplier-name" style="margin-top:8px;">Sam Dizon</div>
        <div class="supplier-cat">Visual &amp; Systems Design</div>
      </div>
    </div>
    <div style="font-size:12.5px; color:var(--text-soft); line-height:1.5; margin-top:10px;">
      Defined the ink-and-brass palette, typography pairing, and the 3-way match visualization.
    </div>
  </div>
</div>

<div class="panel" style="padding:18px 22px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
  <div style="font-size:12.5px; color:var(--text-faint);">Ledgerway · Procurement Management System · v1.0</div>
  <div style="font-size:12.5px; color:var(--text-faint);">Built with PHP, MySQL &amp; XAMPP</div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
