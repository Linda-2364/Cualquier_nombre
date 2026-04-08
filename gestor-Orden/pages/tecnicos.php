<?php
require_once '../includes/session.php';
requireRol(['ADMIN','SUPERVISOR'],'../index.php');
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Técnicos — MantTech</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.tec-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:18px}
.tec-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:20px;transition:all .2s;position:relative;overflow:hidden}
.tec-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--border)}
.tec-card.c1::before{background:var(--accent)}.tec-card.chi::before{background:var(--red)}
.tec-card:hover{border-color:var(--accent);transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.3)}
.tec-av{width:48px;height:48px;border-radius:12px;display:grid;place-items:center;font-family:var(--fw);font-weight:800;font-size:18px;flex-shrink:0;background:var(--aD);color:var(--accent);border:2px solid var(--accent)}
.tec-name{font-family:var(--fw);font-weight:700;font-size:15px}
.info-row{display:flex;justify-content:space-between;align-items:center;padding:5px 0;border-bottom:1px solid var(--border)}
.info-row:last-child{border:none}
.info-k{font-size:11px;color:var(--t3)}
.info-v{font-size:12px;font-weight:500}
.cnt-badge{font-family:var(--fw);font-weight:800;font-size:26px;line-height:1}
.cred-box{background:var(--card2);border:1px solid var(--border);border-radius:10px;padding:14px;margin-top:14px}
.cred-title{font-size:11px;color:var(--accent);text-transform:uppercase;letter-spacing:1px;margin-bottom:10px;display:flex;align-items:center;gap:6px}
.toggle-pwd{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--t3);cursor:pointer;font-size:14px}
.toggle-pwd:hover{color:var(--accent)}
.pw-wrap{position:relative}
.badge-usr{display:inline-flex;align-items:center;gap:6px;background:var(--card2);border:1px solid var(--border);border-radius:6px;padding:3px 10px;font-size:11px;font-family:var(--fm)}
</style>
</head>
<body>
<div class="shell">
<?php include '../includes/nav.php'; ?>
<main class="main">
 <div class="ph">
  <div><div class="ph-title">Técnicos</div><div class="ph-sub" id="cnt-t">Cargando...</div></div>
  <div class="ph-actions"><button class="btn btn-p" onclick="abrirModal()">+ Nuevo Técnico</button></div>
 </div>
 <div class="tec-grid" id="grid-t"><div class="loader"><div class="spinner"></div></div></div>
</main>
</div>

<!-- MODAL TÉCNICO -->
<div class="modal-ov" id="m-tec">
 <div class="modal">
  <div class="modal-hd">
   <div class="modal-title" id="mt-tit">Nuevo Técnico</div>
   <button class="modal-close">✕</button>
  </div>
  <div class="modal-body">

   <!-- Datos personales -->
   <div style="font-size:11px;color:var(--t3);text-transform:uppercase;letter-spacing:1px;margin-bottom:12px">◈ Datos Personales</div>
   <div class="fr">
    <div class="fg"><label class="fl">Nombre *</label><input class="fc" id="t-no" placeholder="Carlos"></div>
    <div class="fg"><label class="fl">Apellido *</label><input class="fc" id="t-ap" placeholder="Mamani"></div>
   </div>
   <div class="fr">
    <div class="fg"><label class="fl">CI/DNI</label><input class="fc" id="t-dni" placeholder="12345678"></div>
    <div class="fg"><label class="fl">Teléfono</label><input class="fc" id="t-tel" placeholder="77712345"></div>
   </div>
   <div class="fg"><label class="fl">Email</label><input class="fc" id="t-em" type="email" placeholder="carlos@empresa.com"></div>

   <!-- Datos profesionales -->
   <div style="font-size:11px;color:var(--t3);text-transform:uppercase;letter-spacing:1px;margin:16px 0 12px">⚙ Datos Profesionales</div>
   <div class="fr">
    <div class="fg"><label class="fl">Especialidad</label><input class="fc" id="t-esp" placeholder="Electricidad Industrial"></div>
    <div class="fg"><label class="fl">Nivel</label>
     <select class="fc" id="t-niv"><option>Junior</option><option>Intermedio</option><option>Senior</option></select>
    </div>
   </div>

   <!-- Credenciales de acceso -->
   <div class="cred-box" id="div-cred">
    <div class="cred-title">🔑 Acceso a la Plataforma <span style="color:var(--t3);font-weight:400;text-transform:none;letter-spacing:0">(opcional)</span></div>
    <div style="font-size:12px;color:var(--t2);margin-bottom:12px">
     Si completas estos campos, el técnico podrá iniciar sesión con rol TÉCNICO.
    </div>
    <div class="fg">
     <label class="fl">Username</label>
     <input class="fc" id="t-usr" placeholder="carlos.m" autocomplete="off">
     <div style="font-size:11px;color:var(--t3);margin-top:4px">Ej: nombre.apellido (sin espacios ni tildes)</div>
    </div>
    <div class="fg" style="margin-bottom:0">
     <label class="fl">Contraseña</label>
     <div class="pw-wrap">
      <input class="fc" id="t-pwd" type="password" placeholder="Mínimo 6 caracteres" autocomplete="new-password">
      <button class="toggle-pwd" type="button" onclick="togglePwd('t-pwd','eye-pwd')">
       <span id="eye-pwd">👁</span>
      </button>
     </div>
     <div style="font-size:11px;color:var(--t3);margin-top:4px">
      <span id="pwd-strength"></span>
     </div>
    </div>
   </div>

   <!-- Info credenciales en modo edicion -->
   <div class="cred-box" id="div-cred-edit" style="display:none">
    <div class="cred-title">🔑 Credenciales de Acceso</div>
    <div id="info-cred-edit" style="margin-bottom:12px"></div>
    <div class="fg">
     <label class="fl">Nueva contraseña <span style="color:var(--t3);font-weight:400">(dejar vacío para no cambiar)</span></label>
     <div class="pw-wrap">
      <input class="fc" id="t-pwd-edit" type="password" placeholder="Nueva contraseña..." autocomplete="new-password">
      <button class="toggle-pwd" type="button" onclick="togglePwd('t-pwd-edit','eye-edit')">
       <span id="eye-edit">👁</span>
      </button>
     </div>
    </div>
   </div>

  </div>
  <div class="modal-ft">
   <button class="btn btn-s" onclick="closeModal('m-tec')">Cancelar</button>
   <button class="btn btn-p" id="mt-btn" onclick="guardar()">Crear Técnico</button>
  </div>
 </div>
