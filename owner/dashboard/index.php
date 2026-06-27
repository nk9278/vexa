<?php
// owner/dashboard/index.php
require_once __DIR__ . '/../../includes/functions.php';
requireOwner();

define('PAGE_TITLE', 'Owner Dashboard');
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/owner/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    <?php require_once BASE_PATH . '/owner/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Control Center</h1>
                    <p class="mt-1 text-sm text-slate-500">Welcome back. Here is your company overview.</p>
                </div>
                <div class="mt-4 sm:mt-0 flex gap-3">
                    <button class="btn-primary" onclick="showToast('info', 'Report Generation Placeholder');">Generate Report</button>
                </div>
            </div>

            <!-- Dashboard Statistics Cards -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                <div class="card card-body flex items-center">
                    <div class="p-3 rounded-xl bg-indigo-100 text-indigo-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500 truncate">Total Clients</p>
                        <p class="mt-1 text-2xl font-semibold text-slate-900">0</p>
                    </div>
                </div>

                <div class="card card-body flex items-center">
                    <div class="p-3 rounded-xl bg-emerald-100 text-emerald-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500 truncate">Active Projects</p>
                        <p class="mt-1 text-2xl font-semibold text-slate-900">0</p>
                    </div>
                </div>

                <div class="card card-body flex items-center">
                    <div class="p-3 rounded-xl bg-amber-100 text-amber-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500 truncate">Pending Tasks</p>
                        <p class="mt-1 text-2xl font-semibold text-slate-900">0</p>
                    </div>
                </div>

                <div class="card card-body flex items-center">
                    <div class="p-3 rounded-xl bg-sky-100 text-sky-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500 truncate">Total Employees</p>
                        <p class="mt-1 text-2xl font-semibold text-slate-900">1</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

                <!-- Quick Actions Widget -->
                <div class="card">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-lg font-medium text-slate-900">Quick Actions</h2>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-2 gap-4">
                            <button class="flex flex-col items-center justify-center p-4 bg-slate-50 hover:bg-indigo-50 border border-slate-100 rounded-xl transition-colors text-slate-600 hover:text-indigo-600" onclick="showToast('info', 'Client module not built yet.');">
                                <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                <span class="text-xs font-medium">Add Client</span>
                            </button>
                            <button class="flex flex-col items-center justify-center p-4 bg-slate-50 hover:bg-indigo-50 border border-slate-100 rounded-xl transition-colors text-slate-600 hover:text-indigo-600" onclick="showToast('info', 'Project module not built yet.');">
                                <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span class="text-xs font-medium">New Project</span>
                            </button>
                            <button class="flex flex-col items-center justify-center p-4 bg-slate-50 hover:bg-indigo-50 border border-slate-100 rounded-xl transition-colors text-slate-600 hover:text-indigo-600" onclick="showToast('info', 'Task module not built yet.');">
                                <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                <span class="text-xs font-medium">Create Task</span>
                            </button>
                            <a href="<?= BASE_URL ?>owner/profile/" class="flex flex-col items-center justify-center p-4 bg-slate-50 hover:bg-indigo-50 border border-slate-100 rounded-xl transition-colors text-slate-600 hover:text-indigo-600">
                                <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span class="text-xs font-medium">Settings</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Charts Placeholder -->
                <div class="card lg:col-span-2">
                    <div class="border-b border-slate-100 px-6 py-4 flex justify-between items-center">
                        <h2 class="text-lg font-medium text-slate-900">Revenue & Projects</h2>
                        <select class="form-input text-xs py-1 w-auto"><option>This Month</option></select>
                    </div>
                    <div class="card-body h-72 flex items-center justify-center bg-slate-50/50">
                        <p class="text-slate-400 text-sm italic">Chart.js rendering area placeholder.</p>
                    </div>
                </div>
            </div>

            <!-- Activity & Notifications -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <div class="card">
                    <div class="border-b border-slate-100 px-6 py-4 flex justify-between items-center">
                        <h2 class="text-lg font-medium text-slate-900">Today's Notifications</h2>
                        <span class="badge badge-rose">2 New</span>
                    </div>
                    <div class="card-body">
                        <ul class="space-y-4">
                            <li class="flex items-start">
                                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div class="ml-3 w-full border-b border-slate-50 pb-4">
                                    <p class="text-sm font-medium text-slate-900">Workspace successfully initialized.</p>
                                    <p class="text-xs text-slate-500 mt-1">10 minutes ago</p>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div class="ml-3 w-full border-b border-slate-50 pb-4">
                                    <p class="text-sm font-medium text-slate-900">Please review company branding settings.</p>
                                    <p class="text-xs text-slate-500 mt-1">1 hour ago</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card">
                    <div class="border-b border-slate-100 px-6 py-4 flex justify-between items-center">
                        <h2 class="text-lg font-medium text-slate-900">Subscription Status</h2>
                        <a href="#" class="text-indigo-600 text-sm hover:underline">Manage</a>
                    </div>
                    <div class="card-body">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Enterprise Plan</h3>
                                <p class="text-xs text-slate-500">Billed Annually</p>
                            </div>
                            <span class="badge badge-success">Active</span>
                        </div>

                        <div class="space-y-4 border-t border-slate-100 pt-4">
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="font-medium text-slate-700">Storage Usage</span>
                                    <span class="text-slate-600">0 MB / 5000 MB</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-1.5">
                                    <div class="bg-indigo-500 h-1.5 rounded-full" style="width: 5%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="font-medium text-slate-700">Active Projects</span>
                                    <span class="text-slate-600">0 / 50</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-1.5">
                                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: 2%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- Mobile FAB Placeholder -->
        <button class="lg:hidden fixed bottom-20 right-4 p-4 bg-indigo-600 text-white rounded-full shadow-lg hover:bg-indigo-700 focus:outline-none z-40">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        </button>
    </main>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
