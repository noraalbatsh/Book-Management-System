<?php
/**
 * Simple PDO connection wrapper.
 *
 * This project is intended to run on XAMPP (Apache + PHP + MySQL/MariaDB).
 */

class DBConnection {
    private ?PDO $connection = null;

    public function __construct(
        private string $host,
        private string $dbName,
        private string $username,
        private string $password,
        private string $charset = 'utf8mb4'
    ) {
    }

    /**
     * Get a shared PDO instance.
     */
    public function getConnection(): PDO {
        if ($this->connection instanceof PDO) {
            return $this->connection;
        }

        $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset={$this->charset}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        return $this->connection;
    }
}
