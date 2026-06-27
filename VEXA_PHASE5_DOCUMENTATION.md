# VEXA - PHASE 5 DOCUMENTATION
## COMPLETE DATABASE DESIGN, TABLE PLANNING & RELATIONSHIP DOCUMENTATION

---

## 1. Complete Module Wise Table List & 2. Purpose of Every Table

This section outlines every required table grouped by module, defining their purpose, dependencies, and structure constraints. All tables inherently require `id` (PK), `company_id` (FK), `created_at`, `updated_at`, and `deleted_at` (Soft Delete), except where noted (e.g., Super Admin tables).

### Super Admin / Core SaaS Module
*   **`saas_subscriptions`**: Tracks tenant subscription tiers, limits, and billing cycles. (Dependencies: `companies`)
*   **`saas_plans`**: Defines the available plans (e.g., Basic, Pro, Enterprise) and their feature limits.

### Company & User Module
*   **`companies`**: The master tenant record. Holds core business info. (Dependencies: `saas_plans`. NO `company_id` field).
*   **`users`**: Represents all human entities logging into the system (Owners, Managers, Staff). (Dependencies: `roles`, `departments`).
*   **`roles`**: Defines access control profiles (e.g., "CRM Manager").
*   **`role_permissions`**: Maps specific application permissions to roles (Pivot table).

### HR & Operations Module
*   **`departments`**: Logical grouping of users (e.g., "Design", "SEO").
*   **`attendance`**: Daily punch-in/out records. (Dependencies: `users`).
*   **`leaves`**: Time-off requests and their approval statuses. (Dependencies: `users`, `leave_types`).
*   **`holidays`**: Company-wide non-working days.

### CRM & Client Module
*   **`leads`**: Potential clients currently in the sales pipeline.
*   **`clients`**: Active/Archived businesses utilizing the agency's services.
*   **`client_services`**: Links a client to a recurring service package (e.g., "Monthly SEO"). (Dependencies: `clients`, `services`).

### Projects & Tasks Module
*   **`projects`**: High-level container for client deliverables. (Dependencies: `clients`).
*   **`tasks`**: Atomic units of work. (Dependencies: `projects`, `users`).
*   **`task_assignees`**: Maps multiple users to a single task (Pivot table: `task_id`, `user_id`).
*   **`deliverables`**: Milestones or specific end-products attached to a project.

### Financial Module
*   **`invoices`**: Billing records issued to clients. (Dependencies: `clients`).
*   **`payments`**: Records of transactions received against invoices. (Dependencies: `invoices`).

### Utilities & Communication Module
*   **`notifications`**: System alerts generated for users. (Dependencies: `users`).
*   **`comments`**: Polymorphic table for communication on tasks, projects, or leads. (Dependencies: `users`, polymorphic `entity_id` / `entity_type`).

### Storage & Files Module
*   **`uploads`**: Polymorphic table tracking physical files uploaded to the server/S3.
*   **`google_drive_mappings`**: Maps internal entities (Clients/Projects) to external Google Drive Folder IDs.

---

## 3. Relationships Documentation

The database relies on strict foreign key constraints to maintain data integrity and tenant isolation.

*   **Super Admin Flow:**
    `One Plan` -> `Many Companies`
    `One Company` -> `Many Subscriptions`
*   **User & Access Flow:**
    `One Company` -> `Many Roles` -> `Many Permissions (N:N)`
    `One Company` -> `Many Departments`
    `One Department` -> `Many Users`
    `One Role` -> `Many Users`
*   **CRM Flow:**
    `One Company` -> `Many Services`
    `One CRM User` -> `Many Leads`
    `One CRM User` -> `Many Clients`
    `One Client` -> `Many Client_Services`
*   **Project Flow:**
    `One Client` -> `Many Projects`
    `One Project` -> `Many Deliverables`
    `One Project` -> `Many Tasks`
    `One Task` -> `Many Users (Assignees N:N)`
*   **Interaction Flow:**
    `One Task` -> `Many Comments (Polymorphic)`
    `One Task` -> `Many Uploads (Polymorphic)`
    `One Project` -> `Many Google Drive Mappings`
*   **Financial Flow:**
    `One Client` -> `Many Invoices`
    `One Invoice` -> `Many Payments`

---

## 4. Lookup Tables

Lookup tables provide standardized, predefined values to maintain data consistency across the application, avoiding hardcoded magic strings.

*   **`sys_statuses`**: Global statuses (e.g., Active, Inactive, Pending, Approved, Archived).
*   **`sys_priorities`**: Low, Medium, High, Urgent.
*   **`sys_task_types`**: Bug, Feature, Content, Design, Review.
*   **`sys_leave_types`**: Sick, Casual, Maternity, Unpaid.
*   **`sys_payment_methods`**: Bank Transfer, Credit Card, PayPal, Stripe, Cash.
*   **`sys_client_categories`**: E-commerce, Real Estate, Healthcare, Technology.
*   **`sys_currencies`**: USD, EUR, GBP, INR (for multi-currency invoicing).

---

## 5. Settings Tables

