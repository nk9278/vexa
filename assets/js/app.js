/**
 * assets/js/app.js
 * Core Global JavaScript Foundation
 */

$(document).ready(function() {

    // --- Mobile Sidebar Toggle ---
    $('[data-toggle="sidebar"]').on('click', function(e) {
        e.preventDefault();
        $('#mobile-drawer').toggleClass('-translate-x-full');
        $('#drawer-overlay').toggleClass('hidden opacity-0 opacity-100');
    });

    // Close Sidebar on overlay click
    $('#drawer-overlay').on('click', function() {
        $('#mobile-drawer').addClass('-translate-x-full');
        $(this).removeClass('opacity-100').addClass('hidden opacity-0');
    });

    // --- Dropdown Toggles ---
    $(document).on('click', '[data-toggle="dropdown"]', function(e) {
        e.preventDefault();
        e.stopPropagation();
        let target = $(this).data('target');

        // Close others
        $('.dropdown-menu').not(target).addClass('hidden');

        // Toggle target
        $(target).toggleClass('hidden');
    });

    // Close dropdowns when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('[data-toggle="dropdown"], .dropdown-menu').length) {
            $('.dropdown-menu').addClass('hidden');
        }
    });

    // --- Modal Toggles ---
    $('[data-toggle="modal"]').on('click', function(e) {
        e.preventDefault();
        let target = $(this).data('target');
        $(target).removeClass('hidden').addClass('flex');
        $('body').addClass('overflow-hidden'); // Prevent background scroll
    });

    $('[data-dismiss="modal"]').on('click', function(e) {
        e.preventDefault();
        $(this).closest('.modal-container').addClass('hidden').removeClass('flex');
        $('body').removeClass('overflow-hidden');
    });

    // --- Toast Notification System ---
    window.showToast = function(type, message) {
        const toastId = 'toast-' + Date.now();
        const colors = {
            'success': 'bg-emerald-500',
            'error': 'bg-rose-500',
            'warning': 'bg-amber-500',
            'info': 'bg-sky-500'
        };
        const bgColor = colors[type] || colors['info'];

        const toastHtml = `
            <div id="${toastId}" class="flex items-center w-full max-w-xs p-4 mb-4 text-white ${bgColor} rounded-lg shadow animate-fade-in" role="alert">
                <div class="ms-3 text-sm font-normal">${message}</div>
                <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white/20 text-white hover:bg-white/30 rounded-lg focus:ring-2 focus:ring-white p-1.5 inline-flex items-center justify-center h-8 w-8 transition-colors" data-dismiss-toast="${toastId}">
                    <span class="sr-only">Close</span>
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                </button>
            </div>
        `;

        $('#toast-container').append(toastHtml);

        // Auto remove after 3 seconds
        setTimeout(() => {
            $('#' + toastId).fadeOut(300, function() { $(this).remove(); });
        }, 3000);
    };

    $(document).on('click', '[data-dismiss-toast]', function() {
        const targetId = $(this).data('dismiss-toast');
        $('#' + targetId).fadeOut(300, function() { $(this).remove(); });
    });

    // --- Global AJAX Setup ---
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Global Loader Controls
    window.showLoader = function() {
        $('#global-loader').removeClass('hidden').addClass('flex');
    };

    window.hideLoader = function() {
        $('#global-loader').addClass('hidden').removeClass('flex');
    };
});
