<?php
// owner/profile/index.php
require_once __DIR__ . '/../../includes/functions.php';
requireOwner();

define('PAGE_TITLE', 'My Profile');
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/owner/includes/sidebar.php';

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    redirect(BASE_URL . 'errors/500.php');
}
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    <?php require_once BASE_PATH . '/owner/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">My Profile</h1>
                    <p class="mt-1 text-sm text-slate-500">Manage your personal account details and security.</p>
                </div>
            </div>

            <div class="card mb-8">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-lg font-medium text-slate-900">Personal Information</h2>
                </div>
                <div class="card-body flex flex-col md:flex-row gap-8 items-start">

                    <!-- Profile Picture Upload -->
                    <div class="flex flex-col items-center space-y-4">
                        <div class="h-32 w-32 rounded-full bg-indigo-100 flex items-center justify-center font-bold text-indigo-600 text-4xl border border-indigo-200 shadow-sm overflow-hidden">
                            <?php if ($user['profile_image']): ?>
                                <img src="<?= BASE_URL . 'uploads/' . esc($user['profile_image']) ?>" class="h-full w-full object-cover">
                            <?php else: ?>
                                <?= substr(esc($user['first_name']), 0, 1) ?>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-slate-500">JPG or PNG. Max 2MB.</p>
                    </div>

                    <form id="profileForm" class="flex-1 space-y-4 w-full">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">First Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="first_name" class="form-input" value="<?= esc($user['first_name']) ?>" required>
                            </div>
                            <div>
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-input" value="<?= esc($user['last_name']) ?>">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Email Address (Login ID)</label>
                            <input type="email" class="form-input bg-slate-100" value="<?= esc($user['email']) ?>" readonly>
                            <p class="text-xs text-slate-500 mt-1">To change your email, contact the Super Admin.</p>
                        </div>
                        <div>
                            <label class="form-label">Mobile Phone</label>
                            <input type="text" name="mobile" class="form-input" value="<?= esc($user['mobile']) ?>">
                        </div>
                        <div class="pt-4 flex justify-end">
                            <button type="submit" class="btn-primary">Save Profile</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-rose-200">
                <div class="border-b border-rose-100 px-6 py-4 bg-rose-50/50">
                    <h2 class="text-lg font-medium text-rose-800">Security</h2>
                </div>
                <div class="card-body">
                    <form id="passwordForm" class="space-y-4">
                        <div>
                            <label class="form-label">Current Password <span class="text-rose-500">*</span></label>
                            <input type="password" name="current_password" class="form-input" required>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">New Password <span class="text-rose-500">*</span></label>
                                <input type="password" name="new_password" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label">Confirm New Password <span class="text-rose-500">*</span></label>
                                <input type="password" name="confirm_password" class="form-input" required>
                            </div>
                        </div>
                        <div class="pt-4 flex justify-end">
                            <button type="submit" class="btn-danger">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </main>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
<script>
$(document).ready(function() {
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        let submitBtn = $(this).find('button[type="submit"]');
        let originalText = submitBtn.text();
        submitBtn.html('<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mx-auto"></div>').prop('disabled', true);

        $.post('<?= BASE_URL ?>api/owner/profile/update.php', $(this).serialize(), function(response) {
            if(response.status === 'success') {
                showToast('success', response.message);
            } else {
                showToast('error', response.message);
            }
            submitBtn.text(originalText).prop('disabled', false);
        }).fail(function() {
            showToast('error', 'Network error.');
            submitBtn.text(originalText).prop('disabled', false);
        });
    });

    $('#passwordForm').on('submit', function(e) {
        e.preventDefault();
        let submitBtn = $(this).find('button[type="submit"]');
        let originalText = submitBtn.text();
        submitBtn.html('<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mx-auto"></div>').prop('disabled', true);

        $.post('<?= BASE_URL ?>api/owner/profile/update.php?action=password', $(this).serialize(), function(response) {
            if(response.status === 'success') {
                showToast('success', response.message);
                $('#passwordForm')[0].reset();
            } else {
                showToast('error', response.message);
            }
            submitBtn.text(originalText).prop('disabled', false);
        }).fail(function(xhr) {
            let res = xhr.responseJSON;
            showToast('error', res ? res.message : 'Network error.');
            submitBtn.text(originalText).prop('disabled', false);
        });
    });
});
</script>
