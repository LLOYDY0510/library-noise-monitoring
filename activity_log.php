<?php
// ============================================================
// activity_log.php — Activity Log (Admin only)
// Table powered by DataTables — handles search, filter,
// pagination, and column sorting entirely client-side.
// ============================================================
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();
requireRole('Administrator');

$db        = getDB();
$pageTitle = 'Activity Log';
$pageSubtitle = 'All user actions across the system';
$user      = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'clear_logs') {
    logActivity('Cleared Activity Logs', 'Admin cleared all activity log history', 'activity_log');
    $db->exec('TRUNCATE TABLE activity_logs');
    logActivity('Cleared Activity Logs', 'Activity log was cleared — this is the first new entry', 'activity_log');
    header('Location: ' . BASE_URL . '/activity_log.php');
    exit;
}

$logs      = $db->query('SELECT * FROM activity_logs ORDER BY created_at DESC')->fetchAll();
$totalRows = count($logs);

function pageToUrl(string $page): string {
    $base = BASE_URL;
    $map  = [
        'dashboard'      => $base . '/dashboard.php',
        'zones'          => $base . '/zones.php',
        'alerts'         => $base . '/alerts.php',
        'reports'        => $base . '/reports.php',
        'users'          => $base . '/users.php',
        'activity_log'   => $base . '/activity_log.php',
        'index'          => $base . '/index.php',
        'logout'         => $base . '/php/logout.php',
        'simulate_noise' => $base . '/php/simulate_noise.php',
    ];
    return $map[$page] ?? ($page ? $base . '/' . $page . '.php' : '');
}

logActivity('Viewed Activity Log', 'Opened activity log page', 'activity_log');

$extraStyles = '
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.3/css/dataTables.dataTables.min.css">
<style>
div.dataTables_wrapper { font-family: var(--font-body); font-size: 13.5px; }
div.dataTables_length, div.dataTables_filter { margin-bottom: 14px; }
div.dataTables_length label, div.dataTables_filter label {
    font-size: 12.5px; color: var(--gray-500);
    display: flex; align-items: center; gap: 8px;
}
div.dataTables_filter input, div.dataTables_length select {
    border: 1px solid var(--gray-200); border-radius: var(--radius-sm);
    padding: 6px 10px; font-family: var(--font-body); font-size: 13px;
    color: var(--gray-800); background: #fff; outline: none;
    transition: border-color .12s, box-shadow .12s;
}
div.dataTables_filter input:focus, div.dataTables_length select:focus {
    border-color: var(--blue-400); box-shadow: 0 0 0 3px rgba(59,130,246,.12);
}
div.dataTables_info { font-size: 12px; color: var(--gray-400); padding-top: 10px; }
div.dataTables_paginate { padding-top: 10px; }
div.dataTables_paginate .paginate_button {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 32px; height: 32px; padding: 0 8px; margin: 0 2px;
    border-radius: var(--radius-sm) !important; font-size: 12px; font-weight: 600;
    border: 1px solid var(--gray-200) !important;
    color: var(--gray-600) !important; background: #fff !important;
    cursor: pointer; box-shadow: none !important; transition: all .12s;
}
div.dataTables_paginate .paginate_button:hover {
    background: var(--blue-50) !important; border-color: var(--blue-200) !important;
    color: var(--blue-600) !important;
}
div.dataTables_paginate .paginate_button.current,
div.dataTables_paginate .paginate_button.current:hover {
    background: var(--blue-600) !important; border-color: var(--blue-600) !important; color: #fff !important;
}
div.dataTables_paginate .paginate_button.disabled,
div.dataTables_paginate .paginate_button.disabled:hover { opacity: .4; cursor: default; }
table.dataTable thead th {
    border-bottom: 1px solid var(--gray-200) !important;
    background: var(--gray-50) !important; color: var(--gray-400) !important;
    font-size: 10.5px !important; font-weight: 700 !important;
    text-transform: uppercase !important; letter-spacing: .7px !important;
    padding: 10px 14px !important; white-space: nowrap;
}
table.dataTable tbody td {
    padding: 11px 14px !important; border-bottom: 1px solid var(--gray-100) !important;
    vertical-align: middle;
}
table.dataTable tbody tr:hover td { background: var(--blue-50) !important; }
table.dataTable tbody tr:last-child td { border-bottom: none !important; }
div.dataTables_wrapper div.dataTables_length,
div.dataTables_wrapper div.dataTables_filter { display: inline-block; }
div.dt-layout-row { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; }
</style>';

