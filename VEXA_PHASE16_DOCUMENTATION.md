# VEXA - PHASE 16 DOCUMENTATION
## SUPER ADMIN - SUBSCRIPTION, LICENSE & PLAN MANAGEMENT ENGINE

---

## 1. Newly Created Files
The following files were introduced to build the SaaS Subscription engine:

*   **APIs:**
    *   `api/super-admin/plans/create.php`: Handles the creation of billing tiers and enforces usage limits (`limit_employees`, `limit_projects`, etc.).
    *   `api/super-admin/plans/action.php`: Bulk action endpoint to activate, deactivate, or archive plans.
    *   `api/super-admin/subscriptions/assign.php`: Core logic for assigning a plan to a tenant. It calculates the expiry date based on the plan type (monthly, yearly, etc.) and auto-generates a unique `VEXA-XXXX-XXXX-XXXX` license key.

*   **UI Views:**
    *   `super-admin/plans/index.php`: A premium card-based list of all available plans, highlighting pricing, plan codes, and summarized feature limits.
    *   `super-admin/plans/create.php`: The form to configure a new plan, separating billing configuration from usage constraints.
    *   `super-admin/subscriptions/index.php`: The Master Subscription Dashboard. Displays KPI cards for active plans, trials, and upcoming renewals, followed by a data table of assigned licenses.
    *   `super-admin/subscriptions/assign.php`: The form interface enabling the Super Admin to map a `company` to a `plan`. Includes trial days overrides.
    *   `super-admin/subscriptions/view.php`: The License Details page. Visually maps out the tenant's current usage against the plan limits using dynamic progress bars.

## 2. Updated Files
*   `setup_db.php`: Appended the `saas_plans` and `saas_subscriptions` tables to the database schema. The structures were strictly typed for cross-compatibility (SQLite for local sandboxing, MySQL ready for prod).

## 3. Database Changes
*   **`saas_plans`:** Engineered to be the source of truth for features. Contains explicit integer limits (e.g., `limit_storage_mb`, `limit_projects`) and boolean feature toggles (`feature_gdrive`, `feature_leave`) ensuring the application can gracefully degrade based on the tenant's active plan.
*   **`saas_subscriptions`:** Maps the `company_id` to the `plan_id`. Enforces strict state management (`start_date`, `expiry_date`, `trial_days`) and holds the unique `license_key`. Foreign keys `ON DELETE CASCADE` ensure that wiping a company wipes their subscription securely.

## 4. Subscription Module Summary
The subscription system is fully active. A Super Admin can view all tenants, see exactly what plan they are on, and instantly determine if their subscription is `Active`, `Expired`, or in `Trial` mode.

## 5. Plan Management Summary
Plans are modular and heavily customizable. The UI supports creating plans with varying billing cycles (Monthly, Yearly, Lifetime). Archiving a plan (`status='archived'`) prevents it from being assigned to new tenants while keeping existing subscriptions intact.

## 6. License Management Summary
License generation is fully automated within `assign.php`. By executing `bin2hex(random_bytes())`, cryptographically random keys are assigned upon subscription creation. The `last_verified_at` column is prepped for future online "phone-home" verification hooks.

## 7. Usage Limit Summary
The `view.php` dashboard for an active subscription now reads the plan limits directly from the database and renders visual progress bars. If an employee limit is reached (e.g., 3 / 10), the bar displays the capacity, turning green, yellow, or red based on consumption thresholds.

## 8. Responsive Test Report
*   **Desktop:** The Plan Cards (`grid-cols-3`) and Usage Progress Bars sit cleanly beside the License Metadata cards.
*   **Tablet/Mobile:** CSS Grid properties automatically fold multi-column layouts into single columns. Data tables leverage `overflow-x-auto` to prevent viewport breaking.

## 9. Permission Test Report
All newly created files, both API and UI, explicitly invoke `requireSuperAdmin()`. Direct access via URL bypassing the login screen or attempting access via a non-root tenant user correctly triggers an HTTP 403 / Redirect.

## 10. Verification Report
**Cross-Check Validation:**
- [x] Plan Management CRUD working.
- [x] License Generation logic working.
- [x] Expiry date math (e.g., +1 month, +1 year) functional.
- [x] Usage Limit progress bars functional.
- [x] Activity Logs populated.
- [x] Validations (Unique Plan Code) working.
- [x] No PHP or SQL syntax errors detected.
- [x] Playwright integration tests passed successfully.

**Phase 16 Objective Achieved:** The SaaS Subscription and Licensing engine is fully operational. VEXA can now package its features into monetizable tiers and assign them to independent companies securely.