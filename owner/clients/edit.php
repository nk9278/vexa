<?php
require_once '../../includes/session.php';
require_once '../../includes/auth.php';
require_once '../../includes/database.php';
require_once '../../includes/helpers.php';

requireLogin();
requirePermission('edit_client');

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /owner/clients/");
    exit;
}

$db = Database::getInstance()->getConnection();
$company_id = $_SESSION['company_id'];

$stmt = $db->prepare("SELECT * FROM clients WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
$stmt->execute([$id, $company_id]);
$client = $stmt->fetch();

if (!$client) {
    header("Location: /owner/clients/");
    exit;
}

require_once '../../includes/header.php';
require_once '../includes/topbar.php';
require_once '../includes/sidebar.php';
?>

<div class="flex-1 ml-64 mt-16 p-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center mb-6">
            <a href="/owner/clients/profile.php?id=<?= $id ?>" class="text-gray-500 hover:text-gray-700 mr-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Edit Client: <?= esc($client['name']) ?></h1>
        </div>

        <form id="editClientForm" class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="id" value="<?= $client['id'] ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Core Info -->
                <div class="space-y-4 md:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Business Information</h3>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Business/Company Name *</label>
                    <input type="text" name="name" value="<?= esc($client['name']) ?>" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Brand Name</label>
                    <input type="text" name="brand_name" value="<?= esc($client['brand_name'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Owner Name</label>
                    <input type="text" name="owner_name" value="<?= esc($client['owner_name'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                    <input type="text" name="contact_person" value="<?= esc($client['contact_person'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Primary Email</label>
                    <input type="email" name="email" value="<?= esc($client['email'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Business Email</label>
                    <input type="email" name="business_email" value="<?= esc($client['business_email'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Number</label>
                    <input type="text" name="mobile" value="<?= esc($client['mobile'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alternate Mobile</label>
                    <input type="text" name="alternate_mobile" value="<?= esc($client['alternate_mobile'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <!-- Business Details -->
                <div class="space-y-4 md:col-span-2 mt-4">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Business Details</h3>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Website URL</label>
                    <input type="url" name="website" value="<?= esc($client['website'] ?? '') ?>" placeholder="https://" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Business Category</label>
                    <select name="business_category" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors bg-white">
                        <option value="">Select Category</option>
                        <?php
                        $cats = ['E-Commerce', 'Real Estate', 'Healthcare', 'Education', 'Technology', 'Hospitality', 'Retail', 'Other'];
                        foreach($cats as $c) {
                            $sel = ($client['business_category'] === $c) ? 'selected' : '';
                            echo "<option value=\"$c\" $sel>$c</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">GST Number (Optional)</label>
                    <input type="text" name="gst_number" value="<?= esc($client['gst_number'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Google Business Link</label>
                    <input type="url" name="google_business_link" value="<?= esc($client['google_business_link'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Business Description</label>
                    <textarea name="business_description" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors"><?= esc($client['business_description'] ?? '') ?></textarea>
                </div>

                <!-- Location -->
                <div class="space-y-4 md:col-span-2 mt-4">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Location</h3>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <input type="text" name="address" value="<?= esc($client['address'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <input type="text" name="city" value="<?= esc($client['city'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">State / Province</label>
                    <input type="text" name="state" value="<?= esc($client['state'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                    <input type="text" name="country" value="<?= esc($client['country'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">PIN / Zip Code</label>
                    <input type="text" name="pin_code" value="<?= esc($client['pin_code'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <!-- Status -->
                <div class="space-y-4 md:col-span-2 mt-4">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Account Setup</h3>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors bg-white">
                        <?php
                        $statuses = ['lead', 'active', 'paused', 'pending', 'completed', 'closed', 'archived'];
                        foreach($statuses as $s) {
                            $sel = ($client['status'] === $s) ? 'selected' : '';
                            echo "<option value=\"$s\" $sel>".ucfirst($s)."</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Internal Remarks</label>
                    <textarea name="remarks" rows="2" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors"><?= esc($client['remarks'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3 border-t pt-6">
                <a href="/owner/clients/profile.php?id=<?= $id ?>" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">Cancel</a>
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-dark transition-colors font-medium">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#editClientForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        const originalText = $btn.text();

        $btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: '/api/owner/clients/update.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if(res.status === 'success') {
                    window.location.href = '/owner/clients/profile.php?id=<?= $id ?>';
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
