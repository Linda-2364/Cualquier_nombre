<?php
require_once __DIR__.'/../includes/session.php';
requireLogin('../login.php');
requireRol(['TECNICO']);
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Mi Dashboard — MantTech</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.dash-hero{background:linear-gradient(135deg,var(--card) 0%,var(--card2) 100%);border:1px solid var(--border);border-radius:16px;padding:28px 32px;margin-bottom:24px;display:flex;align-items:center;gap:24px}
.dh-av{width:72px;height:72px;background:var(--aD);border:3px solid var(--accent);border-radius:16px;display:grid;place-items:center;font-family:var(--fw);font-size:28px;font-weight:800;color:var(--accent);flex-shrink:0}
.dh-info{flex:1}
.dh-saludo{font-size:13px;color:var(--t3);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px}
.dh-nombre{font-family:var(--fw);font-size:24px;font-weight:800;margin-bottom:6px}
.dh-badges{display:flex;gap:8px;flex-wrap:wrap}
.dh-badge{padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase}
.dh-esp{background:#7c6cf820;color:#7c6cf8;border:1px solid #7c6cf850}
.dh-urg{background:var(--rD);color:var(--red);border:1px solid #ff475740;animation:pulse-badge 2s infinite}
@keyframes pulse-badge{0%,100%{opacity:1}50%{opacity:.6}}

.kpi-row{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px}
.kpi{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:20px;text-align:center;transition:border-color .2s}
.kpi:hover{border-color:var(--accent)}
.kpi-n{font-family:var(--fw);font-size:36px;font-weight:800;line-height:1;margin-bottom:6px}
.kpi-l{font-size:11px;color:var(--t3);text-transform:uppercase;letter-spacing:.8px}
.kpi-bar{height:3px;border-radius:2px;margin-top:14px;background:var(--border)}
.kpi-fill{height:100%;border-radius:2px;transition:width .8s ease}

.ord-urgentes{background:linear-gradient(135deg,#ff475712,#ff475705);border:1px solid #ff475730;border-radius:14px;padding:20px;margin-bottom:20px}
.ou-title{font-family:var(--fw);font-size:15px;font-weight:700;color:var(--red);margin-bottom:14px;display:flex;align-items:center;gap:8px}
.ou-item{background:#ff475718;border:1px solid #ff475740;border-radius:10px;padding:13px 16px;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center;gap:12px;cursor:pointer;transition:all .2s}
.ou-item:hover{background:#ff475730;border-color:#ff4757}
.ou-item:last-child{margin-bottom:0}

.timeline{position:relative;padding-left:24px}
.timeline::before{content:'';position:absolute;left:7px;top:8px;bottom:8px;width:2px;background:linear-gradient(180deg,var(--accent),#7c6cf8,var(--green))}
.tl-item{position:relative;margin-bottom:18px;padding:14px 16px;background:var(--card2);border:1px solid var(--border);border-radius:10px}
.tl-item::before{content:'';position:absolute;left:-21px;top:18px;width:10px;height:10px;border-radius:50%;background:var(--accent);border:2px solid var(--bg)}
.tl-fecha{font-size:11px;color:var(--t3);margin-bottom:4px}
.tl-titulo{font-size:13px;font-weight:600;margin-bottom:6px}
.tl-meta{display:flex;gap:8px;flex-wrap:wrap}
.prog-wrap{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:20px}
.prog-title{font-family:var(--fw);font-size:14px;font-weight:700;margin-bottom:16px;color:var(--t2);text-transform:uppercase;letter-spacing:1px}
.prog-item{margin-bottom:16px}
.prog-label{display:flex;justify-content:space-between;font-size:12px;margin-bottom:6px}
.prog-bar{height:8px;background:var(--border);border-radius:4px;overflow:hidden}
.prog-fill{height:100%;border-radius:4px;transition:width 1s ease}
</style>
</head>
<body>
<div class="shell">
<?php include __DIR__.'/../includes/nav.php'; ?>
<main class="main">

 <div class="ph">
  <div><div class="ph-title">Mi Dashboard</div><div class="ph-sub" id="fecha-hoy"></div></div>
  <div class="ph-actions">
   <button class="btn btn-p" onclick="location.href='mis_ordenes.php'">Ver Mis Órdenes</button>
  </div>
 </div>

 <!-- Hero técnico -->
 <div class="dash-hero" id="hero">
  <div class="dh-av" id="hero-av">…</div>
  <div class="dh-info">
   <div class="dh-saludo">Bienvenido de nuevo</div>
   <div class="dh-nombre" id="hero-nombre">Cargando...</div>
   <div class="dh-badges" id="hero-badges"></div>
  </div>
 </div>

 <!-- KPIs -->
 <div class="kpi-row" id="kpis">
  <div class="loader"><div class="spinner"></div></div>
 </div>

 <div class="g2" style="margin-bottom:20px">
  <!-- Órdenes urgentes primero -->
  <div>
   <div class="ord-urgentes" id="urgentes-wrap" style="display:none">
    <div class="ou-title">🚨 Órdenes CRÍTICAS / EMERGENCIA</div>
    <div id="urgentes-list"></div>
   </div>
   <div class="card">
    <div class="card-title">📋 Próximas Órdenes Programadas</div>
    <div class="tw"><table><thead><tr><th>#</th><th>Título</th><th>Equipo</th><th>Programada</th><th>Estado</th></tr></thead>
    <tbody id="t-proximas"></tbody></table></div>
   </div>
  </div>

  <!-- Panel derecho -->
  <div style="display:flex;flex-direction:column;gap:16px">
   <div class="prog-wrap">
    <div class="prog-title">📊 Distribución de Mis Órdenes</div>
    <div id="distribucion"></div>
   </div>
   <div class="card">
    <div class="card-title">⏱ Actividad Reciente</div>
    <div class="timeline" id="timeline">
     <div class="loader"><div class="spinner"></div></div>
    </div>
   </div>
  </div>
 </div>

</main>
</div>
<script src="../assets/js/app.js"></script>
<script>
document.getElementById('fecha-hoy').textContent =
 new Date().toLocaleDateString('es-BO',{weekday:'long',year:'numeric',month:'long',day:'numeric'});

async function loadDash(){
 try{
  const [dash, usrs] = await Promise.all([API.get('/dashboard.php'), API.get('/usuarios.php')]);
  const s = dash.stats||{};
  const rec = dash.recientes||[];
  const idTec = dash.idTecnico;
  const USR_ID = <?=(int)($u['id']??0)?>;
  const yo = Array.isArray(usrs) ? usrs.find(x=>Number(x.idUsuario)===USR_ID) : null;
  const nombre = yo ? (yo.nombre||'Técnico') : '<?=htmlspecialchars($u['nombre']??'Técnico')?>';
  const ini = nombre.split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase();

  document.getElementById('hero-av').textContent    = ini;
  document.getElementById('hero-nombre').textContent = nombre;

  const urgCnt = Number(s.urgentes||0);
  let badges = `<span class="dh-badge dh-esp">⚙ Técnico</span>`;
  if(urgCnt>0) badges += `<span class="dh-badge dh-urg">🚨 ${urgCnt} URGENTE${urgCnt>1?'S':''}</span>`;
  document.getElementById('hero-badges').innerHTML = badges;

  // KPIs
  const tot  = Number(s.total||0);
  const pend = Number(s.pendientes||0);
  const proc = Number(s.en_proceso||0);
  const cerr = Number(s.cerradas||0);
  document.getElementById('kpis').innerHTML=`
   <div class="kpi"><div class="kpi-n" style="color:var(--accent)">${tot}</div><div class="kpi-l">Total Asignadas</div>
    <div class="kpi-bar"><div class="kpi-fill" style="width:100%;background:var(--accent)"></div></div></div>
   <div class="kpi"><div class="kpi-n" style="color:#7c6cf8">${proc}</div><div class="kpi-l">En Proceso</div>
    <div class="kpi-bar"><div class="kpi-fill" style="width:${tot?Math.round(proc/tot*100):0}%;background:#7c6cf8"></div></div></div>
   <div class="kpi"><div class="kpi-n" style="color:var(--green)">${cerr}</div><div class="kpi-l">Cerradas</div>
    <div class="kpi-bar"><div class="kpi-fill" style="width:${tot?Math.round(cerr/tot*100):0}%;background:var(--green)"></div></div></div>
   <div class="kpi"><div class="kpi-n" style="color:${urgCnt>0?'var(--red)':'var(--t3)'}">${urgCnt}</div><div class="kpi-l">Urgentes</div>
    <div class="kpi-bar"><div class="kpi-fill" style="width:${tot?Math.round(urgCnt/tot*100):0}%;background:var(--red)"></div></div></div>
  `;

  // Órdenes urgentes
  const urgentes = rec.filter(o=>['CRITICA','EMERGENCIA'].includes(o.prioridad) && !['CERRADA','CANCELADA'].includes(o.estado));
  if(urgentes.length>0){
   document.getElementById('urgentes-wrap').style.display='block';
   document.getElementById('urgentes-list').innerHTML = urgentes.map(o=>`
    <div class="ou-item" onclick="location.href='mis_ordenes.php'">
     <div><div style="font-weight:600;font-size:13px">${o.titulo}</div>
     <div style="font-size:11px;color:var(--t3);margin-top:3px">${o.equipo||'—'}</div></div>
     <div style="display:flex;flex-direction:column;align-items:flex-end;gap:5px">
      ${badgeP(o.prioridad)}${badgeE(o.estado)}</div>
    </div>`).join('');
  }

  // Próximas programadas
  const proximas = rec.filter(o=>!['CERRADA','CANCELADA'].includes(o.estado));
  document.getElementById('t-proximas').innerHTML = proximas.length===0
   ? `<tr><td colspan="5"><div class="empty"><div class="empty-icon">◉</div>Sin órdenes pendientes</div></td></tr>`
   : proximas.map(o=>`<tr>
    <td style="color:var(--t3);font-family:var(--fm)">#${o.idOrden}</td>
    <td style="font-weight:500">${o.titulo}</td>
    <td style="color:var(--t2)">${o.equipo||'—'}</td>
    <td style="color:var(--t3);font-size:12px">${o.fechaProgramada ? new Date(o.fechaProgramada).toLocaleDateString('es-BO') : '—'}</td>
    <td>${badgeE(o.estado)}</td></tr>`).join('');

  // Distribución
  const espera = Number(s.en_espera||0);
  const items = [
   {l:'Pendientes', v:pend, c:'var(--accent)', pct:tot?Math.round(pend/tot*100):0},
   {l:'En Proceso',  v:proc, c:'#7c6cf8',      pct:tot?Math.round(proc/tot*100):0},
   {l:'En Espera',   v:espera, c:'var(--orange)', pct:tot?Math.round(espera/tot*100):0},
   {l:'Cerradas',    v:cerr, c:'var(--green)',  pct:tot?Math.round(cerr/tot*100):0},
  ];
  document.getElementById('distribucion').innerHTML = items.map(i=>`
   <div class="prog-item">
    <div class="prog-label"><span>${i.l}</span><span style="color:${i.c};font-weight:700">${i.v}</span></div>
    <div class="prog-bar"><div class="prog-fill" style="width:${i.pct}%;background:${i.c}"></div></div>
   </div>`).join('');

  // Timeline
  document.getElementById('timeline').innerHTML = rec.length===0
   ? '<div class="empty">Sin actividad reciente</div>'
   : rec.slice(0,5).map(o=>`
    <div class="tl-item">
     <div class="tl-fecha">${o.fechaProgramada ? new Date(o.fechaProgramada).toLocaleDateString('es-BO') : 'Sin fecha'}</div>
     <div class="tl-titulo">${o.titulo}</div>
     <div class="tl-meta">${badgeE(o.estado)}${badgeP(o.prioridad)}</div>
    </div>`).join('');

 }catch(e){console.error(e)}
}
loadDash();
</script>
</body></html>