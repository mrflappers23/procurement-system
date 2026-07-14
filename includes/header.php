<?php
/**
 * Shared header: opens <html>, renders sidebar + topbar.
 * Expects $pdo, and the calling page to set:
 *   $active        -> one of: dashboard, requisitions, suppliers, orders, matching, about
 *   $pageEyebrow   -> small label above the title
 *   $pageTitle     -> main page title
 *   $mainBtn       -> ['label' => 'New Requisition', 'modal' => 'modal-new-req'] or null to hide
 */
require_login();
$me = current_user($pdo);
$flash = flash_get();

$active      = $active ?? 'dashboard';
$pageEyebrow = $pageEyebrow ?? 'Overview';
$pageTitle   = $pageTitle ?? 'Ledgerway';
$mainBtn     = $mainBtn ?? null;

function nav_count($pdo, $sql) {
    try { return (int)$pdo->query($sql)->fetchColumn(); } catch (\Throwable $e) { return 0; }
}
$countReq   = nav_count($pdo, "SELECT COUNT(*) FROM requisitions");
$countSup   = nav_count($pdo, "SELECT COUNT(*) FROM suppliers");
$countPO    = nav_count($pdo, "SELECT COUNT(*) FROM purchase_orders");
$countMatch = nav_count($pdo, "SELECT COUNT(*) FROM invoice_matching WHERE match_status != 'matched' OR payment_approved = 0");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ledgerway · <?= e($pageTitle) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">

  <aside class="sidebar">
    <div class="brand">
      <div class="brand-mark">L</div>
      <div>
        <div class="brand-name">Ledgerway</div>
        <div class="brand-sub">Procurement</div>
      </div>
    </div>

    <nav class="nav">
      <div class="nav-label">Overview</div>
      <a class="nav-item <?= $active==='dashboard'?'active':'' ?>" href="index.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
        Dashboard
      </a>

      <div class="nav-label">Procure-to-Pay</div>
      <a class="nav-item <?= $active==='requisitions'?'active':'' ?>" href="requisitions.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 3h8a1 1 0 0 1 1 1v16l-3-2-2 2-2-2-2 2-2-2-1 .7V4a1 1 0 0 1 1-1z"/><path d="M9 8h6M9 12h6M9 16h3"/></svg>
        Requisitions <span class="nav-count"><?= $countReq ?></span>
      </a>
      <a class="nav-item <?= $active==='suppliers'?'active':'' ?>" href="suppliers.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 20c.7-3.4 3-5.3 5.5-5.3S13.8 16.6 14.5 20"/><circle cx="17.5" cy="8.5" r="2.5"/><path d="M15.8 14.3c2.4.2 4.1 2 4.7 5"/></svg>
        Suppliers <span class="nav-count"><?= $countSup ?></span>
      </a>
      <a class="nav-item <?= $active==='orders'?'active':'' ?>" href="purchase_orders.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="15" rx="2"/><path d="M8 6V4.5A2.5 2.5 0 0 1 10.5 2h3A2.5 2.5 0 0 1 16 4.5V6"/><path d="M3 11h18"/></svg>
        Purchase Orders <span class="nav-count"><?= $countPO ?></span>
      </a>
      <a class="nav-item <?= $active==='matching'?'active':'' ?>" href="goods_receipt.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 3v6l-2 3v9h10v-9l-2-3V3"/><path d="M9 3h6M6 21h12"/></svg>
        Goods Receipt &amp; Matching <span class="nav-count"><?= $countMatch ?></span>
      </a>

      <div class="nav-label">Info</div>
      <a class="nav-item <?= $active==='about'?'active':'' ?>" href="about.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v5h1"/></svg>
        About the Developers
      </a>
    </nav>

    <div class="sidebar-foot">
      <div class="avatar"><?= e(strtoupper(substr($me['name'] ?? '?', 0, 1))) ?></div>
      <div>
        <div class="who-name"><?= e($me['name'] ?? 'Guest') ?></div>
        <div class="who-role"><?= e(ucwords(str_replace('_',' ', $me['role'] ?? ''))) ?></div>
      </div>
      <a class="logout-link" href="actions/logout.php">Log out</a>
    </div>
  </aside>

  <div class="main">
    <div class="topbar">
      <div>
        <div class="topbar-eyebrow"><?= e($pageEyebrow) ?></div>
        <div class="topbar-title"><?= e($pageTitle) ?></div>
      </div>
      <div class="topbar-actions">
        <?php if ($mainBtn): ?>
        <button class="btn btn-primary" onclick="openModal('<?= e($mainBtn['modal']) ?>')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
          <?= e($mainBtn['label']) ?>
        </button>
        <?php endif; ?>
      </div>
    </div>

    <section class="view">
      <?php if ($flash): ?>
        <div class="banner <?= $flash['type']==='error' ? 'warn' : 'ok' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 2"/></svg>
          <?= e($flash['message']) ?>
        </div>
      <?php endif; ?>
