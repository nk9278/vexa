<?php
// auth/verify-account.php
require_once __DIR__ . '/../includes/constants.php';
define('PAGE_TITLE', 'Verify Account');

require_once BASE_PATH . '/includes/header.php';

// UI Placeholder logic to demonstrate states
$status = $_GET['status'] ?? 'success'; // success, error
?>
<div class="min-h-screen w-full bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden">
    <div class="sm:mx-auto sm:w-full sm:max-w-md animate-fade-in text-center">

        <div class="bg-white py-12 px-4 shadow-sm sm:rounded-2xl border border-slate-100 sm:px-10 flex flex-col items-center">

            <?php if ($status === 'success'): ?>
                <div class="h-16 w-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mb-6">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 mb-2">Account Verified!</h2>
                <p class="text-sm text-slate-600 mb-8">Your email address has been successfully verified. You can now sign in to your account.</p>
                <a href="<?= BASE_URL ?>auth/login.php" class="btn-primary w-full">Go to Login</a>

            <?php else: ?>
                <div class="h-16 w-16 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mb-6">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 mb-2">Verification Failed</h2>
                <p class="text-sm text-slate-600 mb-8">The verification link is invalid or has expired.</p>
                <button onclick="showToast('info', 'Verification email resent.');" class="btn-primary w-full mb-3">Resend Link</button>
                <a href="<?= BASE_URL ?>auth/login.php" class="text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors">Return to Login</a>
            <?php endif; ?>

        </div>
    </div>
</div>
<?php require_once BASE_PATH . '/includes/footer.php'; ?>
