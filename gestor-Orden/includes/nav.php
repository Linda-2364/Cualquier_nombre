<?php
require_once __DIR__.'/session.php';
requireLogin('../index.php');
$u   = getUsuario();
$rol = getRol();
$cur = basename($_SERVER['PHP_SELF'],'.php');
$nav=[
 ['h'=>'../dashboard.php',                 'i'=>'◈','l'=>'Dashboard',       'k'=>'dashboard',              'r'=>['ADMIN','SUPERVISOR']],
 ['h'=>'../pages/dashboard_tecnico.php',   'i'=>'◈','l'=>'Mi Dashboard',    'k'=>'dashboard_tecnico',      'r'=>['TECNICO']],
 ['h'=>'../pages/dashboard_solicitante.php','i'=>'◈','l'=>'Mi Panel',        'k'=>'dashboard_solicitante',  'r'=>['SOLICITANTE']],
 ['h'=>'../pages/ordenes.php',             'i'=>'◉','l'=>'Órdenes',         'k'=>'ordenes',                'r'=>['ADMIN','SUPERVISOR']],
 ['h'=>'../pages/mis_ordenes.php',         'i'=>'⚙','l'=>'Mis Órdenes',    'k'=>'mis_ordenes',            'r'=>['TECNICO']],
 ['h'=>'../pages/solicitudes.php',         'i'=>'✦','l'=>'Solicitudes',     'k'=>'solicitudes',            'r'=>['SOLICITANTE','ADMIN','SUPERVISOR']],
 ['h'=>'../pages/tecnicos.php',            'i'=>'◎','l'=>'Técnicos',        'k'=>'tecnicos',               'r'=>['ADMIN','SUPERVISOR']],
 ['h'=>'../pages/repuestos.php',           'i'=>'◍','l'=>'Repuestos',       'k'=>'repuestos',              'r'=>['ADMIN','SUPERVISOR']],
 ['h'=>'../pages/equipos.php',             'i'=>'⬡','l'=>'Equipos',         'k'=>'equipos',                'r'=>['ADMIN','SUPERVISOR']],
 ['h'=>'../pages/usuarios.php',            'i'=>'◑','l'=>'Usuarios',        'k'=>'usuarios',               'r'=>['ADMIN']],
 ['h'=>'../pages/reportes.php',            'i'=>'📊','l'=>'Reportes',        'k'=>'reportes',               'r'=>['ADMIN','SUPERVISOR']],
 ['h'=>'../pages/perfil.php',              'i'=>'◐','l'=>'Mi Perfil',       'k'=>'perfil',                 'r'=>['ADMIN','SUPERVISOR','TECNICO','SOLICITANTE']],
];
$ini=implode('',array_map(fn($w)=>$w[0],array_slice(explode(' ',$u['nombre']??'U'),0,2)));
?>
<aside class="sidebar">
 <div class="sb-logo"><div class="sb-icon">⚙</div><span class="sb-name">MantTech</span></div>
 <button class="sb-toggle" id="toggle-btn">◀</button>
 <nav class="sb-nav">
  <?php foreach($nav as $n): if(!in_array($rol,$n['r'])) continue; ?>
  <a href="<?=$n['h']?>" class="nav-link <?=$cur===$n['k']?'active':''?>">
   <span class="nav-icon"><?=$n['i']?></span><span class="nav-label"><?=$n['l']?></span>
  </a>
  <?php endforeach; ?>
 </nav>
 <div class="sb-footer">
  <div class="user-badge">
   <div class="user-av"><?=htmlspecialchars($ini)?></div>
   <div class="user-info">
    <div class="user-name"><?=htmlspecialchars($u['nombre']??'')?></div>
    <div class="user-role"><?=htmlspecialchars($rol??'')?></div>
   </div>
  </div>
  <a href="../api/auth.php?action=logout" class="logout-btn" onclick="return confirm('¿Cerrar sesión?')">⏻ Cerrar sesión</a>
 </div>
</aside>