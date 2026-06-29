<?php
require_once '../../includes/session.php';
require_once '../../includes/auth.php';
require_once '../../includes/database.php';
require_once '../../includes/helpers.php';

requireLogin();
requirePermission('create_client');

require_once '../../includes/header.php';
require_once '../includes/topbar.php';
require_once '../includes/sidebar.php';
?>

<div class="flex-1 ml-64 mt-16 p-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center mb-6">
            <a href="/owner/clients/" class="text-gray-500 hover:text-gray-700 mr-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Add New Client</h1>
        </div>

        <form id="createClientForm" class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Core Info -->
                <div class="space-y-4 md:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Business Information</h3>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Business/Company Name *</label>
                    <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Brand Name</label>
                    <input type="text" name="brand_name" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Owner Name</label>
                    <input type="text" name="owner_name" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                    <input type="text" name="contact_person" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Primary Email</label>
                    <input type="email" name="email" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Business Email</label>
                    <input type="email" name="business_email" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Number</label>
                    <input type="text" name="mobile" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alternate Mobile</label>
                    <input type="text" name="alternate_mobile" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <!-- Business Details -->
                <div class="space-y-4 md:col-span-2 mt-4">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Business Details</h3>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Website URL</label>
                    <input type="url" name="website" placeholder="https://" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Business Category</label>
                    <select name="business_category" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors bg-white">
                        <option value="">Select Category</option>
                        <option value="E-Commerce">E-Commerce</option>
                        <option value="Real Estate">Real Estate</option>
                        <option value="Healthcare">Healthcare</option>
                        <option value="Education">Education</option>
                        <option value="Technology">Technology</option>
                        <option value="Hospitality">Hospitality</option>
                        <option value="Retail">Retail</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">GST Number (Optional)</label>
                    <input type="text" name="gst_number" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Google Business Link</label>
                    <input type="url" name="google_business_link" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Business Description</label>
                    <textarea name="business_description" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors" placeholder="Brief overview of the client's business..."></textarea>
                </div>

                <!-- Location -->
                <div class="space-y-4 md:col-span-2 mt-4">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Location</h3>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <input type="text" name="address" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <input type="text" name="city" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">State / Province</label>
                    <input type="text" name="state" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                    <input type="text" name="country" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">PIN / Zip Code</label>
                    <input type="text" name="pin_code" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <!-- Status -->
                <div class="space-y-4 md:col-span-2 mt-4">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Account Setup</h3>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors bg-white">
                        <option value="lead">Lead</option>
                        <option value="active">Active</option>
                        <option value="pending">Pending Onboarding</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Internal Remarks</label>
                    <textarea name="remarks" rows="2" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors"></textarea>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3 border-t pt-6">
                <a href="/owner/clients/" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">Cancel</a>
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-dark transition-colors font-medium">Save Client</button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#createClientForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        const originalText = $btn.text();

        $btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: '/api/owner/clients/create.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if(res.status === 'success') {
                    // Redirect to the new workspace profile
                    window.location.href = '/owner/clients/profile.php?id=' + res.data.id;
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
