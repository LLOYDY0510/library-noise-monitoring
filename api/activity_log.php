<?php
// ============================================================
// api/activity_log.php — Recent activity JSON feed
// Used by the Admin dashboard live widget
// UPDATED: page field now returns full URL path
// ============================================================
require_once __DIR__ . '/../includes/config.php';
requireLogin();
requireRole('Administrator');

header('Content-Type: application/json');
header('Cache-Control: no-store');

$limit = max(1, min(100, (int)($_GET['limit'] ?? 20)));
$since = $_GET['since'] ?? null;

// Map bare page names → full URL paths for display
function pageToUrl(string $page): string {
    $base = BASE_URL;
    $map  = [
        'dashboard'     => $base . '/dashboard.php',
        'zones'         => $base . '/zones.php',
        'alerts'        => $base . '/alerts.php',
        'reports'       => $base . '/reports.php',
        'users'         => $base . '/users.php',
        'activity_log'  => $base . '/activity_log.php',
        'index'         => $base . '/index.php',
        'logout'        => $base . '/php/logout.php',
        'simulate_noise'=> $base . '/php/simulate_noise.php',
        'trigger_sim'   => $base . '/api/trigger_sim.php',
    ];
    return $map[$page] ?? ($page ? $base . '/' . $page . '.php' : '—');
}

try {
    $db = getDB();

    if ($since) {
        $stmt = $db->prepare(
            'SELECT id, user_name, user_role, action, detail, page, ip, created_at
             FROM activity_logs
             WHERE created_at > ?
             ORDER BY created_at DESC
             LIMIT ?'
        );
        $stmt->execute([$since, $limit]);
    } else {
        $stmt = $db->prepare(
            'SELECT id, user_name, user_role, action, detail, page, ip, created_at
             FROM activity_logs
             ORDER BY created_at DESC
             LIMIT ?'
        );
        $stmt->execute([$limit]);
    }

    $logs = $stmt->fetchAll();

    // Enrich each log with full page URL
    foreach ($logs as &$log) {
        $log['page_url'] = pageToUrl($log['page'] ?? '');
    }
    unset($log);

    $stats = $db->query(
        'SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS today,
            COUNT(DISTINCT user_id) AS unique_users,
            SUM(CASE WHEN action LIKE "%Login%" THEN 1 ELSE 0 END) AS logins
         FROM activity_logs'
    )->fetch();

    echo json_encode([
        'logs'  => $logs,
        'stats' => $stats,
        'ts'    => date('c'),
    ]);
} catch (Exception $e) {
    echo json_encode(['logs' => [], 'stats' => [], 'ts' => date('c')]);
}
