<?php
// super-admin/companies/create.php
require_once __DIR__ . '/../../includes/functions.php';
requireSuperAdmin();

define('PAGE_TITLE', 'Add New Company');
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/super-admin/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    <?php require_once BASE_PATH . '/super-admin/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-8 flex items-center justify-between">
                <div class="flex items-center">
                    <a href="index.php" class="mr-4 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Add New Company</h1>
                        <p class="mt-1 text-sm text-slate-500">Provision a new tenant workspace.</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <form id="createCompanyForm" enctype="multipart/form-data">
                    <div class="card-body border-b border-slate-100">
                        <h2 class="text-lg font-medium text-slate-900 mb-6">Company Information</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="form-label">Company Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="company_name" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label">Company Code <span class="text-rose-500">*</span></label>
                                <input type="text" name="company_code" class="form-input" placeholder="e.g. VEXA-001" required>
                            </div>
                            <div>
                                <label class="form-label">Business Type</label>
                                <select name="business_type" class="form-input">
                                    <option value="">Select Type</option>
                                    <option value="Agency">Digital Agency</option>
                                    <option value="Enterprise">Enterprise</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Industry</label>
                                <input type="text" name="industry" class="form-input" placeholder="e.g. Technology">
                            </div>
                            <div>
                                <label class="form-label">Office Phone</label>
                                <input type="text" name="office_phone" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Website</label>
                                <input type="url" name="website" class="form-input" placeholder="https://">
                            </div>
                            <div>
                                <label class="form-label">GST Number</label>
                                <input type="text" name="gst_number" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">PAN Number</label>
                                <input type="text" name="pan_number" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Status</label>
                                <select name="status" class="form-input" required>
                                    <option value="demo">Demo</option>
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            <div class="md:col-span-2 border-t border-slate-100 pt-6 mt-2">
                                <h3 class="text-sm font-medium text-slate-900 mb-4">Location & Settings</h3>
                            </div>
                            <div class="md:col-span-2">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">State</label>
                                <input type="text" name="state" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Postal Code</label>
                                <input type="text" name="postal_code" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Timezone</label>
                                <input type="text" name="timezone" class="form-input" value="UTC">
                            </div>
                            <div>
                                <label class="form-label">Currency</label>
                                <input type="text" name="currency" class="form-input" value="USD">
                            </div>
                            <div>
                                <label class="form-label">Language</label>
                                <input type="text" name="language" class="form-input" value="en">
                            </div>

                            <div class="md:col-span-2 border-t border-slate-100 pt-6 mt-2">
                                <label class="form-label mb-2">Company Logo</label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-lg">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-slate-600 justify-center">
                                            <label for="company_logo" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                <span>Upload a file</span>
                                                <input id="company_logo" name="company_logo" type="file" class="sr-only" accept="image/png, image/jpeg, image/webp">
                                            </label>
                                        </div>
                                        <p class="text-xs text-slate-500">PNG, JPG, WEBP up to 2MB</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body bg-slate-50 border-b border-slate-100">
                        <h2 class="text-lg font-medium text-slate-900 mb-6">Owner Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="form-label">Owner Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="owner_name" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label">Owner Email <span class="text-rose-500">*</span></label>
                                <input type="email" name="owner_email" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label">Owner Mobile</label>
                                <input type="text" name="owner_mobile" class="form-input">
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="flex justify-end gap-3">
                            <a href="index.php" class="btn-secondary">Cancel</a>
                            <button type="submit" class="btn-primary">Create Company</button>
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
    $('#createCompanyForm').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);
        let submitBtn = $(this).find('button[type="submit"]');
        let originalText = submitBtn.text();

        submitBtn.html('<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mx-auto"></div>').prop('disabled', true);

        $.ajax({
            url: '<?= BASE_URL ?>api/super-admin/companies/create.php',
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
