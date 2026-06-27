# VEXA - PHASE 11 DOCUMENTATION
## CORE CONFIGURATION & SYSTEM FOUNDATION

---

## 1. Updated Folder Tree
No new directories were required for Phase 11. The foundation leverages the extensive folder structure established in Phase 10. The core action took place entirely within the `includes/` directory.

## 2. Newly Created Files
*   `includes/error-handler.php`: Establishes a global custom error and exception handler (`vexaErrorHandler` and `vexaExceptionHandler`). It intercepts fatal errors and safely logs them while serving clean 500 error pages to end-users (or detailed traces in dev mode).
*   `includes/logger.php`: Introduces two core logging mechanics:
    *   `writeSysLog()`: A file-based logger for internal system states and SMTP errors.
    *   `activityLog()`: A database-backed logger tracking user activities (`action`, `entity_type`, JSON payload diffs), essential for the future audit trail.
*   `includes/upload.php`: Features `handleSecureUpload()`. Validates file size, enforces strict MIME-type checks using `finfo`, creates isolated tenant directories (e.g., `/uploads/12/2024/11/`), and safely sanitizes and hashes file names to prevent collisions or directory traversal attacks.
*   `includes/mail.php`: Features `sendMail()`. Currently acts as a smart placeholder. In development mode, it routes emails to `writeSysLog()` to prevent spamming. It establishes the exact function signature required for the future PHPMailer/Symfony integration.
*   `includes/helpers.php`: A suite of global utility functions: `jsonResponse()`, `redirect()`, flash messaging (`setFlashMessage`, `getFlashMessage`), `formatDate()`, `formatCurrency()`, and UI components like `getStatusBadge()`.

## 3. Modified Files
*   `includes/constants.php`: Expanded to include strict path constants (`BASE_PATH`, `UPLOADS_PATH`, `TEMP_PATH`), environment definitions (`development` vs `production`), and global constraints like `MAX_UPLOAD_SIZE`. Added direct access prevention.
*   `includes/config.php`: Centralized `$dbConfig` into an array structure supporting future multi-tenant mappings. Configured `$sessionConfig` for timeouts and strict cookie rules. Added a placeholder `$mailConfig`. Added direct access prevention.
*   `includes/database.php`: Completely refactored into a Singleton class (`Database::getInstance()`). This ensures only one PDO connection is opened per request lifecycle, reducing database overhead.
*   `includes/session.php`: Upgraded to include inactivity timeout (`checkSessionTimeout()`) and periodic session ID regeneration (`secureSessionRegenerate()`) to thwart session fixation attacks.
*   `includes/security.php`: Added data sanitization helpers (`sanitizeInput()`), secure random token generators, and native password hashing wrappers (`hashPassword`, `verifyPassword`).
*   `includes/functions.php`: Refactored to act as the "Master Bootstrapper". Including this single file initializes constants, database, session, security, error handling, and helpers. Added generic form validation (`validateRequiredFields`).
*   `includes/*.php`: All core include files were updated to include a hard `die('Direct access denied.');` check if accessed directly via URL.

## 4. Configuration Summary
*   **Environment Driven:** The system behaves differently based on the `ENVIRONMENT` constant. In `production`, PHP errors are hidden, cookies are forced `secure`, and emails might process. In `development`, full stack traces are rendered.
*   **Timezone:** Globally anchored to `UTC` via `DEFAULT_TIMEZONE`.
*   **Extensibility:** Configuration arrays (`$dbConfig`, `$sessionConfig`) are centralized, meaning environment-specific files (like `.env`) can easily map onto these variables in the future without changing core logic.

## 5. Security Summary
*   **File Upload Hardening:** Relies on `finfo` for MIME-type validation, ignoring the easily spoofed `$_FILES['type']`.
*   **Exception Safety:** Raw database credentials or query syntax errors will never leak to the UI in production due to the centralized `vexaExceptionHandler`.
*   **Session Hardening:** Sessions automatically expire after an inactivity period defined in `config.php`, and session IDs are regenerated every 30 minutes.
*   **Direct Access Prevention:** No core file in the `includes/` directory can be executed directly by navigating to its URL.
*   **CSRF & XSS:** Active token verification and output escaping (`esc()`) are fully established.

## 6. Verification Report

**Cross-Check Validation:**
- [x] All base configurations created (DB, Session, Constants).
- [x] File upload constraints built (MIME validation, secure naming, folder creation).
- [x] Error handling and system logging implemented.
- [x] Direct access protection added to all includes.
- [x] PHP Syntax verified (`php -l` passed on all generated files).
- [x] Strict adherence to project rules: No authentication flow, no business modules, no database tables created.

**Phase 11 Objective Achieved:** The core configuration and system foundation are robust, secure, and production-ready. The system is structurally prepared for Phase 12.