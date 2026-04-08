<?php
// api/usuarios.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
require_once __DIR__ . '/../includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;
$db     = getDB();

if ($method === 'GET') {
    $rows = $db->query('
        SELECT u.idUsuario, u.username, u.activo, u.ultimoAcceso, u.intentosFallidos,
               u.fechaCreacion,
               r.nombre AS nombreRol, r.idRol,
               p.nombre, p.apellido, p.email, p.telefono, p.ci,
               CONCAT(p.nombre," ",p.apellido) AS nombre
        FROM usuario u
        JOIN rol     r ON u.idRol     = r.idRol
        JOIN persona p ON u.idPersona = p.idPersona
        ORDER BY p.nombre
    ')->fetchAll();
    jsonResponse($rows);
}

if ($method === 'POST') {
    $d = getInput();
    if (empty($d['password'])) jsonResponse(['error' => 'Contrasena requerida'], 400);
    try {
        $db->beginTransaction();
        $st = $db->prepare('INSERT INTO persona (nombre,apellido,ci,email,telefono) VALUES (?,?,?,?,?)');
        $st->execute([$d['nombre'] ?? $d['nombres'], $d['apellido'] ?? $d['apellidos'], $d['ci'] ?? $d['dni'] ?? null, $d['email'] ?? null, $d['telefono'] ?? null]);
        $idP = $db->lastInsertId();
        $stRol = $db->prepare('SELECT idRol FROM rol WHERE nombre=?');
        $stRol->execute([$d['rol']]);
        $rol = $stRol->fetch();
        if (!$rol) throw new Exception('Rol no encontrado: ' . $d['rol']);
        $hash = password_hash($d['password'], PASSWORD_DEFAULT);
        $db->prepare('INSERT INTO usuario (idPersona,idRol,username,passwordHash,activo) VALUES (?,?,?,?,1)')
           ->execute([$idP, $rol['idRol'], $d['username'], $hash]);
        $db->commit();
        jsonResponse(['ok' => true, 'mensaje' => 'Usuario creado']);
    } catch(Exception $e) {
        $db->rollBack();
        jsonResponse(['error' => $e->getMessage()], 500);
    }
}

if ($method === 'PUT' && $id) {
    $d = getInput();
    $action = $_GET['action'] ?? '';

    if ($action === 'pwd') {
        // Cambiar contrasena
        $st = $db->prepare('SELECT passwordHash FROM usuario WHERE idUsuario=?');
        $st->execute([$id]);
        $u = $st->fetch();
        if (!$u || !password_verify($d['passwordActual'] ?? '', $u['passwordHash']))
            jsonResponse(['ok' => false, 'error' => 'Contrasena actual incorrecta']);
        $db->prepare('UPDATE usuario SET passwordHash=? WHERE idUsuario=?')
           ->execute([password_hash($d['passwordNuevo'], PASSWORD_DEFAULT), $id]);
        jsonResponse(['ok' => true, 'mensaje' => 'Contrasena actualizada']);
    }

    if (isset($d['bloquear'])) {
        $db->prepare('UPDATE usuario SET activo=?,intentosFallidos=0 WHERE idUsuario=?')
           ->execute([$d['bloquear'] ? 0 : 1, $id]);
        jsonResponse(['ok' => true, 'mensaje' => $d['bloquear'] ? 'Usuario bloqueado' : 'Usuario desbloqueado']);
    }

    try {
        $db->beginTransaction();
        $st = $db->prepare('SELECT idPersona FROM usuario WHERE idUsuario=?');
        $st->execute([$id]);
        $u = $st->fetch();
        if (!$u) jsonResponse(['error' => 'No encontrado'], 404);
        $db->prepare('UPDATE persona SET nombre=?,apellido=?,ci=?,email=?,telefono=? WHERE idPersona=?')
           ->execute([$d['nombre'] ?? $d['nombres'], $d['apellido'] ?? $d['apellidos'], $d['ci'] ?? null, $d['email'] ?? null, $d['telefono'] ?? null, $u['idPersona']]);
        $stRol = $db->prepare('SELECT idRol FROM rol WHERE nombre=?');
        $stRol->execute([$d['rol']]);
        $rol = $stRol->fetch();
        if (!$rol) throw new Exception('Rol no encontrado');
        $upd = 'UPDATE usuario SET username=?,idRol=?';
        $params = [$d['username'], $rol['idRol']];
        if (!empty($d['password'])) { $upd .= ',passwordHash=?'; $params[] = password_hash($d['password'], PASSWORD_DEFAULT); }
        $upd .= ' WHERE idUsuario=?'; $params[] = $id;
        $db->prepare($upd)->execute($params);
        $db->commit();
        jsonResponse(['ok' => true, 'mensaje' => 'Usuario actualizado']);
    } catch(Exception $e) {
        $db->rollBack();
        jsonResponse(['error' => $e->getMessage()], 500);
    }
}

if ($method === 'DELETE' && $id) {
    $db->prepare('UPDATE usuario SET activo=0 WHERE idUsuario=?')->execute([$id]);
    jsonResponse(['ok' => true, 'mensaje' => 'Usuario desactivado']);
}

jsonResponse(['error' => 'Metodo no permitido'], 405);