Settings tables store configuration at both the global and tenant levels.

*   **`global_settings`**: Super Admin configurations (e.g., Maintenance Mode, Default SMTP, API rate limits).
*   **`company_settings`**: Key/Value pair table for tenant-specific configs.
    *   *Examples of keys:* `theme_color`, `timezone`, `date_format`, `invoice_prefix`, `working_hours_start`, `working_hours_end`.
*   **`user_preferences`**: Key/Value pair table for individual user settings (e.g., `email_notifications_enabled`, `dark_mode_enabled`).
*   **`integration_settings`**: Stores encrypted API keys and tokens for third-party services (e.g., `google_drive_refresh_token`, `whatsapp_api_key`).

---

## 6. Log Tables

Activity logging is separated into specific domains to prevent a single massive table from causing performance bottlenecks.

*   **`log_authentication`**: Tracks Login, Logout, Failed attempts, IP, and User Agent.
*   **`log_system`**: Tracks core system events (e.g., Backups executed, Cron jobs run).
*   **`log_entity_activity`**: A polymorphic table tracking CRUD operations.
    *   *Columns:* `company_id`, `user_id`, `action` (Created, Updated, Deleted), `entity_type` (e.g., 'Task', 'Client'), `entity_id`, `old_data` (JSON), `new_data` (JSON).
*   **`log_financials`**: Tracks sensitive payment state changes, invoice voiding, or refund issuances.

---

## 7. Reporting Tables

To ensure dashboard performance over time, analytical data is aggregated and cached.

*   **`report_daily_summaries`**: Cron-generated table storing end-of-day metrics (e.g., `total_tasks_completed`, `hours_logged`, `attendance_count`).
*   **`report_monthly_financials`**: Aggregated revenue, outstanding invoices, and expenses per month.
*   **`report_employee_performance`**: Pre-calculated scores based on task delivery times and QA approvals, updated weekly.

---

## 8. File Storage Documentation

The `uploads` table manages metadata for all files, while physical storage relies on external systems or partitioned local directories.

*   **Table Structure (`uploads`):** `id`, `company_id`, `uploader_id`, `entity_type` (Task/Project), `entity_id`, `file_name`, `original_name`, `mime_type`, `file_size`, `storage_disk` (local/s3/gdrive), `file_path`.
*   **Google Drive References (`google_drive_mappings`):** Stores `drive_folder_id`, `drive_file_id`, and `web_view_link` tied to internal `project_id` or `client_id`. Does not store the physical file.
*   **Profile Images:** Lightweight, stored in S3/Local `/uploads/profiles/`.
*   **Project Files:** Heavy graphics/videos offloaded to Google Drive or S3 to prevent database/server bloat.

---

## 9. Performance Planning

Designing for hundreds of thousands of records requires strict database rules.

*   **Indexes:**
    *   Every foreign key MUST be indexed (e.g., `company_id`, `client_id`).
    *   Columns used frequently in `WHERE` clauses (e.g., `status`, `deleted_at`) MUST be indexed.
*   **Search Optimization:**
    *   Implement Full-Text indexes on heavy text fields like `tasks.description` or `clients.notes`.
*   **Archive Strategy:**
    *   Tables like `tasks` and `notifications` will grow massively. Implement cron jobs to move records older than 12 months with a status of 'Completed' to identical schema archive tables (e.g., `tasks_archive`) to keep operational tables fast.
*   **Soft Deletes:** Use `deleted_at` globally. Ensure all application queries automatically append `AND deleted_at IS NULL`.

---

## 10. Future Expansion Planning

The schema is designed to easily bolt on future modules without structural refactoring.

*   **HR & Payroll:** `attendance` and `leaves` tables naturally link to a future `payroll_slips` table via `user_id`.
*   **Inventory:** A new `inventory_assets` table can link to `users` for equipment tracking (laptops, monitors).
*   **Mobile App / REST API:** The reliance on `company_id` and strict foreign keys ensures API endpoints can safely serve data using stateless JWT authentication.
*   **Client Portal:** The `users` table can accommodate a `client_user` role, restricted via the `role_permissions` table to only view data where `project.client_id` matches their profile.
*   **AI Assistant:** Standardized `log_entity_activity` (JSON payloads) and Polymorphic `comments` tables allow easy data extraction for training AI models or vector search embeddings.

---

## 11. Verification Report

**Cross-Check Validation:**
- [x] All primary operational modules (Auth, CRM, Projects, HR, Financials) have defined tables.
- [x] Pivot tables (`role_permissions`, `task_assignees`) designed for Many-to-Many relationships.
- [x] Strict Multi-Tenant rule (`company_id` on all tenant data) enforced.
- [x] Scalability strategies (Lookup tables, Log segregation, Aggregated Reporting tables) mapped out.
- [x] Soft Delete (`deleted_at`) and Audit tracking applied globally.
- [x] No PHP, HTML, CSS, or SQL code generated.

**Phase 5 Objective Achieved:** The complete database planning, table structure, and relationship documentation have been successfully established. The foundation is ready for actual schema migration coding in the next phase.