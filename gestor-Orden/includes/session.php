<?php
// includes/session.php

if (session_status() === PHP_SESSION_NONE) session_start();

// Incluir db.php desde ruta absoluta
require_once __DIR__ . '/db.php';

if (!function_exists('requireLogin')) {
    function requireLogin(string $redirect = '../login.php'): void {
        if (empty($_SESSION['u'])) {
            header("Location: $redirect"); exit;
        }
    }
}

if (!function_exists('requireRol')) {
    function requireRol(array $roles, string $redirect = '../pages/sin_acceso.php'): void {
        requireLogin();
        if (!in_array($_SESSION['u']['rol'] ?? '', $roles)) {
            header("Location: $redirect"); exit;
        }
    }
}

if (!function_exists('getUsuario')) {
    function getUsuario(): array { return $_SESSION['u'] ?? []; }
}

if (!function_exists('getRol')) {
    function getRol(): string { return $_SESSION['u']['rol'] ?? ''; }
}