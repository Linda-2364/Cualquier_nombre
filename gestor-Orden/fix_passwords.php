<?php
// fix_passwords.php
// Coloca este archivo en: C:\xampp\htdocs\gestor-Orden\fix_passwords.php
// Abre en navegador: http://localhost/gestor-Orden/fix_passwords.php
// BORRA este archivo despues de usarlo

require_once __DIR__ . '/includes/db.php';

$password = '123456';
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

// Verificar que el hash funciona
$ok = password_verify($password, $hash);

echo "<h2>MantTech — Fix Contraseñas</h2>";
echo "<p>Hash generado: <code>" . htmlspecialchars($hash) . "</code></p>";
echo "<p>Verificación: " . ($ok ? "✅ CORRECTO" : "❌ ERROR") . "</p>";

if ($ok) {
    $db = getDB();
    
    // Actualizar todos los usuarios
    $usuarios = ['admin','supervisor','carlos.m','luis.q','ana.f','roberto.g','maria.s'];
    
    $stmt = $db->prepare("UPDATE usuario SET passwordHash=?, intentosFallidos=0, activo=1 WHERE username=?");
    
    $actualizados = 0;
    foreach ($usuarios as $usr) {
        $nuevoHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
        $stmt->execute([$nuevoHash, $usr]);
        if ($stmt->rowCount() > 0) $actualizados++;
    }
    
    echo "<p>✅ Actualizados: <strong>$actualizados usuarios</strong> con contraseña: <strong>123456</strong></p>";
    
    // Mostrar estado actual
    $rows = $db->query("SELECT idUsuario, username, activo, intentosFallidos FROM usuario")->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' cellpadding='8' style='border-collapse:collapse'>";
    echo "<tr><th>ID</th><th>Usuario</th><th>Activo</th><th>Intentos</th><th>Test Login</th></tr>";
    foreach ($rows as $r) {
        // Re-leer hash actualizado
        $st2 = $db->prepare("SELECT passwordHash FROM usuario WHERE idUsuario=?");
        $st2->execute([$r['idUsuario']]);
        $u = $st2->fetch(PDO::FETCH_ASSOC);
        $test = password_verify('123456', $u['passwordHash']) ? '✅ OK' : '❌ FALLO';
        echo "<tr>
            <td>{$r['idUsuario']}</td>
            <td><strong>{$r['username']}</strong></td>
            <td>{$r['activo']}</td>
            <td>{$r['intentosFallidos']}</td>
            <td>$test</td>
        </tr>";
    }
    echo "</table>";
    echo "<br><p style='color:green;font-weight:bold'>✅ Listo. Ahora ve a <a href='index.php'>index.php</a> e inicia sesion con cualquier usuario / 123456</p>";
    echo "<p style='color:red'>⚠ IMPORTANTE: Borra este archivo fix_passwords.php despues de usarlo</p>";
} else {
    echo "<p style='color:red'>Error generando hash. Revisa la version de PHP.</p>";
}
?>