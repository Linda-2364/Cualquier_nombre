<?php
require_once '../includes/session.php';
requireRol(['ADMIN'],'../index.php');
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Usuarios — MantTech</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="shell">
<?php include '../includes/nav.php'; ?>
<main class="main">
 <div class="ph">
  <div><div class="ph-title">Usuarios</div><div class="ph-sub" id="cnt-u">Cargando...</div></div>
  <div class="ph-actions"><button class="btn btn-p" onclick="abrirModal()">+ Nuevo Usuario</button></div>
 </div>
 <div class="card">
  <div class="tw">
   <table>
    <thead><tr><th>Usuario</th><th>Nombre</th><th>Rol</th><th>Email</th><th>Estado</th><th>Último acceso</th><th>Acciones</th></tr></thead>
    <tbody id="t-usrs"></tbody>
   </table>
  </div>
 </div>
</main>
</div>

<div class="modal-ov" id="m-usr">
 <div class="modal">
  <div class="modal-hd"><div class="modal-title" id="mu-tit">Nuevo Usuario</div><button class="modal-close">✕</button></div>
  <div class="modal-body">
   <div class="fr">
    <div class="fg"><label class="fl">Nombre *</label><input class="fc" id="u-no" placeholder="Carlos"></div>
    <div class="fg"><label class="fl">Apellido *</label><input class="fc" id="u-ap" placeholder="Mamani"></div>
   </div>
   <div class="fr">
    <div class="fg"><label class="fl">Username *</label><input class="fc" id="u-usr" placeholder="carlos.m"></div>
    <div class="fg"><label class="fl">Contraseña</label><input class="fc" id="u-pwd" type="password" placeholder="••••••••"></div>
   </div>
   <div class="fr">
    <div class="fg"><label class="fl">Rol *</label>
     <select class="fc" id="u-rol">
      <option value="ADMIN">Admin</option>
      <option value="SUPERVISOR">Supervisor</option>
      <option value="TECNICO">Técnico</option>
      <option value="SOLICITANTE">Solicitante</option>
     </select>
    </div>
    <div class="fg"><label class="fl">Email</label><input class="fc" id="u-em" type="email" placeholder="email@empresa.com"></div>
   </div>
   <div class="fr">
    <div class="fg"><label class="fl">CI/DNI</label><input class="fc" id="u-ci" placeholder="12345678"></div>
    <div class="fg"><label class="fl">Teléfono</label><input class="fc" id="u-tel" placeholder="77712345"></div>
   </div>
  </div>
  <div class="modal-ft">
   <button class="btn btn-s" onclick="closeModal('m-usr')">Cancelar</button>
   <button class="btn btn-p" id="mu-btn" onclick="guardar()">Crear Usuario</button>
  </div>
 </div>
</div>

<script src="../assets/js/app.js"></script>
<script>
let usrs=[], editId=null;

async function init(){
 usrs = await API.get('/usuarios.php');
 document.getElementById('cnt-u').textContent=(usrs||[]).length+' usuarios registrados';
 render();
}

function render(){
 document.getElementById('t-usrs').innerHTML=(usrs||[]).map(u=>`<tr>
  <td style="font-family:var(--fm);font-weight:500">${u.username}</td>
  <td>${u.nombre||((u.nombre||'')+(u.apellido?' '+u.apellido:''))}</td>
  <td>${badgeR(u.nombreRol)}</td>
  <td style="color:var(--t2);font-size:12px">${u.email||'—'}</td>
  <td>${u.activo=='1'?'<span style="color:var(--green);font-size:12px">● Activo</span>':'<span style="color:var(--red);font-size:12px">● Inactivo</span>'}</td>
  <td style="color:var(--t3);font-size:12px">${fmtDateTime(u.ultimoAcceso)}</td>
  <td>
   <div style="display:flex;gap:6px">
    <button class="btn btn-s btn-sm" onclick="abrirEditar(${u.idUsuario})">✎</button>
    <button class="btn btn-sm" style="background:${u.activo=='1'?'var(--rD)':'var(--gD)'};color:${u.activo=='1'?'var(--red)':'var(--green)'};border:none;border-radius:6px;padding:5px 10px;cursor:pointer;font-size:11px" onclick="toggleBloqueo(${u.idUsuario},${u.activo})">
     ${u.activo=='1'?'🔒 Bloquear':'🔓 Activar'}
    </button>
   </div>
  </td>
 </tr>`).join('')||'<tr><td colspan="7"><div class="empty">Sin usuarios</div></td></tr>';
}

function abrirModal(){
 editId=null;
 ['u-no','u-ap','u-usr','u-pwd','u-em','u-ci','u-tel'].forEach(id=>document.getElementById(id).value='');
 document.getElementById('u-rol').value='TECNICO';
 document.getElementById('mu-tit').textContent='Nuevo Usuario';
 document.getElementById('mu-btn').textContent='Crear Usuario';
 openModal('m-usr');
}

function abrirEditar(id){
 const u=usrs.find(x=>x.idUsuario===id); if(!u) return;
 editId=id;
 document.getElementById('u-no').value=u.nombre||'';
 document.getElementById('u-ap').value=u.apellido||'';
 document.getElementById('u-usr').value=u.username||'';
 document.getElementById('u-pwd').value='';
 document.getElementById('u-rol').value=u.nombreRol||'TECNICO';
 document.getElementById('u-em').value=u.email||'';
 document.getElementById('u-ci').value=u.ci||'';
 document.getElementById('u-tel').value=u.telefono||'';
 document.getElementById('mu-tit').textContent='Editar Usuario';
 document.getElementById('mu-btn').textContent='Guardar Cambios';
 openModal('m-usr');
}

async function guardar(){
 const no=document.getElementById('u-no').value.trim();
 const ap=document.getElementById('u-ap').value.trim();
 const usr=document.getElementById('u-usr').value.trim();
 const pwd=document.getElementById('u-pwd').value;
 if(!no||!ap||!usr){toast('Nombre, apellido y username son requeridos','warn');return;}
 if(!editId&&!pwd){toast('La contraseña es requerida para nuevo usuario','warn');return;}
 const data={
  nombre:no, apellido:ap, username:usr, password:pwd||null,
  rol:document.getElementById('u-rol').value,
  email:document.getElementById('u-em').value||null,
  ci:document.getElementById('u-ci').value||null,
  telefono:document.getElementById('u-tel').value||null,
 };
 const r=editId?await API.put(`/usuarios.php?id=${editId}`,data):await API.post('/usuarios.php',data);
 if(r.error||!r.ok){toast(r.error||'Error','err');return;}
 toast(r.mensaje||'✓ Guardado'); closeModal('m-usr'); await init();
}

async function toggleBloqueo(id, activo){
 const bloquear = activo=='1';
 const msg = bloquear?'¿Bloquear este usuario?':'¿Activar este usuario?';
 confirmDel(msg, async()=>{
  const r=await API.put(`/usuarios.php?id=${id}`,{bloquear});
  if(r.error){toast(r.error,'err');return;}
  toast(r.mensaje||'✓'); await init();
 });
}
init();
</script>
</body></html>