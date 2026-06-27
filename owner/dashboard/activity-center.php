<?php
// owner/dashboard/activity-center.php
require_once __DIR__ . '/../../includes/functions.php';
requireOwner();

define('PAGE_TITLE', 'Activity Center');
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/owner/includes/sidebar.php';

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM activity_logs WHERE company_id = ? ORDER BY created_at DESC LIMIT 50");
    $stmt->execute([$_SESSION['company_id']]);
    $logs = $stmt->fetchAll();
} catch (PDOException $e) {
    $logs = [];
}
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    <?php require_once BASE_PATH . '/owner/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-8">
                <h1 class="text-2xl font-bold text-slate-900">Activity Center</h1>
                <p class="mt-1 text-sm text-slate-500">Timeline of all events across your workspace.</p>
            </div>

            <div class="card card-body">
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        <?php if (empty($logs)): ?>
                            <p class="text-sm text-slate-500 italic">No activity logged yet.</p>
                        <?php else: ?>
                            <?php foreach ($logs as $index => $log): ?>
                                <li>
                                    <div class="relative pb-8">
                                        <?php if ($index !== count($logs) - 1): ?>
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-slate-200" aria-hidden="true"></span>
                                        <?php endif; ?>
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center ring-8 ring-white">
                                                    <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-slate-900 font-medium"><?= ucwords(str_replace('_', ' ', esc($log['action']))) ?></p>
                                                    <p class="text-xs text-slate-500 mt-1">Entity: <?= esc($log['entity_type']) ?> | User ID: <?= $log['user_id'] ?></p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-slate-500">
                                                    <time><?= formatDate($log['created_at'], 'M d, Y H:i') ?></time>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

        </div>
    </main>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
