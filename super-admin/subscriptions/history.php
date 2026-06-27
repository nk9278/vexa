<?php
// super-admin/subscriptions/history.php
require_once __DIR__ . '/../../includes/functions.php';
requireSuperAdmin();

define('PAGE_TITLE', 'Subscription History');
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/super-admin/includes/sidebar.php';

try {
    $db = Database::getInstance()->getConnection();
    // Using activity logs to infer history for demonstration
    $stmt = $db->query("
        SELECT a.*, c.company_name
        FROM activity_logs a
        LEFT JOIN companies c ON a.company_id = c.id
        WHERE a.entity_type = 'saas_subscription'
        ORDER BY a.created_at DESC
    ");
    $history = $stmt->fetchAll();
} catch (PDOException $e) {
    $history = [];
}
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    <?php require_once BASE_PATH . '/super-admin/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Subscription History</h1>
                    <p class="mt-1 text-sm text-slate-500">Audit trail of assignments, renewals, and revocations.</p>
                </div>
            </div>

            <div class="card overflow-x-auto">
                <table class="table-container">
                    <thead class="table-header">
                        <tr>
                            <th>Date</th>
                            <th>Company</th>
                            <th>Action</th>
                            <th>Performed By</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if(empty($history)): ?>
                            <tr><td colspan="5" class="table-cell text-center py-8 text-slate-500 italic">No history found.</td></tr>
                        <?php else: ?>
                            <?php foreach($history as $h): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="table-cell text-sm"><?= formatDate($h['created_at'], 'M d, Y H:i') ?></td>
                                    <td class="table-cell font-medium"><?= esc($h['company_name'] ?? 'N/A') ?></td>
                                    <td class="table-cell">
                                        <span class="badge badge-info"><?= esc($h['action']) ?></span>
                                    </td>
                                    <td class="table-cell text-sm text-slate-500">User ID: <?= $h['user_id'] ?></td>
                                    <td class="table-cell text-sm text-slate-400 font-mono text-xs">
                                        <?= esc(substr($h['new_payload'], 0, 30)) ?>...
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
