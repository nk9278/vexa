<?php
// super-admin/includes/sidebar.php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) die('Direct access denied.');

$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
if ($current_dir === 'super-admin') $current_dir = 'dashboard';
?>
<!-- Mobile Drawer Overlay -->
<div id="drawer-overlay" class="fixed inset-0 bg-slate-900/50 z-40 hidden opacity-0 transition-opacity duration-300 lg:hidden"></div>

<!-- Sidebar / Drawer -->
<aside id="mobile-drawer" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-300 transform -translate-x-full lg:translate-x-0 lg:static lg:flex lg:flex-col transition-transform duration-300 ease-in-out">

    <!-- Sidebar Header / Logo -->
    <div class="flex items-center justify-center h-16 border-b border-slate-800 bg-slate-950">
        <span class="text-xl font-bold text-white tracking-wider"><?= esc(APP_NAME) ?> <span class="text-xs text-indigo-400 align-top">MASTER</span></span>
    </div>

    <!-- Sidebar Navigation -->
    <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1">

        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-4 px-3">Overview</div>
        <a href="<?= BASE_URL ?>super-admin/" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= $current_dir === 'dashboard' ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?> transition-colors">
            <svg class="w-5 h-5 mr-3 <?= $current_dir === 'dashboard' ? 'text-white' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            Dashboard
        </a>

        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6 px-3">SaaS Management</div>
        <a href="<?= BASE_URL ?>super-admin/companies/" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= $current_dir === 'companies' ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?> transition-colors">
            <svg class="w-5 h-5 mr-3 <?= $current_dir === 'companies' ? 'text-white' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            Companies
        </a>
        <a href="<?= BASE_URL ?>super-admin/subscriptions/" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= $current_dir === 'subscriptions' ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?> transition-colors">
            <svg class="w-5 h-5 mr-3 <?= $current_dir === 'subscriptions' ? 'text-white' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
            Subscriptions
        </a>
        <a href="<?= BASE_URL ?>super-admin/licenses/" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= $current_dir === 'licenses' ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?> transition-colors">
            <svg class="w-5 h-5 mr-3 <?= $current_dir === 'licenses' ? 'text-white' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            Licenses
        </a>
        <a href="<?= BASE_URL ?>super-admin/payments/" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= $current_dir === 'payments' ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?> transition-colors">
            <svg class="w-5 h-5 mr-3 <?= $current_dir === 'payments' ? 'text-white' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Payments
        </a>

        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6 px-3">System</div>
        <a href="<?= BASE_URL ?>super-admin/reports/" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= $current_dir === 'reports' ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?> transition-colors">
            <svg class="w-5 h-5 mr-3 <?= $current_dir === 'reports' ? 'text-white' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Reports
        </a>
        <a href="<?= BASE_URL ?>super-admin/logs/" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= $current_dir === 'logs' ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?> transition-colors">
            <svg class="w-5 h-5 mr-3 <?= $current_dir === 'logs' ? 'text-white' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
            Activity Logs
        </a>
        <a href="<?= BASE_URL ?>super-admin/support/" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= $current_dir === 'support' ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?> transition-colors">
            <svg class="w-5 h-5 mr-3 <?= $current_dir === 'support' ? 'text-white' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            Support
        </a>
    </div>

    <!-- Sidebar Footer -->
    <div class="border-t border-slate-800 p-4 bg-slate-950">
        <a href="<?= BASE_URL ?>super-admin/settings/" class="flex items-center text-sm font-medium <?= $current_dir === 'settings' ? 'text-white' : 'text-slate-400 hover:text-white' ?> transition-colors mb-4">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            System Settings
        </a>
        <a href="<?= BASE_URL ?>api/auth/logout.php" class="flex items-center text-sm font-medium text-rose-400 hover:text-rose-300 transition-colors">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            Secure Logout
        </a>
    </div>
</aside>
