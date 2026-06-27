<?php
// api/auth/logout.php
require_once __DIR__ . '/../../includes/functions.php';

// Allow GET for simple link-based logout, but check session
logoutUser();

// Redirect to login
redirect(BASE_URL . 'auth/login.php');
