# VEXA - PHASE 4 DOCUMENTATION
## DATABASE ARCHITECTURE & DATA FOUNDATION

---

## 1. Data Domains

This section categorizes the core data domains of the system.

*   **Super Admin Domain:** Companies, Subscriptions, Global Settings, System Logs, Plans.
*   **Tenant Core Domain:** Users, Roles, Permissions, Departments.
*   **CRM Domain:** Leads, Clients, Services, Contracts.
*   **Project Domain:** Projects, Tasks, Deliverables, Milestones.
*   **HR Domain:** Attendance, Leaves, Holidays.
*   **Financial Domain:** Payments, Invoices, Expenses.
*   **Communication Domain:** Notifications, Comments, Mentions, Activity Logs.
*   **Storage Domain:** Uploads, Media Metadata, Google Drive Mapping.
*   **Utility Domain:** Calendar Events, Dashboard Widgets, Reports.

---

## 2. Domain Ownership

Defines which user persona inherently "owns" or manages specific data domains.

*   **Super Admin Owns:**
    *   Companies / Tenants
    *   SaaS Subscriptions & Billing
    *   Global Feature Toggles
*   **Owner (Tenant Level) Owns:**
    *   Employees & Departments
    *   Tenant Settings & Branding
    *   Roles & Permissions definition
    *   Global Tenant Financials (Payments/Revenue)
*   **CRM / Manager Owns:**
    *   Clients & Leads
    *   Projects (High-level creation)
    *   Deliverables & Deadlines
    *   Client Invoicing
*   **Employee Owns:**
    *   Assigned Tasks (Status updates)
    *   Task-specific Uploads & Comments
    *   Personal Attendance Records & Leave Requests

---

## 3. Entity Relationships

The relational architecture ensures strict multi-tenant isolation while maintaining fluid internal connections.

**Core Hierarchy:**
```text
One Super Admin -> Many Companies
One Company -> Many Employees, Clients, Projects, Settings, Roles, Departments
```

**Operational Workflow:**
```text
One CRM -> Many Clients
One Client -> Many Projects, Invoices, Drive Folders
One Project -> Many Tasks, Deliverables, Uploads
One Task -> Many Assignees (N:N), Comments, Uploads, Activity Logs
```

**HR Workflow:**
```text
One Employee -> Many Attendance Records, Leave Requests, Assigned Tasks, Comments
One Manager -> Many Employees (Hierarchy mapping), Leave Approvals
```

**System Workflow:**
```text
One User -> Many Notifications, Activity Logs
One Task/Project -> Many Notifications (Polymorphic)
```

---

## 4. Record Lifecycle

Entities move through strict, definable states to support filtering and analytics.

*   **Client Lifecycle:**
    `Lead (Unqualified) -> Qualified -> Client (Active) -> Paused -> Closed -> Archived`
*   **Task Lifecycle:**
    `Created -> Assigned -> Accepted -> Working -> Pending Review -> Revisions Required -> Approved -> Delivered -> Completed -> Archived`
*   **Project Lifecycle:**
    `Draft -> Active -> On Hold -> Delivered -> Closed -> Archived`
*   **Leave Request Lifecycle:**
    `Draft -> Pending Approval -> Approved / Rejected -> Taken -> Archived`
*   **Invoice Lifecycle:**
    `Draft -> Sent -> Partially Paid -> Paid -> Overdue -> Cancelled`

---

## 5. Naming Standards

*   **Database Names:** lowercase, snake_case (e.g., `vexa_saas_prod`).
*   **Table Names:** lowercase, snake_case, plural nouns (e.g., `companies`, `users`, `project_tasks`). Pivot tables combine singular names alphabetically (e.g., `task_user`).
*   **Column Names:** lowercase, snake_case (e.g., `first_name`, `is_active`).
*   **Primary Keys:** Always `id` (BigInt, Unsigned, Auto-Increment) or UUIDs (for API/Mobile future-proofing).
*   **Foreign Keys:** `[singular_table_name]_id` (e.g., `company_id`, `user_id`).
*   **Indexes:** `idx_[table_name]_[column_name]` (e.g., `idx_users_company_id`).
*   **File Naming (Physical Storage):** `[company_id]/[client_id]/[project_id]/[timestamp]_[sanitized_file_name].[ext]`.
*   **Timestamps:** Every table must have `created_at`, `updated_at`, and `deleted_at` (for soft deletes).

