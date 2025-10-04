<?php
namespace Taskboard\Infrastructure\Database;

use PDO;
use PDOException;
use RuntimeException;

final class Connection
{
    public static function make(): PDO
    {
        $dsn  = $_ENV['DB_DSN']  ?? '';
        $user = $_ENV['DB_USER'] ?? '';
        $pass = $_ENV['DB_PASS'] ?? '';

        if ($dsn === '') {
            throw new RuntimeException('Database configuration missing.');
        }


        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];


        if (!empty($_ENV['DB_SSL_CA'])) {
            $options[\PDO::MYSQL_ATTR_SSL_CA] = $_ENV['DB_SSL_CA'];
        }

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
            return $pdo;
        } catch (PDOException $e) {
            error_log('DB connection failed: ' . $e->getMessage());
            throw new RuntimeException('Unable to connect to database.');
        }
    }
    
}
