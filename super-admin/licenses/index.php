<?php
// super-admin/licenses/index.php
require_once __DIR__ . '/../../includes/functions.php';
requireSuperAdmin();

define('PAGE_TITLE', 'License Management');
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/super-admin/includes/sidebar.php';

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("
        SELECT s.id, s.license_key, s.status, s.last_verified_at, c.company_name
        FROM saas_subscriptions s
        JOIN companies c ON s.company_id = c.id
        WHERE s.deleted_at IS NULL
        ORDER BY s.created_at DESC
    ");
    $licenses = $stmt->fetchAll();
} catch (PDOException $e) {
    $licenses = [];
}
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    <?php require_once BASE_PATH . '/super-admin/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">License Keys</h1>
                    <p class="mt-1 text-sm text-slate-500">Track all generated cryptographic keys and verification statuses.</p>
                </div>
            </div>

            <div class="card overflow-x-auto">
                <table class="table-container">
                    <thead class="table-header">
                        <tr>
                            <th>License Key</th>
                            <th>Assigned Company</th>
                            <th>Status</th>
                            <th>Last Verified (Webhook)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if(empty($licenses)): ?>
                            <tr><td colspan="4" class="table-cell text-center py-8 text-slate-500 italic">No licenses generated yet.</td></tr>
                        <?php else: ?>
                            <?php foreach($licenses as $l): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="table-cell">
                                        <code class="text-indigo-600 font-bold bg-indigo-50 px-2 py-1 rounded select-all"><?= esc($l['license_key']) ?></code>
                                    </td>
                                    <td class="table-cell font-medium"><?= esc($l['company_name']) ?></td>
                                    <td class="table-cell"><?= getStatusBadge($l['status']) ?></td>
                                    <td class="table-cell text-sm text-slate-500">
                                        <?= $l['last_verified_at'] ? formatDate($l['last_verified_at'], 'Y-m-d H:i') : 'Never' ?>
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
