<?php
require_once '../../includes/session.php';
require_once '../../includes/auth.php';
require_once '../../includes/database.php';
require_once '../../includes/helpers.php';

requireLogin();
requirePermission('view_deliverables');

$db = Database::getInstance()->getConnection();
$company_id = $_SESSION['company_id'];

// Get Current Month Filter
$month_filter = $_GET['month'] ?? date('Y-m'); // Format YYYY-MM
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Dashboard Stats (Overall for the selected month)
$stats = [
    'total_plans' => $db->query("SELECT COUNT(*) FROM monthly_plans WHERE company_id = $company_id AND plan_month = '$month_filter' AND deleted_at IS NULL")->fetchColumn(),
    'pending_items' => $db->query("SELECT COUNT(*) FROM deliverables d JOIN monthly_plans p ON d.monthly_plan_id = p.id WHERE d.company_id = $company_id AND p.plan_month = '$month_filter' AND d.status IN ('Pending', 'Working', 'Review', 'Delayed') AND d.deleted_at IS NULL")->fetchColumn(),
    'completed_items' => $db->query("SELECT COUNT(*) FROM deliverables d JOIN monthly_plans p ON d.monthly_plan_id = p.id WHERE d.company_id = $company_id AND p.plan_month = '$month_filter' AND d.status = 'Completed' AND d.deleted_at IS NULL")->fetchColumn()
];

// Build Query for Plans
$query = "
    SELECT p.*, c.name as client_name, c.brand_name,
           (SELECT COUNT(*) FROM deliverables d WHERE d.monthly_plan_id = p.id AND d.deleted_at IS NULL) as total_items,
           (SELECT COUNT(*) FROM deliverables d WHERE d.monthly_plan_id = p.id AND d.status = 'Completed' AND d.deleted_at IS NULL) as completed_items
    FROM monthly_plans p
    JOIN clients c ON p.client_id = c.id
    WHERE p.company_id = ? AND p.plan_month = ? AND p.deleted_at IS NULL
";
$params = [$company_id, $month_filter];

if ($status_filter !== 'all') {
    $query .= " AND p.status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $query .= " AND c.name LIKE ?";
    $params[] = "%$search%";
}

$query .= " ORDER BY c.name ASC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$plans = $stmt->fetchAll();

require_once '../../includes/header.php';
require_once '../includes/topbar.php';
require_once '../includes/sidebar.php';
?>

<div class="flex-1 ml-64 mt-16 p-6">
    <div class="flex flex-col md:flex-row justify-between md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Monthly Deliverables</h1>
            <p class="text-gray-600 text-sm">Plan, allocate, and monitor production resources across clients.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <?php if (hasPermission('manage_deliverable_types')): ?>
                <a href="/owner/deliverables/types.php" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors font-medium text-sm shadow-sm">Manage Types</a>
            <?php endif; ?>
            <a href="/owner/deliverables/calendar.php" class="bg-indigo-50 border border-indigo-100 text-indigo-700 px-4 py-2 rounded-lg hover:bg-indigo-100 transition-colors font-medium text-sm shadow-sm flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> Calendar View
            </a>
            <?php if (hasPermission('create_deliverables')): ?>
                <a href="/owner/deliverables/create_plan.php" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors font-medium text-sm shadow-sm">+ New Monthly Plan</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Month Navigation & Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex flex-col justify-center items-center relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 bg-primary/5 rounded-bl-full"></div>
            <form id="monthForm" class="z-10 w-full text-center">
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Planning Period</label>
                <input type="month" name="month" value="<?= esc($month_filter) ?>" onchange="this.form.submit()" class="text-xl font-bold text-gray-800 bg-transparent border-none p-0 cursor-pointer focus:ring-0 text-center w-full">
            </form>
            <div class="mt-2 text-xs text-gray-500">Select to change period</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-gray-500 text-sm font-medium">Active Plans</h3>
            <div class="text-3xl font-bold text-gray-800 mt-2"><?= $stats['total_plans'] ?></div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-gray-500 text-sm font-medium">Items Pending</h3>
            <div class="text-3xl font-bold text-yellow-600 mt-2"><?= $stats['pending_items'] ?></div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-gray-500 text-sm font-medium">Items Completed</h3>
            <div class="text-3xl font-bold text-green-600 mt-2"><?= $stats['completed_items'] ?></div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-t-xl border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
        <form class="flex gap-2 w-full md:w-auto">
            <input type="hidden" name="month" value="<?= esc($month_filter) ?>">
            <select name="status" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-primary text-sm">
                <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                <option value="draft" <?= $status_filter === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
            </select>

            <div class="relative">
                <input type="text" name="search" value="<?= esc($search) ?>" placeholder="Search client..." class="border border-gray-300 rounded-lg pl-9 pr-3 py-2 w-full focus:outline-none focus:border-primary text-sm">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </form>
    </div>

    <!-- Data Grid -->
    <div class="bg-white rounded-b-xl shadow-sm overflow-hidden border border-gray-100 p-6">
        <?php if (count($plans) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($plans as $plan): ?>
                    <?php
                        $percent = $plan['total_items'] > 0 ? round(($plan['completed_items'] / $plan['total_items']) * 100) : 0;
                        $sColor = 'bg-gray-100 text-gray-700 border-gray-200';
                        if($plan['status'] === 'active') $sColor = 'bg-blue-50 text-blue-700 border-blue-200';
                        if($plan['status'] === 'completed') $sColor = 'bg-green-50 text-green-700 border-green-200';
                    ?>
                    <div class="border border-gray-200 rounded-xl p-5 hover:shadow-md transition-all flex flex-col justify-between group bg-gray-50/30">
                        <div>
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h3 class="font-bold text-gray-800 text-lg truncate" title="<?= esc($plan['client_name']) ?>"><?= esc($plan['client_name']) ?></h3>
                                    <?php if($plan['brand_name']): ?>
                                        <p class="text-xs text-gray-500"><?= esc($plan['brand_name']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <span class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider rounded border <?= $sColor ?>"><?= esc($plan['status']) ?></span>
                            </div>

                            <div class="mt-4 mb-2 flex justify-between items-end">
                                <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Progress</span>
                                <span class="text-sm font-bold text-gray-800"><?= $plan['completed_items'] ?> / <?= $plan['total_items'] ?></span>
                            </div>

                            <div class="w-full bg-gray-200 rounded-full h-2 mb-4 overflow-hidden">
                                <div class="bg-primary h-2 rounded-full transition-all duration-500" style="width: <?= $percent ?>%"></div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex justify-between items-center mt-2">
                            <span class="text-xl font-bold <?= $percent === 100 ? 'text-green-500' : 'text-primary' ?>"><?= $percent ?>%</span>
                            <a href="/owner/deliverables/view_plan.php?id=<?= $plan['id'] ?>" class="text-sm text-primary hover:text-primary-dark font-medium bg-primary/10 px-4 py-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity">Open Planner &rarr;</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="p-12 text-center text-gray-500 border-2 border-dashed border-gray-200 rounded-xl">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <p class="text-xl font-bold text-gray-700">No Monthly Plans Found</p>
                <p class="text-sm mt-2 text-gray-500">There are no active deliverable plans for <?= date('F Y', strtotime($month_filter . '-01')) ?>.</p>
                <?php if (hasPermission('create_deliverables')): ?>
                    <a href="/owner/deliverables/create_plan.php?month=<?= esc($month_filter) ?>" class="inline-block mt-4 bg-primary text-white px-6 py-2 rounded-lg font-medium hover:bg-primary-dark transition-colors">Create First Plan</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
