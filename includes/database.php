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

        // For local Sandbox testing, fall back to SQLite if requested by config
        if (isset($config['driver']) && $config['driver'] === 'sqlite') {
            $dsn = "sqlite:" . $config['path'];
            $user = null;
            $pass = null;
        } else {
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            $user = $config['user'];
            $pass = $config['pass'];
        }

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->connection = new PDO($dsn, $user, $pass, $options);
            if (isset($config['driver']) && $config['driver'] === 'sqlite') {
                $this->connection->exec('PRAGMA foreign_keys = ON;');
            }
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
