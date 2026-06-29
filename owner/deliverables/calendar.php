<?php
require_once '../../includes/session.php';
require_once '../../includes/auth.php';
require_once '../../includes/database.php';
require_once '../../includes/helpers.php';

requireLogin();
requirePermission('view_deliverables');

// For phase 23, providing a basic structured view showing items by date
// Full interactive JS calendar typically requires a heavy frontend lib, so we will build a clean "Upcoming Deliveries" schedule view.

$db = Database::getInstance()->getConnection();
$company_id = $_SESSION['company_id'];

$month_filter = $_GET['month'] ?? date('Y-m');

$stmt = $db->prepare("
    SELECT d.*, c.name as client_name, dt.name as type_name
    FROM deliverables d
    JOIN monthly_plans p ON d.monthly_plan_id = p.id
    JOIN clients c ON d.client_id = c.id
    JOIN deliverable_types dt ON d.deliverable_type_id = dt.id
    WHERE d.company_id = ? AND d.due_date LIKE ? AND d.deleted_at IS NULL
    ORDER BY d.due_date ASC
");
$stmt->execute([$company_id, "$month_filter-%"]);
$items = $stmt->fetchAll();

// Group by Date
$calendar = [];
foreach($items as $i) {
    $calendar[$i['due_date']][] = $i;
}

require_once '../../includes/header.php';
require_once '../includes/topbar.php';
require_once '../includes/sidebar.php';
?>

<div class="flex-1 ml-64 mt-16 p-6">
    <div class="max-w-5xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center">
                <a href="/owner/deliverables/" class="text-gray-500 hover:text-gray-700 mr-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Delivery Schedule</h1>
                    <p class="text-xs text-gray-500 mt-1">Items due in <?= date('F Y', strtotime($month_filter.'-01')) ?></p>
                </div>
            </div>
            <form>
                <input type="month" name="month" value="<?= esc($month_filter) ?>" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none text-sm font-bold text-gray-700 cursor-pointer">
            </form>
        </div>

        <?php if(empty($calendar)): ?>
            <div class="p-12 text-center text-gray-500 border-2 border-dashed border-gray-200 rounded-xl bg-white">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <p class="text-xl font-bold text-gray-700">No Deliveries Scheduled</p>
                <p class="text-sm mt-2 text-gray-500">There are no items with a due date set in <?= date('F Y', strtotime($month_filter.'-01')) ?>.</p>
            </div>
        <?php else: ?>
            <div class="space-y-8">
                <?php foreach($calendar as $date => $dayItems): ?>
                    <div class="relative pl-6 md:pl-0">
                        <!-- Desktop Timeline dot hidden on mobile -->
                        <div class="hidden md:block absolute left-40 top-4 w-3 h-3 rounded-full bg-primary ring-4 ring-indigo-50"></div>

                        <div class="flex flex-col md:flex-row gap-4 md:gap-12">
                            <!-- Date Label -->
                            <div class="md:w-32 md:text-right shrink-0 md:pt-2">
                                <?php
                                    $isToday = $date === date('Y-m-d');
                                    $isPast = $date < date('Y-m-d');
                                ?>
                                <div class="text-2xl font-black <?= $isToday ? 'text-primary' : ($isPast ? 'text-gray-400' : 'text-gray-700') ?>"><?= date('d', strtotime($date)) ?></div>
                                <div class="text-sm font-bold uppercase tracking-widest <?= $isToday ? 'text-primary' : ($isPast ? 'text-gray-400' : 'text-gray-500') ?>"><?= date('D, M', strtotime($date)) ?></div>
                                <?php if($isToday): ?><span class="text-[10px] bg-primary text-white px-2 py-0.5 rounded-full mt-1 inline-block">TODAY</span><?php endif; ?>
                            </div>

                            <!-- Items list for this date -->
                            <div class="flex-1 space-y-3 md:border-l-2 md:border-indigo-100 md:pl-10">
                                <?php foreach($dayItems as $item): ?>
                                    <div class="bg-white border <?= $item['status'] === 'Completed' ? 'border-green-200 bg-green-50/30' : 'border-gray-200 hover:border-primary/50' ?> rounded-xl p-4 shadow-sm transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4">
                                        <div>
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px] font-bold uppercase tracking-wider border border-gray-200"><?= esc($item['client_name']) ?></span>
                                                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded text-[10px] font-bold uppercase tracking-wider border border-indigo-100"><?= esc($item['type_name']) ?></span>
                                            </div>
                                            <a href="/owner/deliverables/view_plan.php?id=<?= $item['monthly_plan_id'] ?>" class="text-base font-bold text-gray-800 hover:text-primary transition-colors"><?= esc($item['title']) ?></a>
                                        </div>
                                        <div class="flex items-center gap-4 shrink-0">
                                            <?php
                                                $s = $item['status'];
                                                $sc = 'bg-gray-100 text-gray-700 border-gray-200';
                                                if(in_array($s, ['Working', 'Review'])) $sc = 'bg-blue-50 text-blue-700 border-blue-200';
                                                if($s === 'Approved') $sc = 'bg-green-50 text-green-700 border-green-200';
                                                if($s === 'Completed') $sc = 'bg-green-100 text-green-800 border-green-300';
                                                if($s === 'Delayed') $sc = 'bg-red-50 text-red-700 border-red-200';
                                            ?>
                                            <span class="text-xs font-bold px-3 py-1 rounded-lg border <?= $sc ?> shadow-sm"><?= esc($s) ?></span>
                                            <a href="/owner/deliverables/view_plan.php?id=<?= $item['monthly_plan_id'] ?>" class="w-8 h-8 rounded-lg bg-gray-50 border border-gray-200 flex items-center justify-center text-gray-500 hover:text-primary hover:bg-white transition-colors" title="Go to Plan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
