<?php
require_once '../includes/session.php';
requireRol(['ADMIN','SUPERVISOR'],'../index.php');
$idUsuario = getUsuario()['id'] ?? 1;
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Órdenes — MantTech</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.filters{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px}
.filters .fc{flex:1;min-width:160px}
</style>
</head>
<body>
<div class="shell">
<?php include '../includes/nav.php'; ?>
<main class="main">
 <div class="ph">
  <div><div class="ph-title">Órdenes de Trabajo</div><div class="ph-sub" id="cnt-o">Cargando...</div></div>
  <div class="ph-actions"><button class="btn btn-p" onclick="openModal('m-crear')">+ Nueva Orden</button></div>
 </div>

 <div class="stat-grid" id="stats-o"></div>

 <div class="filters">
  <input class="fc" id="f-txt" placeholder="🔍 Buscar por título o equipo..." oninput="render()">
  <select class="fc" id="f-est" onchange="render()">
   <option value="">Todos los estados</option>
   <option>PENDIENTE</option><option>EN_PROCESO</option>
   <option>EN_ESPERA</option><option>CERRADA</option><option>CANCELADA</option>
  </select>
  <select class="fc" id="f-tipo" onchange="render()">
   <option value="">Todos los tipos</option>
   <option>PREVENTIVO</option><option>CORRECTIVO</option><option>PREDICTIVO</option>
  </select>
  <select class="fc" id="f-prio" onchange="render()">
   <option value="">Todas las prioridades</option>
   <option>BAJA</option><option>MEDIA</option><option>ALTA</option><option>CRITICA</option><option>EMERGENCIA</option>
  </select>
 </div>

 <div class="card">
  <div class="tw">
   <table>
    <thead><tr><th>#</th><th>Título</th><th>Equipo</th><th>Tipo</th><th>Prioridad</th><th>Estado</th><th>Programada</th><th>Acciones</th></tr></thead>
    <tbody id="t-ords"></tbody>
   </table>
  </div>
 </div>
</main>
</div>

<!-- MODAL CREAR -->
<div class="modal-ov" id="m-crear">
 <div class="modal modal-lg">
  <div class="modal-hd"><div class="modal-title">Nueva Orden de Trabajo</div><button class="modal-close">✕</button></div>
  <div class="modal-body">
   <div class="fg"><label class="fl">Título *</label><input class="fc" id="c-tit" placeholder="Ej: Cambio de filtro compresor A"></div>
   <div class="fr">
    <div class="fg"><label class="fl">Equipo *</label><select class="fc" id="c-eq"><option value="">Seleccionar...</option></select></div>
    <div class="fg"><label class="fl">Prioridad *</label><select class="fc" id="c-pr"><option value="">Seleccionar...</option></select></div>
   </div>
   <div class="fr">
    <div class="fg"><label class="fl">Tipo</label>
     <select class="fc" id="c-tp">
      <option>CORRECTIVO</option><option>PREVENTIVO</option><option>PREDICTIVO</option>
     </select>
    </div>
    <div class="fg"><label class="fl">Hrs. Estimadas</label><input class="fc" id="c-hrs" type="number" min="0" step="0.5" placeholder="4"></div>
   </div>
   <div class="fg"><label class="fl">Fecha Programada</label><input class="fc" id="c-fec" type="datetime-local"></div>
   <div class="fg"><label class="fl">Descripción *</label><textarea class="fc" id="c-des" rows="3" placeholder="Describe el trabajo a realizar..."></textarea></div>
  </div>
  <div class="modal-ft">
   <button class="btn btn-s" onclick="closeModal('m-crear')">Cancelar</button>
   <button class="btn btn-p" onclick="crear()">Crear Orden</button>
  </div>
 </div>
</div>

