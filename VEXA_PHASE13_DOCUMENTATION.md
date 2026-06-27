# VEXA - PHASE 13 DOCUMENTATION
## AUTHENTICATION ENGINE, SESSION MANAGEMENT & LOGIN LOGIC

---

## 1. Newly Created Files
*   `includes/auth.php`: The core Authentication Engine containing logic for `attemptLogin`, `logoutUser`, session middleware (`requireLogin`, `requireGuest`), "Remember Me" validation (`checkRememberMe`), and `generatePasswordResetToken`.
*   `api/auth/login.php`: Receives AJAX POST requests from the UI, verifies CSRF, sanitizes input, and returns success/error JSON.
*   `api/auth/logout.php`: Destroys the session and redirects the user to the login screen.
*   `api/auth/forgot-password.php`: Validates emails and triggers the token generator.
*   `api/auth/reset-password.php`: Validates password resets against the database tokens.
*   `setup_db.php`: A local setup script utilizing SQLite to generate the necessary test tables and a test user (`admin@vexa.app`).

## 2. Updated Files
*   `includes/functions.php`: Modified to globally include `auth.php` and invoke `checkRememberMe()` early in the request lifecycle to automatically log users in.
*   `includes/session.php`: Modified to actually intercept expired sessions, differentiate between standard requests and AJAX requests, and correctly redirect to `/auth/session-expired.php`.
*   `includes/topbar.php`: The "Sign out" link was hooked up to point to `/api/auth/logout.php`.
*   `auth/*.php`: The UI files from Phase 12 were updated with `requireGuest()` middleware at the top to prevent logged-in users from seeing the login screen. The form submission handlers were wired to use jQuery AJAX against the new `/api/auth/` endpoints.

## 3. Authentication Flow Summary
1.  **Form Submit:** User submits `login.php`. JS intercepts, triggers loader, sends POST to `api/auth/login.php`.
2.  **API Check:** API validates CSRF token and sanitizes email string.
3.  **Engine Verification:** `attemptLogin()` checks the `login_attempts` table. If > 5 failures within 15 minutes, brute force protection halts the process.
4.  **Database Match:** Engine queries `users` table, verifies using `password_verify()`, and checks `status == 'active'`.
5.  **Session Establishment:** User ID, Company ID, and Role ID are written to `$_SESSION`. `secureSessionRegenerate()` fires to prevent fixation.
6.  **Return:** API returns JSON `success`. JS redirects user to `/dashboard/`.

## 4. Session Flow Summary
*   **Idle Timeout:** Handled by `checkSessionTimeout()` in `session.php`. Default 8-hour expiry. Redirects to `/auth/session-expired.php` if triggered.
*   **Regeneration:** `secureSessionRegenerate()` rotates the PHPSESSID every 30 minutes to mitigate session hijacking.
*   **Remember Me:** If checked, a 64-character cryptographically secure token is generated, hashed, and stored in `user_tokens`. A cookie is set with `user_id:raw_token`. If a session dies, `checkRememberMe()` verifies the cookie against the DB and logs the user back in, immediately rotating the token for security.

## 5. Security Features Implemented
*   **Password Hashing:** Strict use of native `password_hash()` (bcrypt/Argon2). No plaintext, MD5, or SHA1.
*   **Brute Force Protection:** Limits accounts to 5 failed attempts per 15 minutes.
*   **CSRF Protection:** Validated on every single authentication endpoint.
*   **XSS Protection:** Used heavily on the UI end.
*   **Auth Middleware:** Implemented `requireLogin()` and `requireGuest()` functions.

## 6. Activity Log Summary
Every core authentication event relies on the global `activityLog()` method created in Phase 11.
*   `login_failed`: Logs IP and Email to `login_attempts` and writes to `activity_logs`.
*   `login_success`: Wires into the activity table including the user's isolated `company_id`.
*   `logout`: Tracks explicit user logouts.
*   `forgot_password_requested`: Tracks when tokens are generated.
*   `password_reset_completed`: Tracks when the final reset is processed.

## 7. Testing Results
Playwright UI testing was executed against a live SQLite database.
*   **Invalid Login:** Successfully returned `Invalid email or password` via Toast.
*   **Successful Login:** Properly established session and redirected to the `/dashboard/` layout placeholder.
*   **Logout:** Verified that accessing `api/auth/logout.php` clears the session and redirects to the login screen.
*   **Remember Me:** Cookie generation and DB token insertion confirmed.

**Phase 13 Objective Achieved:** A complete, secure, multi-tenant capable authentication engine has been connected to the Phase 12 UI, ready to secure the remainder of the VEXA SaaS application.