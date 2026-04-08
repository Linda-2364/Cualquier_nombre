<?php
// api/equipos.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
require_once __DIR__ . '/../includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$path   = trim($_GET['path'] ?? '', '/');
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;
$db     = getDB();

if ($method === 'GET') {
    if ($path === 'categorias') jsonResponse($db->query('SELECT * FROM categoriaequipo ORDER BY nombre')->fetchAll());
    if ($path === 'ubicaciones') jsonResponse($db->query('SELECT * FROM ubicacion ORDER BY nombre')->fetchAll());
    $rows = $db->query('
        SELECT e.*, ce.nombre AS categoria, u.nombre AS ubicacion
        FROM equipo e
        JOIN categoriaequipo ce ON e.idCategoriaEquipo = ce.idCategoriaEquipo
        JOIN ubicacion u        ON e.idUbicacion       = u.idUbicacion
        WHERE e.activo = 1 ORDER BY e.nombre
    ')->fetchAll();
    jsonResponse($rows);
}

if ($method === 'POST') {
    $d = getInput();
    $db->prepare('INSERT INTO equipo (idCategoriaEquipo,idUbicacion,nombre,marca,modelo,numeroSerie,fechaAdquisicion) VALUES (?,?,?,?,?,?,?)')
       ->execute([$d['idCategoriaEquipo'], $d['idUbicacion'], $d['nombre'], $d['marca'] ?? null, $d['modelo'] ?? null, $d['numeroSerie'] ?? null, $d['fechaAdquisicion'] ?? null]);
    jsonResponse(['ok' => true, 'mensaje' => 'Equipo creado']);
}

if ($method === 'PUT' && $id) {
    $d = getInput();
    $db->prepare('UPDATE equipo SET idCategoriaEquipo=?,idUbicacion=?,nombre=?,marca=?,modelo=?,numeroSerie=?,fechaAdquisicion=? WHERE idEquipo=?')
       ->execute([$d['idCategoriaEquipo'], $d['idUbicacion'], $d['nombre'], $d['marca'] ?? null, $d['modelo'] ?? null, $d['numeroSerie'] ?? null, $d['fechaAdquisicion'] ?? null, $id]);
    jsonResponse(['ok' => true, 'mensaje' => 'Equipo actualizado']);
}

if ($method === 'DELETE' && $id) {
    $db->prepare('UPDATE equipo SET activo=0 WHERE idEquipo=?')->execute([$id]);
    jsonResponse(['ok' => true, 'mensaje' => 'Equipo desactivado']);
}

jsonResponse(['error' => 'Metodo no permitido'], 405);