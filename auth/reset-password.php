<?php
// auth/reset-password.php
require_once __DIR__ . '/../includes/functions.php';
requireGuest();

define('PAGE_TITLE', 'Create New Password');

require_once BASE_PATH . '/includes/header.php';
?>
<div class="min-h-screen w-full bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden">
    <div class="sm:mx-auto sm:w-full sm:max-w-md animate-fade-in">

        <div class="text-center mb-8">
            <span class="text-3xl font-bold text-indigo-600 tracking-wider"><?= esc(APP_NAME) ?></span>
            <h2 class="mt-4 text-2xl font-bold text-slate-900">Create new password</h2>
            <p class="mt-2 text-sm text-slate-600">Your new password must be different from previously used passwords.</p>
        </div>

        <div class="bg-white py-8 px-4 shadow-sm sm:rounded-2xl border border-slate-100 sm:px-10">
            <form id="resetForm" class="space-y-6">
                <!-- Hidden inputs for token and email passed via URL -->
                <input type="hidden" name="token" value="<?= esc($_GET['token'] ?? '') ?>">
                <input type="hidden" name="email" value="<?= esc($_GET['email'] ?? '') ?>">

                <div>
                    <label for="password" class="form-label">New Password <span class="text-rose-500">*</span></label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        </div>
                        <input id="password" name="password" type="password" required class="form-input pl-10" placeholder="••••••••">
                    </div>
                    <!-- Password Strength Placeholder -->
                    <div class="mt-2 flex gap-1 h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                        <div class="w-1/3 bg-amber-400"></div>
                        <div class="w-1/3"></div>
                        <div class="w-1/3"></div>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Fair (Add numbers & symbols)</p>
                </div>

                <div>
                    <label for="password_confirmation" class="form-label">Confirm Password <span class="text-rose-500">*</span></label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <input id="password_confirmation" name="password_confirmation" type="password" required class="form-input pl-10" placeholder="••••••••">
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Reset Password
                    </button>
                </div>

                <div class="mt-4 text-center">
                    <a href="<?= BASE_URL ?>auth/login.php" class="text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors">
                        Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once BASE_PATH . '/includes/footer.php'; ?>
<script>
$(document).ready(function() {
    $('#resetForm').on('submit', function(e) {
        e.preventDefault();

        let submitBtn = $(this).find('button[type="submit"]');
        let originalText = submitBtn.text();

        submitBtn.html('<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mx-auto"></div>').prop('disabled', true);

        $.ajax({
            url: '<?= BASE_URL ?>api/auth/reset-password.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if(response.status === 'success') {
                    showToast('success', response.message);
                    setTimeout(function() {
                        window.location.href = response.data.redirect;
                    }, 2000);
                } else {
                    showToast('error', response.message);
                    submitBtn.text(originalText).prop('disabled', false);
                }
            },
            error: function(xhr) {
                let res = xhr.responseJSON;
                showToast('error', res ? res.message : 'Network error. Please try again.');
                submitBtn.text(originalText).prop('disabled', false);
            }
        });
    });
});
</script>
