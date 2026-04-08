<?php
require_once '../includes/session.php';
requireRol(['TECNICO'], '../index.php');

$u         = getUsuario();
$idUsuario = $u['id'] ?? 0;

// Obtener idTecnico desde la BD usando el idUsuario de sesion
$db = getDB();
$st = $db->prepare("
    SELECT t.idTecnico 
    FROM tecnico t
    INNER JOIN persona p ON t.idPersona = p.idPersona
    INNER JOIN usuario u ON u.idPersona = p.idPersona
    WHERE u.idUsuario = ?
    LIMIT 1
");
$st->execute([$idUsuario]);
$tec = $st->fetch(PDO::FETCH_ASSOC);
$idTecnico = $tec ? (int)$tec['idTecnico'] : 0;
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Mis Órdenes — MantTech</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.orden-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:20px;margin-bottom:14px;position:relative;overflow:hidden;transition:border-color .2s,transform .2s}
.orden-card:hover{border-color:var(--accent);transform:translateY(-2px)}
.orden-card::before{content:'';position:absolute;left:0;top:0;bottom:0;width:4px;border-radius:4px 0 0 4px}
.orden-card.PENDIENTE::before{background:var(--accent)}
.orden-card.EN_PROCESO::before{background:#7c6cf8}
.orden-card.EN_ESPERA::before{background:#00c8b4}
.orden-card.CERRADA::before{background:var(--green)}
.urgente-badge{display:inline-flex;align-items:center;gap:5px;background:var(--rD);color:var(--red);border:1px solid #ff475740;border-radius:6px;padding:3px 10px;font-size:11px;font-weight:700;animation:pulse-u 2s infinite}
@keyframes pulse-u{0%,100%{opacity:1}50%{opacity:.6}}
.info-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;margin:14px 0}
.info-item{background:var(--card2);border:1px solid var(--border);border-radius:8px;padding:10px 12px}
.info-lbl{font-size:10px;color:var(--t3);text-transform:uppercase;letter-spacing:1px;margin-bottom:3px}
.info-val{font-size:13px;font-weight:600}
</style>
</head>
<body>
<div class="shell">
<?php include '../includes/nav.php'; ?>
<main class="main">
 <div class="ph">
  <div>
   <div class="ph-title">Mis Órdenes</div>
   <div class="ph-sub">Bienvenido, <?=htmlspecialchars($u['nombre']??'Técnico')?></div>
  </div>
 </div>

 <?php if($idTecnico === 0): ?>
 <div class="alert alert-err">
  ⚠ Tu usuario no está vinculado a ningún técnico. Contacta al administrador.
 </div>
 <?php else: ?>

 <div class="stat-grid" id="mis-stats"><div class="loader"><div class="spinner"></div></div></div>

 <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px">
  <select class="fc" id="filtro-estado" onchange="renderOrdenes()" style="max-width:200px">
   <option value="">Todos los estados</option>
   <option>PENDIENTE</option><option>EN_PROCESO</option>
   <option>EN_ESPERA</option><option>CERRADA</option>
  </select>
  <select class="fc" id="filtro-tipo" onchange="renderOrdenes()" style="max-width:200px">
   <option value="">Todos los tipos</option>
   <option>PREVENTIVO</option><option>CORRECTIVO</option><option>PREDICTIVO</option>
  </select>
 </div>

 <div id="lista-ordenes"><div class="loader"><div class="spinner"></div></div></div>

 <?php endif; ?>
</main>
</div>

<!-- MODAL ACTUALIZAR ESTADO -->
<div class="modal-ov" id="m-update">
 <div class="modal">
  <div class="modal-hd">
   <div><div class="modal-title">Actualizar Orden</div><div style="font-size:12px;color:var(--t2)" id="upd-sub"></div></div>
   <button class="modal-close">✕</button>
  </div>
  <div class="modal-body">
   <div class="fg">
    <label class="fl">Nuevo Estado</label>
    <select class="fc" id="upd-estado" onchange="toggleHoras()">
     <option value="2">EN PROCESO — Iniciar trabajo</option>
     <option value="3">EN ESPERA — Esperando repuestos</option>
     <option value="4">CERRADA — Trabajo finalizado</option>
    </select>
   </div>
   <div class="fg" id="div-horas" style="display:none">
    <label class="fl">Horas Reales Trabajadas</label>
    <input class="fc" id="upd-hrs" type="number" min="0" step="0.5" placeholder="ej. 3.5">
   </div>
   <div class="fg">
    <label class="fl">Observaciones *</label>
    <textarea class="fc" id="upd-obs" rows="3" placeholder="Describe el avance o resultado del trabajo..."></textarea>
   </div>
  </div>
  <div class="modal-ft">
   <button class="btn btn-s" onclick="closeModal('m-update')">Cancelar</button>
   <button class="btn btn-p" onclick="actualizarOrden()">Guardar Cambios</button>
  </div>
 </div>
</div>

<!-- MODAL REPUESTOS -->
<div class="modal-ov" id="m-reps">
 <div class="modal modal-lg">
  <div class="modal-hd">
   <div><div class="modal-title">◍ Repuestos de la Orden</div><div style="font-size:12px;color:var(--t2)" id="reps-sub"></div></div>
   <button class="modal-close">✕</button>
  </div>
  <div class="modal-body" id="reps-body"></div>
  <div class="modal-ft"><button class="btn btn-s" onclick="closeModal('m-reps')">Cerrar</button></div>
 </div>
</div>

<script src="../assets/js/app.js"></script>
<script>
const ID_TECNICO = <?= $idTecnico ?>;
let misOrdenes = [], estados = [];

// Mapa estado nombre → idEstado (se carga desde la API)
let mapaEstados = {};

function toggleHoras(){
 const sel = document.getElementById('upd-estado');
 const txt = sel.options[sel.selectedIndex]?.text || '';
 document.getElementById('div-horas').style.display = txt.includes('CERRADA') ? 'block' : 'none';
}

async function init(){
 if(ID_TECNICO === 0) return;

 // Cargar catálogo de estados y las dos fuentes de órdenes
 const [pend, ests, todas] = await Promise.all([
  API.get('/ordenes.php?path=vista/pendientes'),
  API.get('/catalogos.php?tipo=estados'),
  API.get('/ordenes.php')
 ]);

 estados = ests || [];
 mapaEstados = {};
 estados.forEach(e => { mapaEstados[e.nombre] = e.idEstado; });

 // Obtener los IDs de las órdenes que están asignadas a este técnico (según la vista pendientes)
 // La vista pendientes ya incluye el campo idTecnico y cubre todas las órdenes NO cerradas.
 const idsAsignados = new Set(
   (pend || [])
     .filter(o => Number(o.idTecnico) === ID_TECNICO)
     .map(o => Number(o.idOrden))
 );

 // Ahora filtrar TODAS las órdenes (incluyendo cerradas) cuyos ID estén en ese conjunto
 misOrdenes = (todas || [])
   .filter(ord => idsAsignados.has(Number(ord.idOrden)))
   .map(ord => ({ ...ord, estado: ord.nombreEstado }));

 // Si por algún motivo no se obtuvieron órdenes (ejemplo: el técnico no tiene ninguna pendiente,
 // pero sí tiene cerradas que no aparecen en la vista pendientes), hacemos un segundo intento:
 // buscar en todas las órdenes aquellas que tengan idTecnico explícito (si la API lo devuelve).
 // Esto es opcional, pero lo dejamos como respaldo.
 if(misOrdenes.length === 0 && todas && todas.length) {
   // Algunas APIs devuelven idTecnico directamente en /ordenes.php
   const porIdTecnico = todas.filter(ord => Number(ord.idTecnico) === ID_TECNICO);
   if(porIdTecnico.length) {
     misOrdenes = porIdTecnico.map(ord => ({ ...ord, estado: ord.nombreEstado }));
   }
 }

 // Stats
 const cnt = e => misOrdenes.filter(o => (o.estado || o.nombreEstado) === e).length;
 document.getElementById('mis-stats').innerHTML = buildStats([
  {l:'Total Asignadas', v:misOrdenes.length,   c:'c-y'},
  {l:'Pendientes',      v:cnt('PENDIENTE'),     c:'c-y'},
  {l:'En Proceso',      v:cnt('EN_PROCESO'),    c:'c-c'},
  {l:'En Espera',       v:cnt('EN_ESPERA'),     c:'c-b'},
 ]);

 renderOrdenes();
}

let ordenActual = null;

function renderOrdenes(){
 const filtroEst  = document.getElementById('filtro-estado')?.value||'';
 const filtroTipo = document.getElementById('filtro-tipo')?.value||'';

 const filtradas = misOrdenes.filter(o=>{
  const est  = o.estado || o.nombreEstado || '';
  const tipo = o.tipoMantenimiento || '';
  return (!filtroEst||est===filtroEst) && (!filtroTipo||tipo===filtroTipo);
 });

 // Ordenar: CRITICA/EMERGENCIA primero
 filtradas.sort((a,b)=>{
  const pA={'EMERGENCIA':0,'CRITICA':1,'ALTA':2,'MEDIA':3,'BAJA':4};
  const pB={'EMERGENCIA':0,'CRITICA':1,'ALTA':2,'MEDIA':3,'BAJA':4};
  return (pA[a.nombrePrioridad]??5)-(pB[b.nombrePrioridad]??5);
 });

 const el = document.getElementById('lista-ordenes');
 if(filtradas.length===0){
  el.innerHTML='<div class="empty"><div class="empty-icon">◉</div>No tienes órdenes asignadas con estos filtros</div>';
  return;
 }

 el.innerHTML = filtradas.map(o=>{
  const est  = o.estado || o.nombreEstado || '';
  const prio = o.nombrePrioridad || '';
  const esUrgente = ['CRITICA','EMERGENCIA'].includes(prio);
  return `
   <div class="orden-card ${est}">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:10px">
     <div style="flex:1;min-width:200px">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap">
       <span style="font-family:var(--fm);font-size:11px;color:var(--t3)">#${o.idOrden}</span>
       ${badgeT(o.tipoMantenimiento)}
       ${esUrgente?`<span class="urgente-badge">🚨 ${prio}</span>`:''}
      </div>
      <div style="font-family:var(--fw);font-weight:700;font-size:16px">${o.titulo}</div>
     </div>
     <div style="display:flex;flex-direction:column;align-items:flex-end;gap:5px">
      ${badgeE(est)}
      ${!esUrgente?badgeP(prio):''}
     </div>
    </div>

    <div class="info-grid">
     <div class="info-item"><div class="info-lbl">Equipo</div><div class="info-val">${o.equipo||'—'}</div></div>
     <div class="info-item"><div class="info-lbl">Especialidad req.</div><div class="info-val">${o.especialidad||'—'}</div></div>
     <div class="info-item"><div class="info-lbl">Programada</div><div class="info-val">${fmtDate(o.fechaProgramada)}</div></div>
     <div class="info-item"><div class="info-lbl">Prioridad</div><div class="info-val">${prio||'—'}</div></div>
    </div>

    <div style="display:flex;gap:8px;flex-wrap:wrap">
     ${est!=='CERRADA'?`<button class="btn btn-p btn-sm" onclick="abrirUpdate(${o.idOrden},'${(o.titulo||'').replace(/'/g,"\\'")}')">✎ Actualizar Estado</button>`:''}
     <button class="btn btn-s btn-sm" onclick="verRepuestos(${o.idOrden},'${(o.titulo||'').replace(/'/g,"\\'")}')">◍ Ver Repuestos</button>
    </div>
   </div>`;
 }).join('');
}

function abrirUpdate(id, titulo){
 ordenActual = misOrdenes.find(o=>o.idOrden==id||Number(o.idOrden)===Number(id));
 document.getElementById('upd-sub').textContent = `#${id} — ${titulo}`;
 document.getElementById('upd-obs').value='';
 document.getElementById('upd-hrs').value='';
 document.getElementById('div-horas').style.display='none';
 openModal('m-update');
}

async function actualizarOrden(){
 const sel     = document.getElementById('upd-estado');
 const idEst   = sel.value;
 const esCierre= sel.options[sel.selectedIndex]?.text?.includes('CERRADA');
 const obs     = document.getElementById('upd-obs').value.trim();
 const hrs     = document.getElementById('upd-hrs').value;

 if(!obs){ toast('Las observaciones son requeridas','warn'); return; }

 let r;
 if(esCierre){
  r = await API.put(`/ordenes.php?path=${ordenActual.idOrden}/cerrar`,{
   horasReales:  hrs||null,
   observaciones:obs,
  });
 } else {
  r = await API.put(`/ordenes.php?path=${ordenActual.idOrden}/estado`,{
   idEstado: Number(idEst),
  });
 }

 if(r.error||!r.ok){ toast(r.error||'Error al actualizar','err'); return; }
 toast(r.mensaje||'✓ Orden actualizada');
 closeModal('m-update');
 await init();
}

async function verRepuestos(id, titulo){
 document.getElementById('reps-sub').textContent=`#${id} — ${titulo}`;
 document.getElementById('reps-body').innerHTML='<div class="loader"><div class="spinner"></div></div>';
 openModal('m-reps');
 const lista = await API.get(`/ordenes.php?path=${id}/repuestos`);
 const total = (lista||[]).reduce((a,r)=>a+Number(r.cantidad)*Number(r.precioUnitario||0),0);
 document.getElementById('reps-body').innerHTML = (lista||[]).length===0
  ? '<div class="empty"><div class="empty-icon">◍</div>Sin repuestos en esta orden</div>'
  : (lista.map(r=>`
   <div style="background:var(--card2);border:1px solid var(--border);border-radius:8px;padding:10px 14px;display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:8px">
    <div style="flex:1">
     <div style="font-weight:600">${r.nombre||'—'}</div>
     <div style="font-size:11px;color:var(--t3)">${r.codigo||''}${r.observacion?' · '+r.observacion:''}</div>
    </div>
    <div style="text-align:center;min-width:44px">
     <div style="font-size:10px;color:var(--t3)">CANT.</div>
     <div style="font-family:var(--fw);font-weight:800;font-size:20px;color:var(--accent);line-height:1">${r.cantidad}</div>
    </div>
    <div style="text-align:right;min-width:88px">
     <div style="font-size:10px;color:var(--t3)">SUBTOTAL</div>
     <div style="font-family:var(--fm);font-weight:600;color:var(--cyan)">${fmtBs(Number(r.cantidad)*Number(r.precioUnitario||0))}</div>
    </div>
   </div>`).join('')
  + `<div style="background:var(--card2);border:1px solid #00c8b430;border-radius:8px;padding:10px 14px;display:flex;justify-content:space-between;align-items:center;margin-top:4px">
      <span style="font-weight:700;color:var(--cyan)">TOTAL</span>
      <span style="font-family:var(--fw);font-weight:800;font-size:20px;color:var(--cyan)">${fmtBs(total)}</span>
     </div>`);
}

init();
</script>
</body></html>