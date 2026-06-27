# VEXA - PHASE 18 DOCUMENTATION
## OWNER DASHBOARD FOUNDATION & COMPANY CONTROL CENTER

---

## 1. New Files
*   `owner/includes/sidebar.php`: Dedicated tenant navigation sidebar featuring modules like Operations (CRM, Projects, Tasks), Team (Employees, Attendance), and Finance (Invoices).
*   `owner/includes/topbar.php`: Context-aware header rendering the logged-in Owner's initials, name, and notifications. Includes a global search placeholder spanning across tenant data.
*   `owner/dashboard/index.php`: The Master Control Center for the tenant. Integrates KPI summary cards, quick action links, progress rings (usage monitor), and a recent notifications timeline.
*   `owner/dashboard/company-profile.php`: A clean, read-only view of the tenant's global settings, legal formatting, contact info, and registration metadata.
*   `owner/dashboard/activity-center.php`: Fetches data from `activity_logs` scoped strictly by `company_id`. Renders an interactive vertical timeline of events across the workspace.
*   `owner/profile/index.php`: User management view allowing the owner to update their name, phone number, and securely cycle their password.
*   `api/owner/profile/update.php`: Safe, CSRF-protected backend endpoint to handle profile detail mutations and password hashing/updating.

## 2. Updated Files
*   `includes/auth.php`: Added the `requireOwner()` middleware.

## 3. Dashboard Structure
The Owner Dashboard leverages CSS Grid for modular widget placement. The top layer comprises four critical KPI metrics (Clients, Projects, Tasks, Employees). The secondary layer hosts "Quick Actions" for rapid data entry without navigating to specific modules, adjacent to a large area reserved for future `Chart.js` implementations. The bottom layer hosts active notifications and real-time subscription limit progress bars.

## 4. Permission Summary
The `requireOwner()` middleware strictly guards the `/owner/` namespace.
*   It ensures the user is logged in.
*   It ensures the user is **NOT** a Super Admin (`role_id == 1`), enforcing strict boundary separation between SaaS administration and tenant operation.
*   It queries the database to ensure the active user's role is specifically named 'owner'. If a standard employee attempts access, they are kicked to the `errors/403.php` "Access Denied" page.

## 5. Responsive Test Report
*   **Desktop:** Sidebar locked left. Flexible central panel.
*   **Tablet:** KPI grid transitions from 4-columns to 2-columns.
*   **Mobile:** The sidebar converts to an off-canvas drawer. The KPI grid stacks into a single column. The FAB placeholder remains anchored to the bottom-right corner for quick data entry.

## 6. Verification Report
**Cross-Check Validation:**
- [x] All base Owner pages constructed utilizing reusable CSS/Tailwind components.
- [x] `requireOwner()` middleware properly shields tenant scopes from Super Admins and unauthorized users.
- [x] Profile Update APIs correctly sanitize inputs, hash passwords securely, and restrict updates to the user's `company_id`.
- [x] `activity_logs` correctly query only events matching the session's `company_id`.
- [x] No business logic for unbuilt modules (CRM, Projects) was generated, leaving perfect placeholders.
- [x] Playwright integration test passed cleanly.

**Phase 18 Objective Achieved:** The Owner Dashboard Foundation is complete. It stands ready as the central hub to anchor all future operational modules (CRM, Tasks, HR).