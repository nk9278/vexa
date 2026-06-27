<?php
// auth/forgot-password.php
require_once __DIR__ . '/../includes/functions.php';
requireGuest();

define('PAGE_TITLE', 'Reset Password');

require_once BASE_PATH . '/includes/header.php';
?>
<div class="min-h-screen w-full bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden">
    <div class="sm:mx-auto sm:w-full sm:max-w-md animate-fade-in">

        <div class="text-center mb-8">
            <span class="text-3xl font-bold text-indigo-600 tracking-wider"><?= esc(APP_NAME) ?></span>
            <h2 class="mt-4 text-2xl font-bold text-slate-900">Reset your password</h2>
            <p class="mt-2 text-sm text-slate-600">Enter your email and we'll send you a recovery link.</p>
        </div>

        <div class="bg-white py-8 px-4 shadow-sm sm:rounded-2xl border border-slate-100 sm:px-10">
            <form id="forgotForm" class="space-y-6">

                <div>
                    <label for="email" class="form-label">Email address <span class="text-rose-500">*</span></label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" /></svg>
                        </div>
                        <input id="email" name="email" type="email" autocomplete="email" required class="form-input pl-10" placeholder="you@company.com">
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Send Reset Link
                    </button>
                </div>

                <div class="mt-4 text-center">
                    <a href="<?= BASE_URL ?>auth/login.php" class="text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors flex items-center justify-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
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
    $('#forgotForm').on('submit', function(e) {
        e.preventDefault();

        let submitBtn = $(this).find('button[type="submit"]');
        let originalText = submitBtn.text();

        submitBtn.html('<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mx-auto"></div>').prop('disabled', true);

        $.ajax({
            url: '<?= BASE_URL ?>api/auth/forgot-password.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if(response.status === 'success') {
                    showToast('success', response.message);
                    $('#email').val(''); // Clear form
                } else {
                    showToast('error', response.message);
                }
                submitBtn.text(originalText).prop('disabled', false);
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
