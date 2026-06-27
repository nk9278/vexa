<?php
// super-admin/plans/index.php
require_once __DIR__ . '/../../includes/functions.php';
requireSuperAdmin();

define('PAGE_TITLE', 'SaaS Plans');
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/super-admin/includes/sidebar.php';

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT * FROM saas_plans WHERE deleted_at IS NULL ORDER BY sort_order ASC, created_at DESC");
    $plans = $stmt->fetchAll();
} catch (PDOException $e) {
    $plans = [];
}
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    <?php require_once BASE_PATH . '/super-admin/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">SaaS Plans</h1>
                    <p class="mt-1 text-sm text-slate-500">Manage billing tiers and feature limits.</p>
                </div>
                <div class="mt-4 sm:mt-0 flex gap-3">
                    <a href="create.php" class="btn-primary">+ Create Plan</a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if(empty($plans)): ?>
                    <div class="col-span-full text-center py-12 bg-white rounded-xl border border-slate-100 shadow-sm">
                        <p class="text-slate-500 italic">No plans created yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($plans as $plan): ?>
                        <div class="card flex flex-col">
                            <div class="p-6 border-b border-slate-100 flex-1">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h2 class="text-xl font-bold text-slate-900"><?= esc($plan['plan_name']) ?></h2>
                                        <span class="text-xs font-mono text-slate-400"><?= esc($plan['plan_code']) ?></span>
                                    </div>
                                    <?= getStatusBadge($plan['status']) ?>
                                </div>
                                <p class="text-3xl font-extrabold text-indigo-600 mb-1">
                                    <?= esc($plan['currency']) ?> <?= number_format($plan['price'], 2) ?>
                                    <span class="text-sm font-normal text-slate-500">/ <?= esc($plan['plan_type']) ?></span>
                                </p>
                                <p class="text-sm text-slate-600 mb-6"><?= esc($plan['description']) ?></p>

                                <h3 class="text-xs font-semibold text-slate-900 uppercase tracking-wider mb-3">Limits</h3>
                                <ul class="space-y-2 text-sm text-slate-600">
                                    <li class="flex items-center"><svg class="w-4 h-4 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> <?= $plan['limit_employees'] ?> Employees</li>
                                    <li class="flex items-center"><svg class="w-4 h-4 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> <?= $plan['limit_projects'] ?> Projects</li>
                                    <li class="flex items-center"><svg class="w-4 h-4 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> <?= $plan['limit_storage_mb'] ?> MB Storage</li>
                                </ul>
                            </div>
                            <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex justify-between items-center">
                                <span class="text-xs text-slate-500"><?= $plan['feature_gdrive'] ? 'Google Drive Enabled' : '' ?></span>
                                <a href="edit.php?id=<?= $plan['id'] ?>" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Edit Plan</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