<!-- MODAL DETALLE -->
<div class="modal-ov" id="m-det">
 <div class="modal modal-xl">
  <div class="modal-hd">
   <div><div class="modal-title" id="d-tit"></div><div style="font-size:13px;color:var(--t2)" id="d-sub"></div></div>
   <button class="modal-close">✕</button>
  </div>
  <div class="modal-body" id="d-body"></div>
  <div class="modal-ft" id="d-ft"></div>
 </div>
</div>

<!-- MODAL ASIGNAR -->
<div class="modal-ov" id="m-asi">
 <div class="modal">
  <div class="modal-hd"><div class="modal-title">Asignar Técnico</div><button class="modal-close">✕</button></div>
  <div class="modal-body">
   <div class="fg"><label class="fl">Técnico *</label><select class="fc" id="asi-tec"><option value="">Seleccionar...</option></select></div>
   <div class="fg"><label class="fl">Rol</label>
    <select class="fc" id="asi-rol"><option>Principal</option><option>Apoyo</option><option>Supervisor</option></select>
   </div>
  </div>
  <div class="modal-ft">
   <button class="btn btn-s" onclick="closeModal('m-asi')">Cancelar</button>
   <button class="btn btn-p" onclick="asignar()">Asignar</button>
  </div>
 </div>
</div>

<!-- MODAL CERRAR -->
<div class="modal-ov" id="m-cer">
 <div class="modal">
  <div class="modal-hd"><div class="modal-title">Cerrar Orden</div><button class="modal-close">✕</button></div>
  <div class="modal-body">
   <div class="alert alert-err" style="margin-bottom:16px">⚠ Esta acción no se puede deshacer</div>
   <div class="fg"><label class="fl">Horas Reales</label><input class="fc" id="cer-h" type="number" min="0" step="0.5"></div>
   <div class="fg"><label class="fl">Observaciones de Cierre</label><textarea class="fc" id="cer-o" rows="3" placeholder="Trabajo realizado, resultado..."></textarea></div>
  </div>
  <div class="modal-ft">
   <button class="btn btn-s" onclick="closeModal('m-cer')">Cancelar</button>
   <button class="btn btn-d" onclick="cerrar()">Cerrar Orden</button>
  </div>
 </div>
</div>

<!-- MODAL REPUESTOS -->
<div class="modal-ov" id="m-rep">
 <div class="modal modal-lg">
  <div class="modal-hd"><div class="modal-title">Repuestos de la Orden</div><button class="modal-close">✕</button></div>
  <div class="modal-body">
   <div class="fg"><label class="fl">Repuesto *</label><select class="fc" id="rp-r" onchange="autoP()"><option value="">Seleccionar...</option></select></div>
   <div id="rp-stock-info" style="margin-bottom:12px"></div>
   <div class="fr">
    <div class="fg"><label class="fl">Cantidad *</label><input class="fc" id="rp-c" type="number" min="1" value="1" oninput="calcSub()"></div>
    <div class="fg"><label class="fl">Precio Unit. (Bs.)</label><input class="fc" id="rp-p" type="number" min="0" step="0.01" value="0" oninput="calcSub()"></div>
   </div>
   <div id="rp-subtotal" style="display:none;background:var(--aD);border:1px solid #f0a50030;border-radius:8px;padding:8px 14px;margin-bottom:12px;display:flex;justify-content:space-between">
    <span style="font-size:12px;color:var(--t2)">Subtotal</span>
    <span id="rp-sv" style="font-family:var(--fw);font-weight:800;font-size:18px;color:var(--accent)"></span>
   </div>
   <div class="fg"><label class="fl">Observación</label><input class="fc" id="rp-o" placeholder="Descripción del uso..."></div>
   <button class="btn btn-p" style="width:100%;margin-bottom:20px" onclick="regRep()">+ Registrar Repuesto</button>
   <div style="font-family:var(--fw);font-weight:700;font-size:14px;margin-bottom:12px">Repuestos utilizados</div>
   <div id="rep-lista"><div class="loader"><div class="spinner"></div></div></div>
  </div>
  <div class="modal-ft"><button class="btn btn-s" onclick="closeModal('m-rep')">Cerrar</button></div>
 </div>
