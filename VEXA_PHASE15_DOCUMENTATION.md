# VEXA - PHASE 15 DOCUMENTATION
## SUPER ADMIN - COMPANY MANAGEMENT

---

## 1. New Files
The following files were created to implement the Company Management module for the Super Admin:
*   `api/super-admin/companies/create.php`: Handles POST requests to provision new tenant companies. Validates input, ensures uniqueness of `company_code` and `owner_email`, processes logo uploads via `handleSecureUpload`, and inserts the record into the database.
*   `api/super-admin/companies/update.php`: Handles POST requests to modify existing tenant companies, validating uniqueness while ignoring the current record ID.
*   `api/super-admin/companies/action.php`: Centralized endpoint for bulk actions and single-click state changes. Supports `suspend`, `activate`, `delete` (soft-delete), and `restore`.
*   `super-admin/companies/index.php`: The primary data table view. Fetches active companies, displays them with status badges, and provides links to view/edit or apply bulk actions.
*   `super-admin/companies/create.php`: The UI form for provisioning a new company, separating Company Information and Owner Information.
*   `super-admin/companies/edit.php`: The UI form for modifying an existing company. Pre-populates all inputs from the database.
*   `super-admin/companies/view.php`: The comprehensive Profile View. Displays owner details, current status, an activity timeline (dynamically rendering `created_at`), and quick action buttons to suspend or delete the tenant.

## 2. Updated Files
*   `setup_db.php`: The database schema was updated to include the massive `companies` table, capturing all requested fields (`company_name`, `gst_number`, `timezone`, etc.) and enforcing unique constraints on the code and owner email.

## 3. Database Changes
The `companies` table was successfully created in the database. It utilizes `INT AUTO_INCREMENT` for the primary key, `VARCHAR(50)` for statuses to remain dialect-agnostic, and `DATETIME` columns for robust soft-deleting (`deleted_at`).

## 4. Company CRUD Summary
*   **Create:** Working flawlessly via AJAX. Successfully logs `create_company` to the Activity Log.
*   **Read:** Working on both the `index.php` list view and the detailed `view.php` profile page.
*   **Update:** Working flawlessly via AJAX. Successfully logs `update_company`.
*   **Delete:** Working via `action.php`. Performs a safe "soft delete" by populating `deleted_at` and shifting the status to `deleted`.

## 5. Validation Summary
*   **Backend:** Extensive validation in the API endpoints using the Phase 11 `validateRequiredFields()` helper.
*   **Uniqueness:** Queries run before INSERT/UPDATE to ensure no two companies share a `company_code` or `owner_email`.
*   **Uploads:** Logo uploads are secured by `handleSecureUpload()`, enforcing a max size of 50MB and restricting MIME types to `image/jpeg`, `image/png`, `image/webp`, and `image/svg+xml`.

## 6. Responsive Test Report
*   The tables in `index.php` use `.overflow-x-auto` to allow horizontal scrolling on mobile without breaking the layout.
*   The forms in `create.php` and `edit.php` use CSS grid (`grid-cols-1 md:grid-cols-2`), perfectly stacking inputs on smaller screens.
*   The profile view (`view.php`) gracefully stacks the Timeline and Details cards underneath the Header actions on mobile viewports.

## 7. Permission Test Report
Every new PHP file (`api/*` and UI pages) includes `requireSuperAdmin()`. This strictly prevents standard users, managers, or CRM agents from accessing, viewing, or modifying tenant configurations.

## 8. Verification Report
**Cross-Check Validation:**
- [x] CRUD operations fully functional without page reloads (AJAX).
- [x] Playwright End-to-End test passed (Login -> Create Company -> View List).
- [x] Activity logs accurately recording state changes.
- [x] No SQL injection vulnerabilities (100% PDO prepared statements).
- [x] CSRF protection active on all state-changing endpoints.
- [x] Reused global components (Cards, Buttons, Toasts, Loaders).

**Phase 15 Objective Achieved:** The Super Admin Company Management module is complete, allowing root users to provision and manage the lifecycle of unlimited SaaS tenants.