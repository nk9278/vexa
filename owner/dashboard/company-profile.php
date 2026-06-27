<?php
// owner/dashboard/company-profile.php
require_once __DIR__ . '/../../includes/functions.php';
requireOwner();

define('PAGE_TITLE', 'Company Profile');
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/owner/includes/sidebar.php';

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM companies WHERE id = ?");
    $stmt->execute([$_SESSION['company_id']]);
    $company = $stmt->fetch();
} catch (PDOException $e) {
    $company = null;
}
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    <?php require_once BASE_PATH . '/owner/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-8">
                <h1 class="text-2xl font-bold text-slate-900">Company Profile</h1>
                <p class="mt-1 text-sm text-slate-500">View your workspace metadata and organizational details.</p>
            </div>

            <div class="card mb-8">
                <div class="border-b border-slate-100 px-6 py-4 flex justify-between items-center">
                    <h2 class="text-lg font-medium text-slate-900">Organization Details</h2>
                    <?= getStatusBadge($company['status']) ?>
                </div>
                <div class="card-body flex flex-col md:flex-row gap-8 items-start">
                    <div class="flex-shrink-0 h-32 w-32 bg-indigo-100 rounded-xl flex items-center justify-center font-bold text-indigo-600 text-4xl border border-indigo-200 shadow-sm">
                        <?= substr(esc($company['company_name']), 0, 1) ?>
                    </div>
                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-4">
                        <div>
                            <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Company Name</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-900"><?= esc($company['company_name']) ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Company Code</dt>
                            <dd class="mt-1 text-sm text-slate-600 font-mono"><?= esc($company['company_code']) ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Business Type / Industry</dt>
                            <dd class="mt-1 text-sm text-slate-900"><?= esc($company['business_type']) ?> - <?= esc($company['industry'] ?: 'Not Specified') ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Registration Date</dt>
                            <dd class="mt-1 text-sm text-slate-900"><?= formatDate($company['created_at']) ?></dd>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="card">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-lg font-medium text-slate-900">Contact Information</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Office Phone</dt>
                            <dd class="mt-1 text-sm text-slate-900"><?= esc($company['office_phone'] ?: 'Not provided') ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Website</dt>
                            <dd class="mt-1 text-sm text-indigo-600 hover:underline"><a href="#" target="_blank"><?= esc($company['website'] ?: 'Not provided') ?></a></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Address</dt>
                            <dd class="mt-1 text-sm text-slate-900">
                                <?= esc($company['address']) ?><br>
                                <?= esc($company['city']) ?>, <?= esc($company['state']) ?> <?= esc($company['postal_code']) ?><br>
                                <?= esc($company['country']) ?>
                            </dd>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-lg font-medium text-slate-900">Legal & Formatting</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">GST Number</dt>
                            <dd class="mt-1 text-sm text-slate-900 font-mono"><?= esc($company['gst_number'] ?: 'Not provided') ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Timezone</dt>
                            <dd class="mt-1 text-sm text-slate-900"><?= esc($company['timezone']) ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Currency</dt>
                            <dd class="mt-1 text-sm text-slate-900 font-mono"><?= esc($company['currency']) ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Language</dt>
                            <dd class="mt-1 text-sm text-slate-900 uppercase"><?= esc($company['language']) ?></dd>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
