# VEXA - PHASE 9 DOCUMENTATION
## MASTER SCREEN SPECIFICATION & UI DOCUMENTATION

---

## 1. Screen Inventory & 2. Screen Purpose

A comprehensive inventory of all primary screens within the VEXA application.

### Authentication Module
*   **Login:** Secure entry point. Access: All users. Parent: None.
*   **Forgot Password:** Password recovery request. Access: All users. Parent: Login.
*   **Reset Password:** Setting a new password via email link. Access: All users. Parent: Forgot Password.

### Dashboard Module
*   **Owner Dashboard:** High-level tenant overview (Revenue, Health). Access: Owner. Parent: Root.
*   **Manager Dashboard:** Department overview (Task load, Leaves). Access: Manager. Parent: Root.
*   **CRM Dashboard:** Sales pipeline and lead metrics. Access: CRM, Owner. Parent: Root.
*   **Employee Dashboard:** Personal tasks and attendance. Access: Employee. Parent: Root.
*   **Super Admin Dashboard:** Global SaaS metrics. Access: Super Admin. Parent: Root.

### Clients Module
*   **Client List:** Master table of all clients. Access: Owner, CRM, Manager. Parent: CRM.
*   **Client Details (Master View):** Comprehensive tabbed view of a single client. Access: Owner, CRM, Manager. Parent: Client List.
*   **Add/Edit Client (Modal/Form):** Data entry for client records. Access: Owner, CRM. Parent: Client List.

### Projects & Tasks Module
*   **Project List:** Master table of active/archived projects. Access: Owner, Manager, CRM, Employee (assigned). Parent: Projects.
*   **Project Details:** Milestone and overall progress tracker. Access: Owner, Manager, CRM. Parent: Project List.
*   **Task Board (Kanban/List):** Execution view for daily work. Access: All (filtered by assignment). Parent: Projects.
*   **Task Details (Drawer):** Deep dive into a single task (description, files, comments). Access: All (filtered by assignment). Parent: Task Board.

### Team & HR Module
*   **Employee List:** Directory of staff. Access: Owner, Manager. Parent: Team.
*   **Employee Details:** Profile, attendance history, leave balance. Access: Owner, Manager. Parent: Employee List.
*   **Attendance Log:** Master grid of all punch-ins. Access: Owner, Manager. Parent: Team.
*   **Leave Management:** Request (Employee) and Approval (Manager) lists. Access: All. Parent: Team.

### Finance & Settings
*   **Invoices & Payments:** Financial tracking tables. Access: Owner, CRM. Parent: Finance.
*   **Tenant Settings:** Configuration for the company. Access: Owner. Parent: Settings.

---

## 3. Layout Standards

Every standard screen (e.g., Client List, Task Board) follows a strict layout pattern:
1.  **Header:** Contains Global Search, '+' Quick Action button, Notification Bell, User Avatar.
2.  **Breadcrumb:** Positioned below the header (e.g., `Home > CRM > Client List`).
3.  **Page Title & Quick Buttons:** H1 Title aligned left; primary action buttons (e.g., `Export`, `+ Add New`) aligned right.
4.  **Statistics Cards (Optional):** Top row, highlighting key metrics for the current view (e.g., "Total Active Clients").
5.  **Search & Filters Bar:** Horizontal bar spanning the width of the content area. Contains text search, dropdown filters, and date pickers.
6.  **Main Content Area:** Typically a Data Table, a Kanban Board, or a Tabbed Interface (for Details pages).
7.  **Side Panels / Drawers:** Hidden by default. Slides out from the right for detailed views (e.g., Task Details) to keep the user in context.
8.  **Footer:** Simple copyright and version number.

---

## 4. Components

Reusable UI components standardized across the application.
*   **Cards:** Soft shadow (`shadow-sm`), rounded corners (`rounded-xl`), white background. Used for metrics and Kanban items.
*   **Badges:** Pill-shaped (`rounded-full`), padded (`px-2 py-1`), small text (`text-xs`). Used exclusively for Statuses.
*   **Progress Bars:** Thin, horizontally filling bars with percentage indicators. Used in Project Details and Task tracking.
*   **Avatar:** Circular images or initial-based placeholders (`rounded-full`).
*   **Dropdowns:** Triggered by ellipsis (`...`) or buttons. Floating panels with soft shadows.
*   **File Upload:** Drag-and-drop dashed zones with a central "Browse" button.

---

## 5. Cards Specification

*   **KPI Card:**
    *   *Purpose:* Display a single high-level metric.
    *   *Displayed Data:* Title, Large Numeric Value, Icon, Delta Indicator (+5% vs last month).
    *   *Refresh Method:* AJAX on load, or via WebSocket (future).
*   **Task Kanban Card:**
    *   *Purpose:* Represent a unit of work on the Task Board.
    *   *Displayed Data:* Task Title, Project Name, Due Date, Assigned Avatars, Status Badge, Attachment Count Icon, Comment Count Icon.
