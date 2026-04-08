<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Si ya hay sesion activa, redirigir segun rol
if (isset($_SESSION['u'])) {
    $rol = $_SESSION['u']['rol'];
    if ($rol === 'TECNICO')     { header('Location: pages/dashboard_tecnico.php'); exit; }
    if ($rol === 'SOLICITANTE') { header('Location: pages/dashboard_solicitante.php'); exit; }
    // ADMIN y SUPERVISOR van al dashboard
}

// Si no hay sesion, mostrar login embebido en index
$sinSesion = !isset($_SESSION['u']);
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>MantTech — Sistema de Mantenimiento</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
/* ===== VARIABLES ===== */
:root{
  --bg:#0d0f12;--card:#141720;--card2:#1a1f2e;--input:#1e2435;
  --border:#2a3148;--accent:#f0a500;--aD:#f0a50022;
  --red:#ff4757;--rD:#ff475720;--green:#2ecc71;--gD:#2ecc7118;
  --cyan:#00c8b4;--purple:#7c6cf8;--orange:#f39c12;
  --t1:#e8eaf0;--t2:#8892a4;--t3:#4a5568;
  --fw:'Syne',sans-serif;--fm:'DM Mono',monospace;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--t1);font-family:var(--fm);min-height:100vh;overflow-x:hidden}

