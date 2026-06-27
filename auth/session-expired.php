<?php
// auth/session-expired.php
require_once __DIR__ . '/../includes/constants.php';
define('PAGE_TITLE', 'Session Expired');

require_once BASE_PATH . '/includes/header.php';
?>
<div class="min-h-screen w-full bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden">
    <div class="sm:mx-auto sm:w-full sm:max-w-md animate-fade-in text-center">

        <div class="bg-white py-12 px-4 shadow-sm sm:rounded-2xl border border-slate-100 sm:px-10 flex flex-col items-center">

            <div class="h-20 w-20 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mb-6 border border-amber-100">
                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>

            <h2 class="text-2xl font-bold text-slate-900 mb-2">Session Expired</h2>
            <p class="text-sm text-slate-600 mb-8">For your security, your session has timed out due to inactivity. Please log in again to continue working.</p>

            <a href="<?= BASE_URL ?>auth/login.php" class="btn-primary w-full text-center">Login Again</a>

        </div>
    </div>
</div>
<?php require_once BASE_PATH . '/includes/footer.php'; ?>
