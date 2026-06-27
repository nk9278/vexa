<?php
// super-admin/settings/system-info.php
require_once __DIR__ . '/../../includes/functions.php';
requireSuperAdmin();

define('PAGE_TITLE', 'System Information');
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/super-admin/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    <?php require_once BASE_PATH . '/super-admin/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-8 flex items-center justify-between">
                <div class="flex items-center">
                    <a href="<?= BASE_URL ?>super-admin/settings/" class="mr-4 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">System Information</h1>
                        <p class="mt-1 text-sm text-slate-500">Technical environment diagnostics.</p>
                    </div>
                </div>
            </div>

            <div class="card overflow-hidden">
                <table class="min-w-full divide-y divide-slate-200">
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 w-1/3 bg-slate-50 border-r border-slate-100">Application Version</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?= esc(APP_VERSION) ?></td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 bg-slate-50 border-r border-slate-100">Environment</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><span class="badge badge-warning"><?= esc(ENVIRONMENT) ?></span></td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 bg-slate-50 border-r border-slate-100">PHP Version</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?= phpversion() ?></td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 bg-slate-50 border-r border-slate-100">Database Connection</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-emerald-600 flex items-center">
                                <span class="h-2 w-2 rounded-full bg-emerald-500 mr-2"></span> Active
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 bg-slate-50 border-r border-slate-100">Max Upload Size</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?= ini_get('upload_max_filesize') ?></td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 bg-slate-50 border-r border-slate-100">Memory Limit</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?= ini_get('memory_limit') ?></td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 bg-slate-50 border-r border-slate-100">Server Software</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?= esc($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </main>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
