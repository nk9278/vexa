<?php
// owner/includes/sidebar.php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) die('Direct access denied.');

$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
if ($current_dir === 'owner') $current_dir = 'dashboard';
?>
<!-- Mobile Drawer Overlay -->
<div id="drawer-overlay" class="fixed inset-0 bg-slate-900/50 z-40 hidden opacity-0 transition-opacity duration-300 lg:hidden"></div>

<!-- Sidebar / Drawer -->
<aside id="mobile-drawer" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 transform -translate-x-full lg:translate-x-0 lg:static lg:flex lg:flex-col transition-transform duration-300 ease-in-out shadow-sm">

    <!-- Sidebar Header / Logo -->
    <div class="flex items-center justify-center h-16 border-b border-slate-100">
        <span class="text-xl font-bold text-indigo-600 tracking-wider"><?= esc(APP_NAME) ?></span>
    </div>

    <!-- Sidebar Navigation -->
    <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1">

        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 mt-2 px-3">Overview</div>
        <a href="<?= BASE_URL ?>owner/dashboard/" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= $current_dir === 'dashboard' ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?> transition-colors">
            <svg class="w-5 h-5 mr-3 <?= $current_dir === 'dashboard' ? 'text-indigo-600' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            Dashboard
        </a>

        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 mt-6 px-3">Operations</div>

        <?php if (hasPermission('view_team')): ?>
        <a href="<?= BASE_URL ?>owner/team/" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= $current_dir === 'team' ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?> transition-colors mb-1">
            <svg class="w-5 h-5 mr-3 <?= $current_dir === 'team' ? 'text-indigo-600' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            Team Management
        </a>
        <?php endif; ?>

        <?php if (hasPermission('view_client')): ?>
        <a href="/owner/clients/" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 <?= strpos($_SERVER['REQUEST_URI'], '/owner/clients/') !== false ? 'bg-primary text-white shadow-md shadow-primary/20 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>">
            <svg class="w-5 h-5 <?= strpos($_SERVER['REQUEST_URI'], '/owner/clients/') !== false ? 'text-white' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            <span>Client Workspace</span>
        </a>
        <?php endif; ?>

        <?php if (hasPermission('view_crm')): ?>
        <a href="/owner/crm/" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 <?= strpos($_SERVER['REQUEST_URI'], '/owner/crm/') !== false ? 'bg-primary text-white shadow-md shadow-primary/20 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>">
            <svg class="w-5 h-5 <?= strpos($_SERVER['REQUEST_URI'], '/owner/crm/') !== false ? 'text-white' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <span>CRM Management</span>
        </a>
        <?php endif; ?>

        <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            <span>Projects</span>
        </a>
        <a href="#" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
            <svg class="w-5 h-5 mr-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            Tasks
        </a>

        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 mt-6 px-3">Team & HR</div>
        <a href="#" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
            <svg class="w-5 h-5 mr-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            Employees
        </a>
        <a href="#" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
            <svg class="w-5 h-5 mr-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Attendance
        </a>

        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 mt-6 px-3">Finance</div>
        <a href="#" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
            <svg class="w-5 h-5 mr-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"></path></svg>
            Invoices
        </a>
    </div>

    <!-- Sidebar Footer -->
    <div class="border-t border-slate-100 p-4 bg-slate-50">
        <a href="<?= BASE_URL ?>owner/profile/" class="flex items-center text-sm font-medium <?= $current_dir === 'profile' ? 'text-indigo-600' : 'text-slate-600 hover:text-slate-900' ?> transition-colors mb-4">
            <svg class="w-5 h-5 mr-3 <?= $current_dir === 'profile' ? 'text-indigo-600' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            My Profile
        </a>
        <a href="<?= BASE_URL ?>owner/settings/" class="flex items-center text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors mb-4">
            <svg class="w-5 h-5 mr-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            Company Settings
        </a>
        <a href="<?= BASE_URL ?>api/auth/logout.php" class="flex items-center text-sm font-medium text-rose-600 hover:text-rose-700 transition-colors">
            <svg class="w-5 h-5 mr-3 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            Log Out
        </a>
    </div>
</aside>
