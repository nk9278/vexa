<?php
// includes/database.php
require_once __DIR__ . '/config.php';

function getDBConnection() {
    global $dbConfig;

    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], $options);
    } catch (\PDOException $e) {
        // Log the actual error, but don't output it to the user
        error_log($e->getMessage());

        // If critical DB failure, redirect to a 500 error page
        if (php_sapi_name() !== 'cli') {
            header("Location: " . BASE_URL . "errors/500.php");
            exit;
        }
        throw new \Exception("Database connection failed.");
    }
}