</div>

<script src="../assets/js/app.js"></script>
<script>
let tecs=[], pend=[], cnt={}, editId=null;

function togglePwd(inputId, eyeId){
 const i=document.getElementById(inputId);
 i.type = i.type==='password'?'text':'password';
 document.getElementById(eyeId).textContent = i.type==='password'?'👁':'🙈';
}

// Indicador de fortaleza de contraseña
document.addEventListener('DOMContentLoaded',()=>{
 const pwd = document.getElementById('t-pwd');
 if(pwd) pwd.addEventListener('input',()=>{
  const v = pwd.value;
  const el = document.getElementById('pwd-strength');
  if(!v){ el.textContent=''; return; }
  let score=0;
  if(v.length>=6) score++;
  if(v.length>=8) score++;
  if(/[A-Z]/.test(v)) score++;
  if(/[0-9]/.test(v)) score++;
  if(/[^A-Za-z0-9]/.test(v)) score++;
  const niveles=[
   {t:'Muy débil', c:'var(--red)'},
   {t:'Débil',     c:'var(--red)'},
   {t:'Regular',   c:'var(--orange)'},
   {t:'Buena',     c:'var(--accent)'},
   {t:'Fuerte',    c:'var(--green)'},
   {t:'Muy fuerte',c:'var(--green)'},
  ];
  const n = niveles[Math.min(score,5)];
  el.innerHTML = `<span style="color:${n.c}">● ${n.t}</span>`;
 });
});

async function init(){
 [tecs, pend] = await Promise.all([
  API.get('/tecnicos.php'),
  API.get('/ordenes.php?path=vista/pendientes'),
 ]);
 cnt={};
 (pend||[]).forEach(p=>{ cnt[p.idTecnico]=(cnt[p.idTecnico]||0)+1; });
 document.getElementById('cnt-t').textContent=(tecs||[]).length+' técnicos registrados';
 render();
}

function render(){
 const g=document.getElementById('grid-t');
 if(!tecs||!tecs.length){
  g.innerHTML='<div class="empty"><div class="empty-icon">◎</div>Sin técnicos registrados.<br>Crea el primero con el botón de arriba.</div>';
  return;
 }
 g.innerHTML=tecs.map(t=>{
  const c   = cnt[t.idTecnico]||0;
  const cls = c>3?'chi':c>0?'c1':'';
  const ini = ((t.nombre||'?')[0]+(t.apellido||'?')[0]).toUpperCase();
  return `<div class="tec-card ${cls}">
   <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
    <div class="tec-av">${ini}</div>
    <div style="flex:1;min-width:0">
     <div class="tec-name" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${t.nombre||''} ${t.apellido||''}</div>
     <div style="font-size:11px;color:var(--t3);margin-top:2px">${t.email||'Sin email'}</div>
    </div>
   </div>
   <div style="margin-bottom:14px">
    <div class="info-row">
     <span class="info-k">Especialidad</span>
     <span class="info-v">${t.especialidad||'—'}</span>
    </div>
    <div class="info-row">
     <span class="info-k">Teléfono</span>
     <span class="info-v">${t.telefono||'—'}</span>
    </div>
    <div class="info-row">
     <span class="info-k">Certificación</span>
     ${badgeN(t.nivelCertificacion)}
    </div>
    <div class="info-row">
     <span class="info-k">Órdenes pendientes</span>
     <span class="cnt-badge" style="color:${c>3?'var(--red)':c>0?'var(--accent)':'var(--t3)'}">${c}</span>
    </div>
   </div>
   <div style="display:flex;gap:8px">
    <a class="btn btn-p btn-sm" style="flex:1;text-align:center;text-decoration:none" href="ver_tecnico.php?id=${t.idTecnico}">👁 Ver</a>
    <button class="btn btn-s btn-sm" style="flex:1" onclick="abrirEditar(${t.idTecnico})">✎ Editar</button>
    <button class="btn btn-d btn-sm" onclick="eliminar(${t.idTecnico})">✕</button>
   </div>
  </div>`;
 }).join('');
}

