<?php
// super-admin/settings/index.php
require_once __DIR__ . '/../../includes/functions.php';
requireSuperAdmin();

define('PAGE_TITLE', 'System Settings');
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/super-admin/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    <?php require_once BASE_PATH . '/super-admin/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">System Settings</h1>
                    <p class="mt-1 text-sm text-slate-500">Configure global platform parameters.</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="<?= BASE_URL ?>super-admin/settings/system-info.php" class="btn-secondary">View System Info</a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Settings Navigation -->
                <div class="md:col-span-1 space-y-1">
                    <a href="#" class="block px-4 py-2 bg-indigo-50 text-indigo-700 font-medium rounded-lg">General Settings</a>
                    <a href="#" class="block px-4 py-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium rounded-lg">Email Configuration</a>
                    <a href="#" class="block px-4 py-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium rounded-lg">Payment Gateway</a>
                    <a href="#" class="block px-4 py-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium rounded-lg">White Labeling</a>
                </div>

                <!-- Settings Content -->
                <div class="md:col-span-3">
                    <div class="card">
                        <div class="border-b border-slate-100 px-6 py-4">
                            <h2 class="text-lg font-medium text-slate-900">General Configuration</h2>
                        </div>
                        <div class="card-body">
                            <form class="space-y-6" onsubmit="event.preventDefault(); showToast('success', 'Settings saved successfully.');">

                                <div>
                                    <label class="form-label">Application Name</label>
                                    <input type="text" class="form-input" value="<?= esc(APP_NAME) ?>">
                                    <p class="text-xs text-slate-500 mt-1">Displayed in emails and global headers.</p>
                                </div>

                                <div>
                                    <label class="form-label">Support Email Address</label>
                                    <input type="email" class="form-input" value="support@vexa.app">
                                </div>

                                <div>
                                    <label class="form-label">Default Timezone</label>
                                    <select class="form-input">
                                        <option value="UTC">UTC (Universal Coordinated Time)</option>
                                        <option value="America/New_York">America/New_York</option>
                                        <option value="Europe/London">Europe/London</option>
                                    </select>
                                </div>

                                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg border border-slate-200">
                                    <div>
                                        <h4 class="text-sm font-medium text-slate-900">Maintenance Mode</h4>
                                        <p class="text-xs text-slate-500">Disable access for all tenants while performing upgrades.</p>
                                    </div>
                                    <div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer">
                                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                        </label>
                                    </div>
                                </div>

                                <div class="pt-4 flex justify-end">
                                    <button type="submit" class="btn-primary">Save Settings</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
