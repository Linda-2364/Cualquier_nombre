<?php
require_once '../includes/session.php';
requireLogin('../index.php');
$u         = getUsuario();
$rol       = getRol();
$idUsuario = $u['id'] ?? 0;
$nombre    = $u['nombre'] ?? 'Usuario';
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Solicitudes — MantTech</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.step-bar{display:flex;align-items:center;gap:0;margin:10px 0}
.step{display:flex;flex-direction:column;align-items:center;flex:1;position:relative}
.step:not(:last-child)::after{content:'';position:absolute;top:10px;left:calc(50% + 10px);width:calc(100% - 20px);height:2px;background:var(--border)}
.step.done:not(:last-child)::after{background:var(--green)}
.step-dot{width:20px;height:20px;border-radius:50%;border:2px solid var(--border);background:var(--card);display:grid;place-items:center;font-size:9px;z-index:1}
.step.done .step-dot{background:var(--green);border-color:var(--green);color:#fff}
.step.active .step-dot{background:var(--accent);border-color:var(--accent);color:#000}
.step-lbl{font-size:9px;color:var(--t3);margin-top:4px;text-align:center;text-transform:uppercase}
.sol-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:18px;margin-bottom:12px;cursor:pointer;transition:all .2s}
.sol-card:hover{border-color:var(--accent);transform:translateY(-2px)}
</style>
</head>
<body>
<div class="shell">
<?php include '../includes/nav.php'; ?>
<main class="main">
 <div class="ph">
  <div>
   <div class="ph-title">Mis Solicitudes</div>
   <div class="ph-sub">Solicitudes de mantenimiento creadas por <?=htmlspecialchars($nombre)?></div>
  </div>
  <div class="ph-actions">
   <select class="fc" id="f-est" onchange="render()" style="width:180px;margin-right:10px">
    <option value="">Todos los estados</option>
    <option>PENDIENTE</option><option>EN_PROCESO</option>
    <option>EN_ESPERA</option><option>CERRADA</option><option>CANCELADA</option>
   </select>
   <button class="btn btn-p" onclick="openModal('m-nueva')">+ Nueva Solicitud</button>
  </div>
 </div>

 <!-- Stats solo para admin/supervisor -->
 <?php if(in_array($rol,['ADMIN','SUPERVISOR'])): ?>
 <div class="stat-grid" id="stats-sol" style="margin-bottom:20px"></div>
 <?php endif; ?>

 <div id="lista-sol"><div class="loader"><div class="spinner"></div></div></div>
</main>
</div>

<!-- MODAL NUEVA SOLICITUD -->
<div class="modal-ov" id="m-nueva">
 <div class="modal modal-lg">
  <div class="modal-hd"><div class="modal-title">Nueva Solicitud</div><button class="modal-close">✕</button></div>
  <div class="modal-body">
   <div class="fg"><label class="fl">Título del problema *</label><input class="fc" id="n-tit" placeholder="Ej: Falla en compresor del área A"></div>
   <div class="fr">
    <div class="fg"><label class="fl">Equipo *</label><select class="fc" id="n-eq"><option value="">Seleccionar...</option></select></div>
    <div class="fg"><label class="fl">Prioridad *</label><select class="fc" id="n-pr"><option value="">Seleccionar...</option></select></div>
   </div>
   <div class="fg"><label class="fl">Tipo</label>
    <select class="fc" id="n-tp"><option>CORRECTIVO</option><option>PREVENTIVO</option><option>PREDICTIVO</option></select>
   </div>
   <div class="fg"><label class="fl">Descripción detallada *</label>
    <textarea class="fc" id="n-des" rows="4" placeholder="Describe el problema con el mayor detalle posible: síntomas, cuándo ocurrió, área exacta..."></textarea>
   </div>
  </div>
  <div class="modal-ft">
   <button class="btn btn-s" onclick="closeModal('m-nueva')">Cancelar</button>
   <button class="btn btn-p" onclick="crearSolicitud()">Enviar Solicitud</button>
  </div>
 </div>
</div>

<!-- MODAL DETALLE -->
<div class="modal-ov" id="m-det">
 <div class="modal modal-lg">
  <div class="modal-hd"><div><div class="modal-title" id="dt-tit"></div><div style="font-size:12px;color:var(--t2)" id="dt-sub"></div></div><button class="modal-close">✕</button></div>
  <div class="modal-body" id="dt-body"></div>
  <div class="modal-ft"><button class="btn btn-s" onclick="closeModal('m-det')">Cerrar</button></div>
 </div>
</div>

<script src="../assets/js/app.js"></script>
<script>
const ID_USUARIO = <?= (int)$idUsuario ?>;
const ROL = '<?= htmlspecialchars($rol) ?>';
const PASOS = ['PENDIENTE','EN_ESPERA','EN_PROCESO','CERRADA'];
const PASO_L = ['Pendiente','En Espera','En Proceso','Completada'];

let solicitudes=[], equips=[], prios=[];

async function init(){
 [solicitudes, equips, prios] = await Promise.all([
  API.get('/ordenes.php'),
  API.get('/equipos.php'),
  API.get('/catalogos.php?tipo=prioridades'),
 ]);
 solicitudes = (solicitudes||[]);
 equips = (equips||[]);
 prios  = (prios||[]);

 // Filtrar por usuario si es SOLICITANTE
 if(ROL === 'SOLICITANTE'){
  solicitudes = solicitudes.filter(s => Number(s.idSolicitante) === ID_USUARIO);
 }

 fillSel('n-eq', equips, 'idEquipo',    'nombre',  'Seleccionar equipo...');
 fillSel('n-pr', prios,  'idPrioridad', 'nombre',  'Seleccionar prioridad...');

 // Stats para admin/supervisor
 const statsEl = document.getElementById('stats-sol');
 if(statsEl){
  const cnt = e => solicitudes.filter(s=>s.nombreEstado===e).length;
  statsEl.innerHTML = buildStats([
   {l:'Total',      v:solicitudes.length,       c:'c-y'},
   {l:'Pendientes', v:cnt('PENDIENTE'),          c:'c-y'},
   {l:'En Proceso', v:cnt('EN_PROCESO'),         c:'c-c'},
   {l:'Cerradas',   v:cnt('CERRADA'),            c:'c-g'},
  ]);
 }
 render();
}

function stepper(estado){
 if(estado==='CANCELADA') return `<span style="color:var(--red);font-size:12px;font-weight:700">✕ CANCELADA</span>`;
 let idx = PASOS.indexOf(estado);
 if(idx<0) idx=0;
 return `<div class="step-bar">${PASOS.map((p,i)=>`
  <div class="step ${i<idx?'done':i===idx?'active':''}">
   <div class="step-dot">${i<idx?'✓':i+1}</div>
   <div class="step-lbl">${PASO_L[i]}</div>
  </div>`).join('')}</div>`;
}

function render(){
 const filtroEst = document.getElementById('f-est').value;
 const f = solicitudes.filter(s => !filtroEst || s.nombreEstado===filtroEst);

 const el = document.getElementById('lista-sol');
 if(f.length===0){
  el.innerHTML='<div class="empty"><div class="empty-icon">✦</div>No tienes solicitudes aún. Crea tu primera solicitud.</div>';
  return;
 }
 el.innerHTML = f.map(s=>`
  <div class="sol-card" onclick="verDetalle(${s.idOrden})">
   <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px">
    <div>
     <div style="font-size:11px;color:var(--t3);font-family:var(--fm)">#${s.idOrden}</div>
     <div style="font-weight:600;font-size:14px;margin-top:2px">${s.titulo}</div>
    </div>
    ${badgeP(s.nombrePrioridad)}
   </div>
   ${stepper(s.nombreEstado)}
   <div style="display:flex;gap:10px;align-items:center;margin-top:10px;flex-wrap:wrap">
    ${badgeT(s.tipoMantenimiento)}
    <span style="font-size:11px;color:var(--t3)">${s.equipo||'—'}</span>
    <span style="font-size:11px;color:var(--t3);margin-left:auto">${fmtDate(s.fechaCreacion)}</span>
   </div>
  </div>`).join('');
}

async function verDetalle(id){
 const [o, tecs, rps] = await Promise.all([
  API.get(`/ordenes.php?path=${id}`),
  API.get(`/ordenes.php?path=${id}/tecnicos`),
  API.get(`/ordenes.php?path=${id}/repuestos`),
 ]);
 document.getElementById('dt-tit').textContent = `Solicitud #${id}`;
 document.getElementById('dt-sub').textContent = o.titulo||'';
 document.getElementById('dt-body').innerHTML = `
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px;margin-bottom:14px">
   ${[['Estado',badgeE(o.nombreEstado)],['Prioridad',badgeP(o.nombrePrioridad)],['Tipo',badgeT(o.tipoMantenimiento)],
      ['Equipo',o.equipo||'—'],['Creada',fmtDate(o.fechaCreacion)],['Programada',fmtDate(o.fechaProgramada)]]
     .map(([k,v])=>`<div class="info-box"><div class="info-lbl">${k}</div><div class="info-val">${v}</div></div>`).join('')}
  </div>
  ${o.descripcion?`<div class="info-box" style="margin-bottom:14px"><div class="info-lbl">Descripción</div><div style="font-size:13px;margin-top:4px;line-height:1.7">${o.descripcion}</div></div>`:''}
  <div style="font-family:var(--fw);font-weight:700;font-size:13px;margin-bottom:8px">Progreso</div>
  ${stepper(o.nombreEstado)}
  ${(tecs||[]).length>0?`
   <div style="font-family:var(--fw);font-weight:700;font-size:13px;margin:14px 0 8px">Técnicos Asignados</div>
   ${tecs.map(t=>`<div style="background:var(--card2);border:1px solid var(--border);border-radius:8px;padding:9px 14px;display:flex;justify-content:space-between;margin-bottom:6px">
    <div style="font-weight:600">${t.tecnico||t.nombre||'—'}</div><span class="badge b-bl">${t.rol||'Principal'}</span></div>`).join('')}`:''}
  ${o.observaciones?`<div style="background:var(--gD);border:1px solid #2ecc7130;border-radius:8px;padding:12px;margin-top:12px">
   <div style="font-size:11px;color:var(--green);text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px">Observaciones de cierre</div>
   <div style="font-size:13px">${o.observaciones}</div></div>`:''}
 `;
 openModal('m-det');
}

async function crearSolicitud(){
 const t  = document.getElementById('n-tit').value.trim();
 const eq = document.getElementById('n-eq').value;
 const pr = document.getElementById('n-pr').value;
 const d  = document.getElementById('n-des').value.trim();
 if(!t||!eq||!pr||!d){ toast('Complete todos los campos requeridos','warn'); return; }
 const r = await API.post('/ordenes.php',{
  titulo:t, idEquipo:Number(eq), idPrioridad:Number(pr),
  tipoMantenimiento: document.getElementById('n-tp').value,
  descripcion: d,
  idSolicitante: ID_USUARIO,
 });
 if(r.error||!r.ok){ toast(r.error||'Error al crear','err'); return; }
 toast('✓ Solicitud enviada correctamente');
 closeModal('m-nueva');
 ['n-tit','n-des'].forEach(id=>document.getElementById(id).value='');
 document.getElementById('n-eq').value='';
 document.getElementById('n-pr').value='';
 await init();
}

init();
</script>
</body></html>