</div>

<script src="../assets/js/app.js"></script>
<script>
const ID_USUARIO = <?= (int)$idUsuario ?>;
let ords=[], equips=[], prios=[], tecs=[], reps=[], orActual=null;

async function init(){
 [ords, equips, prios, tecs, reps] = await Promise.all([
  API.get('/ordenes.php'),
  API.get('/equipos.php'),
  API.get('/catalogos.php?tipo=prioridades'),
  API.get('/tecnicos.php'),
  API.get('/repuestos.php'),
 ]);

 ords  = ords  || [];
 equips= equips|| [];
 prios = prios || [];
 tecs  = tecs  || [];
 reps  = reps  || [];

 document.getElementById('cnt-o').textContent = ords.length + ' órdenes registradas';

 const cnt = e => ords.filter(o=>o.nombreEstado===e).length;
 document.getElementById('stats-o').innerHTML = buildStats([
  {l:'Total',    v:ords.length,         c:'c-y'},
  {l:'Pendientes',v:cnt('PENDIENTE'),   c:'c-y'},
  {l:'En Proceso',v:cnt('EN_PROCESO'), c:'c-c'},
  {l:'En Espera', v:cnt('EN_ESPERA'),  c:'c-p'},
  {l:'Cerradas',  v:cnt('CERRADA'),    c:'c-g'},
  {l:'Urgentes',  v:ords.filter(o=>['CRITICA','EMERGENCIA'].includes(o.nombrePrioridad)&&!['CERRADA','CANCELADA'].includes(o.nombreEstado)).length, c:'c-r'},
 ]);

 fillSel('c-eq',  equips, 'idEquipo',    'nombre',         'Seleccionar equipo...');
 fillSel('c-pr',  prios,  'idPrioridad', 'nombre',         'Seleccionar prioridad...');
 fillSel('asi-tec',tecs,  'idTecnico',   'nombre',         'Seleccionar técnico...');

 const sr = document.getElementById('rp-r');
 sr.innerHTML = '<option value="">Seleccionar repuesto...</option>';
 reps.forEach(r=>{
  const o = document.createElement('option');
  o.value = r.idRepuesto;
  o.textContent = `${r.nombre} | Stock: ${r.stockActual}`;
  o.dataset.precio = r.precioUnitario;
  o.dataset.stock  = r.stockActual;
  o.dataset.min    = r.stockMinimo;
  sr.appendChild(o);
 });

 render();
}

function render(){
 const txt = (document.getElementById('f-txt').value||'').toLowerCase();
 const est = document.getElementById('f-est').value;
 const tip = document.getElementById('f-tipo').value;
 const pri = document.getElementById('f-prio').value;

 const f = ords.filter(o =>
  (!txt || (o.titulo+' '+(o.equipo||'')).toLowerCase().includes(txt)) &&
  (!est || o.nombreEstado === est) &&
  (!tip || o.tipoMantenimiento === tip) &&
  (!pri || o.nombrePrioridad === pri)
 );

 const tbody = document.getElementById('t-ords');
 if(f.length === 0){
  tbody.innerHTML = '<tr><td colspan="8"><div class="empty"><div class="empty-icon">◉</div>No hay órdenes</div></td></tr>';
  return;
 }

 tbody.innerHTML = f.map(o => `<tr>
  <td style="color:var(--t3);font-family:var(--fm)">#${o.idOrden}</td>
  <td style="font-weight:500;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
   <span style="color:var(--accent);cursor:pointer" onclick="verDet(${o.idOrden})">${o.titulo}</span>
  </td>
  <td style="font-size:12px;color:var(--t2)">${o.equipo||'—'}</td>
  <td>${badgeT(o.tipoMantenimiento)}</td>
  <td>${badgeP(o.nombrePrioridad)}</td>
  <td>${badgeE(o.nombreEstado)}</td>
  <td style="font-size:12px;color:var(--t3)">${fmtDate(o.fechaProgramada)}</td>
  <td>
   <div style="display:flex;gap:4px;flex-wrap:wrap">
    ${o.nombreEstado!=='CANCELADA'?`<button class="btn btn-s btn-xs" onclick="abrirRep(${o.idOrden})">◍ Rep.</button>`:''}
    ${!['CERRADA','CANCELADA'].includes(o.nombreEstado)?`
     <button class="btn btn-s btn-xs" onclick="abrirAsi(${o.idOrden})">◎ Asignar</button>
     <button class="btn btn-d btn-xs" onclick="abrirCer(${o.idOrden})">✕ Cerrar</button>`:''}
   </div>
  </td>
 </tr>`).join('');
}

