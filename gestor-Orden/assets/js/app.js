// ================================================================
// MantTech — app.js
// ================================================================

// ── API BASE: detecta subcarpeta automaticamente ──────────────────
const API = (() => {
  // Encuentra la raiz del proyecto buscando /gestor-Orden/ en la URL
  // Funciona desde cualquier nivel: /, /pages/, etc.
  const path = window.location.pathname;

  // Estrategia: subir hasta encontrar la raiz (donde esta index.php)
  // Eliminar /pages/ si existe, luego agregar /api
  let base = path
    .replace(/\/pages\/[^/]*$/, '')   // quitar /pages/archivo.php
    .replace(/\/[^/]*\.php$/, '')     // quitar /archivo.php de la raiz
    .replace(/\/$/, '');              // quitar trailing slash

  // Si base quedo vacio, usar origen limpio
  if (!base) base = '';

  const apiBase = window.location.origin + base + '/api';

  const headers = { 'Content-Type': 'application/json' };

  const handleResp = async (resp) => {
    const text = await resp.text();
    try {
      return JSON.parse(text);
    } catch(e) {
      console.error('API respuesta no-JSON:', text.substring(0, 300));
      return { ok: false, error: 'Respuesta inválida del servidor' };
    }
  };

  return {
    base: apiBase,
    get:  (u) => fetch(apiBase + u).then(handleResp),
    post: (u, d) => fetch(apiBase + u, { method:'POST', headers, body: JSON.stringify(d) }).then(handleResp),
    put:  (u, d) => fetch(apiBase + u, { method:'PUT',  headers, body: JSON.stringify(d) }).then(handleResp),
    del:  (u) => fetch(apiBase + u, { method:'DELETE' }).then(handleResp),
  };
})();

// ── TOAST ─────────────────────────────────────────────────────────
function toast(msg, type = 'ok') {
  let el = document.getElementById('_toast');
  if (!el) {
    el = document.createElement('div');
    el.id = '_toast';
    el.style.cssText = 'position:fixed;bottom:22px;right:22px;z-index:9999;padding:12px 20px;border-radius:10px;font-size:13px;font-family:var(--fm);max-width:360px;transition:all .3s;opacity:0;transform:translateY(8px);pointer-events:none;box-shadow:0 8px 24px rgba(0,0,0,.4)';
    document.body.appendChild(el);
  }
  const styles = {
    ok:   { background:'#2ecc7118', color:'#2ecc71', border:'1px solid #2ecc7140' },
    err:  { background:'#ff475720', color:'#ff4757', border:'1px solid #ff475750' },
    warn: { background:'#f0a50022', color:'#f0a500', border:'1px solid #f0a50050' },
    info: { background:'#4a9eff18', color:'#4a9eff', border:'1px solid #4a9eff40' },
  };
  Object.assign(el.style, styles[type] || styles.ok, { opacity:'1', transform:'translateY(0)' });
  el.textContent = msg;
  clearTimeout(el._t);
  el._t = setTimeout(() => { el.style.opacity = '0'; el.style.transform = 'translateY(8px)'; }, 3500);
}

// ── MODAL ─────────────────────────────────────────────────────────
function openModal(id)  { document.getElementById(id)?.classList.add('open'); }
function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }

document.addEventListener('click', e => {
  if (e.target.classList.contains('modal-ov'))
    e.target.classList.remove('open');
  if (e.target.classList.contains('modal-close'))
    e.target.closest('.modal-ov')?.classList.remove('open');
});

// ── UTILS ─────────────────────────────────────────────────────────
function confirmDel(msg, cb) { if (window.confirm(msg)) cb(); }
function fmtDate(d)     { return d ? new Date(d).toLocaleDateString('es-BO') : '—'; }
function fmtDateTime(d) { return d ? new Date(d).toLocaleString('es-BO') : '—'; }
function fmtBs(v)       { return 'Bs. ' + Number(v || 0).toFixed(2); }

// ── BADGES ────────────────────────────────────────────────────────
const EC = { PENDIENTE:'b-y', EN_PROCESO:'b-bl', EN_ESPERA:'b-p', CERRADA:'b-g', CANCELADA:'b-gr' };
const PC = { BAJA:'b-g', MEDIA:'b-y', ALTA:'b-o', CRITICA:'b-r', EMERGENCIA:'b-r' };
const NC = { Junior:'b-bl', Intermedio:'b-c', Senior:'b-y' };
const RC = { ADMIN:'b-r', SUPERVISOR:'b-y', TECNICO:'b-c', SOLICITANTE:'b-bl' };

function badgeE(e) { return `<span class="badge ${EC[e]||'b-gr'}">${e||'—'}</span>`; }
function badgeP(p) { return `<span class="badge ${PC[p]||'b-gr'}">${p||'—'}</span>`; }
function badgeT(t) { return `<span class="tipo-badge t-${(t||'').toLowerCase()}">${t||'—'}</span>`; }
function badgeN(n) { return `<span class="badge ${NC[n]||'b-gr'}">${n||'—'}</span>`; }
function badgeR(r) { return `<span class="badge ${RC[r]||'b-gr'}">${r||'—'}</span>`; }

// ── FILL SELECT ───────────────────────────────────────────────────
function fillSel(id, data, vk, lk, placeholder = 'Seleccionar...') {
  const s = document.getElementById(id);
  if (!s) return;
  const cur = s.value;
  s.innerHTML = `<option value="">${placeholder}</option>`;
  (data || []).forEach(d => {
    const o = document.createElement('option');
    o.value = d[vk];
    o.textContent = d[lk];
    s.appendChild(o);
  });
  if (cur) s.value = cur;
}

// ── STAT CARDS ────────────────────────────────────────────────────
function buildStats(arr) {
  return arr.map(s => `
    <div class="stat-card ${s.c}">
      <div class="stat-lbl">${s.l}</div>
      <div class="stat-val v-${s.c.replace('c-','')}">${s.v}</div>
      ${s.sub ? `<div style="font-size:11px;color:var(--t3);margin-top:4px">${s.sub}</div>` : ''}
    </div>`).join('');
}

// ── SIDEBAR TOGGLE ────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  // Toggle sidebar
  const btn = document.getElementById('toggle-btn');
  if (btn) btn.addEventListener('click', () => {
    const collapsed = document.body.classList.toggle('collapsed');
    btn.textContent = collapsed ? '▶' : '◀';
  });

  // ── INDICADOR DE USUARIO LOGUEADO ────────────────────────────
  // Lee datos de sesion via API y muestra quien esta conectado
  // en el header si existe el elemento #session-info
  const sessionEl = document.getElementById('session-info');
  if (sessionEl) {
    API.get('/auth.php?action=whoami').then(d => {
      if (d && d.ok) {
        sessionEl.innerHTML = `
          <span style="font-size:12px;color:var(--t2)">Conectado como</span>
          <strong style="color:var(--accent)">${d.nombre}</strong>
          <span class="badge ${RC[d.rol]||'b-gr'}" style="font-size:10px">${d.rol}</span>`;
      }
    }).catch(() => {});
  }

  // Marcar nav-link activo segun URL actual
  const current = window.location.pathname.split('/').pop().replace('.php','');
  document.querySelectorAll('.nav-link').forEach(a => {
    const href = a.getAttribute('href') || '';
    const key  = href.split('/').pop().replace('.php','');
    if (key === current) a.classList.add('active');
    else a.classList.remove('active');
  });
});

// ── DEBUG: mostrar base de API en consola ─────────────────────────
console.log('[MantTech] API base:', API.base);