<?php
require_once '../includes/session.php';
requireRol(['ADMIN','SUPERVISOR'],'../index.php');

$idTecnico = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$idTecnico) {
    header('Location: tecnicos.php');
    exit;
}
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Ver Técnico — MantTech</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
/* ── Layout principal ── */
.perfil-grid {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 24px;
    align-items: start;
}
@media(max-width:900px){ .perfil-grid{ grid-template-columns:1fr; } }

/* ── Tarjeta lateral del técnico ── */
.card-tec {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    position: sticky;
    top: 80px;
}
.card-tec-header {
    background: linear-gradient(135deg, var(--aD) 0%, var(--card2) 100%);
    border-bottom: 3px solid var(--accent);
    padding: 28px 20px 20px;
    text-align: center;
}
.avatar-xl {
    width: 80px;
    height: 80px;
    border-radius: 18px;
    background: var(--accent);
    color: #000;
    font-size: 30px;
    font-weight: 900;
    display: inline-grid;
    place-items: center;
    margin-bottom: 14px;
    font-family: var(--fw);
    border: 3px solid rgba(255,255,255,.15);
}
.tec-nombre-xl {
    font-family: var(--fw);
    font-weight: 800;
    font-size: 18px;
    margin-bottom: 4px;
}
.tec-esp {
    font-size: 12px;
    color: var(--t3);
    margin-bottom: 10px;
}
.card-tec-body { padding: 20px; }

/* ── Filas de datos ── */
.dato-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 10px 0;
    border-bottom: 1px solid var(--border);
    gap: 12px;
}
.dato-row:last-child { border: none; }
.dato-k { font-size: 11px; color: var(--t3); flex-shrink: 0; }
.dato-v { font-size: 13px; font-weight: 500; text-align: right; word-break: break-all; }

/* ── Badge de nivel ── */
.badge-niv {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}
.niv-junior   { background: rgba(100,100,100,.2); color: var(--t2); }
.niv-inter    { background: rgba(59,130,246,.15); color: #60a5fa; }
.niv-senior   { background: rgba(234,179,8,.15);  color: #facc15; }

/* ── Badge usuario ── */
.badge-usr {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--card2);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 3px 10px;
    font-size: 11px;
    font-family: var(--fm);
}
.badge-activo { color: var(--green); font-size: 10px; }

/* ── Estadísticas rápidas ── */
.stats-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid var(--border);
}
.stat-box {
    background: var(--card2);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 12px 8px;
    text-align: center;
}
.stat-num {
    font-family: var(--fw);
    font-weight: 800;
    font-size: 22px;
    line-height: 1;
    margin-bottom: 4px;
}
.stat-lbl { font-size: 10px; color: var(--t3); }

/* ── Sección de órdenes ── */
.ordenes-section {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
}
.section-hd {
    padding: 18px 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
}
.section-title {
    font-family: var(--fw);
    font-weight: 700;
    font-size: 15px;
}

