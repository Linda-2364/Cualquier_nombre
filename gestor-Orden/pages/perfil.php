<?php
require_once __DIR__.'/../includes/session.php';
requireLogin('../login.php');
$u   = getUsuario();
$rol = getRol();
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Mi Perfil — MantTech</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.perfil-hero{display:flex;align-items:center;gap:28px;background:var(--card);border:1px solid var(--border);border-radius:16px;padding:32px;margin-bottom:24px}
.pav{width:90px;height:90px;background:var(--aD);border:3px solid var(--accent);border-radius:20px;display:grid;place-items:center;font-family:var(--fw);font-size:34px;font-weight:800;color:var(--accent);flex-shrink:0}
.pinfo{flex:1}
.pname{font-family:var(--fw);font-size:26px;font-weight:800;line-height:1.1;margin-bottom:6px}
.prol{display:inline-block;padding:4px 14px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:10px}
.rol-ADMIN{background:#f0a50020;color:var(--accent);border:1px solid #f0a50050}
.rol-SUPERVISOR{background:#00c8b420;color:#00c8b4;border:1px solid #00c8b450}
.rol-TECNICO{background:#7c6cf820;color:#7c6cf8;border:1px solid #7c6cf850}
.rol-SOLICITANTE{background:#2ecc7120;color:var(--green);border:1px solid #2ecc7150}
.pmeta{display:flex;gap:20px;flex-wrap:wrap}
.pmeta-item{display:flex;align-items:center;gap:7px;font-size:13px;color:var(--t2)}
.pmeta-item span{color:var(--t1)}
.info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin-bottom:24px}
.info-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:24px}
.ic-title{font-family:var(--fw);font-size:14px;font-weight:700;color:var(--t2);text-transform:uppercase;letter-spacing:1px;margin-bottom:18px;display:flex;align-items:center;gap:8px}
.field-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--border)}
.field-row:last-child{border-bottom:none}
.field-label{font-size:12px;color:var(--t3);text-transform:uppercase;letter-spacing:.8px}
.field-val{font-size:14px;color:var(--t1);font-weight:500}
.stat-mini{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-top:6px}
.sm-card{background:var(--card2);border:1px solid var(--border);border-radius:10px;padding:16px;text-align:center}
.sm-n{font-family:var(--fw);font-size:28px;font-weight:800;line-height:1}
.sm-l{font-size:11px;color:var(--t3);margin-top:4px;text-transform:uppercase;letter-spacing:.8px}
.pwd-form{display:flex;flex-direction:column;gap:14px}
.pwd-form label{font-size:11px;color:var(--t3);text-transform:uppercase;letter-spacing:.8px;display:block;margin-bottom:5px}
.pwd-form input{width:100%;background:var(--input);border:1px solid var(--border);border-radius:9px;padding:11px 14px;color:var(--t1);font-size:14px;outline:none;transition:border-color .2s}
.pwd-form input:focus{border-color:var(--accent)}
.activity-list{display:flex;flex-direction:column;gap:8px}
.act-item{display:flex;align-items:center;gap:12px;padding:10px 13px;background:var(--card2);border:1px solid var(--border);border-radius:9px}
.act-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.act-text{flex:1;font-size:13px}
.act-time{font-size:11px;color:var(--t3);font-family:var(--fm)}
</style>
</head>
<body>
<div class="shell">
<?php include __DIR__.'/../includes/nav.php'; ?>
<main class="main">
 <div class="ph">
  <div><div class="ph-title">Mi Perfil</div><div class="ph-sub">Información de tu cuenta y actividad</div></div>
 </div>

 <div class="perfil-hero">
  <div class="pav" id="pav-ini"></div>
  <div class="pinfo">
   <div class="pname" id="p-nombre">Cargando...</div>
   <div class="prol rol-<?=htmlspecialchars($rol)?>"><?=htmlspecialchars($rol)?></div>
   <div class="pmeta">
    <div class="pmeta-item">◑ Usuario: <span id="p-user"><?=htmlspecialchars($u['username']??'')?></span></div>
    <div class="pmeta-item">✉ <span id="p-email">—</span></div>
    <div class="pmeta-item">📅 Desde: <span id="p-desde">—</span></div>
   </div>
  </div>
 </div>

 <div class="info-grid">
  <!-- Datos personales -->
  <div class="info-card">
   <div class="ic-title">◈ Datos Personales</div>
   <div class="field-row"><div class="field-label">Nombre completo</div><div class="field-val" id="f-nombre">—</div></div>
   <div class="field-row"><div class="field-label">CI / Documento</div><div class="field-val" id="f-ci">—</div></div>
   <div class="field-row"><div class="field-label">Teléfono</div><div class="field-val" id="f-tel">—</div></div>
   <div class="field-row"><div class="field-label">Dirección</div><div class="field-val" id="f-dir">—</div></div>
   <div class="field-row"><div class="field-label">Estado cuenta</div><div class="field-val" id="f-activo">—</div></div>
  </div>

  <!-- Estadísticas del rol -->
  <div class="info-card">
   <div class="ic-title">◉ Mi Actividad</div>
   <div class="stat-mini" id="stat-mini"></div>
  </div>

  <!-- Cambiar contraseña -->
  <div class="info-card">
   <div class="ic-title">🔒 Cambiar Contraseña</div>
   <div class="pwd-form">
    <div><label>Contraseña actual</label><input type="password" id="pwd-act" placeholder="••••••••"></div>
    <div><label>Nueva contraseña</label><input type="password" id="pwd-new" placeholder="••••••••"></div>
    <div><label>Confirmar nueva</label><input type="password" id="pwd-cfm" placeholder="••••••••"></div>
    <button class="btn btn-p" onclick="cambiarPwd()">Actualizar Contraseña</button>
    <div id="pwd-msg" style="font-size:13px;margin-top:4px"></div>
   </div>
  </div>

  <!-- Actividad reciente -->
  <div class="info-card">
   <div class="ic-title">⏱ Sesiones Recientes</div>
   <div class="activity-list" id="act-list">
    <div class="loader"><div class="spinner"></div></div>
   </div>
  </div>
 </div>