async function crear(){
 const t = document.getElementById('c-tit').value.trim();
 const e = document.getElementById('c-eq').value;
 const p = document.getElementById('c-pr').value;
 const d = document.getElementById('c-des').value.trim();
 if(!t||!e||!p||!d){ toast('Complete título, equipo, prioridad y descripción','warn'); return; }
 const r = await API.post('/ordenes.php',{
  titulo:t, idEquipo:Number(e), idPrioridad:Number(p),
  tipoMantenimiento: document.getElementById('c-tp').value,
  horasEstimadas:    document.getElementById('c-hrs').value||null,
  fechaProgramada:   document.getElementById('c-fec').value||null,
  descripcion: d,
  idSolicitante: ID_USUARIO,
 });
 if(r.error||!r.ok){ toast(r.error||'Error al crear','err'); return; }
 toast('Orden creada ✓');
 closeModal('m-crear');
 ['c-tit','c-des','c-fec','c-hrs'].forEach(id=>document.getElementById(id).value='');
 await init();
}

async function verDet(id){
 const [o,tecsList,rps] = await Promise.all([
  API.get(`/ordenes.php?path=${id}`),
  API.get(`/ordenes.php?path=${id}/tecnicos`),
  API.get(`/ordenes.php?path=${id}/repuestos`),
 ]);
 document.getElementById('d-tit').textContent = `Orden #${id}`;
 document.getElementById('d-sub').textContent = o.titulo||'';
 const totRep = (rps||[]).reduce((a,r)=>a+Number(r.cantidad)*Number(r.precioUnitario||0),0);
 document.getElementById('d-body').innerHTML = `
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;margin-bottom:16px">
   ${[['Equipo',o.equipo],['Estado',o.nombreEstado],['Prioridad',o.nombrePrioridad],
      ['Tipo',o.tipoMantenimiento],['Hrs. Est.',o.horasEstimadas||'—'],['Hrs. Reales',o.horasReales||'—'],
      ['Creación',fmtDate(o.fechaCreacion)],['Programada',fmtDate(o.fechaProgramada)],['Cierre',fmtDate(o.fechaCierre)]]
     .map(([k,v])=>`<div class="info-box"><div class="info-lbl">${k}</div><div class="info-val">${v||'—'}</div></div>`).join('')}
  </div>
  ${o.descripcion?`<div class="info-box" style="margin-bottom:16px"><div class="info-lbl">Descripción</div><div style="font-size:13px;margin-top:4px;line-height:1.7">${o.descripcion}</div></div>`:''}
  <div style="font-family:var(--fw);font-weight:700;font-size:14px;margin:16px 0 10px">Técnicos Asignados</div>
  ${(tecsList||[]).length===0?'<p style="color:var(--t3);font-size:12px">Sin técnicos asignados</p>':
    tecsList.map(t=>`<div style="background:var(--card2);border:1px solid var(--border);border-radius:8px;padding:9px 14px;display:flex;justify-content:space-between;margin-bottom:6px">
     <div><div style="font-weight:600">${t.tecnico||t.nombre||'—'}</div><div style="font-size:11px;color:var(--t3)">${t.especialidad||''}</div></div>
     <span class="badge b-bl">${t.rol||'Principal'}</span></div>`).join('')}
  <div style="font-family:var(--fw);font-weight:700;font-size:14px;margin:16px 0 10px">Repuestos Utilizados
   ${totRep>0?`<span style="font-family:var(--fm);font-size:13px;font-weight:400;color:var(--cyan)"> Total: ${fmtBs(totRep)}</span>`:''}
  </div>
  ${(rps||[]).length===0?'<p style="color:var(--t3);font-size:12px">Sin repuestos</p>':
    rps.map(r=>`<div style="background:var(--card2);border:1px solid var(--border);border-radius:8px;padding:9px 14px;display:flex;justify-content:space-between;margin-bottom:6px">
     <div><div style="font-weight:600">${r.nombre||'—'}</div><div style="font-size:11px;color:var(--t3)">${r.codigo||''}</div></div>
     <div style="text-align:right"><div style="font-family:var(--fw);font-weight:800;color:var(--accent)">×${r.cantidad}</div>
     <div style="font-size:12px;color:var(--cyan)">${fmtBs(Number(r.cantidad)*Number(r.precioUnitario||0))}</div></div></div>`).join('')}
  ${o.observaciones?`<div style="background:var(--gD);border:1px solid #2ecc7130;border-radius:8px;padding:12px;margin-top:12px"><div style="font-size:11px;color:var(--green);text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px">Observaciones de cierre</div><div style="font-size:13px">${o.observaciones}</div></div>`:''}
 `;
 document.getElementById('d-ft').innerHTML = `
  <button class="btn btn-s" onclick="closeModal('m-det')">Cerrar</button>
  ${!['CERRADA','CANCELADA'].includes(o.nombreEstado)?`
   <button class="btn btn-s btn-sm" onclick="closeModal('m-det');abrirRep(${id})">◍ Repuestos</button>
   <button class="btn btn-p btn-sm" onclick="closeModal('m-det');abrirAsi(${id})">◎ Asignar</button>
   <button class="btn btn-d btn-sm" onclick="closeModal('m-det');abrirCer(${id})">✕ Cerrar Orden</button>`:''}
 `;
 openModal('m-det');
}

