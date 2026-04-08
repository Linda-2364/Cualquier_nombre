<?php
// api/ordenes.php
if (session_status() === PHP_SESSION_NONE) session_start();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

require_once __DIR__ . '/../includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$path   = trim($_GET['path'] ?? '', '/');
$parts  = explode('/', $path);
$id     = isset($parts[0]) && is_numeric($parts[0]) ? (int)$parts[0] : null;
$sub    = $parts[1] ?? null;
$db     = getDB();


// =======================
// ======== GET ==========
// =======================
if ($method === 'GET') {

    if ($path === 'vista/pendientes') {
        $rows = $db->query('SELECT * FROM v_ordenes_pendientes_por_tecnico')->fetchAll();
        jsonResponse($rows);
    }

    if ($id && $sub === 'repuestos') {
        $st = $db->prepare('
            SELECT orp.*, r.nombre, r.codigo
            FROM ordenrepuesto orp
            JOIN repuesto r ON orp.idRepuesto = r.idRepuesto
            WHERE orp.idOrden = ?
            ORDER BY orp.fechaUso DESC
        ');
        $st->execute([$id]);
        jsonResponse($st->fetchAll());
    }

    if ($id && $sub === 'tecnicos') {
        $st = $db->prepare('
            SELECT a.idAsignacion, a.rol, a.fechaAsignada,
                   t.idTecnico, t.especialidad,
                   CONCAT(p.nombre," ",p.apellido) AS tecnico
            FROM asignacion a
            JOIN tecnico t ON a.idTecnico = t.idTecnico
            JOIN persona p ON t.idPersona = p.idPersona
            WHERE a.idOrden = ?
        ');
        $st->execute([$id]);
        jsonResponse($st->fetchAll());
    }

    if ($id) {
        $st = $db->prepare('
            SELECT ot.*, 
                   eq.nombre AS equipo, 
                   e.nombre AS nombreEstado, 
                   p.nombre AS nombrePrioridad
            FROM ordentrabajo ot
            LEFT JOIN equipo eq ON ot.idEquipo = eq.idEquipo
            LEFT JOIN estado e  ON ot.idEstado = e.idEstado
            LEFT JOIN prioridad p ON ot.idPrioridad = p.idPrioridad
            WHERE ot.idOrden = ?
        ');
        $st->execute([$id]);
        $row = $st->fetch();
        $row ? jsonResponse($row) : jsonResponse(['error' => 'No encontrada'], 404);
    }

    // ===============================
    // 🔥 LISTAR TODAS (CORREGIDO)
    // ===============================
    $rows = $db->query('
        SELECT 
            ot.idOrden,
            ot.idEquipo,
            ot.idPrioridad,
            ot.idEstado,
            ot.titulo,
            ot.descripcion,
            ot.fechaCreacion,
            ot.fechaCierre,
            ot.horasReales,

            eq.nombre AS equipo,
            e.nombre  AS nombreEstado,
            p.nombre  AS nombrePrioridad,

            MAX(CONCAT(pe.nombre," ",pe.apellido)) AS tecnicoAsignado,

            ot.idSolicitante

        FROM ordentrabajo ot
        LEFT JOIN equipo    eq ON ot.idEquipo    = eq.idEquipo
        LEFT JOIN estado    e  ON ot.idEstado    = e.idEstado
        LEFT JOIN prioridad p  ON ot.idPrioridad = p.idPrioridad
        LEFT JOIN asignacion a ON ot.idOrden     = a.idOrden
        LEFT JOIN tecnico t    ON a.idTecnico    = t.idTecnico
        LEFT JOIN persona pe   ON t.idPersona    = pe.idPersona

        GROUP BY 
            ot.idOrden,
            ot.idEquipo,
            ot.idPrioridad,
            ot.idEstado,
            ot.titulo,
            ot.descripcion,
            ot.fechaCreacion,
            ot.fechaCierre,
            ot.horasReales,
            eq.nombre,
            e.nombre,
            p.nombre,
            ot.idSolicitante

        ORDER BY ot.fechaCreacion DESC
    ')->fetchAll();

    jsonResponse($rows);
}


// =======================
// ======== POST =========
// =======================
if ($method === 'POST') {
    $d = getInput();

    $stE = $db->query("SELECT idEstado FROM estado WHERE nombre='PENDIENTE' LIMIT 1");
    $est = $stE->fetch();
    $idEstado = $est ? $est['idEstado'] : 1;

    $idSol = $_SESSION['u']['id'] ?? 1;

    $st = $db->prepare('
        INSERT INTO ordentrabajo
        (idEquipo,idPrioridad,idEstado,idSolicitante,tipoMantenimiento,titulo,descripcion,fechaProgramada,horasEstimadas)
        VALUES (?,?,?,?,?,?,?,?,?)
    ');

    $st->execute([
        $d['idEquipo'],
        $d['idPrioridad'],
        $idEstado,
        $idSol,
        $d['tipoMantenimiento'] ?? 'CORRECTIVO',
        $d['titulo'],
        $d['descripcion'] ?? null,
        $d['fechaProgramada'] ?? null,
        $d['horasEstimadas'] ?? null
    ]);

    jsonResponse([
        'ok' => true,
        'idOrden' => $db->lastInsertId(),
        'mensaje' => 'Orden creada'
    ]);
}


// =======================
// ======== PUT ==========
// =======================
if ($method === 'PUT') {
    $d = getInput();

    if ($id && $sub === 'asignar') {
        $db->prepare('
            INSERT INTO asignacion (idOrden,idTecnico,rol) 
            VALUES (?,?,?)
            ON DUPLICATE KEY UPDATE rol=VALUES(rol)
        ')->execute([$id, $d['idTecnico'], $d['rol'] ?? 'Principal']);

        $stE = $db->query("SELECT idEstado FROM estado WHERE nombre='EN_PROCESO' LIMIT 1")->fetch();

        if ($stE) {
            $db->prepare("UPDATE ordentrabajo SET idEstado=? WHERE idOrden=?")
               ->execute([$stE['idEstado'], $id]);
        }

        jsonResponse(['ok' => true, 'mensaje' => 'Tecnico asignado']);
    }

    if ($id && $sub === 'cerrar') {
        $stE = $db->query("SELECT idEstado FROM estado WHERE nombre='CERRADA' LIMIT 1")->fetch();
        $idE = $stE ? $stE['idEstado'] : 5;

        $db->prepare("
            UPDATE ordentrabajo 
            SET idEstado=?, horasReales=?, observaciones=?, fechaCierre=NOW() 
            WHERE idOrden=?
        ")->execute([
            $idE,
            $d['horasReales'] ?? null,
            $d['observaciones'] ?? null,
            $id
        ]);

        jsonResponse(['ok' => true, 'mensaje' => 'Orden cerrada']);
    }

    if ($id && $sub === 'estado') {
        $db->prepare('UPDATE ordentrabajo SET idEstado=? WHERE idOrden=?')
           ->execute([$d['idEstado'], $id]);

        jsonResponse(['ok' => true, 'mensaje' => 'Estado actualizado']);
    }

    jsonResponse(['error' => 'Ruta no valida'], 400);
}

jsonResponse(['error' => 'Metodo no permitido'], 405);