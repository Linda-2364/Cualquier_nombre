<?php
require_once 'includes/session.php';
requireLogin('index.php');
$u   = getUsuario();
$rol = getRol();

// Redirigir tecnicos y solicitantes a su dashboard
if ($rol === 'TECNICO')     { header('Location: pages/dashboard_tecnico.php'); exit; }
if ($rol === 'SOLICITANTE') { header('Location: pages/dashboard_solicitante.php'); exit; }
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard — MantTech</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="shell">
<?php $currentPage='index'; include 'includes/nav_root.php'; ?>
<main class="main">
  <div class="ph">
    <div>
      <div class="ph-title">Dashboard</div>
      <div class="ph-sub" id="fecha"></div>
    </div>
    <div class="ph-actions">
      <button class="btn btn-p" onclick="location.href='pages/ordenes.php'">+ Nueva Orden</button>
    </div>
  </div>

  <div class="stat-grid" id="stats"><div class="loader"><div class="spinner"></div></div></div>
  <div id="alertas"></div>

  <div class="g2" style="margin-bottom:20px">
    <div class="card">
      <div class="card-title">Órdenes Recientes</div>
      <div class="tw"><table><thead><tr><th>#</th><th>Título</th><th>Tipo</th><th>Prioridad</th><th>Estado</th></tr></thead>
      <tbody id="t-ord"></tbody></table></div>
    </div>
    <div class="card">
      <div class="card-title">Carga por Técnico</div>
      <div id="carga-tec"></div>
    </div>
  </div>

  <div class="g2">
    <div class="card"><div class="card-title">◍ Stock Crítico</div><div id="stock-crit"></div></div>
    <div class="card"><div class="card-title">⚡ Órdenes Urgentes</div><div id="ord-crit"></div></div>
  </div>
</main>
</div>
<script src="assets/js/app.js"></script>
<script>
document.getElementById('fecha').textContent=new Date().toLocaleDateString('es-BO',{weekday:'long',year:'numeric',month:'long',day:'numeric'});
async function loadDash(){
  const[ords,tecs,reps,pend]=await Promise.all([
    API.get('/ordenes.php'),API.get('/tecnicos.php'),
    API.get('/repuestos.php'),API.get('/ordenes.php?path=vista/pendientes')
  ]);
  const cnt=e=>ords.filter(o=>o.nombreEstado===e).length;
  const bajo=reps.filter(r=>Number(r.stockActual)<=Number(r.stockMinimo));
  const criticas=ords.filter(o=>['CRITICA','EMERGENCIA'].includes(o.nombrePrioridad)&&!['CERRADA','CANCELADA'].includes(o.nombreEstado));
  document.getElementById('stats').innerHTML=buildStats([
    {l:'Total Órdenes',v:ords.length,c:'c-y'},{l:'Pendientes',v:cnt('PENDIENTE'),c:'c-y'},
    {l:'En Proceso',v:cnt('EN_PROCESO'),c:'c-c'},{l:'En Espera',v:cnt('EN_ESPERA'),c:'c-p'},
    {l:'Cerradas',v:cnt('CERRADA'),c:'c-g'},{l:'Técnicos',v:tecs.length,c:'c-b'},
    {l:'Stock Bajo',v:bajo.length,c:'c-r'},{l:'Urgentes',v:criticas.length,c:'c-o'},
  ]);
  if(bajo.length>0||criticas.length>0){
    document.getElementById('alertas').innerHTML=
      (bajo.length?`<div class="alert alert-err" style="margin-bottom:12px">⚠ ${bajo.length} repuesto(s) con stock bajo.</div>`:'')
      +(criticas.length?`<div class="alert alert-warn" style="margin-bottom:20px">🚨 ${criticas.length} orden(es) urgente(s) requieren atención.</div>`:'');
  }
  document.getElementById('t-ord').innerHTML=ords.slice(0,8).map(o=>`<tr>
    <td style="color:var(--t3);font-family:var(--fm)">#${o.idOrden}</td>
    <td style="font-weight:500;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${o.titulo}</td>
    <td>${badgeT(o.tipoMantenimiento)}</td><td>${badgeP(o.nombrePrioridad)}</td><td>${badgeE(o.nombreEstado)}</td>
  </tr>`).join('')||'<tr><td colspan="5"><div class="empty">Sin órdenes</div></td></tr>';

  const xTec={};pend.forEach(p=>{if(!xTec[p.idTecnico])xTec[p.idTecnico]={...p,tot:0};xTec[p.idTecnico].tot++;});
  document.getElementById('carga-tec').innerHTML=Object.values(xTec).length===0
    ?'<div class="empty">Sin asignaciones pendientes</div>'
    :Object.values(xTec).map(t=>`
      <div style="background:var(--card2);border:1px solid var(--border);border-radius:8px;padding:11px 14px;display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:8px">
        <div style="display:flex;align-items:center;gap:10px">
          <div style="width:36px;height:36px;background:var(--aD);border:1px solid var(--accent);border-radius:8px;display:grid;place-items:center;color:var(--accent);font-family:var(--fw);font-weight:700;font-size:14px">
            ${(t.tecnico||'?').split(' ').map(w=>w[0]).slice(0,2).join('')}
          </div>
          <div><div style="font-weight:600;font-size:13px">${t.tecnico}</div><div style="font-size:11px;color:var(--t3)">${t.especialidad||''}</div></div>
        </div>
        <div style="text-align:right">
          <div style="font-family:var(--fw);font-weight:800;font-size:24px;color:${t.tot>3?'var(--red)':t.tot>1?'var(--accent)':'var(--green)'}">${t.tot}</div>
          <div style="font-size:10px;color:var(--t3)">órdenes</div>
        </div>
      </div>`).join('');

  document.getElementById('stock-crit').innerHTML=bajo.length===0
    ?'<div class="empty">✓ Stock en niveles normales</div>'
    :bajo.slice(0,5).map(r=>{
      const pct=Math.min(100,Math.round(Number(r.stockActual)/Math.max(Number(r.stockMinimo)*2,1)*100));
      return`<div style="background:var(--card2);border:1px solid var(--border);border-radius:8px;padding:10px 14px;margin-bottom:8px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
          <div><div style="font-weight:600;font-size:13px">${r.nombre}</div><div style="font-size:11px;color:var(--t3)">${r.codigo||'—'}</div></div>
          <div style="text-align:right"><span style="font-family:var(--fw);font-weight:800;font-size:20px;color:var(--red)">${r.stockActual}</span><div style="font-size:10px;color:var(--t3)">/ mín ${r.stockMinimo}</div></div>
        </div>
        <div class="stock-bar"><div class="stock-fill" style="width:${pct}%;background:var(--red)"></div></div>
      </div>`;
    }).join('');

  document.getElementById('ord-crit').innerHTML=criticas.length===0
    ?'<div class="empty">✓ Sin órdenes urgentes</div>'
    :criticas.slice(0,5).map(o=>`
      <div style="background:var(--rD);border:1px solid #ff475730;border-radius:8px;padding:10px 14px;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center;gap:10px">
        <div><div style="font-weight:600;font-size:13px">${o.titulo}</div><div style="font-size:11px;color:var(--t3)">${o.equipo||'—'}</div></div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px">${badgeT(o.tipoMantenimiento)}${badgeE(o.nombreEstado)}</div>
      </div>`).join('');
}
loadDash();
</script>
</body></html>