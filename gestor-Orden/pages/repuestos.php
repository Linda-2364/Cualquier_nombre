<?php
require_once '../includes/session.php';
requireRol(['ADMIN','SUPERVISOR'],'../index.php');
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Repuestos — MantTech</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.stock-bar{height:6px;background:var(--border);border-radius:3px;overflow:hidden;margin-top:6px}
.stock-fill{height:100%;border-radius:3px;transition:width .6s ease}
</style>
</head>
<body>
<div class="shell">
<?php include '../includes/nav.php'; ?>
<main class="main">
 <div class="ph">
  <div><div class="ph-title">Repuestos</div><div class="ph-sub" id="cnt-r">Cargando...</div></div>
  <div class="ph-actions">
   <button class="btn" onclick="loadConsumo()" style="margin-right:8px">📊 Ver Consumo</button>
   <button class="btn btn-p" onclick="abrirModal()">+ Nuevo Repuesto</button>
  </div>
 </div>

 <!-- Alertas stock bajo -->
 <div id="alerta-stock"></div>

 <!-- Tabla principal -->
 <div class="card">
  <div class="card-title" id="tabla-titulo">Inventario de Repuestos</div>
  <div class="tw">
   <table>
    <thead><tr><th>Código</th><th>Nombre</th><th>Categoría</th><th>Stock</th><th>Mínimo</th><th>Precio</th><th>Estado</th><th>Acciones</th></tr></thead>
    <tbody id="t-reps"></tbody>
   </table>
  </div>
 </div>
</main>
</div>

<div class="modal-ov" id="m-rep">
 <div class="modal">
  <div class="modal-hd"><div class="modal-title" id="mr-tit">Nuevo Repuesto</div><button class="modal-close">✕</button></div>
  <div class="modal-body">
   <div class="fr">
    <div class="fg"><label class="fl">Nombre *</label><input class="fc" id="r-nom" placeholder="Filtro de aceite"></div>
    <div class="fg"><label class="fl">Código</label><input class="fc" id="r-cod" placeholder="FIL-001"></div>
   </div>
   <div class="fg"><label class="fl">Categoría *</label><select class="fc" id="r-cat"></select></div>
   <div class="fg"><label class="fl">Descripción</label><input class="fc" id="r-desc" placeholder="Descripción del repuesto"></div>
   <div class="fr">
    <div class="fg"><label class="fl">Stock Actual</label><input class="fc" id="r-sta" type="number" min="0" value="0"></div>
    <div class="fg"><label class="fl">Stock Mínimo</label><input class="fc" id="r-stm" type="number" min="0" value="0"></div>
    <div class="fg"><label class="fl">Precio Unit. (Bs)</label><input class="fc" id="r-pre" type="number" min="0" step="0.01" value="0"></div>
   </div>
  </div>
  <div class="modal-ft">
   <button class="btn btn-s" onclick="closeModal('m-rep')">Cancelar</button>
   <button class="btn btn-p" id="mr-btn" onclick="guardar()">Crear Repuesto</button>
  </div>
 </div>
</div>

<script src="../assets/js/app.js"></script>
<script>
let reps=[], cats=[], editId=null, modoConsumo=false;

async function init(){
 [reps, cats] = await Promise.all([API.get('/repuestos.php'), API.get('/repuestos.php?path=categorias')]);
 fillSel('r-cat', cats, 'idCategoriaRepuesto','nombre','Seleccionar categoría...');
 render();
 alertaStock();
}

function alertaStock(){
 const bajo = (reps||[]).filter(r=>Number(r.stockActual)<=Number(r.stockMinimo));
 document.getElementById('alerta-stock').innerHTML = bajo.length>0
  ? `<div class="alert alert-err" style="margin-bottom:16px">⚠ ${bajo.length} repuesto(s) con stock bajo o agotado</div>` : '';
}

