<?php
// api/catalogos.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
require_once __DIR__ . '/../includes/db.php';

$tipo = $_GET['tipo'] ?? '';
$db   = getDB();

switch ($tipo) {
    case 'prioridades':
        // Devuelve nombre como nombrePrioridad para compatibilidad con el JS antiguo
        $rows = $db->query("SELECT idPrioridad, nombre, nombre AS nombrePrioridad, nivel FROM prioridad ORDER BY nivel")->fetchAll();
        jsonResponse($rows);
        break;
    case 'estados':
        $rows = $db->query("SELECT idEstado, nombre, nombre AS nombreEstado FROM estado ORDER BY idEstado")->fetchAll();
        jsonResponse($rows);
        break;
    case 'roles':
        $rows = $db->query("SELECT idRol, nombre FROM rol ORDER BY idRol")->fetchAll();
        jsonResponse($rows);
        break;
    default:
        jsonResponse(['error' => 'Tipo no valido. Use: prioridades, estados, roles'], 400);
}