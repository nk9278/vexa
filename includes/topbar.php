<?php
// includes/topbar.php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) die('Direct access denied.');
?>
<header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-30 relative">

    <!-- Mobile Hamburger -->
    <div class="flex items-center lg:hidden">
        <button type="button" data-toggle="sidebar" class="text-slate-500 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 rounded-md p-2 -ml-2 transition-colors">
            <span class="sr-only">Open sidebar</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <!-- Global Search Placeholder -->
    <div class="flex-1 flex justify-center px-4 lg:ml-6 lg:justify-start">
        <div class="max-w-lg w-full lg:max-w-xs relative hidden sm:block">
            <label for="global-search" class="sr-only">Search</label>
            <div class="relative text-slate-400 focus-within:text-slate-600">
                <div class="pointer-events-none absolute inset-y-0 left-0 pl-3 flex items-center">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input id="global-search" class="block w-full bg-slate-100 py-2 pl-10 pr-3 border border-transparent rounded-lg leading-5 text-slate-900 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:border-indigo-500 sm:text-sm transition-colors" placeholder="Search tasks, clients..." type="search" name="search">
            </div>
        </div>
    </div>

    <!-- Right Topbar Actions -->
    <div class="ml-4 flex items-center md:ml-6 space-x-3">

        <!-- Quick Action (+) -->
        <button type="button" class="hidden sm:inline-flex items-center p-2 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
        </button>

        <!-- Notification Bell -->
        <button type="button" class="relative p-2 text-slate-400 hover:text-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-full transition-colors">
            <span class="sr-only">View notifications</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span class="absolute top-1.5 right-1.5 block h-2.5 w-2.5 rounded-full bg-rose-500 ring-2 ring-white"></span>
        </button>

        <!-- Profile Dropdown -->
        <div class="relative ml-3">
            <div>
                <button type="button" data-toggle="dropdown" data-target="#profile-menu" class="max-w-xs bg-white flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                    <span class="sr-only">Open user menu</span>
                    <div class="h-8 w-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs uppercase border border-indigo-200">
                        JD
                    </div>
                </button>
            </div>

            <div id="profile-menu" class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
                <div class="px-4 py-2 text-sm text-slate-700 border-b border-slate-100">
                    <div class="font-medium">John Doe</div>
                    <div class="text-xs text-slate-500">Owner</div>
                </div>
                <a href="#" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50" role="menuitem">Your Profile</a>
                <a href="#" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50" role="menuitem">Settings</a>
                <a href="#" class="block px-4 py-2 text-sm text-rose-600 hover:bg-rose-50" role="menuitem">Sign out</a>
            </div>
        </div>

    </div>
</header>
