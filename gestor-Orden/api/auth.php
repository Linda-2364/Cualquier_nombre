<?php
// api/auth.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

$base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');

// Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: ' . $base . '/index.php');
    exit;
}

// Whoami — devuelve datos del usuario en sesion
if (isset($_GET['action']) && $_GET['action'] === 'whoami') {
    if (!empty($_SESSION['u'])) {
        jsonResponse([
            'ok'       => true,
            'id'       => $_SESSION['u']['id'],
            'username' => $_SESSION['u']['username'],
            'nombre'   => $_SESSION['u']['nombre'],
            'rol'      => $_SESSION['u']['rol'],
            'email'    => $_SESSION['u']['email'] ?? '',
        ]);
    }
    jsonResponse(['ok' => false, 'error' => 'No autenticado']);
}

// Login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data     = getInput();
    $username = trim($data['username'] ?? '');
    $password = $data['password'] ?? '';

    if (!$username || !$password) {
        jsonResponse(['ok' => false, 'error' => 'Usuario y contrasena requeridos']);
    }

    try {
        $db = getDB();
        $st = $db->prepare("
            SELECT u.idUsuario, u.username, u.passwordHash,
                   u.activo, u.intentosFallidos,
                   p.nombre, p.apellido, p.email,
                   r.nombre AS rol
            FROM usuario u
            INNER JOIN persona p ON u.idPersona = p.idPersona
            INNER JOIN rol r     ON u.idRol     = r.idRol
            WHERE u.username = ? LIMIT 1
        ");
        $st->execute([$username]);
        $user = $st->fetch();

        if (!$user) jsonResponse(['ok' => false, 'error' => 'Usuario no encontrado']);

        if ((int)$user['intentosFallidos'] >= 5) {
            jsonResponse(['ok' => false, 'error' => 'Demasiados intentos. Contacta al administrador.']);
        }

        if (!password_verify($password, $user['passwordHash'])) {
            $db->prepare("UPDATE usuario SET intentosFallidos = intentosFallidos+1 WHERE idUsuario=?")
               ->execute([$user['idUsuario']]);
            jsonResponse(['ok' => false, 'error' => 'Contrasena incorrecta']);
        }

        // Login exitoso
        $db->prepare("UPDATE usuario SET intentosFallidos=0, activo=1, ultimoAcceso=NOW() WHERE idUsuario=?")
           ->execute([$user['idUsuario']]);

        try {
            $db->prepare("INSERT INTO auditoria(idUsuario,accion,descripcion) VALUES(?,'LOGIN','Inicio de sesion')")
               ->execute([$user['idUsuario']]);
        } catch(Exception $e) {}

        $nombre = trim(($user['nombre'] ?? '') . ' ' . ($user['apellido'] ?? ''));
        $rol    = strtoupper($user['rol'] ?? '');

        $_SESSION['u'] = [
            'id'       => $user['idUsuario'],
            'username' => $user['username'],
            'nombre'   => $nombre,
            'email'    => $user['email'] ?? '',
            'rol'      => $rol,
        ];

        $redirect = match($rol) {
            'TECNICO'     => $base . '/pages/dashboard_tecnico.php',
            'SOLICITANTE' => $base . '/pages/dashboard_solicitante.php',
            default       => $base . '/dashboard.php'
        };

        jsonResponse(['ok' => true, 'nombre' => $nombre, 'rol' => $rol, 'redirect' => $redirect]);

    } catch (PDOException $e) {
        jsonResponse(['ok' => false, 'error' => 'Error BD: ' . $e->getMessage()]);
    }
}

jsonResponse(['ok' => false, 'error' => 'Metodo no permitido']);