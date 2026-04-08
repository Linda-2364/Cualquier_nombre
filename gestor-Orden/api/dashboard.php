<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'session.php';
requireLogin();
header('Content-Type: application/json; charset=utf-8');
$db = getDB();
$rol = getRol();
$u   = getUsuario();

if ($rol === 'TECNICO') {
    // Buscar idTecnico asociado al usuario
    $st = $db->prepare("SELECT t.idTecnico FROM Tecnico t
        INNER JOIN Persona p ON t.idPersona = p.idPersona
        INNER JOIN Usuario u ON u.idPersona = p.idPersona
        WHERE u.idUsuario = ?");
    $st->execute([$u['id']]);
    $tec = $st->fetch(PDO::FETCH_ASSOC);
    $idTec = $tec ? $tec['idTecnico'] : 0;

    $st = $db->prepare("SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN e.nombre='PENDIENTE' THEN 1 ELSE 0 END) AS pendientes,
        SUM(CASE WHEN e.nombre='EN_PROCESO' THEN 1 ELSE 0 END) AS en_proceso,
        SUM(CASE WHEN e.nombre='EN_ESPERA' THEN 1 ELSE 0 END) AS en_espera,
        SUM(CASE WHEN e.nombre='CERRADA' THEN 1 ELSE 0 END) AS cerradas,
        SUM(CASE WHEN p.nombre IN('CRITICA','EMERGENCIA') AND e.nombre NOT IN('CERRADA','CANCELADA') THEN 1 ELSE 0 END) AS urgentes
        FROM Asignacion a
        INNER JOIN OrdenTrabajo ot ON a.idOrden = ot.idOrden
        INNER JOIN Estado e ON ot.idEstado = e.idEstado
        INNER JOIN Prioridad p ON ot.idPrioridad = p.idPrioridad
        WHERE a.idTecnico = ?");
    $st->execute([$idTec]);
    $stats = $st->fetch(PDO::FETCH_ASSOC);

    // Actividad reciente del técnico
    $st2 = $db->prepare("SELECT ot.idOrden, ot.titulo, e.nombre AS estado, p.nombre AS prioridad,
        ot.fechaProgramada, eq.nombre AS equipo
        FROM Asignacion a
        INNER JOIN OrdenTrabajo ot ON a.idOrden = ot.idOrden
        INNER JOIN Estado e ON ot.idEstado = e.idEstado
        INNER JOIN Prioridad p ON ot.idPrioridad = p.idPrioridad
        LEFT JOIN Equipo eq ON ot.idEquipo = eq.idEquipo
        WHERE a.idTecnico = ?
        ORDER BY FIELD(e.nombre,'EN_PROCESO','PENDIENTE','EN_ESPERA','CERRADA','CANCELADA'),
        FIELD(p.nombre,'EMERGENCIA','CRITICA','ALTA','MEDIA','BAJA')
        LIMIT 5");
    $st2->execute([$idTec]);
    $recientes = $st2->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok'=>true,'stats'=>$stats,'recientes'=>$recientes,'idTecnico'=>$idTec]);
    exit;
}

if ($rol === 'SOLICITANTE') {
    $st = $db->prepare("SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN e.nombre='PENDIENTE' THEN 1 ELSE 0 END) AS pendientes,
        SUM(CASE WHEN e.nombre='EN_PROCESO' THEN 1 ELSE 0 END) AS en_proceso,
        SUM(CASE WHEN e.nombre='CERRADA' THEN 1 ELSE 0 END) AS cerradas,
        SUM(CASE WHEN e.nombre='CANCELADA' THEN 1 ELSE 0 END) AS canceladas
        FROM OrdenTrabajo ot
        INNER JOIN Estado e ON ot.idEstado = e.idEstado
        WHERE ot.idSolicitante = ?");
    $st->execute([$u['id']]);
    $stats = $st->fetch(PDO::FETCH_ASSOC);

    $st2 = $db->prepare("SELECT ot.idOrden, ot.titulo, e.nombre AS estado, p.nombre AS prioridad,
        ot.fechaCreacion, ot.fechaProgramada
        FROM OrdenTrabajo ot
        INNER JOIN Estado e ON ot.idEstado = e.idEstado
        INNER JOIN Prioridad p ON ot.idPrioridad = p.idPrioridad
        WHERE ot.idSolicitante = ?
        ORDER BY ot.fechaCreacion DESC LIMIT 5");
    $st2->execute([$u['id']]);
    $recientes = $st2->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok'=>true,'stats'=>$stats,'recientes'=>$recientes]);
    exit;
}

// ADMIN / SUPERVISOR
$stats = $db->query("SELECT
    (SELECT COUNT(*) FROM OrdenTrabajo) AS total,
    (SELECT COUNT(*) FROM OrdenTrabajo ot INNER JOIN Estado e ON ot.idEstado=e.idEstado WHERE e.nombre='PENDIENTE') AS pendientes,
    (SELECT COUNT(*) FROM OrdenTrabajo ot INNER JOIN Estado e ON ot.idEstado=e.idEstado WHERE e.nombre='EN_PROCESO') AS en_proceso,
    (SELECT COUNT(*) FROM OrdenTrabajo ot INNER JOIN Estado e ON ot.idEstado=e.idEstado WHERE e.nombre='CERRADA') AS cerradas,
    (SELECT COUNT(*) FROM Tecnico) AS tecnicos,
    (SELECT COUNT(*) FROM Repuesto WHERE stockActual <= stockMinimo) AS stock_bajo,
    (SELECT COUNT(*) FROM OrdenTrabajo ot INNER JOIN Prioridad p ON ot.idPrioridad=p.idPrioridad INNER JOIN Estado e ON ot.idEstado=e.idEstado WHERE p.nombre IN('CRITICA','EMERGENCIA') AND e.nombre NOT IN('CERRADA','CANCELADA')) AS urgentes,
    (SELECT COUNT(*) FROM Usuario WHERE activo=1) AS usuarios_activos
")->fetch(PDO::FETCH_ASSOC);

// Órdenes por mes (últimos 6 meses)
$tendencia = $db->query("SELECT DATE_FORMAT(fechaCreacion,'%Y-%m') AS mes, COUNT(*) AS total
    FROM OrdenTrabajo WHERE fechaCreacion >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY mes ORDER BY mes")->fetchAll(PDO::FETCH_ASSOC);

// Top equipos con más órdenes
$topEquipos = $db->query("SELECT eq.nombre AS equipo, COUNT(*) AS total
    FROM OrdenTrabajo ot LEFT JOIN Equipo eq ON ot.idEquipo=eq.idEquipo
    WHERE eq.nombre IS NOT NULL
    GROUP BY eq.idEquipo ORDER BY total DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['ok'=>true,'stats'=>$stats,'tendencia'=>$tendencia,'topEquipos'=>$topEquipos]);