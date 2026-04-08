<?php
// login.php — redirige siempre al index que tiene el modal de login
if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['u'])) {
    $rol = $_SESSION['u']['rol'];
    if ($rol === 'TECNICO')     { header('Location: pages/dashboard_tecnico.php'); exit; }
    if ($rol === 'SOLICITANTE') { header('Location: pages/dashboard_solicitante.php'); exit; }
    header('Location: dashboard.php'); exit;
}
header('Location: index.php');
exit;