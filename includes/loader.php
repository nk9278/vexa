<?php
// includes/loader.php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) die('Direct access denied.');

// Empty generic loader file if specific modules need to inject early boot logic.
// Often used in MVC, but in standard PHP we keep it simple.
require_once __DIR__ . '/functions.php';
