<?php
// super-admin/companies/view.php
require_once __DIR__ . '/../../includes/functions.php';
requireSuperAdmin();

define('PAGE_TITLE', 'Company Profile');
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/super-admin/includes/sidebar.php';

$id = $_GET['id'] ?? 0;

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM companies WHERE id = ?");
    $stmt->execute([$id]);
    $company = $stmt->fetch();
} catch (PDOException $e) {
    $company = null;
}

if (!$company) {
    redirect(BASE_URL . 'errors/404.php');
}
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    <?php require_once BASE_PATH . '/super-admin/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center">
                    <a href="index.php" class="mr-4 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </a>
                    <div class="flex-shrink-0 h-12 w-12 bg-indigo-100 rounded-lg flex items-center justify-center font-bold text-indigo-600 text-xl mr-4 border border-indigo-200">
                        <?= substr(esc($company['company_name']), 0, 1) ?>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900"><?= esc($company['company_name']) ?></h1>
                        <p class="mt-1 text-sm text-slate-500">Code: <?= esc($company['company_code']) ?> | <?= getStatusBadge($company['status']) ?></p>
                    </div>
                </div>
                <div class="mt-4 sm:mt-0 flex gap-3">
                    <button type="button" class="btn-secondary text-rose-600 border-rose-200 hover:bg-rose-50" onclick="performAction('delete', <?= $company['id'] ?>)">Delete</button>
                    <?php if($company['status'] === 'active'): ?>
                        <button type="button" class="btn-secondary" onclick="performAction('suspend', <?= $company['id'] ?>)">Suspend</button>
                    <?php else: ?>
                        <button type="button" class="btn-secondary text-emerald-600 border-emerald-200 hover:bg-emerald-50" onclick="performAction('activate', <?= $company['id'] ?>)">Activate</button>
                    <?php endif; ?>
                    <a href="edit.php?id=<?= $company['id'] ?>" class="btn-primary">Edit Profile</a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="card">
                        <div class="border-b border-slate-100 px-6 py-4">
                            <h2 class="text-lg font-medium text-slate-900">Owner Information</h2>
                        </div>
                        <div class="card-body">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                                <div>
                                    <dt class="text-sm font-medium text-slate-500">Full Name</dt>
                                    <dd class="mt-1 text-sm text-slate-900"><?= esc($company['owner_name']) ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-slate-500">Email Address</dt>
                                    <dd class="mt-1 text-sm text-slate-900"><?= esc($company['owner_email']) ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-slate-500">Mobile Phone</dt>
                                    <dd class="mt-1 text-sm text-slate-900"><?= esc($company['owner_mobile']) ?: '-' ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-slate-500">Registration Date</dt>
                                    <dd class="mt-1 text-sm text-slate-900"><?= formatDate($company['created_at']) ?></dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="card">
                        <div class="border-b border-slate-100 px-6 py-4">
                            <h2 class="text-lg font-medium text-slate-900">Activity Timeline</h2>
                        </div>
                        <div class="card-body">
                            <!-- Reusable Timeline Component -->
                            <div class="flow-root">
                                <ul role="list" class="-mb-8">
                                    <li>
                                        <div class="relative pb-8">
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-slate-200" aria-hidden="true"></span>
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-emerald-500 flex items-center justify-center ring-8 ring-white">
                                                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-slate-500">Company account created by <a href="#" class="font-medium text-slate-900">Super Admin</a></p>
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-slate-500">
                                                        <time><?= formatDate($company['created_at']) ?></time>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <div class="card">
                        <div class="border-b border-slate-100 px-6 py-4">
                            <h2 class="text-lg font-medium text-slate-900">Subscription Status</h2>
                        </div>
                        <div class="card-body">
                            <div class="flex items-center justify-center h-24 bg-slate-50 rounded-lg border border-slate-200 border-dashed mb-4">
                                <span class="text-slate-400 text-sm">Subscription Module Pending</span>
                            </div>
                            <button class="btn-secondary w-full">Manage Subscription</button>
                        </div>
                    </div>

                    <div class="card bg-amber-50 border-amber-200">
                        <div class="border-b border-amber-200 px-6 py-4">
                            <h2 class="text-lg font-medium text-amber-900">Internal Notes</h2>
                        </div>
                        <div class="card-body">
                            <p class="text-sm text-amber-800 italic">
                                <?= !empty($company['remarks']) ? nl2br(esc($company['remarks'])) : 'No internal notes found for this company.' ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
<script>
function performAction(action, id) {
    if(confirm(`Are you sure you want to ${action} this company?`)) {
        $.post('<?= BASE_URL ?>api/super-admin/companies/action.php', {
            action: action,
            ids: [id]
        }, function(response) {
            if(response.status === 'success') {
                showToast('success', response.message);
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showToast('error', response.message);
            }
        });
    }
}
</script>
