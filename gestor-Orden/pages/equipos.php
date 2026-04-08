<?php
require_once '../includes/session.php';
requireRol(['ADMIN','SUPERVISOR'],'../index.php');
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Equipos — MantTech</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.eq-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px}
.eq-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:20px;transition:all .2s}
.eq-card:hover{border-color:var(--accent);transform:translateY(-2px)}
.eq-icon{width:44px;height:44px;background:var(--aD);border-radius:10px;display:grid;place-items:center;font-size:20px;margin-bottom:12px}
.eq-name{font-family:var(--fw);font-weight:700;font-size:15px;margin-bottom:4px}
.eq-sub{font-size:12px;color:var(--t3);margin-bottom:14px}
</style>
</head>
<body>
<div class="shell">
<?php include '../includes/nav.php'; ?>
<main class="main">
 <div class="ph">
  <div><div class="ph-title">Equipos</div><div class="ph-sub" id="cnt-e">Cargando...</div></div>
  <div class="ph-actions"><button class="btn btn-p" onclick="abrirModal()">+ Nuevo Equipo</button></div>
 </div>
 <div class="eq-grid" id="grid-e"><div class="loader"><div class="spinner"></div></div></div>
</main>
</div>

<div class="modal-ov" id="m-eq">
 <div class="modal">
  <div class="modal-hd"><div class="modal-title" id="me-tit">Nuevo Equipo</div><button class="modal-close">✕</button></div>
  <div class="modal-body">
   <div class="fg"><label class="fl">Nombre *</label><input class="fc" id="e-nom" placeholder="Prensa Hidráulica #1"></div>
   <div class="fr">
    <div class="fg"><label class="fl">Categoría *</label><select class="fc" id="e-cat"></select></div>
    <div class="fg"><label class="fl">Ubicación *</label><select class="fc" id="e-ubi"></select></div>
   </div>
   <div class="fr">
    <div class="fg"><label class="fl">Marca</label><input class="fc" id="e-mar" placeholder="Bosch"></div>
    <div class="fg"><label class="fl">Modelo</label><input class="fc" id="e-mod" placeholder="PH-500"></div>
   </div>
   <div class="fr">
    <div class="fg"><label class="fl">N° Serie</label><input class="fc" id="e-ser" placeholder="SN-001"></div>
    <div class="fg"><label class="fl">Fecha Adquisición</label><input class="fc" id="e-fad" type="date"></div>
   </div>
  </div>
  <div class="modal-ft">
   <button class="btn btn-s" onclick="closeModal('m-eq')">Cancelar</button>
   <button class="btn btn-p" id="me-btn" onclick="guardar()">Crear Equipo</button>
  </div>
 </div>
</div>

<script src="../assets/js/app.js"></script>
<script>
let equipos=[], cats=[], ubics=[], editId=null;

async function init(){
 [equipos,cats,ubics] = await Promise.all([
  API.get('/equipos.php'),
  API.get('/equipos.php?path=categorias'),
  API.get('/equipos.php?path=ubicaciones'),
 ]);
 fillSel('e-cat',cats,'idCategoriaEquipo','nombre','Seleccionar categoría...');
 fillSel('e-ubi',ubics,'idUbicacion','nombre','Seleccionar ubicación...');
 document.getElementById('cnt-e').textContent=(equipos||[]).length+' equipos registrados';
 render();
}

function render(){
 const g=document.getElementById('grid-e');
 if(!equipos||!equipos.length){g.innerHTML='<div class="empty">Sin equipos</div>';return;}
 g.innerHTML=equipos.map(e=>`
  <div class="eq-card">
   <div class="eq-icon">⬡</div>
   <div class="eq-name">${e.nombre}</div>
   <div class="eq-sub">${e.marca||'—'} ${e.modelo||''}</div>
   <div style="display:flex;flex-direction:column;gap:5px;margin-bottom:14px">
    <div style="display:flex;justify-content:space-between"><span style="font-size:11px;color:var(--t3)">Categoría</span><span class="badge b-gr" style="font-size:10px">${e.categoria||'—'}</span></div>
    <div style="display:flex;justify-content:space-between"><span style="font-size:11px;color:var(--t3)">Ubicación</span><span style="font-size:12px">${e.ubicacion||'—'}</span></div>
    <div style="display:flex;justify-content:space-between"><span style="font-size:11px;color:var(--t3)">N° Serie</span><span style="font-size:12px;font-family:var(--fm)">${e.numeroSerie||'—'}</span></div>
    <div style="display:flex;justify-content:space-between"><span style="font-size:11px;color:var(--t3)">Adquisición</span><span style="font-size:12px">${fmtDate(e.fechaAdquisicion)}</span></div>
   </div>
   <div style="display:flex;gap:8px">
    <button class="btn btn-s btn-sm" style="flex:1" onclick="abrirEditar(${e.idEquipo})">✎ Editar</button>
    <button class="btn btn-d btn-sm" onclick="eliminar(${e.idEquipo})">✕</button>
   </div>
  </div>`).join('');
}

function abrirModal(){
 editId=null;
 ['e-nom','e-mar','e-mod','e-ser','e-fad'].forEach(id=>document.getElementById(id).value='');
 document.getElementById('e-cat').value=''; document.getElementById('e-ubi').value='';
 document.getElementById('me-tit').textContent='Nuevo Equipo';
 document.getElementById('me-btn').textContent='Crear Equipo';
 openModal('m-eq');
}

function abrirEditar(id){
 const e=equipos.find(x=>x.idEquipo===id); if(!e) return;
 editId=id;
 document.getElementById('e-nom').value=e.nombre||'';
 document.getElementById('e-cat').value=e.idCategoriaEquipo||'';
 document.getElementById('e-ubi').value=e.idUbicacion||'';
 document.getElementById('e-mar').value=e.marca||'';
 document.getElementById('e-mod').value=e.modelo||'';
 document.getElementById('e-ser').value=e.numeroSerie||'';
 document.getElementById('e-fad').value=e.fechaAdquisicion?e.fechaAdquisicion.split('T')[0]:'';
 document.getElementById('me-tit').textContent='Editar Equipo';
 document.getElementById('me-btn').textContent='Guardar Cambios';
 openModal('m-eq');
}

async function guardar(){
 const nom=document.getElementById('e-nom').value.trim();
 const cat=document.getElementById('e-cat').value;
 const ubi=document.getElementById('e-ubi').value;
 if(!nom||!cat||!ubi){toast('Nombre, categoría y ubicación son requeridos','warn');return;}
 const data={
  nombre:nom, idCategoriaEquipo:Number(cat), idUbicacion:Number(ubi),
  marca:document.getElementById('e-mar').value||null,
  modelo:document.getElementById('e-mod').value||null,
  numeroSerie:document.getElementById('e-ser').value||null,
  fechaAdquisicion:document.getElementById('e-fad').value||null,
 };
 const r=editId?await API.put(`/equipos.php?id=${editId}`,data):await API.post('/equipos.php',data);
 if(r.error||!r.ok){toast(r.error||'Error','err');return;}
 toast(r.mensaje||'✓ Guardado'); closeModal('m-eq'); await init();
}

async function eliminar(id){
 confirmDel('¿Desactivar este equipo?',async()=>{
  const r=await API.del(`/equipos.php?id=${id}`);
  if(r.error){toast(r.error,'err');return;}
  toast('Equipo desactivado'); await init();
 });
}
init();
</script>
</body></html>