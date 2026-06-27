<?php
// super-admin/subscriptions/view.php
require_once __DIR__ . '/../../includes/functions.php';
requireSuperAdmin();

$id = $_GET['id'] ?? 0;

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT s.*, c.company_name, c.company_code, p.plan_name, p.limit_employees, p.limit_storage_mb, p.limit_projects
        FROM saas_subscriptions s
        JOIN companies c ON s.company_id = c.id
        JOIN saas_plans p ON s.plan_id = p.id
        WHERE s.id = ? AND s.deleted_at IS NULL
    ");
    $stmt->execute([$id]);
    $sub = $stmt->fetch();
} catch (PDOException $e) {
    $sub = null;
}

if (!$sub) {
    redirect(BASE_URL . 'errors/404.php');
}

define('PAGE_TITLE', 'Manage Subscription');
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/super-admin/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    <?php require_once BASE_PATH . '/super-admin/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center">
                    <a href="index.php" class="mr-4 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900"><?= esc($sub['company_name']) ?></h1>
                        <p class="mt-1 text-sm text-slate-500">Plan: <span class="font-medium text-slate-700"><?= esc($sub['plan_name']) ?></span> | <?= getStatusBadge($sub['status']) ?></p>
                    </div>
                </div>
                <div class="mt-4 sm:mt-0 flex gap-3">
                    <a href="history.php" class="btn-secondary">View History</a>
                    <a href="renew.php?id=<?= $sub['id'] ?>" class="btn-secondary text-indigo-600 border-indigo-200 hover:bg-indigo-50">Renew License</a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="card">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-lg font-medium text-slate-900">License Information</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">License Key</p>
                            <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded p-3">
                                <code class="text-indigo-600 font-bold"><?= esc($sub['license_key']) ?></code>
                                <button class="text-slate-400 hover:text-indigo-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg></button>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Start Date</p>
                                <p class="text-sm font-medium text-slate-900"><?= formatDate($sub['start_date']) ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Expiry Date</p>
                                <p class="text-sm font-medium text-rose-600"><?= formatDate($sub['expiry_date']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-lg font-medium text-slate-900">Usage Monitor</h2>
                    </div>
                    <div class="card-body space-y-6">

                        <!-- Employees Progress -->
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-slate-700">Employees</span>
                                <span class="text-slate-600">3 / <?= $sub['limit_employees'] ?: '∞' ?></span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-2">
                                <div class="bg-indigo-500 h-2 rounded-full" style="width: <?= $sub['limit_employees'] > 0 ? (3/$sub['limit_employees'])*100 : 5 ?>%"></div>
                            </div>
                        </div>

                        <!-- Projects Progress -->
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-slate-700">Projects</span>
                                <span class="text-slate-600">12 / <?= $sub['limit_projects'] ?: '∞' ?></span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-2">
                                <div class="bg-emerald-500 h-2 rounded-full" style="width: <?= $sub['limit_projects'] > 0 ? (12/$sub['limit_projects'])*100 : 15 ?>%"></div>
                            </div>
                        </div>

                        <!-- Storage Progress -->
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-slate-700">Storage</span>
                                <span class="text-slate-600">450MB / <?= $sub['limit_storage_mb'] ?: '∞' ?>MB</span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-2">
                                <div class="bg-amber-500 h-2 rounded-full" style="width: <?= $sub['limit_storage_mb'] > 0 ? (450/$sub['limit_storage_mb'])*100 : 10 ?>%"></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