</main>
</div>
<script src="../assets/js/app.js"></script>
<script>
const ROL = '<?=htmlspecialchars($rol)?>';
const USR_ID = <?=(int)($u['id']??0)?>;

async function loadPerfil(){
 try{
  // Cargar datos del usuario actual
  const usuarios = await API.get('/usuarios.php');
  const yo = Array.isArray(usuarios) ? usuarios.find(x=>Number(x.idUsuario)===USR_ID) : null;

  if(yo){
   const ini = (yo.nombre||'U').split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase();
   document.getElementById('pav-ini').textContent = ini;
   document.getElementById('p-nombre').textContent = yo.nombre||'—';
   document.getElementById('p-email').textContent   = yo.email||'Sin email';
   document.getElementById('p-desde').textContent  = yo.fechaCreacion ? new Date(yo.fechaCreacion).toLocaleDateString('es-BO') : '—';
   document.getElementById('f-nombre').textContent = yo.nombre||'—';
   document.getElementById('f-ci').textContent     = yo.ci||'—';
   document.getElementById('f-tel').textContent    = yo.telefono||'—';
   document.getElementById('f-dir').textContent    = yo.direccion||'—';
   document.getElementById('f-activo').innerHTML   = yo.activo=='1'
     ? '<span style="color:var(--green);font-weight:700">● Activo</span>'
     : '<span style="color:var(--red);font-weight:700">● Bloqueado</span>';
  }else{
   // Fallback con datos de sesión
   const ini = ('<?=htmlspecialchars($u['nombre']??'U')?>').split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase();
   document.getElementById('pav-ini').textContent = ini;
   document.getElementById('p-nombre').textContent = '<?=htmlspecialchars($u['nombre']??'')?>';
   document.getElementById('f-activo').innerHTML = '<span style="color:var(--green)">● Activo</span>';
  }

  // Stats por rol
  const dash = await API.get('/dashboard.php');
  const stats = dash.stats||{};
  let sm = '';
  if(ROL==='TECNICO'){
   sm=`
    <div class="sm-card"><div class="sm-n" style="color:var(--accent)">${stats.total||0}</div><div class="sm-l">Total Asignadas</div></div>
    <div class="sm-card"><div class="sm-n" style="color:#7c6cf8">${stats.en_proceso||0}</div><div class="sm-l">En Proceso</div></div>
    <div class="sm-card"><div class="sm-n" style="color:var(--green)">${stats.cerradas||0}</div><div class="sm-l">Cerradas</div></div>
    <div class="sm-card"><div class="sm-n" style="color:var(--red)">${stats.urgentes||0}</div><div class="sm-l">Urgentes</div></div>`;
  }else if(ROL==='SOLICITANTE'){
   sm=`
    <div class="sm-card"><div class="sm-n" style="color:var(--accent)">${stats.total||0}</div><div class="sm-l">Mis Solicitudes</div></div>
    <div class="sm-card"><div class="sm-n" style="color:#7c6cf8">${stats.en_proceso||0}</div><div class="sm-l">En Proceso</div></div>
    <div class="sm-card"><div class="sm-n" style="color:var(--green)">${stats.cerradas||0}</div><div class="sm-l">Resueltas</div></div>
    <div class="sm-card"><div class="sm-n" style="color:var(--t3)">${stats.canceladas||0}</div><div class="sm-l">Canceladas</div></div>`;
  }else{
   sm=`
    <div class="sm-card"><div class="sm-n" style="color:var(--accent)">${stats.total||0}</div><div class="sm-l">Total Órdenes</div></div>
    <div class="sm-card"><div class="sm-n" style="color:#7c6cf8">${stats.en_proceso||0}</div><div class="sm-l">En Proceso</div></div>
    <div class="sm-card"><div class="sm-n" style="color:var(--green)">${stats.cerradas||0}</div><div class="sm-l">Cerradas</div></div>
    <div class="sm-card"><div class="sm-n" style="color:var(--red)">${stats.urgentes||0}</div><div class="sm-l">Urgentes</div></div>`;
  }
  document.getElementById('stat-mini').innerHTML = sm;

  // Sesiones recientes (simulado con auditoría)
  document.getElementById('act-list').innerHTML = `
   <div class="act-item"><div class="act-dot" style="background:var(--green)"></div><div class="act-text">Sesión iniciada</div><div class="act-time">Ahora</div></div>
   <div class="act-item"><div class="act-dot" style="background:var(--t3)"></div><div class="act-text">Último acceso registrado</div><div class="act-time">Hoy</div></div>
  `;
 }catch(e){console.error(e)}
}

async function cambiarPwd(){
 const act=document.getElementById('pwd-act').value;
 const nw=document.getElementById('pwd-new').value;
 const cfm=document.getElementById('pwd-cfm').value;
 const msg=document.getElementById('pwd-msg');
 if(!act||!nw||!cfm){msg.style.color='var(--red)';msg.textContent='Completa todos los campos';return}
 if(nw!==cfm){msg.style.color='var(--red)';msg.textContent='Las contraseñas nuevas no coinciden';return}
 if(nw.length<6){msg.style.color='var(--red)';msg.textContent='Mínimo 6 caracteres';return}
 try{
  const r=await API.put('/usuarios.php?id='+USR_ID+'&action=pwd',{passwordActual:act,passwordNuevo:nw});
  if(r.ok){msg.style.color='var(--green)';msg.textContent='Contraseña actualizada correctamente';
   document.getElementById('pwd-act').value='';document.getElementById('pwd-new').value='';document.getElementById('pwd-cfm').value='';
  }else{msg.style.color='var(--red)';msg.textContent=r.error||'Error al actualizar'}
 }catch(e){msg.style.color='var(--red)';msg.textContent='Error de conexión'}
}

loadPerfil();
</script>
</body></html>