*   **Client Summary Card (in Details View):**
    *   *Purpose:* Quick overview of client health.
    *   *Displayed Data:* Total Revenue, Active Projects, Pending Invoices.

---

## 6. Tables Specification

All standard data tables (Clients, Employees, Invoices) share these features:
*   **Columns:** Explicitly defined per module (e.g., Client List: Name, Company, Email, Phone, Status, Actions).
*   **Status Column:** Always uses a pill-shaped Badge component.
*   **Search:** Dedicated text input filtering the specific table.
*   **Sorting:** Clickable column headers with visual up/down arrows.
*   **Filters:** Dropdowns above the table (e.g., Filter by Status, Department).
*   **Bulk Actions:** A checkbox column on the far left. Selecting rows reveals a "Bulk Actions" dropdown (Delete, Archive, Change Status).
*   **Quick Actions:** The far-right column contains an ellipsis (`...`) dropdown for Row Actions (Edit, View, Delete).
*   **Pagination:** Bottom-right aligned, standard Previous/Next and page numbers. Shows "Showing 1 to 20 of 150 entries".
*   **Export:** A button above the table to export the current filtered view to CSV.

---

## 7. Forms Specification

Forms are standardized for both full-page and modal usage.
*   **Fields:** Top-aligned labels, subtle borders (`border-slate-300`), clear focus states (`ring-indigo-500`).
*   **Validation:** HTML5 required attributes + AJAX backend validation. Errors appear as red text below the specific input.
*   **Required/Optional:** Required fields marked with a red asterisk (`*`).
*   **Uploads:** Drag-and-drop zone. Previews generated instantly for images. List view generated for documents.
*   **Action Area (Footer):** Fixed at the bottom of the form/modal. "Cancel" on the left (Ghost button). "Save/Submit" on the right (Primary button).
*   **Reset/Cancel:** Clears the form or closes the modal without saving. Prompts for confirmation if data was entered.

---

## 8. Status System

A unified vocabulary and color-coding system for entity states across all modules.

*   **Task Statuses:** `Draft` (Slate), `Assigned` (Sky), `Working` (Indigo), `Pending Review` (Amber), `Revisions Required` (Rose), `Approved` (Emerald), `Delivered` (Emerald), `Archived` (Slate).
*   **Client Statuses:** `Lead` (Sky), `Active` (Emerald), `Paused` (Amber), `Closed/Churned` (Rose).
*   **Project Statuses:** `Planning` (Slate), `Active` (Indigo), `On Hold` (Amber), `Completed` (Emerald).
*   **Payment/Invoice Statuses:** `Draft` (Slate), `Sent` (Sky), `Partially Paid` (Indigo), `Paid` (Emerald), `Overdue` (Rose).
*   **Leave Statuses:** `Pending Approval` (Amber), `Approved` (Emerald), `Rejected` (Rose).

---

## 9. Responsive Behaviour

The UI adapts intelligently, not just mechanically.

*   **Desktop (1024px+):** Fixed Left Sidebar. Expansive data tables. Modals use standard widths. Task Drawer slides from the right taking 40% width.
*   **Tablet (768px - 1023px):** Sidebar collapses to icons only. Data tables may truncate less important columns. Task Drawer takes 60% width.
*   **Mobile (< 768px):**
    *   *Navigation:* Sidebar disappears entirely. Replaced by a fixed Bottom Navigation Bar and a Drawer Menu (Hamburger).
    *   *Tables:* Convert from horizontal rows into stacked Cards (one card per row of data).
    *   *Task Drawer:* Becomes a full-screen overlay.
    *   *FAB:* Floating Action Button replaces header '+ Add' buttons for easier thumb access.
    *   *Swipe Actions:* List items can be swiped left/right to reveal quick actions (e.g., Swipe to Complete).

---

## 10. Future Expansion

The screen architecture leaves physical space and logical routing for future modules:
*   **HR / Payroll:** Will inject into the "Team" menu. Dashboards will accommodate a "Payroll Summary" widget.
*   **Inventory:** Will inject into a new "Assets" menu item.
*   **Vendor / Client Portals:** Designed as entirely separate, stripped-down dashboard views, reusing the standard Table and Form components but limiting navigation.
*   **AI Integration:** Reserved space in the Global Header for an "AI Assistant" spark icon, which will trigger a right-side chat drawer globally.

---

## 11. Verification Report

**Cross-Check Validation:**
- [x] All screens from previous planning phases are documented here.
- [x] Layout patterns established (Headers, Filters, Tables, Side Panels).
- [x] Reusable components identified for frontend consistency.
- [x] Comprehensive status color system defined for all entities.
- [x] Mobile-specific UX behaviors (Bottom Nav, FAB, Stacked Tables) explicitly mandated.
- [x] No PHP, HTML, CSS, or SQL code generated.

**Phase 9 Objective Achieved:** The Master Screen Specification and UI Documentation is complete. Every screen and component behavior is fully described, serving as the official UI blueprint for development.