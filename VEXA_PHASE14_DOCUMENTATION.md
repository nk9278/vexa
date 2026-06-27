# VEXA - PHASE 14 DOCUMENTATION
## SUPER ADMIN FOUNDATION

---

## 1. Complete Folder Structure
The Super Admin module is isolated in the `/super-admin/` directory. Empty directories include `.gitkeep` to ensure version tracking.
```text
/super-admin/
  ├── companies/
  ├── dashboard/
  ├── includes/
  │   ├── sidebar.php
  │   └── topbar.php
  ├── licenses/
  ├── logs/
  ├── payments/
  ├── profile/
  ├── reports/
  ├── settings/
  └── support/
```

## 2. Created Pages
*   `super-admin/index.php`: The primary dashboard featuring KPI cards (Companies, Subscriptions, MRR), Chart.js placeholders, System Health progress bars, and a Recent Logins table.
*   `super-admin/profile/index.php`: Master profile management UI with form layouts for personal info and password changes.
*   `super-admin/settings/index.php`: System settings configuration UI (Application Name, Timezone, Maintenance Mode toggle).
*   `super-admin/settings/system-info.php`: A diagnostic table displaying live PHP version, Server Software, Upload limits, and App version.
*   `super-admin/logs/index.php`: Reusable data table layout for the immutable activity audit trail, complete with search and module filters.
*   `super-admin/support/index.php`: Support portal displaying license keys and quick action cards.

## 3. Updated Files
*   `includes/auth.php`: Added the `requireSuperAdmin()` middleware function. This function asserts that the user is logged in AND possesses `role_id == 1`. Unauthorized users are instantly redirected to `errors/403.php`.

## 4. Sidebar Structure
The Sidebar (`super-admin/includes/sidebar.php`) implements the premium dark theme (`bg-slate-900`) to differentiate it from the standard tenant view.
*   **Overview:** Dashboard
*   **SaaS Management:** Companies, Subscriptions, Licenses, Payments
*   **System:** Reports, Activity Logs, Support
*   **Footer:** System Settings, Secure Logout

## 5. Dashboard Layout Summary
The Dashboard utilizes the standard global wrapper established in Phase 10 (`.flex-1.flex-col`) containing the custom topbar and sidebar. The content area leverages CSS Grid (`grid-cols-1 md:grid-cols-2 lg:grid-cols-4`) to create a responsive, widget-based layout. It integrates the global `.card`, `.badge`, and `.btn-*` components to maintain UI consistency.

## 6. Responsive Verification
*   **Desktop:** Fixed dark sidebar on the left, full-width topbar, and 4-column KPI cards.
*   **Tablet:** Grid collapses to 2 columns for KPIs.
*   **Mobile:** The dark sidebar translates completely off-canvas (`-translate-x-full`). A hamburger menu in the topbar triggers the drawer overlay. KPI cards stack into a single column. The Floating Action Button (FAB) placeholder remains accessible.

## 7. Permission Verification
The Phase 14 foundation is entirely wrapped in `requireSuperAdmin()`. Any attempt to access `/super-admin/*` without an active session containing `role_id = 1` yields a 403 Access Denied page, preventing standard tenants from accessing SaaS-level controls.

## 8. Phase Completion Report
**Cross-Check Validation:**
- [x] Super Admin folder structure mapped and created.
- [x] Dedicated UI Layout (Sidebar, Topbar) engineered.
- [x] Base pages constructed using reusable CSS components without introducing database logic or CRUD operations.
- [x] Access securely restricted via `auth.php` middleware.
- [x] Playwright verification successfully completed on the new layout.

**Phase 14 Objective Achieved:** The complete Super Admin module foundation is built, styled, and secured. It is ready for SaaS management logic in future phases.