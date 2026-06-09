<style>
/* ═══ ADMIN PANEL STYLES ═══ */
.adm-guard { display: flex; align-items: center; justify-content: center; min-height: 60vh; }
.adm-guard__card {
  background: var(--clr-card, #1a1f2e);
  border: 1px solid var(--clr-border, #2a3045);
  border-radius: 12px;
  padding: 2.5rem 2rem;
  text-align: center;
  max-width: 400px;
  width: 100%;
}
.adm-guard__icon  { font-size: 3rem; margin-bottom: 1rem; }
.adm-guard__title { font-size: 1.25rem; font-weight: 600; color: var(--clr-text, #e2e8f0); margin-bottom: .5rem; }
.adm-guard__sub   { color: var(--clr-muted, #8892a4); font-size: .9rem; }

/* Layout */
.adm { display: flex; min-height: calc(100vh - 80px); }

/* Sidebar */
.adm__sidebar {
  width: 220px; flex-shrink: 0;
  background: var(--clr-card, #1a1f2e);
  border-right: 1px solid var(--clr-border, #2a3045);
  display: flex; flex-direction: column;
  padding: 1.5rem 0 1rem;
}
.adm__sidebar-head {
  display: flex; align-items: center; gap: .6rem;
  padding: 0 1.2rem 1.5rem;
  border-bottom: 1px solid var(--clr-border, #2a3045);
  margin-bottom: .5rem;
}
.adm__sidebar-logo {
  background: var(--clr-accent, #3b82f6); color: #fff;
  font-weight: 700; font-size: .85rem; border-radius: 8px;
  width: 32px; height: 32px;
  display: flex; align-items: center; justify-content: center;
}
.adm__sidebar-brand { font-weight: 600; font-size: .95rem; color: var(--clr-text, #e2e8f0); }
.adm__sidebar-badge {
  margin-left: auto;
  background: #ef4444; color: #fff;
  font-size: .65rem; font-weight: 700; border-radius: 4px;
  padding: 1px 5px; letter-spacing: .03em;
}
.adm__nav { flex: 1; padding: 0 .75rem; display: flex; flex-direction: column; gap: .25rem; }
.adm__nav-btn {
  display: flex; align-items: center; gap: .65rem;
  width: 100%; padding: .6rem .9rem; border: none; border-radius: 8px;
  background: transparent; color: var(--clr-muted, #8892a4);
  font-size: .875rem; cursor: pointer;
  transition: background .15s, color .15s; text-align: left;
}
.adm__nav-btn:hover   { background: rgba(59,130,246,.08); color: var(--clr-text, #e2e8f0); }
.adm__nav-btn--active { background: rgba(59,130,246,.15); color: var(--clr-accent, #3b82f6); }

.adm__main { flex: 1; display: flex; flex-direction: column; min-width: 0; }

/* Topbar */
.adm__topbar {
  display: flex; align-items: center; justify-content: space-between;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--clr-border, #2a3045);
  background: var(--clr-card, #1a1f2e);
}
.adm__topbar-title { font-size: 1.1rem; font-weight: 600; color: var(--clr-text, #e2e8f0); }
.adm__topbar-user  { display: flex; align-items: center; gap: .5rem; }
.adm__topbar-name  { font-size: .875rem; color: var(--clr-text, #e2e8f0); }
.adm__topbar-chip  {
  background: rgba(239,68,68,.15); color: #ef4444;
  font-size: .7rem; font-weight: 600; border-radius: 4px;
  padding: 2px 7px; letter-spacing: .04em; text-transform: uppercase;
}

/* Content */
.adm__content { padding: 1.5rem; flex: 1; overflow: auto; }
.adm__view { display: none; }
.adm__view--active { display: block; }

/* Stat cards */
.adm-stats {
  display: grid; grid-template-columns: repeat(4, 1fr);
  gap: 1rem; margin-bottom: 1.5rem;
}
@media (max-width: 1000px) { .adm-stats { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px)  { .adm-stats { grid-template-columns: 1fr; } }
.adm-stats__card {
  background: var(--clr-card, #1a1f2e);
  border: 1px solid var(--clr-border, #2a3045);
  border-radius: 10px; padding: 1.2rem 1.4rem;
  display: flex; flex-direction: column; gap: .3rem;
}
.adm-stats__num   { font-size: 2rem; font-weight: 700; color: var(--clr-accent, #3b82f6); line-height: 1; }
.adm-stats__label { font-size: .8rem; color: var(--clr-muted, #8892a4); }
.adm-stats__card--today .adm-stats__num { color: #22c55e; }
.adm-stats__card--users .adm-stats__num { color: #f59e0b; }
.adm-stats__card--dir   .adm-stats__num { font-size: 1.1rem; }

/* Filters */
.adm-filters { display: flex; flex-wrap: wrap; gap: .6rem; margin-bottom: 1rem; align-items: center; }
.adm-filters__select,
.adm-filters__input {
  background: var(--clr-card, #1a1f2e);
  border: 1px solid var(--clr-border, #2a3045);
  border-radius: 7px; color: var(--clr-text, #e2e8f0);
  font-size: .85rem; padding: .45rem .8rem; outline: none;
}
.adm-filters__select:focus,
.adm-filters__input:focus { border-color: var(--clr-accent, #3b82f6); }
.adm-filters__btn {
  background: var(--clr-accent, #3b82f6); color: #fff; border: none;
  border-radius: 7px; padding: .45rem 1rem; font-size: .85rem;
  cursor: pointer; transition: opacity .15s;
}
.adm-filters__btn:hover { opacity: .85; }

/* Table */
.adm-table-wrap {
  background: var(--clr-card, #1a1f2e);
  border: 1px solid var(--clr-border, #2a3045);
  border-radius: 10px; overflow: auto;
}
.adm-table-wrap__title {
  padding: 1rem 1.2rem .75rem; font-size: .9rem; font-weight: 600;
  color: var(--clr-text, #e2e8f0);
  border-bottom: 1px solid var(--clr-border, #2a3045);
}
.adm-table { width: 100%; border-collapse: collapse; font-size: .83rem; }
.adm-table th {
  padding: .7rem 1rem; text-align: left;
  color: var(--clr-muted, #8892a4); font-weight: 500; white-space: nowrap;
  border-bottom: 1px solid var(--clr-border, #2a3045);
}
.adm-table td {
  padding: .7rem 1rem; color: var(--clr-text, #e2e8f0);
  border-bottom: 1px solid rgba(42,48,69,.5); white-space: nowrap;
}
.adm-table tr:last-child td { border-bottom: none; }
.adm-table tr:hover td { background: rgba(59,130,246,.04); }

/* Badges */
.adm-badge {
  display: inline-block; font-size: .72rem; font-weight: 500;
  border-radius: 5px; padding: 2px 8px; white-space: nowrap;
}
.adm-badge--1 { background: rgba(59,130,246,.15);  color: #3b82f6; }
.adm-badge--2 { background: rgba(245,158,11,.15);  color: #f59e0b; }
.adm-badge--3 { background: rgba(168,85,247,.15);  color: #a855f7; }
.adm-badge--4 { background: rgba(6,182,212,.15);   color: #06b6d4; }
.adm-badge--5 { background: rgba(34,197,94,.15);   color: #22c55e; }

.adm-role {
  display: inline-block; font-size: .72rem; font-weight: 600;
  border-radius: 5px; padding: 2px 8px;
  text-transform: uppercase; letter-spacing: .04em;
}
.adm-role--admin  { background: rgba(239,68,68,.15);  color: #ef4444; }
.adm-role--client { background: rgba(59,130,246,.12); color: #60a5fa; }

/* Status select */
.adm-status-sel {
  background: var(--clr-bg, #0f1117);
  border: 1px solid var(--clr-border, #2a3045);
  border-radius: 5px; color: var(--clr-text, #e2e8f0);
  font-size: .78rem; padding: 3px 6px; cursor: pointer; outline: none;
}
.adm-status-sel:focus { border-color: var(--clr-accent, #3b82f6); }

/* Analytics */
.adm-charts { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 800px) { .adm-charts { grid-template-columns: 1fr; } }
.adm-chart-card {
  background: var(--clr-card, #1a1f2e);
  border: 1px solid var(--clr-border, #2a3045);
  border-radius: 10px; padding: 1.2rem;
}
.adm-chart-card--wide { grid-column: 1 / -1; }
.adm-chart-card__title { font-size: .9rem; font-weight: 600; color: var(--clr-text, #e2e8f0); margin-bottom: 1rem; }
.adm-chart-canvas { width: 100% !important; }
.adm-empty { padding: 2rem; text-align: center; color: var(--clr-muted, #8892a4); font-size: .875rem; }
</style>

<!-- GUARD -->
<div class="adm-guard" id="adm-guard" style="display:none;">
  <div class="adm-guard__card">
    <div class="adm-guard__icon">&#128274;</div>
    <p class="adm-guard__title" id="adm-guard-title">&#1055;&#1088;&#1086;&#1074;&#1077;&#1088;&#1082;&#1072; &#1076;&#1086;&#1089;&#1090;&#1091;&#1087;&#1072;&#8230;</p>
    <p class="adm-guard__sub"   id="adm-guard-sub"></p>
  </div>
</div>

<!-- ADMIN LAYOUT -->
<div class="adm" id="adm-app" style="display:none;">

  <aside class="adm__sidebar">
    <div class="adm__sidebar-head">
      <span class="adm__sidebar-logo">CF</span>
      <span class="adm__sidebar-brand">Admin</span>
      <span class="adm__sidebar-badge">ADMIN</span>
    </div>
    <nav class="adm__nav">
      <button class="adm__nav-btn adm__nav-btn--active" data-adm-view="dashboard">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        &#1044;&#1072;&#1096;&#1073;&#1086;&#1088;&#1076;
      </button>
      <button class="adm__nav-btn" data-adm-view="applications">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        &#1047;&#1072;&#1103;&#1074;&#1082;&#1080;
      </button>
      <button class="adm__nav-btn" data-adm-view="users">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        &#1055;&#1086;&#1083;&#1100;&#1079;&#1086;&#1074;&#1072;&#1090;&#1077;&#1083;&#1080;
      </button>
      <button class="adm__nav-btn" data-adm-view="analytics">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        &#1040;&#1085;&#1072;&#1083;&#1080;&#1090;&#1080;&#1082;&#1072;
      </button>
      <a class="adm__nav-btn" href="/index.php?page=cabinet">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        &#1051;&#1080;&#1095;&#1085;&#1099;&#1081; &#1082;&#1072;&#1073;&#1080;&#1085;&#1077;&#1090;
      </a>
      <a class="adm__nav-btn" href="/index.php?page=home">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        &#1053;&#1072; &#1089;&#1072;&#1081;&#1090;
      </a>
    </nav>
  </aside>

  <div class="adm__main">
    <header class="adm__topbar">
      <h2 class="adm__topbar-title" id="adm-page-title">&#1044;&#1072;&#1096;&#1073;&#1086;&#1088;&#1076;</h2>
      <div class="adm__topbar-user">
        <span class="adm__topbar-name" id="adm-user-name"></span>
        <span class="adm__topbar-chip">Admin</span>
      </div>
    </header>

    <div class="adm__content">

      <!-- DASHBOARD -->
      <div class="adm__view adm__view--active" data-adm-panel="dashboard">
        <div class="adm-stats">
          <div class="adm-stats__card">
            <span class="adm-stats__num" id="stat-total">&#8212;</span>
            <span class="adm-stats__label">&#1042;&#1089;&#1077;&#1075;&#1086; &#1079;&#1072;&#1103;&#1074;&#1086;&#1082;</span>
          </div>
          <div class="adm-stats__card adm-stats__card--today">
            <span class="adm-stats__num" id="stat-today">&#8212;</span>
            <span class="adm-stats__label">&#1053;&#1086;&#1074;&#1099;&#1077; &#1089;&#1077;&#1075;&#1086;&#1076;&#1085;&#1103;</span>
          </div>
          <div class="adm-stats__card adm-stats__card--users">
            <span class="adm-stats__num" id="stat-users">&#8212;</span>
            <span class="adm-stats__label">&#1050;&#1083;&#1080;&#1077;&#1085;&#1090;&#1086;&#1074;</span>
          </div>
          <div class="adm-stats__card adm-stats__card--dir">
            <span class="adm-stats__num" id="stat-dir">&#8212;</span>
            <span class="adm-stats__label">&#1055;&#1086;&#1087;&#1091;&#1083;&#1103;&#1088;&#1085;&#1086;&#1077; &#1085;&#1072;&#1087;&#1088;&#1072;&#1074;&#1083;&#1077;&#1085;&#1080;&#1077;</span>
          </div>
        </div>
        <div class="adm-table-wrap">
          <div class="adm-table-wrap__title">&#1055;&#1086;&#1089;&#1083;&#1077;&#1076;&#1085;&#1080;&#1077; &#1079;&#1072;&#1103;&#1074;&#1082;&#1080;</div>
          <table class="adm-table">
            <thead>
              <tr>
                <th>&#8470;</th>
                <th>&#1050;&#1083;&#1080;&#1077;&#1085;&#1090;</th>
                <th>&#1052;&#1072;&#1088;&#1096;&#1088;&#1091;&#1090;</th>
                <th>&#1058;&#1080;&#1087;</th>
                <th>&#1044;&#1072;&#1090;&#1072;</th>
                <th>&#1057;&#1090;&#1072;&#1090;&#1091;&#1089;</th>
              </tr>
            </thead>
            <tbody id="adm-recent-body">
              <tr><td colspan="6" class="adm-empty">&#1047;&#1072;&#1075;&#1088;&#1091;&#1079;&#1082;&#1072;&#8230;</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- APPLICATIONS -->
      <div class="adm__view" data-adm-panel="applications">
        <div class="adm-filters">
          <select id="adm-filter-status" class="adm-filters__select">
            <option value="">&#1042;&#1089;&#1077; &#1089;&#1090;&#1072;&#1090;&#1091;&#1089;&#1099;</option>
            <option value="1">&#1053;&#1086;&#1074;&#1072;&#1103;</option>
            <option value="2">&#1042; &#1086;&#1073;&#1088;&#1072;&#1073;&#1086;&#1090;&#1082;&#1077;</option>
            <option value="3">&#1044;&#1086;&#1082;&#1091;&#1084;&#1077;&#1085;&#1090;&#1099; &#1087;&#1088;&#1086;&#1074;&#1077;&#1088;&#1077;&#1085;&#1099;</option>
            <option value="4">&#1042; &#1087;&#1091;&#1090;&#1080;</option>
            <option value="5">&#1047;&#1072;&#1074;&#1077;&#1088;&#1096;&#1077;&#1085;&#1072;</option>
          </select>
          <input type="date" id="adm-filter-from" class="adm-filters__input">
          <input type="date" id="adm-filter-to"   class="adm-filters__input">
          <button class="adm-filters__btn" id="adm-filter-apply">&#1055;&#1088;&#1080;&#1084;&#1077;&#1085;&#1080;&#1090;&#1100;</button>
        </div>
        <div class="adm-table-wrap">
          <table class="adm-table">
            <thead>
              <tr>
                <th>&#8470;</th>
                <th>&#1050;&#1083;&#1080;&#1077;&#1085;&#1090;</th>
                <th>&#1050;&#1086;&#1084;&#1087;&#1072;&#1085;&#1080;&#1103;</th>
                <th>&#1052;&#1072;&#1088;&#1096;&#1088;&#1091;&#1090;</th>
                <th>&#1058;&#1080;&#1087;</th>
                <th>&#1042;&#1077;&#1089;, &#1082;&#1075;</th>
                <th>&#1044;&#1072;&#1090;&#1072;</th>
                <th>&#1057;&#1090;&#1072;&#1090;&#1091;&#1089;</th>
              </tr>
            </thead>
            <tbody id="adm-apps-body">
              <tr><td colspan="8" class="adm-empty">&#1047;&#1072;&#1075;&#1088;&#1091;&#1079;&#1082;&#1072;&#8230;</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- USERS -->
      <div class="adm__view" data-adm-panel="users">
        <div class="adm-table-wrap">
          <table class="adm-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>&#1060;&#1048;&#1054;</th>
                <th>Email</th>
                <th>&#1050;&#1086;&#1084;&#1087;&#1072;&#1085;&#1080;&#1103;</th>
                <th>&#1056;&#1086;&#1083;&#1100;</th>
                <th>Email &#1087;&#1086;&#1076;&#1090;&#1074;&#1077;&#1088;&#1078;&#1076;&#1105;&#1085;</th>
                <th>&#1044;&#1072;&#1090;&#1072; &#1088;&#1077;&#1075;&#1080;&#1089;&#1090;&#1088;&#1072;&#1094;&#1080;&#1080;</th>
              </tr>
            </thead>
            <tbody id="adm-users-body">
              <tr><td colspan="7" class="adm-empty">&#1047;&#1072;&#1075;&#1088;&#1091;&#1079;&#1082;&#1072;&#8230;</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ANALYTICS -->
      <div class="adm__view" data-adm-panel="analytics">
        <div class="adm-charts">
          <div class="adm-chart-card adm-chart-card--wide">
            <div class="adm-chart-card__title">&#1047;&#1072;&#1103;&#1074;&#1082;&#1080; &#1087;&#1086; &#1084;&#1077;&#1089;&#1103;&#1094;&#1072;&#1084;</div>
            <canvas id="chart-month" class="adm-chart-canvas" height="80"></canvas>
          </div>
          <div class="adm-chart-card">
            <div class="adm-chart-card__title">&#1055;&#1086; &#1090;&#1080;&#1087;&#1091; &#1087;&#1077;&#1088;&#1077;&#1074;&#1086;&#1079;&#1082;&#1080;</div>
            <canvas id="chart-transport" class="adm-chart-canvas" height="180"></canvas>
          </div>
          <div class="adm-chart-card">
            <div class="adm-chart-card__title">&#1055;&#1086; &#1089;&#1090;&#1072;&#1090;&#1091;&#1089;&#1091;</div>
            <canvas id="chart-status" class="adm-chart-canvas" height="180"></canvas>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
  'use strict';

  var API    = '/api/admin.php';
  var CABAPI = '/api/cabinet.php';
  var csrfToken  = '';
  var chartsDrawn = false;
  var chartMonth, chartTransport, chartStatus;

  var STATUS_LABELS = {1:'Новая',2:'В обработке',3:'Доки проверены',4:'В пути',5:'Завершена'};
  var TRANSPORT_LABELS = {sea:'Море',air:'Авиа',road:'Авто',rail:'Ж/д',multi:'Мульти'};

  function esc(s) {
    var d = document.createElement('div');
    d.textContent = (s == null ? '' : String(s));
    return d.innerHTML;
  }
  function statusBadge(s) {
    return '<span class="adm-badge adm-badge--' + s + '">' + esc(STATUS_LABELS[s] || String(s)) + '</span>';
  }
  function fmtDate(dt) { return dt ? String(dt).slice(0, 10) : '—'; }

  function apiFetch(url, opts) {
    return fetch(url, Object.assign({credentials: 'same-origin'}, opts || {})).then(function(r){ return r.json(); });
  }

  /* ── Init ── */
  function init() {
    apiFetch(CABAPI + '?action=csrf').then(function(c) {
      if (c.token) csrfToken = c.token;
      return apiFetch(CABAPI + '?action=check');
    }).then(function(auth) {
      if (!auth.authenticated) {
        showGuard('Необходима авторизация', 'Пожалуйста, войдите в личный кабинет');
        return;
      }
      if (!auth.user || auth.user.role !== 'admin') {
        showGuard('Доступ запрещён', 'Эта страница доступна только администраторам');
        return;
      }
      document.getElementById('adm-user-name').textContent = auth.user.full_name || auth.user.email;
      document.getElementById('adm-guard').style.display = 'none';
      document.getElementById('adm-app').style.display   = 'flex';
      loadDashboard();
    }).catch(function() {
      showGuard('Ошибка подключения', 'Не удалось проверить авторизацию');
    });
  }

  function showGuard(title, sub) {
    document.getElementById('adm-guard').style.display = 'flex';
    document.getElementById('adm-guard-title').textContent = title;
    document.getElementById('adm-guard-sub').textContent   = sub;
  }

  /* ── Navigation ── */
  var NAV_TITLES = {dashboard:'Дашборд',applications:'Заявки',users:'Пользователи',analytics:'Аналитика'};

  document.querySelectorAll('.adm__nav-btn[data-adm-view]').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var view = btn.dataset.admView;
      document.querySelectorAll('.adm__nav-btn').forEach(function(b){ b.classList.remove('adm__nav-btn--active'); });
      btn.classList.add('adm__nav-btn--active');
      document.querySelectorAll('.adm__view').forEach(function(p){ p.classList.remove('adm__view--active'); });
      document.querySelector('[data-adm-panel="' + view + '"]').classList.add('adm__view--active');
      document.getElementById('adm-page-title').textContent = NAV_TITLES[view] || view;
      if (view === 'dashboard')    loadDashboard();
      if (view === 'applications') loadApplications();
      if (view === 'users')        loadUsers();
      if (view === 'analytics')    loadAnalytics();
    });
  });

  /* ── Dashboard ── */
  function loadDashboard() {
    apiFetch(API + '?action=stats').then(function(data) {
      if (data.status !== 'ok') return;
      var s = data.stats;
      document.getElementById('stat-total').textContent = s.total_applications;
      document.getElementById('stat-today').textContent = s.new_today;
      document.getElementById('stat-users').textContent = s.total_users;
      document.getElementById('stat-dir').textContent   = s.popular_direction;
    });

    apiFetch(API + '?action=applications').then(function(data) {
      var tbody = document.getElementById('adm-recent-body');
      if (data.status !== 'ok') {
        tbody.innerHTML = '<tr><td colspan="6" class="adm-empty">Ошибка загрузки</td></tr>';
        return;
      }
      var recent = data.applications.slice(0, 10);
      if (!recent.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="adm-empty">Нет заявок</td></tr>';
        return;
      }
      tbody.innerHTML = recent.map(function(a) {
        return '<tr>' +
          '<td>#' + a.id + '</td>' +
          '<td>' + esc(a.full_name || a.user_email || '—') + '</td>' +
          '<td>' + esc(a.route) + '</td>' +
          '<td>' + esc(TRANSPORT_LABELS[a.transport_type] || a.transport_type) + '</td>' +
          '<td>' + fmtDate(a.created_at) + '</td>' +
          '<td>' + statusBadge(a.status) + '</td>' +
          '</tr>';
      }).join('');
    });
  }

  /* ── Applications ── */
  function loadApplications() {
    var status   = document.getElementById('adm-filter-status').value;
    var dateFrom = document.getElementById('adm-filter-from').value;
    var dateTo   = document.getElementById('adm-filter-to').value;
    var qs = '?action=applications';
    if (status)   qs += '&status='    + encodeURIComponent(status);
    if (dateFrom) qs += '&date_from=' + encodeURIComponent(dateFrom);
    if (dateTo)   qs += '&date_to='   + encodeURIComponent(dateTo);

    var tbody = document.getElementById('adm-apps-body');
    tbody.innerHTML = '<tr><td colspan="8" class="adm-empty">Загрузка…</td></tr>';

    apiFetch(API + qs).then(function(data) {
      if (data.status !== 'ok') {
        tbody.innerHTML = '<tr><td colspan="8" class="adm-empty">Ошибка загрузки</td></tr>';
        return;
      }
      var apps = data.applications;
      if (!apps.length) {
        tbody.innerHTML = '<tr><td colspan="8" class="adm-empty">Заявок не найдено</td></tr>';
        return;
      }
      tbody.innerHTML = apps.map(function(a) {
        var sel = '<select class="adm-status-sel" data-app-id="' + a.id + '">' +
          [1,2,3,4,5].map(function(v){
            return '<option value="' + v + '"' + (a.status === v ? ' selected' : '') + '>' + esc(STATUS_LABELS[v]) + '</option>';
          }).join('') +
          '</select>';
        return '<tr>' +
          '<td>#' + a.id + '</td>' +
          '<td>' + esc(a.full_name || '—') + '</td>' +
          '<td>' + esc(a.company  || '—') + '</td>' +
          '<td>' + esc(a.route) + '</td>' +
          '<td>' + esc(TRANSPORT_LABELS[a.transport_type] || a.transport_type) + '</td>' +
          '<td>' + (a.weight_kg ? Number(a.weight_kg).toLocaleString('ru') : '—') + '</td>' +
          '<td>' + fmtDate(a.created_at) + '</td>' +
          '<td>' + sel + '</td>' +
          '</tr>';
      }).join('');

      tbody.querySelectorAll('.adm-status-sel').forEach(function(sel) {
        sel.addEventListener('change', function() {
          updateStatus(parseInt(sel.dataset.appId, 10), parseInt(sel.value, 10), sel);
        });
      });
    });
  }

  function updateStatus(appId, newStatus, selEl) {
    selEl.disabled = true;
    apiFetch(API + '?action=update_status', {
      method: 'POST',
      headers: {'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken},
      body: JSON.stringify({id: appId, status: newStatus}),
    }).then(function(data) {
      if (data.status !== 'ok') alert('Ошибка: ' + (data.message || ''));
    }).catch(function() {
      alert('Ошибка сети');
    }).finally(function() {
      selEl.disabled = false;
    });
  }

  document.getElementById('adm-filter-apply').addEventListener('click', loadApplications);

  /* ── Users ── */
  function loadUsers() {
    var tbody = document.getElementById('adm-users-body');
    tbody.innerHTML = '<tr><td colspan="7" class="adm-empty">Загрузка…</td></tr>';
    apiFetch(API + '?action=users').then(function(data) {
      if (data.status !== 'ok') {
        tbody.innerHTML = '<tr><td colspan="7" class="adm-empty">Ошибка загрузки</td></tr>';
        return;
      }
      var users = data.users;
      if (!users.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="adm-empty">Пользователей нет</td></tr>';
        return;
      }
      tbody.innerHTML = users.map(function(u) {
        var verified = u.email_verified
          ? '<span style="color:#22c55e">&#10003; Да</span>'
          : '<span style="color:#f59e0b">&#10007; Нет</span>';
        return '<tr>' +
          '<td>' + u.id + '</td>' +
          '<td>' + esc(u.full_name || '—') + '</td>' +
          '<td>' + esc(u.email) + '</td>' +
          '<td>' + esc(u.company || '—') + '</td>' +
          '<td><span class="adm-role adm-role--' + esc(u.role) + '">' + esc(u.role) + '</span></td>' +
          '<td>' + verified + '</td>' +
          '<td>' + fmtDate(u.created_at) + '</td>' +
          '</tr>';
      }).join('');
    });
  }

  /* ── Analytics ── */
  function loadAnalytics() {
    if (chartsDrawn) return;
    chartsDrawn = true;

    apiFetch(API + '?action=analytics').then(function(data) {
      if (data.status !== 'ok') return;

      var GRID = 'rgba(42,48,69,.6)';
      var TEXT = '#8892a4';
      Chart.defaults.color       = TEXT;
      Chart.defaults.borderColor = GRID;

      /* — by month — */
      var mLabels = data.by_month.map(function(r){ return r.month; });
      var mData   = data.by_month.map(function(r){ return r.count; });
      if (chartMonth) chartMonth.destroy();
      chartMonth = new Chart(document.getElementById('chart-month'), {
        type: 'bar',
        data: { labels: mLabels, datasets: [{ label: 'Заявок', data: mData,
          backgroundColor: 'rgba(59,130,246,.65)', borderColor: '#3b82f6',
          borderWidth: 1, borderRadius: 4 }] },
        options: { responsive: true,
          plugins: { legend: { display: false } },
          scales: {
            x: { grid: { color: GRID }, ticks: { color: TEXT } },
            y: { grid: { color: GRID }, ticks: { color: TEXT, stepSize: 5 }, beginAtZero: true }
          }
        }
      });

      /* — by transport — */
      var tColors = ['#3b82f6','#06b6d4','#f59e0b','#a855f7','#22c55e'];
      var tLabels = data.by_transport.map(function(r){ return TRANSPORT_LABELS[r.type] || r.type; });
      var tData   = data.by_transport.map(function(r){ return r.count; });
      if (chartTransport) chartTransport.destroy();
      chartTransport = new Chart(document.getElementById('chart-transport'), {
        type: 'doughnut',
        data: { labels: tLabels, datasets: [{ data: tData,
          backgroundColor: tColors, borderColor: '#1a1f2e', borderWidth: 2 }] },
        options: { responsive: true,
          plugins: { legend: { position: 'bottom', labels: { color: TEXT, padding: 12, boxWidth: 12 } } }
        }
      });

      /* — by status — */
      var sColors = ['#3b82f6','#f59e0b','#a855f7','#06b6d4','#22c55e'];
      var sLabels = data.by_status.map(function(r){ return STATUS_LABELS[r.status] || String(r.status); });
      var sData   = data.by_status.map(function(r){ return r.count; });
      if (chartStatus) chartStatus.destroy();
      chartStatus = new Chart(document.getElementById('chart-status'), {
        type: 'bar',
        data: { labels: sLabels, datasets: [{ label: 'Кол-во', data: sData,
          backgroundColor: sColors.map(function(c){ return c + 'aa'; }),
          borderColor: sColors, borderWidth: 1, borderRadius: 4 }] },
        options: { responsive: true, indexAxis: 'y',
          plugins: { legend: { display: false } },
          scales: {
            x: { grid: { color: GRID }, ticks: { color: TEXT }, beginAtZero: true },
            y: { grid: { color: GRID }, ticks: { color: TEXT } }
          }
        }
      });
    });
  }

  /* ── Start ── */
  document.getElementById('adm-guard').style.display = 'flex';
  init();
})();
</script>
