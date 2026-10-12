<?php
/**
 * Database Connection & Schema Initialization
 * ASHIRU TRADITIONAL BONE SETTER
 */

class Database {
    private static ?PDO $instance = null;
    private static string $dbFile = __DIR__ . '/../database/data.sqlite';

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dir = dirname(self::$dbFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            try {
                self::$instance = new PDO('sqlite:' . self::$dbFile);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                // Enable WAL mode for concurrent performance
                self::$instance->exec('PRAGMA journal_mode = WAL;');

                // Initialize tables if needed
                self::initSchema(self::$instance);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Database connection failed: ' . $e->getMessage()
                ]);
                exit;
            }
        }
        return self::$instance;
    }

    private static function initSchema(PDO $db): void {
        $db->exec("
            CREATE TABLE IF NOT EXISTS appointments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                reference_code TEXT UNIQUE NOT NULL,
                name TEXT NOT NULL,
                phone TEXT NOT NULL,
                sector TEXT DEFAULT 'orthopedic',
                service TEXT NOT NULL,
                urgency TEXT NOT NULL,
                preferred_date TEXT,
                consult_type TEXT,
                details TEXT,
                status TEXT DEFAULT 'Pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS emergency_dispatches (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                caller_name TEXT NOT NULL,
                caller_phone TEXT NOT NULL,
                injury_type TEXT NOT NULL,
                location TEXT,
                status TEXT DEFAULT 'Dispatched',
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS contact_inquiries (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT,
                phone TEXT,
                sector TEXT DEFAULT 'orthopedic',
                subject TEXT,
                message TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");
    }
}
