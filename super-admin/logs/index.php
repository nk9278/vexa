<?php
// super-admin/logs/index.php
require_once __DIR__ . '/../../includes/functions.php';
requireSuperAdmin();

define('PAGE_TITLE', 'Master Activity Logs');
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/super-admin/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    <?php require_once BASE_PATH . '/super-admin/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">System Activity Logs</h1>
                    <p class="mt-1 text-sm text-slate-500">Immutable audit trail of all platform actions.</p>
                </div>
                <div class="mt-4 sm:mt-0 flex gap-3">
                    <button type="button" class="btn-secondary">Export CSV</button>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 mb-6 flex flex-col sm:flex-row gap-4 items-end">
                <div class="w-full sm:w-1/3">
                    <label class="block text-xs font-medium text-slate-500 mb-1 uppercase tracking-wider">Search</label>
                    <input type="text" class="form-input text-sm" placeholder="Search by IP, User ID, or Action...">
                </div>
                <div class="w-full sm:w-1/4">
                    <label class="block text-xs font-medium text-slate-500 mb-1 uppercase tracking-wider">Module</label>
                    <select class="form-input text-sm">
                        <option>All Modules</option>
                        <option>Authentication</option>
                        <option>Settings</option>
                        <option>Companies</option>
                    </select>
                </div>
                <div class="w-full sm:w-auto">
                    <button class="btn-primary w-full sm:w-auto">Apply Filters</button>
                </div>
            </div>

            <div class="card overflow-x-auto">
                <table class="table-container">
                    <thead class="table-header">
                        <tr>
                            <th>Date / Time</th>
                            <th>Company ID</th>
                            <th>User ID</th>
                            <th>Action</th>
                            <th>IP Address</th>
                            <th class="text-right">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td colspan="6" class="table-cell text-slate-500 text-center py-8 italic">No logs generated yet. Database connection pending.</td>
                        </tr>
                    </tbody>
                </table>
                <div class="border-t border-slate-100 px-6 py-4 flex items-center justify-between">
                    <span class="text-sm text-slate-500">Showing 0 entries</span>
                    <div class="flex gap-2">
                        <button class="btn-secondary text-xs disabled:opacity-50" disabled>Previous</button>
                        <button class="btn-secondary text-xs disabled:opacity-50" disabled>Next</button>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
