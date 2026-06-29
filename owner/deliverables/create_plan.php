<?php
require_once '../../includes/session.php';
require_once '../../includes/auth.php';
require_once '../../includes/database.php';
require_once '../../includes/helpers.php';

requireLogin();
requirePermission('create_deliverables');

$db = Database::getInstance()->getConnection();
$company_id = $_SESSION['company_id'];

// Default month prepopulation
$default_month = $_GET['month'] ?? date('Y-m');

// Get active clients
$stmtClients = $db->prepare("SELECT id, name, brand_name FROM clients WHERE company_id = ? AND status = 'active' AND deleted_at IS NULL ORDER BY name ASC");
$stmtClients->execute([$company_id]);
$clients = $stmtClients->fetchAll();

// Get active deliverable types
$stmtTypes = $db->prepare("SELECT id, name FROM deliverable_types WHERE company_id = ? ORDER BY name ASC");
$stmtTypes->execute([$company_id]);
$types = $stmtTypes->fetchAll();

require_once '../../includes/header.php';
require_once '../includes/topbar.php';
require_once '../includes/sidebar.php';
?>

<div class="flex-1 ml-64 mt-16 p-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center mb-6">
            <a href="/owner/deliverables/" class="text-gray-500 hover:text-gray-700 mr-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Create Monthly Delivery Plan</h1>
        </div>

        <?php if(empty($types)): ?>
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl p-4 mb-6">
                <strong>Warning:</strong> You must create Deliverable Types before setting up a monthly plan.
                <a href="/owner/deliverables/types.php" class="underline font-bold">Create Types</a>
            </div>
        <?php endif; ?>

        <form id="createPlanForm" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
            <input type="hidden" name="action" value="create">

            <div class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Client *</label>
                        <select name="client_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors bg-white">
                            <option value="">Select a Client</option>
                            <?php foreach ($clients as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?> <?= $c['brand_name'] ? "(".esc($c['brand_name']).")" : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Plan Month *</label>
                        <input type="month" name="plan_month" value="<?= esc($default_month) ?>" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Budget Allocation (Optional)</label>
                        <input type="number" step="0.01" name="monthly_budget" placeholder="0.00" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none transition-colors">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Initial Status</label>
                        <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none transition-colors bg-white">
                            <option value="draft">Draft (Planning Phase)</option>
                            <option value="active">Active (Ready for Production)</option>
                        </select>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Monthly Delivery Targets</h3>
                    <p class="text-sm text-gray-500 mb-6">Enter the quantity for each deliverable type required this month. Leave at 0 if not applicable.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        <?php foreach($types as $t): ?>
                            <div class="border border-gray-200 rounded-lg p-3 flex justify-between items-center bg-gray-50">
                                <span class="text-sm font-medium text-gray-700 truncate mr-2" title="<?= esc($t['name']) ?>"><?= esc($t['name']) ?></span>
                                <input type="number" min="0" value="0" name="targets[<?= $t['id'] ?>]" class="w-20 border border-gray-300 rounded text-center py-1 focus:ring-primary focus:border-primary outline-none">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="px-8 py-5 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <a href="/owner/deliverables/" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-white transition-colors">Cancel</a>
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-dark transition-colors font-medium">Generate Monthly Plan</button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#createPlanForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        const originalText = $btn.text();

        $btn.prop('disabled', true).text('Generating...');

        $.ajax({
            url: '/api/owner/deliverables/plans.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if(res.status === 'success') {
                    window.location.href = '/owner/deliverables/view_plan.php?id=' + res.data.id;
                } else {
                    alert(res.message);
                    $btn.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                $btn.prop('disabled', false).text(originalText);
            }
        });
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>
