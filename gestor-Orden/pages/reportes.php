<?php
require_once __DIR__.'/../includes/session.php';
requireLogin('../login.php');
requireRol(['ADMIN','SUPERVISOR']);
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reportes — MantTech</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.filtros-bar{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:16px 20px;margin-bottom:24px;display:flex;gap:14px;align-items:flex-end;flex-wrap:wrap}
.fb-group{display:flex;flex-direction:column;gap:5px;flex:1;min-width:140px}
.fb-group label{font-size:11px;color:var(--t3);text-transform:uppercase;letter-spacing:.8px}
.fb-group select,.fb-group input{background:var(--input);border:1px solid var(--border);border-radius:8px;padding:9px 12px;color:var(--t1);font-size:13px;outline:none}
.fb-group select:focus,.fb-group input:focus{border-color:var(--accent)}

.rep-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:24px}
.rep-kpi{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:20px;display:flex;align-items:center;gap:16px}
.rk-icon{width:48px;height:48px;border-radius:12px;display:grid;place-items:center;font-size:22px;flex-shrink:0}
.rk-n{font-family:var(--fw);font-size:28px;font-weight:800;line-height:1}
.rk-l{font-size:11px;color:var(--t3);text-transform:uppercase;letter-spacing:.8px;margin-top:3px}

.chart-wrap{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:22px;margin-bottom:20px}
.cw-title{font-family:var(--fw);font-size:14px;font-weight:700;color:var(--t2);text-transform:uppercase;letter-spacing:1px;margin-bottom:18px;display:flex;justify-content:space-between;align-items:center}
.bar-chart{display:flex;align-items:flex-end;gap:8px;height:160px}
.bar-col{display:flex;flex-direction:column;align-items:center;flex:1;height:100%;justify-content:flex-end;gap:5px}
.bar-body{border-radius:4px 4px 0 0;transition:height .8s ease;min-height:4px;width:100%}
.bar-lbl{font-size:10px;color:var(--t3);text-align:center;writing-mode:vertical-rl;transform:rotate(180deg);padding-bottom:4px}
.bar-val{font-size:11px;color:var(--t1);font-family:var(--fm);font-weight:500}

.pie-row{display:flex;gap:20px;flex-wrap:wrap;align-items:center}
.pie-svg{flex-shrink:0}
.pie-legend{display:flex;flex-direction:column;gap:10px}
.pl-item{display:flex;align-items:center;gap:10px}
.pl-dot{width:12px;height:12px;border-radius:3px;flex-shrink:0}
.pl-label{font-size:13px}
.pl-val{margin-left:auto;font-family:var(--fm);font-size:13px;color:var(--t2)}

.rep-table-wrap{overflow-x:auto}
</style>
</head>
<body>
<div class="shell">
<?php include __DIR__.'/../includes/nav.php'; ?>
<main class="main">

 <div class="ph">
  <div><div class="ph-title">Reportes y Analíticas</div><div class="ph-sub">Visión general del rendimiento operacional</div></div>
  <div class="ph-actions">
   <button class="btn" onclick="window.print()" style="gap:6px">🖨 Imprimir</button>
  </div>
 </div>

 <!-- Filtros -->
 <div class="filtros-bar">
  <div class="fb-group"><label>Período</label><select id="f-periodo" onchange="loadReportes()">
   <option value="1">Este mes</option><option value="3">Últimos 3 meses</option>
   <option value="6" selected>Últimos 6 meses</option><option value="12">Este año</option>
  </select></div>
  <div class="fb-group"><label>Tipo Orden</label><select id="f-tipo" onchange="loadReportes()">
   <option value="">Todos</option><option value="PREVENTIVO">Preventivo</option>
   <option value="CORRECTIVO">Correctivo</option><option value="PREDICTIVO">Predictivo</option>
  </select></div>
  <button class="btn btn-p" onclick="loadReportes()">Actualizar</button>
 </div>

 <!-- KPIs resumen -->
 <div class="rep-grid" id="rep-kpis"><div class="loader"><div class="spinner"></div></div></div>

 <div class="g2" style="margin-bottom:20px">
  <!-- Gráfico de barras por mes -->
  <div class="chart-wrap">
   <div class="cw-title">📊 Órdenes por Mes</div>
   <div class="bar-chart" id="bar-meses"></div>
  </div>

  <!-- Gráfico pie por estado -->
  <div class="chart-wrap">
   <div class="cw-title">◉ Distribución por Estado</div>
   <div class="pie-row" id="pie-estado"></div>
  </div>
 </div>

 <div class="g2" style="margin-bottom:20px">
  <!-- Top técnicos por carga -->
  <div class="chart-wrap">
   <div class="cw-title">◎ Carga por Técnico</div>
   <div id="bar-tecnicos"></div>
  </div>

  <!-- Top equipos -->
  <div class="chart-wrap">
   <div class="cw-title">⬡ Equipos con más Órdenes</div>
   <div id="bar-equipos"></div>
  </div>
 </div>

 <!-- Tabla consumo de repuestos -->
 <div class="card">
  <div class="card-title">◍ Consumo de Repuestos en el Período</div>
  <div class="rep-table-wrap"><table><thead><tr><th>Repuesto</th><th>Categoría</th><th>Código</th><th>Cantidad Usada</th><th>Stock Actual</th><th>Stock Mínimo</th><th>Estado</th></tr></thead>
  <tbody id="t-repuestos"></tbody></table></div>
 </div>

