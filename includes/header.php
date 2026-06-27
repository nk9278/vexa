<?php
// includes/header.php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) die('Direct access denied.');

require_once __DIR__ . '/functions.php';
setSecurityHeaders();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= esc(getCsrfToken()) ?>">
    <title><?= defined('PAGE_TITLE') ? esc(PAGE_TITLE) . ' - ' : '' ?><?= esc(APP_NAME) ?></title>

    <!-- Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (CDN for development foundation) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        indigo: { 50: '#eef2ff', 100: '#e0e7ff', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca' }
                    }
                }
            }
        }
    </script>

    <!-- Custom CSS via Tailwind CDN injected Stylesheet -->
    <style type="text/tailwindcss">
        @layer base {
            body {
                @apply bg-slate-50 text-slate-900 font-sans antialiased;
            }
            /* Smooth Scrollbar */
            ::-webkit-scrollbar {
                width: 8px;
                height: 8px;
            }
            ::-webkit-scrollbar-track {
                @apply bg-transparent;
            }
            ::-webkit-scrollbar-thumb {
                @apply bg-slate-300 rounded-full;
            }
            ::-webkit-scrollbar-thumb:hover {
                @apply bg-slate-400;
            }
        }

        @layer components {
            /* Primary Buttons */
            .btn-primary {
                @apply inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200 cursor-pointer;
            }

            /* Secondary / Ghost Buttons */
            .btn-secondary {
                @apply inline-flex items-center justify-center px-4 py-2 border border-slate-300 rounded-lg shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200 cursor-pointer;
            }

            /* Danger Buttons */
            .btn-danger {
                @apply inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-colors duration-200 cursor-pointer;
            }

            /* Standard Card Container */
            .card {
                @apply bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden;
            }

            .card-body {
                @apply p-6;
            }

            /* Form Inputs */
            .form-input {
                @apply mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors duration-200;
            }

            .form-label {
                @apply block text-sm font-medium text-slate-700;
            }

            /* Status Badges */
            .badge {
                @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
            }
            .badge-success { @apply bg-emerald-100 text-emerald-800; }
            .badge-warning { @apply bg-amber-100 text-amber-800; }
            .badge-danger { @apply bg-rose-100 text-rose-800; }
            .badge-info { @apply bg-sky-100 text-sky-800; }
            .badge-neutral { @apply bg-slate-100 text-slate-800; }

            /* Tables */
            .table-container {
                @apply min-w-full divide-y divide-slate-200;
            }
            .table-header {
                @apply bg-slate-50;
            }
            .table-header th {
                @apply px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider sticky top-0 z-10;
            }
            .table-cell {
                @apply px-6 py-4 whitespace-nowrap text-sm text-slate-900 border-b border-slate-100;
            }
        }

        @layer utilities {
            .animate-fade-in {
                animation: fadeIn 0.3s ease-in-out;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(5px); }
                to { opacity: 1; transform: translateY(0); }
            }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 flex h-screen overflow-hidden">

    <!-- Global Full-screen Loader -->
    <div id="global-loader" class="hidden fixed inset-0 z-[100] bg-slate-900/50 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-white"></div>
    </div>

    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed bottom-5 right-5 z-[90] flex flex-col gap-2"></div>