function abrirModal(){
 editId=null;
 ['t-no','t-ap','t-dni','t-tel','t-em','t-esp','t-usr','t-pwd'].forEach(id=>{
  const el=document.getElementById(id); if(el) el.value='';
 });
 document.getElementById('t-niv').value='Junior';
 document.getElementById('pwd-strength').textContent='';
 document.getElementById('mt-tit').textContent='Nuevo Técnico';
 document.getElementById('mt-btn').textContent='Crear Técnico';
 document.getElementById('div-cred').style.display='block';
 document.getElementById('div-cred-edit').style.display='none';
 openModal('m-tec');
}

function abrirEditar(id){
 const t=tecs.find(x=>x.idTecnico===id); if(!t) return;
 editId=id;
 document.getElementById('t-no').value  = t.nombre||'';
 document.getElementById('t-ap').value  = t.apellido||'';
 document.getElementById('t-dni').value = t.ci||'';
 document.getElementById('t-tel').value = t.telefono||'';
 document.getElementById('t-em').value  = t.email||'';
 document.getElementById('t-esp').value = t.especialidad||'';
 document.getElementById('t-niv').value = t.nivelCertificacion||'Junior';
 document.getElementById('t-pwd-edit').value='';
 document.getElementById('mt-tit').textContent='Editar Técnico';
 document.getElementById('mt-btn').textContent='Guardar Cambios';
 document.getElementById('div-cred').style.display='none';
 document.getElementById('div-cred-edit').style.display='block';
 // Mostrar info del usuario asociado si existe
 document.getElementById('info-cred-edit').innerHTML =
  t.username
   ? `<div style="display:flex;align-items:center;gap:8px;font-size:13px">
       <span style="color:var(--t2)">Usuario activo:</span>
       <span class="badge-usr">◑ ${t.username}</span>
       <span style="color:var(--green);font-size:11px">✓ puede iniciar sesión</span>
      </div>`
   : `<div style="font-size:12px;color:var(--t3)">Este técnico no tiene usuario creado. No puede iniciar sesión.</div>`;
 openModal('m-tec');
}

async function guardar(){
 const no  = document.getElementById('t-no').value.trim();
 const ap  = document.getElementById('t-ap').value.trim();
 if(!no||!ap){ toast('Nombre y apellido son requeridos','warn'); return; }

 const data = {
  nombre:   no,
  apellido: ap,
  ci:       document.getElementById('t-dni').value||null,
  telefono: document.getElementById('t-tel').value||null,
  email:    document.getElementById('t-em').value||null,
  especialidad:       document.getElementById('t-esp').value||null,
  nivelCertificacion: document.getElementById('t-niv').value,
 };

 if(!editId){
  // Crear: incluir credenciales si se llenaron
  const usr = document.getElementById('t-usr').value.trim();
  const pwd = document.getElementById('t-pwd').value;
  if(usr && !pwd){ toast('Si pones username debes poner contraseña','warn'); return; }
  if(pwd && pwd.length < 6){ toast('La contraseña debe tener al menos 6 caracteres','warn'); return; }
  if(usr) data.username = usr;
  if(pwd) data.password = pwd;
 } else {
  // Editar: incluir nueva contraseña si se puso
  const pwd = document.getElementById('t-pwd-edit').value;
  if(pwd){
   if(pwd.length<6){ toast('La contraseña debe tener al menos 6 caracteres','warn'); return; }
   data.password = pwd;
  }
 }

 const r = editId
  ? await API.put(`/tecnicos.php?id=${editId}`, data)
  : await API.post('/tecnicos.php', data);

 if(r.error||!r.ok){ toast(r.error||'Error al guardar','err'); return; }
 toast(r.mensaje||'✓ Guardado correctamente');
 closeModal('m-tec');
 await init();
}

async function eliminar(id){
 confirmDel('¿Eliminar este técnico? Esta acción no se puede deshacer.', async()=>{
  const r = await API.del(`/tecnicos.php?id=${id}`);
  if(r.error){ toast(r.error,'err'); return; }
  toast('Técnico eliminado');
  await init();
 });
}
init();
</script>
</body></html>