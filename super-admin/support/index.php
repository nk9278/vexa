<?php
// super-admin/support/index.php
require_once __DIR__ . '/../../includes/functions.php';
requireSuperAdmin();

define('PAGE_TITLE', 'System Support');
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/super-admin/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    <?php require_once BASE_PATH . '/super-admin/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-8">
                <h1 class="text-2xl font-bold text-slate-900">Support & Documentation</h1>
                <p class="mt-1 text-sm text-slate-500">Resources and ticketing for the VEXA platform.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="card card-body flex items-start hover:shadow-md transition-shadow cursor-pointer">
                    <div class="p-3 rounded-xl bg-indigo-50 text-indigo-600 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-slate-900">Documentation</h3>
                        <p class="text-sm text-slate-500 mt-1">Browse the official architecture and module specifications.</p>
                    </div>
                </div>

                <div class="card card-body flex items-start hover:shadow-md transition-shadow cursor-pointer">
                    <div class="p-3 rounded-xl bg-rose-50 text-rose-600 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-slate-900">Contact Developer</h3>
                        <p class="text-sm text-slate-500 mt-1">Open a direct ticket with the core engineering team.</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-lg font-medium text-slate-900">License Information</h2>
                </div>
                <div class="card-body">
                    <p class="text-sm text-slate-600 mb-4">VEXA Master License is currently active.</p>
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 font-mono text-sm text-slate-700 break-all">
                        VEXA-SAAS-CORP-98A4-XXXX-XXXX-XXXX
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