---

## 6. Audit Strategy

A dedicated Activity Log system tracks all state-changing actions for compliance and debugging.

*   **Authentication:** Login success/failure, Logout, Password Reset, MFA setup.
*   **CRUD Operations:** Creation, modification, and deletion of Users, Clients, Projects, Tasks.
*   **Operational Status:** Task status changes (e.g., "Working" to "Pending Review"), Assignment changes.
*   **Financial:** Payment receipt generation, Invoice status changes.
*   **Administrative:** Permission alterations, Role assignments, Settings modifications.
*   **Data Structure:** Logs capture `user_id`, `company_id`, `action_type`, `entity_type`, `entity_id`, `old_payload` (JSON), `new_payload` (JSON), `ip_address`, and `user_agent`.

---

## 7. Storage Strategy

*   **Profile Images / Logos:** Stored locally or on standard cloud storage (S3). Publicly accessible via CDN.
*   **Task Attachments / Internal Media:** Stored securely. Requires authenticated session and matching `company_id` to download.
*   **Google Drive Integration (External):** Database only stores external folder/file IDs and URLs. Actual large graphics/videos remain in Google Drive to save SaaS storage costs.
*   **Backups & Logs:** Stored in isolated, encrypted cold storage (e.g., AWS S3 Glacier).

---

## 8. Security Planning

*   **Tenant Isolation (Critical):** EVERY query fetching tenant data MUST include `WHERE company_id = ?`.
*   **Sensitive Data:** Passwords hashed using bcrypt/Argon2. API keys (Google Drive, Payment Gateways) encrypted at rest in the database.
*   **Hidden Data:** Soft delete (`deleted_at`) ensures data is never permanently erased immediately, allowing recovery while hiding it from standard UI queries.
*   **Access Rules:** Authorization middleware verifies User Role Permissions before executing any database write operation.
*   **Future MFA:** Database schemas support `mfa_secret` and `mfa_enabled` columns.

---

## 9. Performance Planning

*   **Indexing:** B-Tree indexes on all foreign keys (`company_id`, `user_id`, etc.) and frequently filtered columns (`status`, `created_at`).
*   **Pagination:** Limit/Offset pagination for UI tables; cursor-based pagination for future REST APIs to handle large datasets efficiently.
*   **Search:** Use Full-Text Search indexing for heavy text fields (Task descriptions, Client notes).
*   **Archiving:** Historical data (e.g., completed tasks from 2 years ago) should be soft-deleted or moved to a cold-storage schema to keep active tables small and fast.
*   **Caching:** Read-heavy, infrequently changing data (Permissions, Roles, Settings) cached in memory (Redis/Memcached).

---

## 10. Backup Planning

*   **System Backup:** Full automated daily snapshots of the entire database.
*   **Company-wise Backup:** Logical backups (mysqldump segmented by `company_id`) allowing the Super Admin to restore a single tenant's data without affecting others.
*   **Logs Backup:** Monthly rotation of activity logs into compressed archives.
*   **Media Backup:** Cloud storage bucket replication across multiple availability zones.

---

## 11. Scalability Planning

*   **Database Scaling:**
    *   *Day 1:* Monolithic Relational Database (MySQL) for simplicity.
    *   *1,000+ Companies:* Read-Replicas for heavy dashboard analytics.
    *   *100,000+ Clients:* Sharding the database by `company_id` (each cluster of companies lives on a separate database server).
*   **Stateless Architecture:** Ensure the database handles state, allowing the PHP application servers to scale horizontally behind a load balancer.
*   **File Storage:** Offload all heavy media to AWS S3 or Google Cloud Storage immediately to prevent disk I/O bottlenecks on the database server.
*   **AI Readiness:** Text-heavy tables (Notes, Comments, Descriptions) structured cleanly to allow easy extraction for future AI vector embedding.

---

## 12. Verification Report

**Cross-Check Validation:**
- [x] All data domains identified and ownership assigned.
- [x] Relationships logically map from Super Admin down to the granular Task level.
- [x] Strict tenant isolation rules established (`company_id` requirement).
- [x] Scalability planned for large datasets (Indexing, Sharding, Archiving).
- [x] Comprehensive audit and soft-delete strategies implemented.
- [x] No SQL, PHP, or executable code generated.

**Phase 4 Objective Achieved:** The complete multi-tenant database architecture and data foundation planning is complete. The system is structurally prepared for Phase 5 development.