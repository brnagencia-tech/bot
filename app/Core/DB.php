<?php
namespace App\Core;

use PDO, PDOException;

class DB
{
    private static $instance = null;
    public static function getInstance()
    {
        if (self::$instance === null) {
            $dsn = sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8mb4',
                env('DB_HOST'), env('DB_DATABASE'), env('DB_PORT', 3306));
            try {
                self::$instance = new PDO($dsn, env('DB_USERNAME'), env('DB_PASSWORD'), [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                die('Erro de conexão: ' . $e->getMessage());
            }
        }
        return self::$instance;
    }
}
