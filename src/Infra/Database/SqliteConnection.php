<?php

namespace App\Infra\Database;

use PDO;

class SqliteConnection
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dbPath = __DIR__ . '/../../../database/database.sqlite';
            
            if (!file_exists($dbPath)) {
                touch($dbPath);
                $pdo = new PDO('sqlite:' . $dbPath);
                $pdo->exec("CREATE TABLE IF NOT EXISTS tickets (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    plate TEXT NOT NULL,
                    type TEXT NOT NULL,
                    entry_time TEXT NOT NULL,
                    exit_time TEXT,
                    total_amount REAL
                )");
            } else {
                $pdo = new PDO('sqlite:' . $dbPath);
            }
            
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$instance = $pdo;
        }
        return self::$instance;
    }
}