function render(){
 modoConsumo=false;
 document.getElementById('tabla-titulo').textContent='Inventario de Repuestos';
 document.getElementById('t-reps').innerHTML = (reps||[]).map(r=>{
  const pct=Math.min(100,Math.round(Number(r.stockActual)/Math.max(Number(r.stockMinimo)*2,1)*100));
  const bajo=Number(r.stockActual)<=Number(r.stockMinimo);
  return`<tr>
   <td style="font-family:var(--fm);color:var(--t3)">${r.codigo||'—'}</td>
   <td><div style="font-weight:600">${r.nombre}</div>
    <div class="stock-bar"><div class="stock-fill" style="width:${pct}%;background:${bajo?'var(--red)':'var(--green)'}"></div></div>
   </td>
   <td><span class="badge b-gr">${r.categoria||'—'}</span></td>
   <td style="text-align:center">
    <span style="font-family:var(--fw);font-weight:800;font-size:20px;color:${bajo?'var(--red)':'var(--t1)'}">${r.stockActual}</span>
   </td>
   <td style="text-align:center;color:var(--t3)">${r.stockMinimo}</td>
   <td style="color:var(--t2)">${fmtBs(r.precioUnitario)}</td>
   <td>${bajo?'<span style="color:var(--red);font-weight:700;font-size:11px">⚠ BAJO</span>':'<span style="color:var(--green);font-size:11px">✓ OK</span>'}</td>
   <td>
    <div style="display:flex;gap:6px">
     <button class="btn btn-s btn-sm" onclick="abrirEditar(${r.idRepuesto})">✎</button>
     <button class="btn btn-d btn-sm" onclick="eliminar(${r.idRepuesto})">✕</button>
    </div>
   </td>
  </tr>`;
 }).join('') || '<tr><td colspan="8"><div class="empty">Sin repuestos</div></td></tr>';
}

async function loadConsumo(){
 const data = await API.get('/repuestos.php?path=consumo');
 modoConsumo=true;
 document.getElementById('tabla-titulo').textContent='Consumo de Repuestos';
 document.getElementById('t-reps').innerHTML = (data||[]).map(r=>`<tr>
  <td style="font-family:var(--fm);color:var(--t3)">${r.codigo||'—'}</td>
  <td style="font-weight:600">${r.nombre}</td>
  <td><span class="badge b-gr">${r.categoria||'—'}</span></td>
  <td style="text-align:center;font-family:var(--fw);font-weight:800;font-size:20px;color:var(--accent)">${r.totalConsumido||0}</td>
  <td style="text-align:center">${r.stockActual}</td>
  <td style="text-align:center;color:var(--t3)">${r.stockMinimo}</td>
  <td style="color:var(--t3);font-size:12px">${fmtDate(r.ultimoUso)}</td>
  <td>—</td>
 </tr>`).join('') || '<tr><td colspan="8"><div class="empty">Sin consumo registrado</div></td></tr>';
}

function abrirModal(){
 editId=null;
 ['r-nom','r-cod','r-desc'].forEach(id=>document.getElementById(id).value='');
 document.getElementById('r-sta').value='0';
 document.getElementById('r-stm').value='0';
 document.getElementById('r-pre').value='0';
 document.getElementById('mr-tit').textContent='Nuevo Repuesto';
 document.getElementById('mr-btn').textContent='Crear Repuesto';
 openModal('m-rep');
}

function abrirEditar(id){
 const r=reps.find(x=>x.idRepuesto===id); if(!r) return;
 editId=id;
 document.getElementById('r-nom').value=r.nombre||'';
 document.getElementById('r-cod').value=r.codigo||'';
 document.getElementById('r-cat').value=r.idCategoriaRepuesto||'';
 document.getElementById('r-desc').value=r.descripcion||'';
 document.getElementById('r-sta').value=r.stockActual||0;
 document.getElementById('r-stm').value=r.stockMinimo||0;
 document.getElementById('r-pre').value=r.precioUnitario||0;
 document.getElementById('mr-tit').textContent='Editar Repuesto';
 document.getElementById('mr-btn').textContent='Guardar Cambios';
 openModal('m-rep');
}

async function guardar(){
 const nom=document.getElementById('r-nom').value.trim();
 const cat=document.getElementById('r-cat').value;
 if(!nom||!cat){toast('Nombre y categoría son requeridos','warn');return;}
 const data={
  nombre:nom, codigo:document.getElementById('r-cod').value||null,
  idCategoriaRepuesto:Number(cat),
  descripcion:document.getElementById('r-desc').value||null,
  stockActual:Number(document.getElementById('r-sta').value)||0,
  stockMinimo:Number(document.getElementById('r-stm').value)||0,
  precioUnitario:Number(document.getElementById('r-pre').value)||0,
 };
 const r=editId?await API.put(`/repuestos.php?id=${editId}`,data):await API.post('/repuestos.php',data);
 if(r.error||!r.ok){toast(r.error||'Error','err');return;}
 toast(r.mensaje||'✓ Guardado'); closeModal('m-rep'); await init();
}

async function eliminar(id){
 confirmDel('¿Eliminar este repuesto?',async()=>{
  const r=await API.del(`/repuestos.php?id=${id}`);
  if(r.error){toast(r.error,'err');return;}
  toast('Repuesto eliminado'); await init();
 });
}
init();
</script>
</body></html>