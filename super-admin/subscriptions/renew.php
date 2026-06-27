<?php
// super-admin/subscriptions/renew.php
require_once __DIR__ . '/../../includes/functions.php';
requireSuperAdmin();

$id = $_GET['id'] ?? 0;

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT s.*, c.company_name, p.plan_name
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

define('PAGE_TITLE', 'Renew Subscription');
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/super-admin/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    <?php require_once BASE_PATH . '/super-admin/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-8 flex items-center justify-between">
                <div class="flex items-center">
                    <a href="view.php?id=<?= $sub['id'] ?>" class="mr-4 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Renew Subscription</h1>
                        <p class="mt-1 text-sm text-slate-500">Extend license for <?= esc($sub['company_name']) ?></p>
                    </div>
                </div>
            </div>

            <div class="card">
                <form id="renewForm">
                    <input type="hidden" name="subscription_id" value="<?= $sub['id'] ?>">
                    <div class="card-body border-b border-slate-100">
                        <div class="bg-indigo-50 border border-indigo-100 rounded p-4 mb-6">
                            <p class="text-sm text-indigo-800">Current Expiry: <strong><?= formatDate($sub['expiry_date']) ?></strong></p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="form-label">Extend By</label>
                                <select name="extend_type" class="form-input" required>
                                    <option value="1_month">1 Month</option>
                                    <option value="3_months">3 Months</option>
                                    <option value="1_year" selected>1 Year</option>
                                    <option value="custom">Custom Date</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">New Expiry Date (If Custom)</label>
                                <input type="date" name="custom_date" class="form-input">
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="flex justify-end gap-3">
                            <a href="view.php?id=<?= $sub['id'] ?>" class="btn-secondary">Cancel</a>
                            <button type="submit" class="btn-primary">Process Renewal</button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </main>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
<script>
$(document).ready(function() {
    $('#renewForm').on('submit', function(e) {
        e.preventDefault();
        let submitBtn = $(this).find('button[type="submit"]');
        let originalText = submitBtn.text();
        submitBtn.html('<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mx-auto"></div>').prop('disabled', true);

        $.ajax({
            url: '<?= BASE_URL ?>api/super-admin/subscriptions/renew.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if(response.status === 'success') {
                    showToast('success', response.message);
                    setTimeout(() => window.location.href = response.data.redirect, 1500);
                } else {
                    showToast('error', response.message);
                    submitBtn.text(originalText).prop('disabled', false);
                }
            },
            error: function(xhr) {
                showToast('error', 'Network error.');
                submitBtn.text(originalText).prop('disabled', false);
            }
        });
    });
});
</script>