function abrirAsi(id){
 orActual = ords.find(o=>o.idOrden==id);
 document.getElementById('asi-tec').value='';
 openModal('m-asi');
}
async function asignar(){
 const t = document.getElementById('asi-tec').value;
 const r = document.getElementById('asi-rol').value;
 if(!t){ toast('Seleccione un técnico','warn'); return; }
 const res = await API.put(`/ordenes.php?path=${orActual.idOrden}/asignar`,{idTecnico:Number(t),rol:r});
 if(res.error||!res.ok){ toast(res.error||'Error','err'); return; }
 toast('Técnico asignado ✓'); closeModal('m-asi'); await init();
}

function abrirCer(id){
 orActual = ords.find(o=>o.idOrden==id);
 document.getElementById('cer-h').value='';
 document.getElementById('cer-o').value='';
 openModal('m-cer');
}
async function cerrar(){
 const res = await API.put(`/ordenes.php?path=${orActual.idOrden}/cerrar`,{
  horasReales:    document.getElementById('cer-h').value||null,
  observaciones:  document.getElementById('cer-o').value||null,
 });
 if(res.error||!res.ok){ toast(res.error||'Error','err'); return; }
 toast('Orden cerrada ✓'); closeModal('m-cer'); await init();
}

async function abrirRep(id){
 orActual = ords.find(o=>o.idOrden==id);
 document.getElementById('rp-r').value='';
 document.getElementById('rp-c').value=1;
 document.getElementById('rp-p').value=0;
 document.getElementById('rp-o').value='';
 document.getElementById('rp-stock-info').innerHTML='';
 document.getElementById('rp-subtotal').style.display='none';
 await loadRepLista(id);
 openModal('m-rep');
}

