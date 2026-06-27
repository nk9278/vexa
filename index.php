<?php
// index.php
require_once __DIR__ . '/includes/constants.php';
define('PAGE_TITLE', 'System Foundation Test');

// Standard Layout inclusion
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/sidebar.php';
?>

<!-- Main Content Wrapper -->
<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">

    <?php require_once BASE_PATH . '/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Breadcrumb & Header -->
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <nav class="text-sm font-medium text-slate-500 mb-2">
                        <a href="#" class="hover:text-indigo-600 transition-colors">VEXA</a>
                        <span class="mx-2">/</span>
                        <span class="text-slate-900">Foundation</span>
                    </nav>
                    <h1 class="text-2xl font-bold text-slate-900">Project Foundation Ready</h1>
                </div>
                <div class="mt-4 sm:mt-0 flex gap-3">
                    <button type="button" class="btn-secondary" onclick="showLoader(); setTimeout(hideLoader, 2000);">Test Loader</button>
                    <button type="button" class="btn-primary" onclick="showToast('success', 'Foundation is working perfectly!')">Test Toast</button>
                </div>
            </div>

            <!-- Components Demo Area -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Card Component -->
                <div class="card">
                    <div class="border-b border-slate-100 px-6 py-4 flex justify-between items-center">
                        <h2 class="text-lg font-medium text-slate-900">Theme Components</h2>
                        <span class="badge badge-success">Active</span>
                    </div>
                    <div class="card-body">
                        <p class="text-slate-600 mb-4 text-sm">Testing standard Tailwind components defined in the global app.css.</p>
                        <div class="space-x-2">
                            <button class="btn-primary">Primary</button>
                            <button class="btn-secondary">Secondary</button>
                            <button class="btn-danger">Danger</button>
                        </div>
                    </div>
                </div>

                <!-- Form Component -->
                <div class="card">
                    <div class="card-body">
                        <h2 class="text-lg font-medium text-slate-900 mb-4">Sample Form</h2>
                        <form onsubmit="event.preventDefault(); showToast('info', 'Form submission prevented in foundation test.');">
                            <div class="mb-4">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-input" placeholder="you@example.com">
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" class="btn-secondary">Cancel</button>
                                <button type="submit" class="btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Table Component -->
                <div class="card lg:col-span-2 overflow-x-auto">
                    <table class="table-container">
                        <thead class="table-header">
                            <tr>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr>
                                <td class="table-cell font-medium">John Doe</td>
                                <td class="table-cell text-slate-500">Super Admin</td>
                                <td class="table-cell"><span class="badge badge-success">Active</span></td>
                                <td class="table-cell text-right">
                                    <button class="text-indigo-600 hover:text-indigo-900 font-medium text-sm">Edit</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>

        <!-- Mobile FAB Placeholder -->
        <button class="lg:hidden fixed bottom-20 right-4 p-4 bg-indigo-600 text-white rounded-full shadow-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 z-40 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        </button>

    </main>

    <!-- Mobile Bottom Navigation Placeholder -->
    <nav class="lg:hidden fixed bottom-0 w-full bg-white border-t border-slate-200 flex justify-around items-center h-16 z-40 pb-safe">
        <a href="#" class="flex flex-col items-center justify-center text-indigo-600 w-full h-full hover:bg-slate-50 transition-colors">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-[10px] font-medium">Home</span>
        </a>
        <a href="#" class="flex flex-col items-center justify-center text-slate-500 w-full h-full hover:bg-slate-50 transition-colors">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            <span class="text-[10px] font-medium">Tasks</span>
        </a>
        <a href="#" class="flex flex-col items-center justify-center text-slate-500 w-full h-full hover:bg-slate-50 transition-colors">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            <span class="text-[10px] font-medium">Alerts</span>
        </a>
        <button type="button" data-toggle="sidebar" class="flex flex-col items-center justify-center text-slate-500 w-full h-full hover:bg-slate-50 transition-colors focus:outline-none">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            <span class="text-[10px] font-medium">Menu</span>
        </button>
    </nav>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
