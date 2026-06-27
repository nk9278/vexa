# VEXA - PHASE 17 DOCUMENTATION
## COMPANY ONBOARDING ENGINE & WORKSPACE INITIALIZATION

---

## 1. New Files
*   `includes/onboarding.php`: Contains the `initializeCompanyWorkspace($companyId, $ownerData)` function. This acts as the master bootstrapper for every newly provisioned SaaS tenant, automating the setup of database schema prerequisites and physical file systems.

## 2. Updated Files
*   `setup_db.php`: Appended several foundational tables required by every tenant company (`roles`, `permissions`, `role_permissions`, `departments`, `company_settings`, and `master_data`). Modified the `users` table to include `department_id`, `first_name`, `last_name`, and `profile_image`.
*   `api/super-admin/companies/create.php`: Integrated the Onboarding Engine. When a Super Admin submits the "Create Company" form, this API captures the temporary owner password, creates the company, and immediately passes the new `company_id` and owner payload into `initializeCompanyWorkspace()`.
*   `super-admin/companies/create.php`: Appended the "Temporary Password" field into the Owner Information card so the Super Admin can set the initial credentials for the new tenant.

## 3. Database Changes
Six new tables were introduced, isolated tightly by `company_id` (except for the global `permissions` catalog). The relationships ensure that roles and departments remain strictly siloed per tenant.

## 4. Workspace Initialization Summary
The moment a Super Admin clicks "Create Company", the following occurs completely silently and automatically:
1.  **Permissions Generation:** Global system permissions are ensured.
2.  **Role Creation:** 14 default roles are spun up, mapped to the specific `company_id`.
3.  **Permission Mapping:** Standardized role-to-permission mappings are executed (e.g., 'Manager' gets 'manage_team').
4.  **Department Creation:** 11 default departments are generated for the tenant.
5.  **Master Data:** Standardized tags for Priorities, Statuses, and Leave Types are generated.
6.  **Owner Account:** The first `user` record is injected, securely hashed, and tied to the 'Owner' role.
7.  **Settings:** Default business hours, timezone, and language preferences are stored.
8.  **Physical Storage:** `mkdir` recursively builds the tenant's exact folder tree (`uploads/{id}/documents`, `uploads/{id}/graphics`, etc.) complete with `.gitkeep` and `index.html` blocks to prevent direct directory traversal.

## 5. Owner Account Summary
The first active user account is created dynamically during this flow. The API splits the single string `owner_name` into `first_name` and `last_name`. The raw password supplied by the Super Admin is hashed immediately using `password_hash()` via the `hashPassword()` helper before DB insertion.

## 6. Roles Created
Fourteen default roles are initialized: `Owner`, `Manager`, `CRM`, `Graphic Designer`, `Video Editor`, `Content Writer`, `Photographer`, `SEO Executive`, `Ads Manager`, `Web Developer`, `App Developer`, `Account Executive`, `Reception`, `Intern`.

## 7. Departments Created
Eleven default departments are initialized: `Management`, `Sales`, `CRM`, `Design`, `Video`, `Content`, `Photography`, `SEO`, `Development`, `Accounts`, `Administration`.

## 8. Master Data Summary
The system boots 19 default `master_data` rows mapping values like 'Task Priority' (Low, Medium, High) and 'Leave Types' to their respective `.badge-*` UI color classes.

## 9. Validation Summary
*   **Duplicate Prevention:** API rejects duplicate `company_code` or `owner_email` globally.
*   **Password Security:** The onboarding payload securely passes the raw password directly into the hasher. It is never stored plain-text, nor is it written to the `activity_logs`.
*   **Transactions:** The entire DB provisioning sequence is wrapped in a `PDO::beginTransaction()`. If any step fails, the entire workspace generation rolls back.

## 10. Verification Report
**Cross-Check Validation:**
- [x] Onboarding Engine successfully intercepts the Company Creation process.
- [x] Owner accounts log in successfully using the provided temporary credentials.
- [x] `roles`, `permissions`, and `departments` tables populate correctly per tenant.
- [x] Physical upload directories are dynamically generated per tenant and secured.
- [x] All database changes executed using `INTEGER PRIMARY KEY AUTOINCREMENT` for SQLite sandbox compatibility.
- [x] Playwright integration test passed cleanly.

**Phase 17 Objective Achieved:** The Company Provisioning Engine is complete. New tenants are instantly provided with fully-fledged workspaces, isolated permissions, and dynamic file structures.