async function loadRepLista(id){
 const lista = await API.get(`/ordenes.php?path=${id}/repuestos`);
 const tot = (lista||[]).reduce((a,r)=>a+Number(r.cantidad)*Number(r.precioUnitario||0),0);
 document.getElementById('rep-lista').innerHTML = (lista||[]).length===0
  ? '<div class="empty" style="padding:16px 0">Sin repuestos registrados</div>'
  : (lista.map(r=>`<div style="background:var(--card2);border:1px solid var(--border);border-radius:8px;padding:10px 14px;display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:8px">
   <div><div style="font-weight:600">${r.nombre||'—'}</div><div style="font-size:11px;color:var(--t3)">${r.codigo||''}</div></div>
   <div style="text-align:center;min-width:40px"><div style="font-size:10px;color:var(--t3)">CANT</div><div style="font-family:var(--fw);font-weight:800;font-size:18px;color:var(--accent)">${r.cantidad}</div></div>
   <div style="text-align:right"><div style="font-size:10px;color:var(--t3)">SUBTOTAL</div><div style="font-family:var(--fm);color:var(--cyan)">${fmtBs(Number(r.cantidad)*Number(r.precioUnitario||0))}</div></div>
  </div>`).join('')
  + `<div style="background:var(--card2);border:1px solid #00c8b430;border-radius:8px;padding:10px 14px;display:flex;justify-content:space-between">
    <span style="font-weight:700">TOTAL</span>
    <span style="font-family:var(--fw);font-weight:800;font-size:18px;color:var(--cyan)">${fmtBs(tot)}</span></div>`);
}

function autoP(){
 const sel = document.getElementById('rp-r');
 const opt = sel.options[sel.selectedIndex];
 if(!opt||!opt.value) return;
 document.getElementById('rp-p').value = opt.dataset.precio||0;
 calcSub();
 const stock=Number(opt.dataset.stock||0), min=Number(opt.dataset.min||0);
 const bajo=stock<=min;
 document.getElementById('rp-stock-info').innerHTML=`<div class="${bajo?'alert alert-err':'alert alert-ok'}" style="margin-bottom:0;font-size:12px">
  Stock: <strong>${stock}</strong> uds${bajo?' ⚠ BAJO EL MÍNIMO ('+min+')':' ✓'}
 </div>`;
}

function calcSub(){
 const c=Number(document.getElementById('rp-c').value);
 const p=Number(document.getElementById('rp-p').value);
 const el=document.getElementById('rp-subtotal');
 if(c&&p){ document.getElementById('rp-sv').textContent=fmtBs(c*p); el.style.display='flex'; }
 else     { el.style.display='none'; }
}

async function regRep(){
 const idR = document.getElementById('rp-r').value;
 const cant = Number(document.getElementById('rp-c').value);
 if(!idR||!cant){ toast('Seleccione repuesto y cantidad','warn'); return; }
 const sel = document.getElementById('rp-r');
 const opt = sel.options[sel.selectedIndex];
 if(cant > Number(opt?.dataset?.stock||0)){ toast('⚠ Cantidad supera el stock disponible','warn'); return; }
 const r = await API.post('/repuestos.php',{
  idOrden:     orActual.idOrden,
  idRepuesto:  Number(idR),
  cantidad:    cant,
  precioUnitario: Number(document.getElementById('rp-p').value)||0,
  observacion: document.getElementById('rp-o').value||null,
 });
 if(r.error||!r.ok){ toast(r.error||'Error','err'); return; }
 toast('Repuesto registrado ✓');
 document.getElementById('rp-r').value='';
 document.getElementById('rp-c').value=1;
 document.getElementById('rp-p').value=0;
 document.getElementById('rp-o').value='';
 document.getElementById('rp-stock-info').innerHTML='';
 document.getElementById('rp-subtotal').style.display='none';
 await loadRepLista(orActual.idOrden);
 await init();
}

init();
</script>
</body></html>