<?php
// super-admin/subscriptions/index.php
require_once __DIR__ . '/../../includes/functions.php';
requireSuperAdmin();

define('PAGE_TITLE', 'Subscriptions');
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/super-admin/includes/sidebar.php';

try {
    $db = Database::getInstance()->getConnection();
    // Fetch active subscriptions with company and plan names
    $stmt = $db->query("
        SELECT s.*, c.company_name, p.plan_name, p.price, p.currency
        FROM saas_subscriptions s
        JOIN companies c ON s.company_id = c.id
        JOIN saas_plans p ON s.plan_id = p.id
        WHERE s.deleted_at IS NULL
        ORDER BY s.created_at DESC
    ");
    $subscriptions = $stmt->fetchAll();
} catch (PDOException $e) {
    $subscriptions = [];
}
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    <?php require_once BASE_PATH . '/super-admin/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Tenant Subscriptions</h1>
                    <p class="mt-1 text-sm text-slate-500">Manage licenses, renewals, and statuses.</p>
                </div>
                <div class="mt-4 sm:mt-0 flex gap-3">
                    <a href="assign.php" class="btn-primary">+ Assign Plan</a>
                </div>
            </div>

            <!-- KPI Dashboard -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-4 mb-8">
                <div class="card card-body">
                    <p class="text-sm font-medium text-slate-500">Active Plans</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900">0</p>
                </div>
                <div class="card card-body">
                    <p class="text-sm font-medium text-slate-500">Trial Companies</p>
                    <p class="mt-1 text-2xl font-semibold text-amber-600">0</p>
                </div>
                <div class="card card-body">
                    <p class="text-sm font-medium text-slate-500">Expired</p>
                    <p class="mt-1 text-2xl font-semibold text-rose-600">0</p>
                </div>
                <div class="card card-body">
                    <p class="text-sm font-medium text-slate-500">Renewals This Month</p>
                    <p class="mt-1 text-2xl font-semibold text-indigo-600">0</p>
                </div>
            </div>

            <div class="card overflow-x-auto">
                <table class="table-container">
                    <thead class="table-header">
                        <tr>
                            <th>Company</th>
                            <th>Plan</th>
                            <th>License Key</th>
                            <th>Status</th>
                            <th>Expiry Date</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($subscriptions)): ?>
                            <tr><td colspan="6" class="table-cell text-center py-8 text-slate-500 italic">No subscriptions found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($subscriptions as $s): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="table-cell font-medium"><?= esc($s['company_name']) ?></td>
                                <td class="table-cell text-slate-600"><?= esc($s['plan_name']) ?></td>
                                <td class="table-cell"><span class="font-mono text-xs text-slate-500 bg-slate-100 px-2 py-1 rounded"><?= esc($s['license_key']) ?></span></td>
                                <td class="table-cell"><?= getStatusBadge($s['status']) ?></td>
                                <td class="table-cell text-sm text-slate-600"><?= formatDate($s['expiry_date']) ?></td>
                                <td class="table-cell text-right text-sm font-medium">
                                    <a href="view.php?id=<?= $s['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Manage</a>
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
