<?php
// auth/access-denied.php
require_once __DIR__ . '/../includes/constants.php';
define('PAGE_TITLE', 'Access Denied');

require_once BASE_PATH . '/includes/header.php';
?>
<div class="min-h-screen w-full bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden">
    <div class="sm:mx-auto sm:w-full sm:max-w-md animate-fade-in text-center">

        <div class="bg-white py-12 px-4 shadow-sm sm:rounded-2xl border border-slate-100 sm:px-10 flex flex-col items-center">

            <div class="h-20 w-20 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mb-6 border border-rose-100">
                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>

            <h2 class="text-2xl font-bold text-slate-900 mb-2">Access Denied</h2>
            <p class="text-sm text-slate-600 mb-8">You do not have the required permissions to access this page. Please contact your administrator if you believe this is a mistake.</p>

            <div class="flex gap-3 w-full">
                <button onclick="window.history.back();" class="btn-secondary flex-1">Go Back</button>
                <a href="<?= BASE_URL ?>auth/login.php" class="btn-primary flex-1">Sign In</a>
            </div>

        </div>
    </div>
</div>
<?php require_once BASE_PATH . '/includes/footer.php'; ?>
