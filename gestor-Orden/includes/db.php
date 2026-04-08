<?php
// includes/db.php — Conexion + helpers globales

if (!function_exists('getDB')) {
    function getDB(): PDO {
        static $pdo = null;
        if ($pdo) return $pdo;
        try {
            $pdo = new PDO(
                "mysql:host=localhost;port=3306;dbname=gestor_ordenes;charset=utf8mb4",
                'root', '',
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'error' => 'BD: ' . $e->getMessage()]);
            exit;
        }
        return $pdo;
    }
}

if (!function_exists('jsonResponse')) {
    function jsonResponse(mixed $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

if (!function_exists('getInput')) {
    function getInput(): array {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!$data) $data = $_POST;
        return $data ?: [];
    }
}