/* ===== FONDO ANIMADO ===== */
body::before{
  content:'';position:fixed;inset:-40%;pointer-events:none;z-index:0;
  background:
    radial-gradient(ellipse at 20% 30%,#f0a50008 0%,transparent 50%),
    radial-gradient(ellipse at 80% 70%,#00c8b408 0%,transparent 50%),
    radial-gradient(ellipse at 50% 50%,#7c6cf806 0%,transparent 60%);
  animation:bgpulse 16s ease-in-out infinite alternate;
}
@keyframes bgpulse{from{transform:scale(1)}to{transform:scale(1.1) rotate(3deg)}}

/* ===== LAYOUT ===== */
.page{position:relative;z-index:1;min-height:100vh;display:flex;flex-direction:column}

/* ===== HEADER ===== */
.header{
  display:flex;align-items:center;justify-content:space-between;
  padding:18px 48px;border-bottom:1px solid var(--border);
  background:rgba(13,15,18,.85);backdrop-filter:blur(12px);
  position:sticky;top:0;z-index:100;
}
.header-logo{display:flex;align-items:center;gap:12px;text-decoration:none}
.hl-icon{
  width:40px;height:40px;background:var(--accent);border-radius:10px;
  display:grid;place-items:center;font-size:20px;
  animation:spin 12s linear infinite;
  box-shadow:0 0 20px rgba(240,165,0,.3);
}
@keyframes spin{to{transform:rotate(360deg)}}
.hl-name{font-family:var(--fw);font-size:20px;font-weight:800;letter-spacing:2px;color:var(--t1)}
.header-nav{display:flex;align-items:center;gap:24px}
.hn-link{font-size:13px;color:var(--t2);text-decoration:none;transition:color .2s}
.hn-link:hover{color:var(--t1)}
.btn-header{
  background:var(--accent);color:#000;border:none;border-radius:8px;
  padding:9px 20px;font-family:var(--fw);font-size:13px;font-weight:700;
  cursor:pointer;letter-spacing:.5px;transition:all .2s;
}
.btn-header:hover{background:#ffb820;transform:translateY(-1px)}

/* ===== HERO ===== */
.hero{
  flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;
  text-align:center;padding:80px 24px 60px;
}
.hero-badge{
  display:inline-flex;align-items:center;gap:8px;
  background:var(--aD);border:1px solid #f0a50040;border-radius:20px;
  padding:6px 16px;font-size:11px;color:var(--accent);
  text-transform:uppercase;letter-spacing:1.5px;margin-bottom:24px;
}
.hero-title{
  font-family:var(--fw);font-size:clamp(36px,6vw,72px);font-weight:800;
  line-height:1.05;margin-bottom:20px;
}
.hero-title .ht-line2{
  background:linear-gradient(90deg,var(--accent),var(--cyan));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.hero-sub{
  font-size:clamp(14px,2vw,17px);color:var(--t2);max-width:560px;
  line-height:1.7;margin-bottom:40px;
}
.hero-btns{display:flex;gap:14px;flex-wrap:wrap;justify-content:center;margin-bottom:60px}
.btn-primary{
  background:var(--accent);color:#000;border:none;border-radius:12px;
  padding:14px 32px;font-family:var(--fw);font-size:16px;font-weight:700;
  cursor:pointer;letter-spacing:.5px;transition:all .2s;
  box-shadow:0 8px 32px rgba(240,165,0,.3);
}
.btn-primary:hover{background:#ffb820;transform:translateY(-2px);box-shadow:0 12px 40px rgba(240,165,0,.4)}
.btn-secondary{
  background:transparent;color:var(--t1);border:1px solid var(--border);border-radius:12px;
  padding:14px 32px;font-family:var(--fw);font-size:16px;font-weight:700;
  cursor:pointer;letter-spacing:.5px;transition:all .2s;
}
.btn-secondary:hover{border-color:var(--accent);color:var(--accent)}

/* ===== ROLES CARDS ===== */
.roles-strip{display:flex;gap:12px;flex-wrap:wrap;justify-content:center}
.role-chip{
  display:flex;align-items:center;gap:8px;
  background:var(--card);border:1px solid var(--border);border-radius:10px;
  padding:10px 16px;font-size:12px;transition:all .2s;cursor:default;
}
.role-chip:hover{border-color:var(--accent);transform:translateY(-2px)}
.rc-dot{width:8px;height:8px;border-radius:50%}

/* ===== FEATURES ===== */
.features{
  display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
  gap:20px;padding:40px 48px 60px;max-width:1200px;margin:0 auto;width:100%;
}
.feat-card{
  background:var(--card);border:1px solid var(--border);border-radius:16px;
  padding:28px;transition:all .25s;
}
.feat-card:hover{border-color:var(--accent);transform:translateY(-4px);box-shadow:0 16px 40px rgba(0,0,0,.4)}
.fc-icon{font-size:28px;margin-bottom:14px}
.fc-title{font-family:var(--fw);font-size:16px;font-weight:700;margin-bottom:8px}
.fc-desc{font-size:13px;color:var(--t2);line-height:1.6}

/* ===== LOGIN MODAL ===== */
.modal-overlay{
  display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);
  backdrop-filter:blur(6px);z-index:200;
  align-items:center;justify-content:center;padding:20px;
}
.modal-overlay.active{display:flex}
.modal{
  background:var(--card);border:1px solid var(--border);border-radius:20px;
  padding:40px;width:100%;max-width:420px;position:relative;
  box-shadow:0 32px 80px rgba(0,0,0,.7);animation:modalIn .25s ease;
}
@keyframes modalIn{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
.modal-close{
  position:absolute;top:16px;right:16px;background:none;border:none;
  color:var(--t3);font-size:20px;cursor:pointer;transition:color .2s;
  width:32px;height:32px;display:grid;place-items:center;border-radius:6px;
}
.modal-close:hover{color:var(--t1);background:var(--card2)}
.modal-logo{text-align:center;margin-bottom:24px}
.ml-icon{
  width:56px;height:56px;background:var(--accent);border-radius:14px;
  display:inline-grid;place-items:center;font-size:28px;margin-bottom:10px;
  animation:spin 12s linear infinite;
}
.ml-title{font-family:var(--fw);font-size:22px;font-weight:800}
.ml-sub{font-size:12px;color:var(--t3);margin-top:3px}

.fg{margin-bottom:16px}
.fl{display:block;font-size:11px;color:var(--t3);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px}
.fc{
  width:100%;background:var(--input);border:1px solid var(--border);border-radius:10px;
  padding:11px 15px;color:var(--t1);font-family:var(--fm);font-size:14px;
  outline:none;transition:border-color .2s;
}
.fc:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--aD)}
.pw{position:relative}
.pt{
  position:absolute;right:12px;top:50%;transform:translateY(-50%);
  background:none;border:none;color:var(--t3);cursor:pointer;font-size:15px;
}
.pt:hover{color:var(--accent)}
.btn-login{
  width:100%;background:var(--accent);color:#000;border:none;border-radius:10px;
  padding:13px;font-family:var(--fw);font-size:15px;font-weight:700;
  cursor:pointer;letter-spacing:1px;transition:all .2s;margin-top:4px;
}
.btn-login:hover{background:#ffb820;transform:translateY(-1px)}
.btn-login:disabled{opacity:.5;cursor:not-allowed;transform:none}
.alert{padding:10px 14px;border-radius:9px;font-size:13px;margin-bottom:14px;display:none}
.alert.show{display:block}
.a-err{background:var(--rD);color:var(--red);border:1px solid #ff475740}
.a-ok{background:var(--gD);color:var(--green);border:1px solid #2ecc7140}

.demo-section{margin-top:20px;border-top:1px solid var(--border);padding-top:16px}
.demo-label{font-size:11px;color:var(--t3);text-transform:uppercase;letter-spacing:1px;margin-bottom:10px}
.demo-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.demo-btn{
  background:var(--card2);border:1px solid var(--border);border-radius:8px;
  padding:9px 11px;cursor:pointer;transition:all .2s;text-align:left;width:100%;
}
.demo-btn:hover{border-color:var(--accent);background:var(--aD)}
.db-rol{font-size:10px;color:var(--accent);text-transform:uppercase;letter-spacing:1px;margin-bottom:2px}
.db-usr{font-size:13px;color:var(--t1);font-family:var(--fm);font-weight:500}
.db-desc{font-size:11px;color:var(--t3)}
.sp{display:inline-block;width:13px;height:13px;border:2px solid #00000050;border-top-color:#000;border-radius:50%;animation:spin .6s linear infinite;vertical-align:middle;margin-right:5px}

/* ===== FOOTER ===== */
.footer{
  border-top:1px solid var(--border);padding:20px 48px;
  display:flex;align-items:center;justify-content:space-between;
  font-size:12px;color:var(--t3);flex-wrap:wrap;gap:10px;
}
</style>
</head>
<body>
<div class="page">

  <!-- HEADER -->
  <header class="header">
    <a class="header-logo" href="index.php">
      <div class="hl-icon">⚙</div>
      <span class="hl-name">MantTech</span>
    </a>
    <nav class="header-nav">
      <a class="hn-link" href="#features">Funciones</a>
      <a class="hn-link" href="#roles">Roles</a>
      <button class="btn-header" onclick="abrirLogin()">Iniciar Sesión →</button>
    </nav>
  </header>

  <!-- HERO -->
  <section class="hero">
    <div class="hero-badge">⚙ Sistema Industrial de Mantenimiento</div>
    <h1 class="hero-title">
      Gestión de Órdenes<br>
      <span class="ht-line2">de Mantenimiento</span>
    </h1>
    <p class="hero-sub">
      Planifica, asigna y hace seguimiento de órdenes de trabajo preventivo y correctivo.
      Control total de técnicos, repuestos, equipos y estados en tiempo real.
    </p>
    <div class="hero-btns">
      <button class="btn-primary" onclick="abrirLogin()">🚀 Acceder al Sistema</button>
      <button class="btn-secondary" onclick="document.getElementById('features').scrollIntoView({behavior:'smooth'})">Ver funciones ↓</button>
    </div>

    <!-- Roles strip -->
    <div class="roles-strip" id="roles">
      <div class="role-chip"><div class="rc-dot" style="background:#f0a500"></div><span>👑 Administrador</span></div>
      <div class="role-chip"><div class="rc-dot" style="background:#00c8b4"></div><span>🎯 Supervisor</span></div>
      <div class="role-chip"><div class="rc-dot" style="background:#7c6cf8"></div><span>⚙ Técnico</span></div>
      <div class="role-chip"><div class="rc-dot" style="background:#2ecc71"></div><span>✦ Solicitante</span></div>
    </div>
  </section>

  <!-- FEATURES -->
  <section class="features" id="features">
    <div class="feat-card">
      <div class="fc-icon">◉</div>
      <div class="fc-title">Órdenes de Trabajo</div>
      <div class="fc-desc">Crea, asigna y cierra órdenes preventivas y correctivas. Seguimiento completo de estado y prioridad.</div>
    </div>
    <div class="feat-card">
      <div class="fc-icon">◎</div>
      <div class="fc-title">Gestión de Técnicos</div>
      <div class="fc-desc">Asigna técnicos según especialidad y carga de trabajo. Visualiza la distribución en tiempo real.</div>
    </div>
    <div class="feat-card">
      <div class="fc-icon">◍</div>
      <div class="fc-title">Control de Repuestos</div>
      <div class="fc-desc">Inventario con alertas de stock mínimo, ajuste de cantidades y consumo por período.</div>
    </div>
    <div class="feat-card">
      <div class="fc-icon">⬡</div>
      <div class="fc-title">Equipos e Instalaciones</div>
      <div class="fc-desc">Registro completo de equipos por ubicación, planta, zona y área de la empresa.</div>
    </div>
    <div class="feat-card">
      <div class="fc-icon">📊</div>
      <div class="fc-title">Reportes y Analíticas</div>
      <div class="fc-desc">Gráficos de tendencia, distribución por estado, carga por técnico y consumo de repuestos.</div>
    </div>
    <div class="feat-card">
      <div class="fc-icon">🔒</div>
      <div class="fc-title">Control de Acceso</div>
      <div class="fc-desc">4 roles diferenciados con permisos específicos. Auditoría de accesos y seguridad de sesiones.</div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="footer">
    <span>MantTech © 2026 — Sistema de Gestión de Mantenimiento Industrial</span>
    <span>Apache <?= phpversion() ?> · PHP <?= PHP_VERSION ?></span>
  </footer>

</div>

<!-- MODAL LOGIN -->
<div class="modal-overlay" id="modal-login" onclick="cerrarSiOverlay(event)">
  <div class="modal">
    <button class="modal-close" onclick="cerrarLogin()">✕</button>
    <div class="modal-logo">
      <div class="ml-icon">⚙</div>
      <div class="ml-title">MantTech</div>
      <div class="ml-sub">Ingresa tus credenciales</div>
    </div>

    <div class="alert a-err" id="ae"></div>
    <div class="alert a-ok"  id="ao"></div>

    <div class="fg">
      <label class="fl">Usuario</label>
      <input class="fc" id="usr" type="text" placeholder="nombre.usuario" autocomplete="username">
    </div>
    <div class="fg">
      <label class="fl">Contraseña</label>
      <div class="pw">
        <input class="fc" id="pwd" type="password" placeholder="••••••••" autocomplete="current-password">
        <button class="pt" type="button" onclick="togglePwd()">👁</button>
      </div>
    </div>
    <button class="btn-login" id="btn-login" onclick="doLogin()">Iniciar Sesión</button>

    <div class="demo-section">
      <div class="demo-label">Accesos de prueba — contraseña: 123456</div>
      <div class="demo-grid">
        <button class="demo-btn" onclick="fill('admin')">
          <div class="db-rol">👑 Admin</div>
          <div class="db-usr">admin</div>
          <div class="db-desc">Acceso total</div>
        </button>
        <button class="demo-btn" onclick="fill('supervisor')">
          <div class="db-rol">🎯 Supervisor</div>
          <div class="db-usr">supervisor</div>
          <div class="db-desc">Gestión completa</div>
        </button>
        <button class="demo-btn" onclick="fill('carlos.m')">
          <div class="db-rol">⚙ Técnico</div>
          <div class="db-usr">carlos.m</div>
          <div class="db-desc">Mis órdenes</div>
        </button>
        <button class="demo-btn" onclick="fill('maria.s')">
          <div class="db-rol">✦ Solicitante</div>
          <div class="db-usr">maria.s</div>
          <div class="db-desc">Crear solicitudes</div>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function abrirLogin(){ document.getElementById('modal-login').classList.add('active'); document.getElementById('usr').focus(); }
function cerrarLogin(){ document.getElementById('modal-login').classList.remove('active'); }
function cerrarSiOverlay(e){ if(e.target===document.getElementById('modal-login')) cerrarLogin(); }
function togglePwd(){ const i=document.getElementById('pwd'); i.type=i.type==='password'?'text':'password'; }
function fill(u){ document.getElementById('usr').value=u; document.getElementById('pwd').value='123456'; }

function showAlert(t,m){
  ['ae','ao'].forEach(id=>{ const e=document.getElementById(id); e.classList.remove('show'); e.textContent=''; });
  const e=document.getElementById(t); e.textContent=m; e.classList.add('show');
}

async function doLogin(){
  const u=document.getElementById('usr').value.trim();
  const p=document.getElementById('pwd').value;
  if(!u||!p){ showAlert('ae','Completa usuario y contraseña'); return; }

  const btn=document.getElementById('btn-login');
  btn.disabled=true;
  btn.innerHTML='<span class="sp"></span>Verificando...';

  try {
    const r = await fetch('api/auth.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({username:u, password:p})
    });

    // Primero verificar que la respuesta sea JSON
    const txt = await r.text();
    let d;
    try { d = JSON.parse(txt); }
    catch(e) {
      console.error('Respuesta no es JSON:', txt);
      showAlert('ae','Error del servidor. Ver consola.');
      btn.disabled=false; btn.textContent='Iniciar Sesión';
      return;
    }

    if(d.ok){
      showAlert('ao','¡Bienvenido, '+d.nombre+'!');
      setTimeout(()=>window.location.href=d.redirect, 800);
    } else {
      showAlert('ae', d.error||'Error al iniciar sesión');
      btn.disabled=false; btn.textContent='Iniciar Sesión';
    }
  } catch(e) {
    console.error(e);
    showAlert('ae','Error de conexión con el servidor');
    btn.disabled=false; btn.textContent='Iniciar Sesión';
  }
}

document.addEventListener('keydown', e=>{ if(e.key==='Enter') doLogin(); });
document.addEventListener('keydown', e=>{ if(e.key==='Escape') cerrarLogin(); });

// Si hay error en URL, abrir modal automaticamente
<?php if(isset($_GET['error'])): ?>
abrirLogin();
<?php endif; ?>
</script>
</body>
</html>