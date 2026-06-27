<?php
// includes/database.php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) die('Direct access denied.');

require_once __DIR__ . '/config.php';

/**
 * Singleton Database Class
 */
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        global $dbConfig;

        $config = $dbConfig['default'];
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->connection = new PDO($dsn, $config['user'], $config['pass'], $options);
        } catch (\PDOException $e) {
            require_once __DIR__ . '/logger.php';
            writeSysLog('critical', "PDO Connection Failed: " . $e->getMessage());
            throw new \Exception("Database connection failed. Check server logs.");
        }
    }

    // Prevent cloning of the instance
    private function __clone() {}

    // Prevent unserializing
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }

    /**
     * Get the Singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Get the PDO connection object
     */
    public function getConnection() {
        return $this->connection;
    }
}
