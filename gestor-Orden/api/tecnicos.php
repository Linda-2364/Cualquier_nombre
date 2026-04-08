<?php
// api/tecnicos.php
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
    if ($id) {
        $st = $db->prepare('
            SELECT t.*, CONCAT(p.nombre," ",p.apellido) AS nombre,
                   p.email, p.telefono, p.ci,
                   p.nombre AS nombres, p.apellido AS apellidos,
                   u.username, u.activo AS usuarioActivo
            FROM tecnico t
            JOIN persona p ON t.idPersona = p.idPersona
            LEFT JOIN usuario u ON u.idPersona = p.idPersona
            WHERE t.idTecnico = ?
        ');
        $st->execute([$id]);
        $row = $st->fetch();
        $row ? jsonResponse($row) : jsonResponse(['error' => 'No encontrado'], 404);
    }
    $rows = $db->query('
        SELECT t.idTecnico, t.especialidad, t.nivelCertificacion,
               CONCAT(p.nombre," ",p.apellido) AS nombre,
               p.nombre, p.apellido, p.email, p.telefono, p.ci,
               u.username, u.activo AS usuarioActivo
        FROM tecnico t
        JOIN persona p ON t.idPersona = p.idPersona
        LEFT JOIN usuario u ON u.idPersona = p.idPersona
        ORDER BY p.nombre
    ')->fetchAll();
    jsonResponse($rows);
}

if ($method === 'POST') {
    $d = getInput();
    try {
        $db->beginTransaction();

        // 1. Crear persona
        $st = $db->prepare('INSERT INTO persona (nombre,apellido,ci,email,telefono) VALUES (?,?,?,?,?)');
        $st->execute([
            $d['nombre']   ?? $d['nombres']   ?? '',
            $d['apellido'] ?? $d['apellidos'] ?? '',
            $d['ci']  ?? $d['dni']  ?? null,
            $d['email']    ?? null,
            $d['telefono'] ?? null,
        ]);
        $idPersona = $db->lastInsertId();

        // 2. Crear técnico
        $db->prepare('INSERT INTO tecnico (idPersona,especialidad,nivelCertificacion) VALUES (?,?,?)')
           ->execute([$idPersona, $d['especialidad'] ?? null, $d['nivelCertificacion'] ?? null]);

        // 3. Si viene username+password, crear usuario con rol TECNICO
        if (!empty($d['username']) && !empty($d['password'])) {
            // Verificar que username no exista
            $stChk = $db->prepare('SELECT COUNT(*) FROM usuario WHERE username=?');
            $stChk->execute([$d['username']]);
            if ($stChk->fetchColumn() > 0) {
                throw new Exception('El username "' . $d['username'] . '" ya existe. Elige otro.');
            }
            // Obtener idRol de TECNICO
            $stRol = $db->prepare('SELECT idRol FROM rol WHERE nombre="TECNICO" LIMIT 1');
            $stRol->execute();
            $rol = $stRol->fetch();
            if (!$rol) throw new Exception('Rol TECNICO no encontrado en la BD');

            $hash = password_hash($d['password'], PASSWORD_BCRYPT, ['cost' => 10]);
            $db->prepare('INSERT INTO usuario (idPersona,idRol,username,passwordHash,activo) VALUES (?,?,?,?,1)')
               ->execute([$idPersona, $rol['idRol'], $d['username'], $hash]);
        }

        $db->commit();
        $msg = !empty($d['username'])
            ? 'Técnico creado con acceso a la plataforma como "' . $d['username'] . '"'
            : 'Técnico creado correctamente';
        jsonResponse(['ok' => true, 'mensaje' => $msg]);

    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['ok' => false, 'error' => $e->getMessage()], 500);
    }
}

if ($method === 'PUT' && $id) {
    $d = getInput();
    try {
        $db->beginTransaction();

        // Obtener idPersona del técnico
        $st = $db->prepare('SELECT t.idPersona FROM tecnico t WHERE t.idTecnico=?');
        $st->execute([$id]);
        $tec = $st->fetch();
        if (!$tec) jsonResponse(['error' => 'Técnico no encontrado'], 404);

        // Actualizar persona
        $db->prepare('UPDATE persona SET nombre=?,apellido=?,ci=?,email=?,telefono=? WHERE idPersona=?')
           ->execute([
               $d['nombre']   ?? $d['nombres']   ?? '',
               $d['apellido'] ?? $d['apellidos'] ?? '',
               $d['ci']  ?? $d['dni']  ?? null,
               $d['email']    ?? null,
               $d['telefono'] ?? null,
               $tec['idPersona'],
           ]);

        // Actualizar técnico
        $db->prepare('UPDATE tecnico SET especialidad=?,nivelCertificacion=? WHERE idTecnico=?')
           ->execute([$d['especialidad'] ?? null, $d['nivelCertificacion'] ?? null, $id]);

        // Si viene nueva contraseña, actualizar en usuario asociado
        if (!empty($d['password'])) {
            if (strlen($d['password']) < 6) throw new Exception('La contraseña debe tener al menos 6 caracteres');
            $hash = password_hash($d['password'], PASSWORD_BCRYPT, ['cost' => 10]);
            $stU = $db->prepare('SELECT idUsuario FROM usuario WHERE idPersona=?');
            $stU->execute([$tec['idPersona']]);
            $usr = $stU->fetch();
            if ($usr) {
                $db->prepare('UPDATE usuario SET passwordHash=?, activo=1, intentosFallidos=0 WHERE idUsuario=?')
                   ->execute([$hash, $usr['idUsuario']]);
            }
        }

        $db->commit();
        jsonResponse(['ok' => true, 'mensaje' => 'Técnico actualizado correctamente']);

    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['ok' => false, 'error' => $e->getMessage()], 500);
    }
}

if ($method === 'DELETE' && $id) {
    try {
        $db->beginTransaction();
        // Obtener idPersona
        $st = $db->prepare('SELECT idPersona FROM tecnico WHERE idTecnico=?');
        $st->execute([$id]);
        $tec = $st->fetch();
        if ($tec) {
            // Desactivar usuario asociado si existe
            $db->prepare('UPDATE usuario SET activo=0 WHERE idPersona=?')->execute([$tec['idPersona']]);
        }
        // Eliminar técnico
        $db->prepare('DELETE FROM tecnico WHERE idTecnico=?')->execute([$id]);
        $db->commit();
        jsonResponse(['ok' => true, 'mensaje' => 'Técnico eliminado']);
    } catch(Exception $e) {
        $db->rollBack();
        jsonResponse(['ok' => false, 'error' => $e->getMessage()], 500);
    }
}

jsonResponse(['error' => 'Metodo no permitido'], 405);