$extraScripts = '
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.3/js/dataTables.min.js"></script>
<script src="' . BASE_URL . '/js/activity_log.js"></script>';

include __DIR__ . '/includes/layout.php';
?>

<div class="page-header">
    <div class="page-header-left">
        <h1>Activity Log</h1>
        <p>All user actions — <strong><?= number_format($totalRows) ?></strong> total entries</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= BASE_URL ?>/api/export_logs.php" class="btn btn-outline btn-sm">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Export CSV
        </a>
        <button class="btn btn-danger btn-sm" onclick="openModal('clearLogsModal')">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
            </svg>
            Clear Logs
        </button>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table id="activityTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Action</th>
                    <th>Detail</th>
                    <th>Page URL</th>
                    <th>IP Address</th>
                    <th>Browser</th>
                    <th>Date &amp; Time</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($logs as $i => $log):
                $actionClass = match(true) {
                    str_contains($log['action'], 'Delete') || str_contains($log['action'], 'Clear')   => 'badge-crit',
                    str_contains($log['action'], 'Add')    || str_contains($log['action'], 'Create')  => 'badge-safe',
                    str_contains($log['action'], 'Register')                                          => 'badge-safe',
                    str_contains($log['action'], 'Override') || str_contains($log['action'], 'Edit')  => 'badge-warn',
                    str_contains($log['action'], 'Login')                                             => 'badge-safe',
                    str_contains($log['action'], 'Logout')                                            => 'badge-gray',
                    default => 'badge-gray'
                };
                $roleCls = match($log['user_role']) {
                    'Administrator'   => 'role-admin',
                    'Library Manager' => 'role-manager',
                    default           => 'role-staff'
                };
                $fullUrl = pageToUrl($log['page'] ?? '');
            ?>
            <tr>
                <td class="td-rownum"><?= $i + 1 ?></td>
                <td>
                    <div class="user-cell">
                        <div class="user-avatar user-avatar-sm">
                            <?= strtoupper(substr($log['user_name'], 0, 1)) ?>
                        </div>
                        <span class="td-bold"><?= htmlspecialchars($log['user_name']) ?></span>
                    </div>
                </td>
                <td><span class="role-badge <?= $roleCls ?>"><?= htmlspecialchars($log['user_role']) ?></span></td>
                <td><span class="badge <?= $actionClass ?>"><?= htmlspecialchars($log['action']) ?></span></td>
                <td class="td-detail"><?= htmlspecialchars($log['detail'] ?: '—') ?></td>
                <td>
                    <?php if ($fullUrl): ?>
                    <a class="page-url-chip" href="<?= htmlspecialchars($fullUrl) ?>" target="_blank">
                        <?= htmlspecialchars($fullUrl) ?>
                    </a>
                    <?php else: ?>
                    <span class="page-url-chip page-url-empty">—</span>
                    <?php endif; ?>
                </td>
                <td class="td-mono"><?= htmlspecialchars($log['ip'] ?: '—') ?></td>
                <td>
                    <?php if (!empty($log['browser'])): ?>
                    <span class="page-chip"><?= htmlspecialchars($log['browser']) ?></span>
                    <?php else: ?>
                    <span class="td-sub">—</span>
                    <?php endif; ?>
                </td>
                <td data-order="<?= htmlspecialchars($log['created_at']) ?>">
                    <?= date('M d, Y', strtotime($log['created_at'])) ?>
                    <br><span class="td-sub"><?= date('h:i:s A', strtotime($log['created_at'])) ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Clear Logs Confirm Modal -->
<div class="modal-overlay" id="clearLogsModal">
    <div class="modal modal-sm">
        <div class="modal-header">
            <div class="modal-title">Clear Activity Logs</div>
            <button class="modal-close" onclick="closeModal('clearLogsModal')">✕</button>
        </div>
        <div class="delete-confirm-body">
            <div class="delete-confirm-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                    <path d="M10 11v6"/><path d="M14 11v6"/>
                </svg>
            </div>
            <div class="delete-confirm-title">Clear all activity logs?</div>
            <div class="delete-confirm-sub">
                All <strong><?= number_format($totalRows) ?></strong> entries will be permanently deleted.
                A new entry recording this action will be created. This cannot be undone.
            </div>
        </div>
        <form method="POST" id="clearLogsForm">
            <input type="hidden" name="action" value="clear_logs">
        </form>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('clearLogsModal')">Cancel</button>
            <button type="button" class="btn btn-danger" onclick="document.getElementById('clearLogsForm').submit()">Yes, Clear All</button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/layout_footer.php'; ?>