/* ── Filtros de estado ── */
.filtros {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.filtro-btn {
    padding: 5px 14px;
    border-radius: 20px;
    border: 1px solid var(--border);
    background: var(--card2);
    color: var(--t2);
    font-size: 12px;
    cursor: pointer;
    transition: all .18s;
}
.filtro-btn:hover { border-color: var(--accent); color: var(--accent); }
.filtro-btn.active { background: var(--accent); border-color: var(--accent); color: #000; font-weight: 700; }

/* ── Tabla de órdenes ── */
.tbl-wrap { overflow-x: auto; }
.tbl {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.tbl th {
    padding: 10px 16px;
    text-align: left;
    font-size: 11px;
    color: var(--t3);
    text-transform: uppercase;
    letter-spacing: .7px;
    background: var(--card2);
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}
.tbl td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}
.tbl tr:last-child td { border: none; }
.tbl tr:hover td { background: var(--card2); }

/* ── Badges de estado ── */
.est-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}
.est-PENDIENTE   { background:rgba(234,179,8,.15);  color:#facc15; }
.est-PROCESO     { background:rgba(59,130,246,.15); color:#60a5fa; }
.est-COMPLETADA  { background:rgba(34,197,94,.15);  color:#4ade80; }
.est-CANCELADA   { background:rgba(239,68,68,.15);  color:#f87171; }

/* ── Badge de prioridad ── */
.pri-badge {
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}
.pri-ALTA   { color: var(--red);    }
.pri-MEDIA  { color: var(--orange); }
.pri-BAJA   { color: var(--green);  }

.empty-ord {
    padding: 48px 20px;
    text-align: center;
    color: var(--t3);
}
.empty-ord-icon { font-size: 36px; margin-bottom: 8px; }

/* ── Botón volver ── */
.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 16px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--card2);
    color: var(--t2);
    font-size: 13px;
    cursor: pointer;
    text-decoration: none;
    transition: all .18s;
}
.btn-back:hover { border-color: var(--accent); color: var(--accent); }
</style>
</head>
<body>
<div class="shell">
<?php include '../includes/nav.php'; ?>
<main class="main">

 <!-- Encabezado con botón volver -->
 <div class="ph" style="margin-bottom:24px">
  <div>
   <a href="tecnicos.php" class="btn-back">← Volver a Técnicos</a>
   <div class="ph-title" style="margin-top:10px" id="ph-nombre">Cargando...</div>
   <div class="ph-sub" id="ph-sub">Perfil del técnico</div>
  </div>
 </div>

 <div class="perfil-grid">

  <!-- ══ COLUMNA IZQUIERDA: Datos del técnico ══ -->
  <aside>
   <div class="card-tec" id="card-tec">
    <div class="card-tec-header">
     <div class="avatar-xl" id="tec-av">?</div>
     <div class="tec-nombre-xl" id="tec-nombre">Cargando...</div>
     <div class="tec-esp" id="tec-esp">—</div>
    </div>
    <div class="card-tec-body">

     <div class="dato-row">
      <span class="dato-k">CI / DNI</span>
      <span class="dato-v" id="tec-ci">—</span>
     </div>
     <div class="dato-row">
      <span class="dato-k">Teléfono</span>
      <span class="dato-v" id="tec-tel">—</span>
     </div>
     <div class="dato-row">
      <span class="dato-k">Email</span>
      <span class="dato-v" id="tec-email">—</span>
     </div>
     <div class="dato-row">
      <span class="dato-k">Certificación</span>
      <span class="dato-v" id="tec-niv">—</span>
     </div>
     <div class="dato-row">
      <span class="dato-k">Usuario</span>
      <span class="dato-v" id="tec-usr">—</span>
     </div>
     <div class="dato-row">
      <span class="dato-k">Estado cuenta</span>
      <span class="dato-v" id="tec-activo">—</span>
     </div>

     <!-- Estadísticas rápidas -->
     <div class="stats-row">
      <div class="stat-box">
       <div class="stat-num" id="st-total" style="color:var(--accent)">—</div>
       <div class="stat-lbl">Total</div>
      </div>
      <div class="stat-box">
       <div class="stat-num" id="st-pend" style="color:#facc15">—</div>
       <div class="stat-lbl">Pendientes</div>
      </div>
      <div class="stat-box">
       <div class="stat-num" id="st-ok" style="color:var(--green)">—</div>
       <div class="stat-lbl">Completadas</div>
      </div>
     </div>

    </div>
   </div>
  </aside>

  <!-- ══ COLUMNA DERECHA: Órdenes asignadas ══ -->
  <section class="ordenes-section">
   <div class="section-hd">
    <div class="section-title">📋 Órdenes Asignadas</div>
    <div class="filtros" id="filtros">
     <button class="filtro-btn active" data-est="TODOS" onclick="filtrar(this)">Todas</button>
     <button class="filtro-btn" data-est="PENDIENTE"  onclick="filtrar(this)">Pendientes</button>
     <button class="filtro-btn" data-est="PROCESO"    onclick="filtrar(this)">En Proceso</button>
     <button class="filtro-btn" data-est="COMPLETADA" onclick="filtrar(this)">Completadas</button>
     <button class="filtro-btn" data-est="CANCELADA"  onclick="filtrar(this)">Canceladas</button>
    </div>
   </div>
   <div class="tbl-wrap">
    <table class="tbl">
     <thead>
      <tr>
       <th>#</th>
       <th>Descripción</th>
       <th>Equipo</th>
       <th>Estado</th>
       <th>Prioridad</th>
       <th>Fecha Creación</th>
       <th>Fecha Prog.</th>
      </tr>
     </thead>
     <tbody id="tbl-body">
      <tr><td colspan="7" class="empty-ord"><div class="spinner"></div></td></tr>
     </tbody>
    </table>
   </div>
  </section>

 </div><!-- /perfil-grid -->
</main>
</div>

<script src="../assets/js/app.js"></script>
<script>
const ID_TEC = <?= $idTecnico ?>;
let todasLasOrdenes = [];
let filtroActual = 'TODOS';

/* ── Inicialización ── */
async function init() {
    try {
        // Carga paralela: datos del técnico + todas las órdenes
        const [tec, ordenes] = await Promise.all([
            API.get(`/tecnicos.php?id=${ID_TEC}`),
            API.get('/ordenes.php'),
        ]);

        if (tec.error) {
            toast('Técnico no encontrado', 'err');
            setTimeout(() => location.href = 'tecnicos.php', 1500);
            return;
        }

        renderTecnico(tec);

        // Filtrar solo las órdenes de este técnico
        // Las órdenes tienen idTecnico a través de la asignación
        // Usamos la vista de pendientes + comparamos con ordenes completas
        const ordenesTec = await obtenerOrdenesTecnico(ordenes, ID_TEC);
        todasLasOrdenes = ordenesTec;

        renderStats(ordenesTec);
        renderOrdenes(ordenesTec);

    } catch(e) {
        console.error(e);
        toast('Error al cargar datos', 'err');
    }
}

/* ── Obtener órdenes del técnico ── */
async function obtenerOrdenesTecnico(todasOrdenes, idTec) {
    // Cada orden puede tener técnicos asignados; consultamos la asignación
    // Usamos la vista de pendientes para cruzar, y también verificamos asignaciones
    // Estrategia: para cada orden consultamos sus técnicos asignados
    // Para eficiencia, filtramos por el campo tecnicoAsignado que ya viene en la lista
    // y también hacemos una consulta directa a la API de asignaciones

    // Método directo: usamos el endpoint de órdenes con filtro de técnico
    // (Alternativa: filtramos las que ya tienen el campo idTecnico en la asignación)
    
    // Primero intentamos con la vista de pendientes para obtener los idOrden del técnico
    const pendientes = await API.get('/ordenes.php?path=vista/pendientes');
    const idOrdenesPendTec = new Set(
        (pendientes || [])
            .filter(p => p.idTecnico == idTec)
            .map(p => p.idOrden)
    );

    // Luego para cada orden verificamos si el técnico está asignado
    // Para no hacer N requests, cruzamos con la consulta de asignaciones por orden
    // Hacemos una consulta de todas las órdenes y filtramos por técnico asignado
    // El campo 'tecnicoAsignado' en la lista general es solo el primero; usamos asignaciones
    
    const resultados = [];
    for (const orden of (todasOrdenes || [])) {
        const asigs = await API.get(`/ordenes.php?path=${orden.idOrden}/tecnicos`);
        if ((asigs || []).some(a => a.idTecnico == idTec)) {
            resultados.push(orden);
        }
    }
    return resultados;
}

/* ── Renderizar datos del técnico ── */
function renderTecnico(t) {
    const nombre  = `${t.nombres || t.nombre || ''} ${t.apellidos || t.apellido || ''}`.trim();
    const iniciales = ((t.nombres || t.nombre || '?')[0] + (t.apellidos || t.apellido || '?')[0]).toUpperCase();

    document.getElementById('ph-nombre').textContent  = nombre;
    document.getElementById('ph-sub').textContent     = t.especialidad || 'Técnico';
    document.getElementById('tec-av').textContent     = iniciales;
    document.getElementById('tec-nombre').textContent = nombre;
    document.getElementById('tec-esp').textContent    = t.especialidad || 'Sin especialidad';
    document.getElementById('tec-ci').textContent     = t.ci  || '—';
    document.getElementById('tec-tel').textContent    = t.telefono || '—';
    document.getElementById('tec-email').textContent  = t.email || '—';
    document.getElementById('tec-niv').innerHTML      = badgeNivel(t.nivelCertificacion);
    document.getElementById('tec-usr').innerHTML      = t.username
        ? `<span class="badge-usr">◑ ${t.username}</span>`
        : '<span style="color:var(--t3);font-size:12px">Sin usuario</span>';
    document.getElementById('tec-activo').innerHTML   = t.username
        ? (t.usuarioActivo == 1
            ? '<span class="badge-activo">✓ Activo</span>'
            : '<span style="color:var(--red);font-size:12px">✗ Inactivo</span>')
        : '—';

    document.title = `${nombre} — MantTech`;
}

/* ── Renderizar estadísticas ── */
function renderStats(ords) {
    const total     = ords.length;
    const pendientes = ords.filter(o => (o.nombreEstado||'').toUpperCase().includes('PEND')).length;
    const completadas = ords.filter(o => (o.nombreEstado||'').toUpperCase().includes('COMP')).length;

    document.getElementById('st-total').textContent = total;
    document.getElementById('st-pend').textContent  = pendientes;
    document.getElementById('st-ok').textContent    = completadas;
}

/* ── Filtrar por estado ── */
function filtrar(btn) {
    document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    filtroActual = btn.dataset.est;
    const filtradas = filtroActual === 'TODOS'
        ? todasLasOrdenes
        : todasLasOrdenes.filter(o => (o.nombreEstado || '').toUpperCase().includes(filtroActual));
    renderOrdenes(filtradas);
}

/* ── Renderizar tabla de órdenes ── */
function renderOrdenes(ords) {
    const tbody = document.getElementById('tbl-body');

    if (!ords.length) {
        tbody.innerHTML = `
            <tr><td colspan="7">
                <div class="empty-ord">
                    <div class="empty-ord-icon">📭</div>
                    <div>Sin órdenes ${filtroActual !== 'TODOS' ? 'con este estado' : 'asignadas'}</div>
                </div>
            </td></tr>`;
        return;
    }

    tbody.innerHTML = ords.map(o => {
        const estado   = (o.nombreEstado   || '').toUpperCase();
        const prioridad = (o.nombrePrioridad || '').toUpperCase();
        const fecha    = o.fechaCreacion ? o.fechaCreacion.split('T')[0] : '—';
        const fechaProg = o.fechaProgramada ? o.fechaProgramada.split('T')[0] : '—';
        const desc     = (o.descripcion || '').length > 60
            ? o.descripcion.substring(0, 60) + '…'
            : (o.descripcion || '—');

        return `<tr>
            <td style="font-family:var(--fm);font-weight:700;color:var(--t3)">#${o.idOrden}</td>
            <td title="${o.descripcion || ''}">${desc}</td>
            <td>${o.equipo || '—'}</td>
            <td><span class="est-badge est-${estado}">${o.nombreEstado || '—'}</span></td>
            <td><span class="pri-badge pri-${prioridad}">${o.nombrePrioridad || '—'}</span></td>
            <td style="font-size:12px;color:var(--t3)">${fecha}</td>
            <td style="font-size:12px;color:var(--t3)">${fechaProg}</td>
        </tr>`;
    }).join('');
}

/* ── Badge de nivel de certificación ── */
function badgeNivel(nivel) {
    if (!nivel) return '<span style="color:var(--t3)">—</span>';
    const map = {
        'Junior':     'niv-junior',
        'Intermedio': 'niv-inter',
        'Senior':     'niv-senior',
    };
    const cls = map[nivel] || 'niv-junior';
    return `<span class="badge-niv ${cls}">${nivel}</span>`;
}

init();
</script>
</body>
</html>