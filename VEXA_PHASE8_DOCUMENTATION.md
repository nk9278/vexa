# VEXA - PHASE 8 DOCUMENTATION
## DEVELOPMENT EXECUTION STANDARDS & PROJECT RULEBOOK

---

## 1. Folder Standards

*   **Structure:** Modules must be self-contained within root-level folders (e.g., `/crm/`, `/tasks/`).
*   **Depth:** Maximum folder depth should not exceed 3 levels (e.g., `/module/includes/components/`).
*   **Naming:** All folders must be lowercase and hyphenated (e.g., `super-admin`, `google-drive`).
*   **Shared Files:** Global components (headers, footers, sidebars) reside in `/includes/`.
*   **Assets:** All public files must live in `/assets/css/`, `/assets/js/`, `/assets/images/`.
*   **Uploads:** Must be strictly isolated by tenant `company_id` and located outside of web-accessible paths if storing sensitive data, or within `/uploads/[company_id]/` for public/safe assets.

## 2. File Standards

*   **PHP Files:** Lowercase, hyphenated (e.g., `edit-client.php`). Only core PHP 8+ features allowed.
*   **JavaScript:** Lowercase, hyphenated. Modularized per page or globally bound in `app.js`.
*   **CSS:** Managed exclusively via Tailwind CSS. Custom CSS should be minimal and stored in `app.css`.
*   **AJAX Handlers:** Must reside in an `/api/` or specific module `/ajax/` folder. Must return strictly formatted JSON (`{"status": "success|error", "message": "...", "data": {}}`).
*   **Configuration:** Global variables stored in `/includes/config.php`.

## 3. Coding Standards

*   **Indentation:** Use 4 spaces for PHP/JS. Use 2 spaces for HTML/Tailwind.
*   **Comments:** DocBlocks mandatory for all PHP classes/functions explaining parameters and return types. Complex logic must have inline comments.
*   **Variable Naming:** `camelCase` for variables and functions. `PascalCase` for Classes. `UPPER_SNAKE_CASE` for Constants.
*   **Error Handling:** Use `try...catch` blocks for all database and API interactions. Fail gracefully with generic user-friendly messages and log the exact technical error via `error_log()`.
*   **No Frameworks:** No Laravel, no CodeIgniter. Utilize pure PDO for database interactions.

## 4. Module Standards

*   **Required Structure:** Every module folder must contain an `index.php` (list view) and appropriate action files (`create.php`, `edit.php`).
*   **Independence:** Modules should not hardcode dependencies. Rely on foreign keys and global functions.
*   **Verification:** A module is only complete when its navigation, breadcrumbs, permissions, and responsive views are implemented.

## 5. Page Standards

*   **Mandatory Elements:** Header, Breadcrumb, Action Buttons (Top Right), Main Content (Cards/Tables), Footer.
*   **Permission Check:** Line 1 of every protected PHP file MUST call a centralized permission verification function.
*   **Responsiveness:** Every page must work flawlessly on desktop (1024px+), tablet (768px), and mobile (320px).

## 6. UI Standards

*   **Consistency:** "Every module must look identical." Use standard Tailwind utility classes defined in Phase 3.
*   **Colors & Typography:** Strict adherence to the `indigo` primary, `slate` secondary color palette, and `Inter` font.
*   **Animations:** Keep animations under 300ms. Fade and subtle translate only. No jarring bounce effects.

## 7. Dashboard Standards

*   **Structure:** CSS Grid layouts. KPIs at the top, charts in the middle, recent activity/tables at the bottom.
*   **Performance:** Dashboard widgets MUST load asynchronously via AJAX to prevent slow initial page loads.
*   **Data Isolation:** All dashboard queries MUST strictly filter by the active user's `company_id` and role permissions.

## 8. Table Standards

*   **Features:** Every major data table must have a search bar, status filter, column sorting, and limit/offset pagination.
*   **Bulk Actions:** Checkboxes on the left for bulk delete, assign, or status change.
*   **Responsive:** On mobile devices, tables must stack into individual summary cards rather than forcing horizontal scrolling.

## 9. Form Standards

*   **Validation:** Dual-layer validation required: Frontend (HTML5 + jQuery validation) and Backend (Strict PHP validation/sanitization).
*   **Feedback:** Inline validation errors in red beneath the input. Success/Error Toast messages upon AJAX submission.
*   **Buttons:** "Submit" (Primary, Right), "Cancel" (Ghost, Left).
*   **State:** Buttons must disable and show a loading spinner during submission to prevent double POST requests.

## 10. Permission Standards

*   **Zero Trust:** Never trust the UI. If a button is hidden via CSS, the corresponding PHP backend script MUST still verify the user's role/permission before executing the query.
*   **Granular Checks:** Use specific action-based checks (e.g., `if(!hasPermission('delete_client')) { throwError(); }`).

## 11. Activity Log Standards

*   **Mandatory Logging:** Login, Logout, Create, Update, Delete, Assign, Approve, Reject, Upload, Download, Settings Change, Role Change.
*   **No Exceptions:** If a state changes in the database, it must be logged in the `activity_logs` table with `user_id`, `company_id`, and payload diffs.

## 12. Security Standards

*   **SQL Injection:** 100% adherence to PDO Prepared Statements. No raw queries using variable concatenation.
*   **XSS:** All user-generated text output to HTML must be escaped using `htmlspecialchars()`.
*   **CSRF:** Every `<form>` and state-changing AJAX request must include and verify a CSRF token.
*   **Isolation:** The `WHERE company_id = ?` clause is mandatory on every tenant query.

## 13. Testing Standards

*   **Devices:** Verify every page on Desktop, Tablet, and Mobile viewport sizes.
*   **Roles:** Test pages under different user roles (Owner, Manager, Employee) to verify the UI hides elements correctly and the backend rejects unauthorized actions.
*   **Performance:** Ensure no page takes longer than 1.5 seconds to load data (utilize indexing and AJAX).

## 14. Quality Standards

*   **Code Review:** Check for DRY (Don't Repeat Yourself) violations. Ensure file/folder naming matches the rulebook.
*   **Database:** Ensure no orphan records remain during deletions (enforce foreign key constraints).
*   **Console:** Zero JavaScript console errors or warnings in production views.

## 15. Completion Checklist

A development phase is NOT complete until:
- [ ] All pages render correctly without HTML breaking.
- [ ] No dead links or broken buttons exist.
- [ ] Zero PHP Notices, Warnings, or Fatal Errors in server logs.
- [ ] Zero SQL syntax or logic errors.
- [ ] Permission checks verified by attempting unauthorized direct URL access.
- [ ] Responsive design verified on a physical mobile device or strict emulator.

## 16. Future Expansion Rules

*   **Modularity:** New features (e.g., HR, Payroll) must be built as new self-contained folders. Do not deeply intertwine new logic into existing unrelated modules. Use database foreign keys to link entities rather than hardcoding.
*   **APIs:** Backend PHP logic should be separated from HTML presentation as much as possible so the core functions can be exposed to a REST API in the future.

## 17. Final Verification Report

**Cross-Check Validation:**
- [x] Rulebook explicitly forbids code generation, frameworks, and CMS tools.
- [x] Comprehensive guidelines established for files, folders, security, and UI consistency.
- [x] Strict Multi-Tenant isolation rules codified into development standards.
- [x] Quality control and Completion Checklist established for all future coding phases.
- [x] No PHP, HTML, CSS, or SQL code generated in this document.

**Phase 8 Objective Achieved:** The permanent VEXA Development Execution Standards & Project Rulebook is established. All engineers and future AI agents must strictly adhere to this document during the coding phases.