<?php
/**
 * Session + authentication helpers.
 * Include this AFTER config/db.php on every protected page.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function current_user($pdo) {
    if (!isset($_SESSION['user_id'])) return null;
    static $cached = null;
    if ($cached) return $cached;
    $stmt = $pdo->prepare("SELECT u.*, d.name AS department_name FROM users u
                            LEFT JOIN departments d ON d.department_id = u.department_id
                            WHERE u.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cached = $stmt->fetch();
    return $cached;
}

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Restrict a page/action to a set of roles. 'admin' always passes.
 * Usage: require_role(['manager','admin']);
 */
function require_role(array $roles) {
    require_login();
    $role = $_SESSION['role'] ?? null;
    if ($role !== 'admin' && !in_array($role, $roles, true)) {
        http_response_code(403);
        die('<div style="font-family:sans-serif;padding:40px;">
              <h2>403 — Not authorized</h2>
              <p>Your role ("' . htmlspecialchars($role ?? 'guest') . '") cannot perform this action.</p>
              <p><a href="index.php">Back to dashboard</a></p>
            </div>');
    }
}

function has_role($roles) {
    $role = $_SESSION['role'] ?? null;
    if ($role === 'admin') return true;
    return in_array($role, (array)$roles, true);
}

function flash_set($message, $type = 'ok') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function flash_get() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}
