<?php
// api/repuestos.php
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
    if ($path === 'consumo') {
        try {
            $rows = $db->query('SELECT * FROM v_consumo_repuestos ORDER BY totalConsumido DESC')->fetchAll();
        } catch(Exception $e) {
            // Si la vista no existe devolver datos directos
            $rows = $db->query('
                SELECT r.idRepuesto, r.nombre, r.codigo, cr.nombre AS categoria,
                       r.stockActual, r.stockMinimo,
                       COALESCE(SUM(orp.cantidad),0) AS totalConsumido
                FROM repuesto r
                LEFT JOIN categoriarepuesto cr ON r.idCategoriaRepuesto = cr.idCategoriaRepuesto
                LEFT JOIN ordenrepuesto orp ON r.idRepuesto = orp.idRepuesto
                GROUP BY r.idRepuesto ORDER BY totalConsumido DESC
            ')->fetchAll();
        }
        jsonResponse($rows);
    }
    if ($path === 'categorias') {
        jsonResponse($db->query('SELECT * FROM categoriarepuesto ORDER BY nombre')->fetchAll());
    }
    if ($path === 'stock-bajo') {
        $rows = $db->query('
            SELECT r.*, cr.nombre AS categoria
            FROM repuesto r JOIN categoriarepuesto cr ON r.idCategoriaRepuesto = cr.idCategoriaRepuesto
            WHERE r.stockActual <= r.stockMinimo ORDER BY r.stockActual ASC
        ')->fetchAll();
        jsonResponse($rows);
    }
    $rows = $db->query('
        SELECT r.*, cr.nombre AS categoria
        FROM repuesto r
        JOIN categoriarepuesto cr ON r.idCategoriaRepuesto = cr.idCategoriaRepuesto
        ORDER BY r.nombre
    ')->fetchAll();
    jsonResponse($rows);
}

if ($method === 'POST') {
    $d = getInput();
    if (isset($d['idOrden'])) {
        // Registrar uso en orden
        $stR = $db->prepare('SELECT stockActual FROM repuesto WHERE idRepuesto=?');
        $stR->execute([$d['idRepuesto']]);
        $rep = $stR->fetch();
        if (!$rep) jsonResponse(['error' => 'Repuesto no encontrado'], 404);
        if ($rep['stockActual'] < $d['cantidad']) jsonResponse(['error' => 'Stock insuficiente'], 400);
        $db->prepare('INSERT INTO ordenrepuesto (idOrden,idRepuesto,cantidad,precioUnitario,observacion,fechaUso) VALUES (?,?,?,?,?,NOW())')
           ->execute([$d['idOrden'], $d['idRepuesto'], $d['cantidad'], $d['precioUnitario'] ?? 0, $d['observacion'] ?? null]);
        $db->prepare('UPDATE repuesto SET stockActual=stockActual-? WHERE idRepuesto=?')
           ->execute([$d['cantidad'], $d['idRepuesto']]);
        jsonResponse(['ok' => true, 'mensaje' => 'Repuesto registrado']);
    }
    $db->prepare('INSERT INTO repuesto (idCategoriaRepuesto,nombre,codigo,descripcion,stockActual,stockMinimo,precioUnitario) VALUES (?,?,?,?,?,?,?)')
       ->execute([$d['idCategoriaRepuesto'], $d['nombre'], $d['codigo'] ?? null, $d['descripcion'] ?? null, $d['stockActual'] ?? 0, $d['stockMinimo'] ?? 0, $d['precioUnitario'] ?? 0]);
    jsonResponse(['ok' => true, 'mensaje' => 'Repuesto creado']);
}

if ($method === 'PUT' && $id) {
    $d = getInput();
    if (isset($d['ajuste'])) {
        // Ajuste de stock +/-
        $op = $d['ajuste'] > 0 ? '+' : '-';
        $cant = abs((int)$d['ajuste']);
        $db->prepare("UPDATE repuesto SET stockActual=stockActual{$op}? WHERE idRepuesto=?")->execute([$cant, $id]);
        jsonResponse(['ok' => true, 'mensaje' => 'Stock ajustado']);
    }
    $db->prepare('UPDATE repuesto SET idCategoriaRepuesto=?,nombre=?,codigo=?,descripcion=?,stockActual=?,stockMinimo=?,precioUnitario=? WHERE idRepuesto=?')
       ->execute([$d['idCategoriaRepuesto'], $d['nombre'], $d['codigo'] ?? null, $d['descripcion'] ?? null, $d['stockActual'], $d['stockMinimo'], $d['precioUnitario'] ?? 0, $id]);
    jsonResponse(['ok' => true, 'mensaje' => 'Repuesto actualizado']);
}

if ($method === 'DELETE' && $id) {
    $db->prepare('DELETE FROM repuesto WHERE idRepuesto=?')->execute([$id]);
    jsonResponse(['ok' => true, 'mensaje' => 'Repuesto eliminado']);
}

jsonResponse(['error' => 'Metodo no permitido'], 405);