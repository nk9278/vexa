<?php
// auth/maintenance.php
require_once __DIR__ . '/../includes/constants.php';
define('PAGE_TITLE', 'Under Maintenance');

require_once BASE_PATH . '/includes/header.php';
?>
<div class="min-h-screen w-full bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden">
    <div class="sm:mx-auto sm:w-full sm:max-w-md animate-fade-in text-center">

        <div class="bg-white py-12 px-4 shadow-sm sm:rounded-2xl border border-slate-100 sm:px-10 flex flex-col items-center">

            <div class="h-20 w-20 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-center mb-6 border border-indigo-100 animate-pulse">
                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>

            <h2 class="text-2xl font-bold text-slate-900 mb-2">We'll be back soon!</h2>
            <p class="text-sm text-slate-600 mb-6">We are currently performing scheduled maintenance to improve your experience. We estimate to be back online shortly.</p>

            <div class="p-4 bg-slate-50 rounded-lg w-full text-left">
                <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-1">Need urgent help?</p>
                <a href="mailto:support@vexa.app" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Contact Administrator</a>
            </div>

        </div>
    </div>
</div>
<?php require_once BASE_PATH . '/includes/footer.php'; ?>
