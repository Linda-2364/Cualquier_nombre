<?php
require_once __DIR__.'/../includes/session.php';
requireLogin('../login.php');
requireRol(['SOLICITANTE']);
$u = getUsuario();
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Mi Panel — MantTech</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.sol-hero{background:linear-gradient(135deg,#2ecc7108,var(--card));border:1px solid #2ecc7130;border-radius:16px;padding:28px 32px;margin-bottom:24px;display:flex;align-items:center;gap:24px}
.sh-av{width:72px;height:72px;background:#2ecc7118;border:3px solid var(--green);border-radius:16px;display:grid;place-items:center;font-family:var(--fw);font-size:28px;font-weight:800;color:var(--green);flex-shrink:0}
.kpi-row{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px}
.kpi{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:20px;text-align:center}
.kpi-n{font-family:var(--fw);font-size:36px;font-weight:800;line-height:1;margin-bottom:6px}
.kpi-l{font-size:11px;color:var(--t3);text-transform:uppercase;letter-spacing:.8px}
.kpi-sub{font-size:11px;margin-top:8px;color:var(--t2)}

.nueva-sol{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:24px;margin-bottom:24px}
.ns-title{font-family:var(--fw);font-size:15px;font-weight:700;margin-bottom:16px;color:var(--t2);text-transform:uppercase;letter-spacing:1px;display:flex;align-items:center;gap:8px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px}
.fg{display:flex;flex-direction:column;gap:6px}
.fg label{font-size:11px;color:var(--t3);text-transform:uppercase;letter-spacing:.8px}
.fg input,.fg select,.fg textarea{background:var(--input);border:1px solid var(--border);border-radius:9px;padding:11px 14px;color:var(--t1);font-size:14px;outline:none;font-family:inherit;transition:border-color .2s}
.fg input:focus,.fg select:focus,.fg textarea:focus{border-color:var(--green);box-shadow:0 0 0 3px #2ecc7118}
.fg textarea{resize:vertical;min-height:80px}

.track-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:20px;margin-bottom:12px;cursor:pointer;transition:all .2s}
.track-card:hover{border-color:var(--accent);transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.3)}
.tc-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px}
.tc-id{font-size:11px;color:var(--t3);font-family:var(--fm)}
.tc-titulo{font-size:14px;font-weight:600;margin-bottom:5px}
.tc-meta{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.tc-fecha{font-size:11px;color:var(--t3);margin-left:auto}

.stepper{display:flex;gap:0;margin-bottom:4px}
.step{display:flex;flex-direction:column;align-items:center;flex:1;position:relative}
.step:not(:last-child)::after{content:'';position:absolute;top:12px;left:calc(50% + 12px);width:calc(100% - 24px);height:2px;background:var(--border);z-index:0}
.step.done::after{background:var(--green)}
.step-dot{width:24px;height:24px;border-radius:50%;border:2px solid var(--border);background:var(--card);display:grid;place-items:center;font-size:10px;z-index:1;transition:all .3s}
.step.done .step-dot{background:var(--green);border-color:var(--green);color:#fff}
.step.active .step-dot{background:var(--accent);border-color:var(--accent);color:#000;animation:pulse-s 2s infinite}
@keyframes pulse-s{0%,100%{box-shadow:0 0 0 0 #f0a50050}50%{box-shadow:0 0 0 8px transparent}}
.step-lbl{font-size:9px;color:var(--t3);margin-top:5px;text-align:center;text-transform:uppercase;letter-spacing:.5px}
</style>
</head>
<body>
<div class="shell">
<?php include __DIR__.'/../includes/nav.php'; ?>
<main class="main">

 <div class="ph">
  <div><div class="ph-title">Mi Panel</div><div class="ph-sub" id="fecha-hoy"></div></div>
  <div class="ph-actions">
   <button class="btn btn-p" onclick="location.href='solicitudes.php'">Ver Todas Mis Solicitudes</button>
  </div>
 </div>

 <!-- Hero solicitante -->
 <div class="sol-hero">
  <div class="sh-av" id="sh-ini">…</div>
  <div>
   <div style="font-size:13px;color:var(--t3);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Panel de Solicitante</div>
   <div style="font-family:var(--fw);font-size:24px;font-weight:800;margin-bottom:6px" id="sh-nombre">Cargando...</div>
   <div style="font-size:13px;color:var(--t2)">Crea y hace seguimiento de tus solicitudes de mantenimiento</div>
  </div>
 </div>

 <!-- KPIs -->
 <div class="kpi-row" id="kpis">
  <div class="loader"><div class="spinner"></div></div>
 </div>

 <div class="g2">
  <!-- Nueva solicitud rápida -->
  <div>
   <div class="nueva-sol">
    <div class="ns-title">✦ Nueva Solicitud Rápida</div>
    <div class="form-row">
     <div class="fg"><label>Título / Problema</label><input id="s-tit" type="text" placeholder="Ej: Falla en motor principal"></div>
     <div class="fg"><label>Tipo</label><select id="s-tipo"><option value="CORRECTIVO">Correctivo</option><option value="PREVENTIVO">Preventivo</option><option value="PREDICTIVO">Predictivo</option></select></div>
    </div>
    <div class="form-row">
     <div class="fg"><label>Equipo (ID)</label><select id="s-equipo"><option value="">Cargando...</option></select></div>
     <div class="fg"><label>Prioridad</label><select id="s-prio"><option value="">Cargando...</option></select></div>
    </div>
    <div class="fg" style="margin-bottom:14px"><label>Descripción del problema</label><textarea id="s-desc" placeholder="Describe el problema con el mayor detalle posible..."></textarea></div>
    <button class="btn btn-p" style="background:var(--green);color:#fff;border:none;width:100%;padding:13px;border-radius:10px;font-family:var(--fw);font-size:15px;font-weight:700;cursor:pointer" onclick="crearSolicitud()">✦ Enviar Solicitud</button>
    <div id="s-msg" style="font-size:13px;margin-top:10px;text-align:center"></div>
   </div>
  </div>

  <!-- Seguimiento de solicitudes -->
  <div>
   <div class="card">
    <div class="card-title">🔍 Seguimiento de Mis Solicitudes</div>
    <div id="track-list"><div class="loader"><div class="spinner"></div></div></div>
   </div>
  </div>
 </div>

</main>
</div>
<script src="../assets/js/app.js"></script>
<script>
document.getElementById('fecha-hoy').textContent =
 new Date().toLocaleDateString('es-BO',{weekday:'long',year:'numeric',month:'long',day:'numeric'});

const PASOS = ['PENDIENTE','EN_ESPERA','EN_PROCESO','CERRADA'];
const PASO_L = ['Pendiente','En Espera','En Proceso','Completada'];

function stepper(estado){
 let idx = PASOS.indexOf(estado);
 if(idx<0) idx=0;
 if(estado==='CANCELADA') return `<div style="color:var(--red);font-size:12px;font-weight:700">✕ CANCELADA</div>`;
 return `<div class="stepper">${PASOS.map((p,i)=>`
  <div class="step ${i<idx?'done':i===idx?'active':''}">
   <div class="step-dot">${i<idx?'✓':i+1}</div>
   <div class="step-lbl">${PASO_L[i]}</div>
  </div>`).join('')}</div>`;
}

async function loadPanel(){
 try{
  const USR_ID = <?=(int)($u['id']??0)?>;
  const [dash, ords, equipos, prios, usrs] = await Promise.all([
   API.get('/dashboard.php'),
   API.get('/ordenes.php'),
   API.get('/equipos.php'),
   API.get('/catalogos.php?tipo=prioridades'),
   API.get('/usuarios.php')
  ]);
  const s = dash.stats||{};
  const yo = Array.isArray(usrs) ? usrs.find(x=>Number(x.idUsuario)===USR_ID) : null;
  const nombre = yo ? (yo.nombre||'Solicitante') : '<?=htmlspecialchars($u['nombre']??'Solicitante')?>';
  const ini = nombre.split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase();

  document.getElementById('sh-ini').textContent    = ini;
  document.getElementById('sh-nombre').textContent = nombre;

  // KPIs
  document.getElementById('kpis').innerHTML=`
   <div class="kpi"><div class="kpi-n" style="color:var(--accent)">${s.total||0}</div><div class="kpi-l">Mis Solicitudes</div><div class="kpi-sub">Total enviadas</div></div>
   <div class="kpi"><div class="kpi-n" style="color:#7c6cf8">${s.en_proceso||0}</div><div class="kpi-l">En Proceso</div><div class="kpi-sub">Siendo atendidas</div></div>
   <div class="kpi"><div class="kpi-n" style="color:var(--green)">${s.cerradas||0}</div><div class="kpi-l">Resueltas</div><div class="kpi-sub">Completadas</div></div>
   <div class="kpi"><div class="kpi-n" style="color:var(--t3)">${s.canceladas||0}</div><div class="kpi-l">Canceladas</div><div class="kpi-sub">Sin procesar</div></div>
  `;

  // Cargar equipos en select
  document.getElementById('s-equipo').innerHTML = '<option value="">Seleccionar equipo...</option>' +
   (Array.isArray(equipos) ? equipos.map(e=>`<option value="${e.idEquipo}">${e.nombre} — ${e.marca||''}</option>`).join('') : '');

  // Cargar prioridades
  document.getElementById('s-prio').innerHTML = '<option value="">Seleccionar prioridad...</option>' +
   (Array.isArray(prios) ? prios.map(p=>`<option value="${p.idPrioridad}">${p.nombre}</option>`).join('') : '');

  // Mis órdenes con seguimiento
  const misOrds = Array.isArray(ords) ? ords.filter(o=>Number(o.idSolicitante)===USR_ID) : [];
  document.getElementById('track-list').innerHTML = misOrds.length===0
   ? `<div class="empty"><div class="empty-icon">✦</div>No tienes solicitudes aún.<br>Crea tu primera solicitud.</div>`
   : misOrds.slice(0,6).map(o=>`
    <div class="track-card" onclick="location.href='solicitudes.php'">
     <div class="tc-head">
      <div><div class="tc-id">#${o.idOrden}</div><div class="tc-titulo">${o.titulo}</div></div>
      ${badgeP(o.nombrePrioridad||'')}
     </div>
     ${stepper(o.nombreEstado||'PENDIENTE')}
     <div class="tc-meta" style="margin-top:10px">
      ${badgeT(o.tipoMantenimiento)}
      <span class="tc-fecha">${o.fechaCreacion ? new Date(o.fechaCreacion).toLocaleDateString('es-BO') : '—'}</span>
     </div>
    </div>`).join('');

 }catch(e){console.error(e)}
}

async function crearSolicitud(){
 const tit  = document.getElementById('s-tit').value.trim();
 const tipo  = document.getElementById('s-tipo').value;
 const equipo= document.getElementById('s-equipo').value;
 const prio  = document.getElementById('s-prio').value;
 const desc  = document.getElementById('s-desc').value.trim();
 const msg   = document.getElementById('s-msg');

 if(!tit||!equipo||!prio){msg.style.color='var(--red)';msg.textContent='Completa título, equipo y prioridad';return}
 try{
  const r = await API.post('/ordenes.php',{titulo:tit,tipoMantenimiento:tipo,idEquipo:Number(equipo),idPrioridad:Number(prio),descripcion:desc});
  if(r.ok||r.idOrden){
   msg.style.color='var(--green)';msg.textContent='✓ Solicitud enviada correctamente';
   document.getElementById('s-tit').value='';document.getElementById('s-desc').value='';
   setTimeout(()=>{msg.textContent='';loadPanel()},1500);
  }else{msg.style.color='var(--red)';msg.textContent=r.error||'Error al crear solicitud'}
 }catch(e){msg.style.color='var(--red)';msg.textContent='Error de conexión'}
}

loadPanel();
</script>
</body></html>