</main>
</div>
<script src="../assets/js/app.js"></script>
<script>
const COLORS = ['var(--accent)','#7c6cf8','#00c8b4','var(--green)','var(--red)','#e67e22','#3498db'];

function barH(items, keyL, keyV, color){
 if(!items||items.length===0) return '<div class="empty">Sin datos</div>';
 const max = Math.max(...items.map(i=>Number(i[keyV])||0));
 return items.map((i,idx)=>`
  <div style="background:var(--card2);border:1px solid var(--border);border-radius:8px;padding:10px 14px;margin-bottom:8px">
   <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
    <div style="font-size:13px;font-weight:500;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${i[keyL]||'—'}</div>
    <div style="font-family:var(--fm);font-weight:700;color:${COLORS[idx%COLORS.length]};margin-left:10px">${i[keyV]}</div>
   </div>
   <div style="height:6px;background:var(--border);border-radius:3px;overflow:hidden">
    <div style="height:100%;width:${max?Math.round(Number(i[keyV])/max*100):0}%;background:${COLORS[idx%COLORS.length]};border-radius:3px;transition:width .8s ease"></div>
   </div>
  </div>`).join('');
}

async function loadReportes(){
 try{
  const meses = document.getElementById('f-periodo').value;
  const tipo  = document.getElementById('f-tipo').value;

  const [dash, ords, tecs, reps, consumo] = await Promise.all([
   API.get('/dashboard.php'),
   API.get('/ordenes.php'),
   API.get('/tecnicos.php'),
   API.get('/repuestos.php'),
   API.get('/repuestos.php?path=consumo'),
  ]);

  const s = dash.stats||{};
  const tend = dash.tendencia||[];
  const topEq = dash.topEquipos||[];

  // Filtrar por período
  const fechaLimite = new Date();
  fechaLimite.setMonth(fechaLimite.getMonth()-Number(meses));
  const filtradas = Array.isArray(ords) ? ords.filter(o=>{
   if(!o.fechaCreacion) return true;
   const f = new Date(o.fechaCreacion);
   return f >= fechaLimite && (!tipo || o.tipoMantenimiento===tipo);
  }) : [];

  // KPIs
  const cerradas = filtradas.filter(o=>o.nombreEstado==='CERRADA');
  const pctCierre = filtradas.length ? Math.round(cerradas.length/filtradas.length*100) : 0;
  document.getElementById('rep-kpis').innerHTML=`
   <div class="rep-kpi"><div class="rk-icon" style="background:var(--aD)">◉</div>
    <div><div class="rk-n" style="color:var(--accent)">${filtradas.length}</div><div class="rk-l">Órdenes Período</div></div></div>
   <div class="rep-kpi"><div class="rk-icon" style="background:#2ecc7118">✓</div>
    <div><div class="rk-n" style="color:var(--green)">${cerradas.length}</div><div class="rk-l">Cerradas</div></div></div>
   <div class="rep-kpi"><div class="rk-icon" style="background:#7c6cf818">%</div>
    <div><div class="rk-n" style="color:#7c6cf8">${pctCierre}%</div><div class="rk-l">Tasa de Cierre</div></div></div>
   <div class="rep-kpi"><div class="rk-icon" style="background:var(--rD)">◍</div>
    <div><div class="rk-n" style="color:var(--red)">${s.stock_bajo||0}</div><div class="rk-l">Stock Bajo</div></div></div>
  `;

  // Barras por mes
  const maxTend = Math.max(...tend.map(t=>Number(t.total)),1);
  document.getElementById('bar-meses').innerHTML = tend.length===0
   ? '<div class="empty" style="width:100%">Sin datos de tendencia</div>'
   : tend.map((t,i)=>`
    <div class="bar-col">
     <div class="bar-val">${t.total}</div>
     <div class="bar-body" style="height:${Math.round(Number(t.total)/maxTend*140)}px;background:${COLORS[i%COLORS.length]}"></div>
     <div class="bar-lbl">${t.mes}</div>
    </div>`).join('');

  // Pie estados
  const estados = {};
  filtradas.forEach(o=>{estados[o.nombreEstado]=(estados[o.nombreEstado]||0)+1});
  const pieData = Object.entries(estados).map(([k,v],i)=>({k,v,c:COLORS[i%COLORS.length]}));
  const pieTotal = pieData.reduce((a,b)=>a+b.v,0);
  // Círculo SVG simple
  let offset=0; const R=60, CX=70, CY=70;
  const arcs = pieData.map(d=>{
   const pct=d.v/pieTotal; const angle=pct*2*Math.PI;
   const x1=CX+R*Math.sin(offset*2*Math.PI); const y1=CY-R*Math.cos(offset*2*Math.PI);
   offset+=pct;
   const x2=CX+R*Math.sin(offset*2*Math.PI); const y2=CY-R*Math.cos(offset*2*Math.PI);
   const large=pct>.5?1:0;
   return `<path d="M${CX},${CY} L${x1},${y1} A${R},${R} 0 ${large},1 ${x2},${y2} Z" fill="${d.c}" opacity=".85"/>`;
  }).join('');
  document.getElementById('pie-estado').innerHTML=`
   <svg class="pie-svg" width="140" height="140" viewBox="0 0 140 140"><circle cx="${CX}" cy="${CY}" r="${R}" fill="var(--card2)"/>${arcs}</svg>
   <div class="pie-legend">${pieData.map(d=>`
    <div class="pl-item"><div class="pl-dot" style="background:${d.c}"></div>
    <div class="pl-label">${d.k||'—'}</div><div class="pl-val">${d.v}</div></div>`).join('')}
   </div>`;

  // Carga por técnico
  const cargaTec = {};
  filtradas.forEach(o=>{if(o.tecnicoAsignado){cargaTec[o.tecnicoAsignado]=(cargaTec[o.tecnicoAsignado]||0)+1}});
  const tecItems = Object.entries(cargaTec).map(([k,v])=>({tecnico:k,total:v})).sort((a,b)=>b.total-a.total).slice(0,6);
  document.getElementById('bar-tecnicos').innerHTML = barH(tecItems,'tecnico','total');

  // Top equipos
  document.getElementById('bar-equipos').innerHTML = barH(topEq,'equipo','total');

  // Tabla repuestos consumidos
  document.getElementById('t-repuestos').innerHTML = Array.isArray(consumo) && consumo.length>0
   ? consumo.map(r=>{
      const pct=Math.min(100,Math.round(Number(r.stockActual)/Math.max(Number(r.stockMinimo)*2,1)*100));
      const bajo=Number(r.stockActual)<=Number(r.stockMinimo);
      return`<tr>
       <td style="font-weight:500">${r.nombre}</td>
       <td style="color:var(--t2)">${r.categoria||'—'}</td>
       <td style="font-family:var(--fm);color:var(--t3)">${r.codigo||'—'}</td>
       <td style="text-align:center;font-family:var(--fw);font-weight:800;color:var(--accent)">${r.totalConsumido||0}</td>
       <td style="text-align:center">${r.stockActual||0}</td>
       <td style="text-align:center;color:var(--t3)">${r.stockMinimo||0}</td>
       <td>${bajo?'<span style="color:var(--red);font-weight:700;font-size:11px">⚠ BAJO</span>':'<span style="color:var(--green);font-size:11px">✓ OK</span>'}</td>
      </tr>`}).join('')
   : `<tr><td colspan="7"><div class="empty">Sin datos de consumo en el período</div></td></tr>`;

 }catch(e){console.error(e)}
}
loadReportes();
</script>
</body></html>