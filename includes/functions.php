<?php
/**
 * Shared helper functions used across pages and action scripts.
 */

/** Generate the next sequential code for a table, e.g. REQ-1043 / PO-3313 */
function generate_code(PDO $pdo, string $table, string $column, string $prefix): string {
    $sql = "SELECT MAX(CAST(SUBSTRING_INDEX($column, '-', -1) AS UNSIGNED)) AS max_num FROM $table";
    $row = $pdo->query($sql)->fetch();
    $next = (int)($row['max_num'] ?? 1000) + 1;
    return $prefix . '-' . $next;
}

function peso(float $amount): string {
    return '₱' . number_format($amount, 2);
}

function log_activity(PDO $pdo, ?int $userId, string $action, string $description): void {
    $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action, description) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $action, $description]);
}

function redirect(string $path): void {
    header("Location: $path");
    exit;
}

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Renders a status "stamp" badge — the site's signature visual element */
function stamp(string $status): string {
    $map = [
        'pending'   => 'Pending',
        'approved'  => 'Approved',
        'rejected'  => 'Rejected',
        'sent'      => 'Sent',
        'confirmed' => 'Confirmed',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        'matched'   => 'Matched',
        'mismatch'  => 'Mismatch',
    ];
    $label = $map[$status] ?? ucfirst($status);
    return '<span class="stamp ' . e($status) . '">' . e($label) . '</span>';
}

function stars(float $percent): string {
    // convert a 0-100 average score to a 5-star display
    $count = max(0, min(5, round($percent / 20)));
    $html = '<span class="stars">';
    for ($i = 1; $i <= 5; $i++) {
        $cls = $i <= $count ? 'star-fill' : 'star-empty';
        $html .= '<svg class="' . $cls . '" viewBox="0 0 20 20"><path d="M10 1l2.6 5.8 6.4.6-4.8 4.3 1.4 6.3-5.6-3.4-5.6 3.4 1.4-6.3L1 7.4l6.4-.6z"/></svg>';
    }
    $html .= '</span>';
    return $html;
}
