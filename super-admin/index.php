<?php
// super-admin/index.php
require_once __DIR__ . '/../includes/functions.php';
requireSuperAdmin();

define('PAGE_TITLE', 'Super Admin Dashboard');
require_once BASE_PATH . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Platform Overview</h1>
                    <p class="mt-1 text-sm text-slate-500">Welcome to the VEXA master control panel.</p>
                </div>
                <div class="mt-4 sm:mt-0 flex gap-3">
                    <button type="button" class="btn-primary">Generate Master Report</button>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">

                <div class="card card-body flex items-center">
                    <div class="p-3 rounded-full bg-indigo-100 text-indigo-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500 truncate">Total Companies</p>
                        <p class="mt-1 text-2xl font-semibold text-slate-900">0</p>
                    </div>
                </div>

                <div class="card card-body flex items-center">
                    <div class="p-3 rounded-full bg-emerald-100 text-emerald-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500 truncate">Active Subscriptions</p>
                        <p class="mt-1 text-2xl font-semibold text-slate-900">0</p>
                    </div>
                </div>

                <div class="card card-body flex items-center">
                    <div class="p-3 rounded-full bg-emerald-100 text-emerald-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500 truncate">MRR</p>
                        <p class="mt-1 text-2xl font-semibold text-slate-900">$0.00</p>
                    </div>
                </div>

                <div class="card card-body flex items-center">
                    <div class="p-3 rounded-full bg-rose-100 text-rose-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500 truncate">Suspended Accounts</p>
                        <p class="mt-1 text-2xl font-semibold text-slate-900">0</p>
                    </div>
                </div>

            </div>

            <!-- Charts Placeholder Area -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div class="card lg:col-span-2">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-lg font-medium text-slate-900">Revenue Growth</h2>
                    </div>
                    <div class="card-body h-72 flex items-center justify-center bg-slate-50/50">
                        <p class="text-slate-400 text-sm">Chart.js Implementation Placeholder</p>
                    </div>
                </div>
                <div class="card">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-lg font-medium text-slate-900">System Health</h2>
                    </div>
                    <div class="card-body h-72 flex flex-col justify-center space-y-4">
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-slate-700">CPU Usage</span>
                                <span class="text-emerald-600">12%</span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-2">
                                <div class="bg-emerald-500 h-2 rounded-full" style="width: 12%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-slate-700">Memory Usage</span>
                                <span class="text-amber-600">45%</span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-2">
                                <div class="bg-amber-500 h-2 rounded-full" style="width: 45%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-slate-700">Storage</span>
                                <span class="text-slate-600">28%</span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-2">
                                <div class="bg-slate-500 h-2 rounded-full" style="width: 28%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Logins / Activity -->
            <div class="card overflow-x-auto">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-lg font-medium text-slate-900">Recent Tenant Logins</h2>
                </div>
                <table class="table-container">
                    <thead class="table-header">
                        <tr>
                            <th>Company</th>
                            <th>User</th>
                            <th>IP Address</th>
                            <th>Date / Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-center py-8">
                        <tr>
                            <td colspan="5" class="table-cell text-slate-500 italic py-6">No recent activity found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Mobile FAB Placeholder -->
        <button class="lg:hidden fixed bottom-20 right-4 p-4 bg-indigo-600 text-white rounded-full shadow-lg hover:bg-indigo-700 focus:outline-none z-40">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        </button>
    </main>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
