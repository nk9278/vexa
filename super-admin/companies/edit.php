<?php
// super-admin/companies/edit.php
require_once __DIR__ . '/../../includes/functions.php';
requireSuperAdmin();

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

define('PAGE_TITLE', 'Edit Company');
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/super-admin/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    <?php require_once BASE_PATH . '/super-admin/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-8 flex items-center justify-between">
                <div class="flex items-center">
                    <a href="view.php?id=<?= $company['id'] ?>" class="mr-4 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Edit Company</h1>
                        <p class="mt-1 text-sm text-slate-500">Update configuration for <?= esc($company['company_name']) ?></p>
                    </div>
                </div>
            </div>

            <div class="card">
                <form id="editCompanyForm" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $company['id'] ?>">

                    <div class="card-body border-b border-slate-100">
                        <h2 class="text-lg font-medium text-slate-900 mb-6">Company Information</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="form-label">Company Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="company_name" class="form-input" value="<?= esc($company['company_name']) ?>" required>
                            </div>
                            <div>
                                <label class="form-label">Company Code <span class="text-rose-500">*</span></label>
                                <input type="text" name="company_code" class="form-input" value="<?= esc($company['company_code']) ?>" required>
                            </div>
                            <div>
                                <label class="form-label">Business Type</label>
                                <select name="business_type" class="form-input">
                                    <option value="">Select Type</option>
                                    <option value="Agency" <?= $company['business_type'] == 'Agency' ? 'selected' : '' ?>>Digital Agency</option>
                                    <option value="Enterprise" <?= $company['business_type'] == 'Enterprise' ? 'selected' : '' ?>>Enterprise</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Industry</label>
                                <input type="text" name="industry" class="form-input" value="<?= esc($company['industry']) ?>">
                            </div>
                            <div>
                                <label class="form-label">Office Phone</label>
                                <input type="text" name="office_phone" class="form-input" value="<?= esc($company['office_phone']) ?>">
                            </div>
                            <div>
                                <label class="form-label">Website</label>
                                <input type="url" name="website" class="form-input" value="<?= esc($company['website']) ?>">
                            </div>
                            <div>
                                <label class="form-label">GST Number</label>
                                <input type="text" name="gst_number" class="form-input" value="<?= esc($company['gst_number']) ?>">
                            </div>
                            <div>
                                <label class="form-label">PAN Number</label>
                                <input type="text" name="pan_number" class="form-input" value="<?= esc($company['pan_number']) ?>">
                            </div>
                            <div>
                                <label class="form-label">Status</label>
                                <select name="status" class="form-input" required>
                                    <option value="demo" <?= $company['status'] == 'demo' ? 'selected' : '' ?>>Demo</option>
                                    <option value="active" <?= $company['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="suspended" <?= $company['status'] == 'suspended' ? 'selected' : '' ?>>Suspended</option>
                                    <option value="inactive" <?= $company['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>

                            <div class="md:col-span-2 border-t border-slate-100 pt-6 mt-2">
                                <h3 class="text-sm font-medium text-slate-900 mb-4">Location & Settings</h3>
                            </div>
                            <div class="md:col-span-2">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-input" value="<?= esc($company['address']) ?>">
                            </div>
                            <div>
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-input" value="<?= esc($company['city']) ?>">
                            </div>
                            <div>
                                <label class="form-label">State</label>
                                <input type="text" name="state" class="form-input" value="<?= esc($company['state']) ?>">
                            </div>
                            <div>
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-input" value="<?= esc($company['country']) ?>">
                            </div>
                            <div>
                                <label class="form-label">Postal Code</label>
                                <input type="text" name="postal_code" class="form-input" value="<?= esc($company['postal_code']) ?>">
                            </div>
                            <div>
                                <label class="form-label">Timezone</label>
                                <input type="text" name="timezone" class="form-input" value="<?= esc($company['timezone']) ?>">
                            </div>
                            <div>
                                <label class="form-label">Currency</label>
                                <input type="text" name="currency" class="form-input" value="<?= esc($company['currency']) ?>">
                            </div>
                            <div>
                                <label class="form-label">Language</label>
                                <input type="text" name="language" class="form-input" value="<?= esc($company['language']) ?>">
                            </div>

                            <div class="md:col-span-2 border-t border-slate-100 pt-6 mt-2">
                                <label class="form-label mb-2">Remarks / Internal Notes</label>
                                <textarea name="remarks" class="form-input h-24"><?= esc($company['remarks']) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card-body bg-slate-50 border-b border-slate-100">
                        <h2 class="text-lg font-medium text-slate-900 mb-6">Owner Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="form-label">Owner Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="owner_name" class="form-input" value="<?= esc($company['owner_name']) ?>" required>
                            </div>
                            <div>
                                <label class="form-label">Owner Email <span class="text-rose-500">*</span></label>
                                <input type="email" name="owner_email" class="form-input" value="<?= esc($company['owner_email']) ?>" required>
                            </div>
                            <div>
                                <label class="form-label">Owner Mobile</label>
                                <input type="text" name="owner_mobile" class="form-input" value="<?= esc($company['owner_mobile']) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="flex justify-end gap-3">
                            <a href="view.php?id=<?= $company['id'] ?>" class="btn-secondary">Cancel</a>
                            <button type="submit" class="btn-primary">Save Changes</button>
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
    $('#editCompanyForm').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);
        let submitBtn = $(this).find('button[type="submit"]');
        let originalText = submitBtn.text();

        submitBtn.html('<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mx-auto"></div>').prop('disabled', true);

        $.ajax({
            url: '<?= BASE_URL ?>api/super-admin/companies/update.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if(response.status === 'success') {
                    showToast('success', response.message);
                    setTimeout(function() { window.location.href = response.data.redirect; }, 1500);
                } else {
                    showToast('error', response.message);
                    submitBtn.text(originalText).prop('disabled', false);
                }
            },
            error: function(xhr) {
                let res = xhr.responseJSON;
                showToast('error', res ? res.message : 'Network error occurred.');
                submitBtn.text(originalText).prop('disabled', false);
            }
        });
    });
});
</script>
