/* ─── Yandex SmartCaptcha: рендер виджетов ─── */
window.cfCaptcha = window.cfCaptcha || { key: '', widgets: new Map() };

// Вызывается параметром onload в URL скрипта captcha.js.
window.cfRenderCaptchas = function cfRenderCaptchas() {
  const meta = document.querySelector('meta[name="captcha-sitekey"]');
  const key = meta ? meta.getAttribute('content') : '';
  if (!key || !window.smartCaptcha) return;
  const testMeta = document.querySelector('meta[name="captcha-test"]');
  const test = !!(testMeta && testMeta.getAttribute('content') === '1');
  window.cfCaptcha.key = key;
  document.querySelectorAll('[data-captcha]').forEach((el) => {
    if (window.cfCaptcha.widgets.has(el)) return;
    const id = window.smartCaptcha.render(el, { sitekey: key, hl: 'ru', test });
    window.cfCaptcha.widgets.set(el, id);
  });
};

document.addEventListener('DOMContentLoaded', () => {

  /* ─── Утилиты безопасности ─── */
  function escHtml(str) {
    if (!str) return '';
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
  }

  let csrfToken = (() => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  })();

  function getCsrfToken() {
    return csrfToken;
  }

  // Запрашивает у сервера токен, привязанный к ТЕКУЩЕЙ живой сессии,
  // и синхронизирует его в памяти и в мета-теге.
  async function refreshCsrfToken() {
    try {
      const res = await fetch('/api/cabinet.php?action=csrf', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
      });
      const data = await res.json();
      if (data && data.token) {
        csrfToken = data.token;
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) meta.setAttribute('content', csrfToken);
      }
    } catch { /* оставляем прежний токен */ }
    return csrfToken;
  }

  function isCsrfError(data) {
    return data && data.status === 'error'
      && typeof data.message === 'string'
      && data.message.indexOf('CSRF') !== -1;
  }

  /* ─── SmartCaptcha: получение токена и сброс ─── */
  function captchaRequired() {
    return !!(window.cfCaptcha && window.cfCaptcha.key);
  }

  function getCaptchaToken(form) {
    if (!captchaRequired() || !window.smartCaptcha) return '';
    const el = form.querySelector('[data-captcha]');
    if (!el) return '';
    const id = window.cfCaptcha.widgets.get(el);
    if (id === undefined) return '';
    return window.smartCaptcha.getResponse(id) || '';
  }

  function resetCaptcha(form) {
    if (!captchaRequired() || !window.smartCaptcha) return;
    const el = form.querySelector('[data-captcha]');
    if (!el) return;
    const id = window.cfCaptcha.widgets.get(el);
    if (id !== undefined) window.smartCaptcha.reset(id);
  }

  // POST JSON с CSRF-токеном. При ошибке CSRF обновляет токен из живой
  // сессии и повторяет запрос один раз.
  async function postJsonWithCsrf(url, payload) {
    const send = async () => {
      const res = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ ...payload, csrf_token: csrfToken })
      });
      let data = null;
      try { data = await res.json(); } catch { data = null; }
      return { res, data };
    };

    let { res, data } = await send();
    if ((res.status === 403 || isCsrfError(data))) {
      await refreshCsrfToken();
      ({ res, data } = await send());
    }
    return data;
  }

  /* ─── Burger ─── */
  const burger = document.getElementById('burger');
  const nav = document.getElementById('main-nav');
  if (burger && nav) {
    burger.addEventListener('click', () => nav.classList.toggle('main-nav--open'));
  }

  /* ─── Tabs (services page) ─── */
  document.querySelectorAll('.tabs__btn').forEach(button => {
    button.addEventListener('click', () => {
      const tab = button.dataset.tab;
      document.querySelectorAll('.tabs__btn').forEach(b => b.classList.remove('tabs__btn--active'));
      button.classList.add('tabs__btn--active');
      document.querySelectorAll('.tabs__content').forEach(panel => {
        panel.classList.toggle('tabs__content--active', panel.dataset.tabPanel === tab);
      });
    });
  });

  /* ─── Solutions tabs (home page) ─── */
  document.querySelectorAll('.solutions__btn').forEach(button => {
    button.addEventListener('click', () => {
      const sol = button.dataset.sol;
      document.querySelectorAll('.solutions__btn').forEach(b => b.classList.remove('solutions__btn--active'));
      button.classList.add('solutions__btn--active');
      document.querySelectorAll('.solutions__panel').forEach(panel => {
        panel.classList.toggle('solutions__panel--active', panel.dataset.solPanel === sol);
      });
    });
  });

  /* ─── AJAX forms ─── */
  const handleAjaxForm = (formId, statusId) => {
    const form = document.getElementById(formId);
    const statusEl = document.getElementById(statusId);
    if (!form || !statusEl) return;

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      statusEl.textContent = 'Отправка...';
      statusEl.className = 'form-status';

      const formData = new FormData(form);
      const payload = {};
      formData.forEach((v, k) => { payload[k] = v; });

      if (captchaRequired()) {
        const token = getCaptchaToken(form);
        if (!token) {
          statusEl.textContent = 'Подтвердите, что вы не робот.';
          statusEl.classList.add('form-status--error');
          return;
        }
        payload.smart_token = token;
      }

      try {
        const data = await postJsonWithCsrf('/api/lead.php', payload);
        if (data && data.status === 'ok') {
          statusEl.textContent = data.message || 'Заявка отправлена.';
          statusEl.classList.add('form-status--ok');
          form.reset();
        } else {
          statusEl.textContent = (data && data.message) || 'Не удалось отправить заявку.';
          statusEl.classList.add('form-status--error');
        }
      } catch {
        statusEl.textContent = 'Ошибка соединения с сервером.';
        statusEl.classList.add('form-status--error');
      } finally {
        resetCaptcha(form);
      }
    });
  };

  handleAjaxForm('quick-request-form', 'quick-request-status');
  handleAjaxForm('request-form', 'request-status');

  /* ─── Scroll animations (IntersectionObserver) ─── */
  const animated = document.querySelectorAll('[data-animate]');
  if (animated.length) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.05, rootMargin: '0px 0px 50px 0px' }
    );
    animated.forEach((el) => observer.observe(el));
    // Trigger immediately for elements already in viewport
    requestAnimationFrame(() => {
      animated.forEach((el) => {
        const rect = el.getBoundingClientRect();
        if (rect.top < window.innerHeight && rect.bottom > 0) {
          el.classList.add('is-visible');
        }
      });
    });
  }

  /* ─── Counter animation (geography) ─── */
  const countersRoot = document.getElementById('geo-counters');
  if (countersRoot) {
    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const numbers = countersRoot.querySelectorAll('.geography__number');

    const formatNumber = (n, sep) => {
      if (!sep) return String(n);
      return n.toLocaleString('ru-RU');
    };

    const animateCounter = (el) => {
      const target = parseInt(el.dataset.count, 10);
      const sep = el.dataset.separator;
      if (prefersReduced) { el.textContent = formatNumber(target, sep); return; }
      const duration = 1800;
      const start = performance.now();
      const tick = (now) => {
        const elapsed = now - start;
        const progress = Math.min(elapsed / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        const current = Math.round(eased * target);
        el.textContent = formatNumber(current, sep);
        if (progress < 1) requestAnimationFrame(tick);
      };
      requestAnimationFrame(tick);
    };

    const counterObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          numbers.forEach(animateCounter);
          counterObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.25 });

    counterObserver.observe(countersRoot);
  }

  /* ─── Hero parallax ─── */
  const hero = document.querySelector('.hero');
  if (hero) {
    window.addEventListener('scroll', () => {
      const y = window.scrollY;
      hero.style.backgroundPositionY = `${y * -0.18}px`;
    }, { passive: true });
  }

  /* ─── About: expertise tabs ─── */
  document.querySelectorAll('.about-expertise__btn').forEach(button => {
    button.addEventListener('click', () => {
      const key = button.dataset.exp;
      document.querySelectorAll('.about-expertise__btn').forEach(b =>
        b.classList.remove('about-expertise__btn--active')
      );
      button.classList.add('about-expertise__btn--active');
      document.querySelectorAll('.about-expertise__panel').forEach(panel => {
        panel.classList.toggle(
          'about-expertise__panel--active',
          panel.dataset.expPanel === key
        );
      });
    });
  });

  /* ─── About: scale counter animation ─── */
  const aboutCountersRoot = document.getElementById('about-counters');
  if (aboutCountersRoot) {
    const prefersReducedAbout = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const nums = aboutCountersRoot.querySelectorAll('.about-scale__number');

    const fmtNum = (n, sep) => sep ? n.toLocaleString('ru-RU') : String(n);

    const animateNum = (el) => {
      const target = parseInt(el.dataset.countTo, 10);
      const sep = el.hasAttribute('data-separator');
      if (prefersReducedAbout) { el.textContent = fmtNum(target, sep); return; }
      const duration = 2200;
      const start = performance.now();
      const tick = (now) => {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        el.textContent = fmtNum(Math.round(eased * target), sep);
        if (progress < 1) requestAnimationFrame(tick);
      };
      requestAnimationFrame(tick);
    };

    const aboutCounterObs = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          nums.forEach(animateNum);
          aboutCounterObs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.2 });

    aboutCounterObs.observe(aboutCountersRoot);
  }

  /* ─── About: hero parallax ─── */
  const aboutHero = document.querySelector('.about-hero');
  if (aboutHero) {
    window.addEventListener('scroll', () => {
      const y = window.scrollY;
      aboutHero.style.backgroundPositionY = `${y * -0.15}px`;
    }, { passive: true });
  }

  /* ─── Services: complex solutions tabs ─── */
  document.querySelectorAll('.svc-complex__btn').forEach(button => {
    button.addEventListener('click', () => {
      const key = button.dataset.svc;
      document.querySelectorAll('.svc-complex__btn').forEach(b =>
        b.classList.remove('svc-complex__btn--active')
      );
      button.classList.add('svc-complex__btn--active');
      document.querySelectorAll('.svc-complex__panel').forEach(panel => {
        panel.classList.toggle(
          'svc-complex__panel--active',
          panel.dataset.svcPanel === key
        );
      });
    });
  });

  /* ─── Services: hero parallax ─── */
  const svcHero = document.querySelector('.svc-hero');
  if (svcHero) {
    window.addEventListener('scroll', () => {
      const y = window.scrollY;
      svcHero.style.backgroundPositionY = `${y * -0.15}px`;
    }, { passive: true });
  }

  /* ─── Contacts: form handler ─── */
  const contactForm = document.getElementById('contact-form');
  const contactStatus = document.getElementById('contact-form-status');
  if (contactForm && contactStatus) {
    contactForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      contactStatus.textContent = 'Отправка...';
      contactStatus.className = 'form-status';

      const fd = new FormData(contactForm);
      const payload = {};
      fd.forEach((v, k) => { payload[k] = v; });

      if (captchaRequired()) {
        const token = getCaptchaToken(contactForm);
        if (!token) {
          contactStatus.textContent = 'Подтвердите, что вы не робот.';
          contactStatus.classList.add('form-status--error');
          return;
        }
        payload.smart_token = token;
      }

      try {
        const data = await postJsonWithCsrf('/api/lead.php', payload);
        if (data && data.status === 'ok') {
          contactStatus.textContent = data.message || 'Заявка отправлена. Мы свяжемся с вами.';
          contactStatus.classList.add('form-status--ok');
          contactForm.reset();
        } else {
          contactStatus.textContent = (data && data.message) || 'Не удалось отправить заявку.';
          contactStatus.classList.add('form-status--error');
        }
      } catch {
        contactStatus.textContent = 'Ошибка соединения с сервером.';
        contactStatus.classList.add('form-status--error');
      } finally {
        resetCaptcha(contactForm);
      }
    });
  }

  /* ─── Contacts: hero parallax ─── */
  const cntHero = document.querySelector('.cnt-hero');
  if (cntHero) {
    window.addEventListener('scroll', () => {
      const y = window.scrollY;
      cntHero.style.backgroundPositionY = `${y * -0.15}px`;
    }, { passive: true });
  }

  /* ═══════════════════════════════════════════
     CABINET (личный кабинет)
     ═══════════════════════════════════════════ */

  const cabAuth = document.getElementById('cab-auth');
  const cabApp = document.getElementById('cab-app');
  if (!cabAuth || !cabApp) return;

  const API = '/api/cabinet.php';

  const STATUS_LABELS = {
    1: 'Новая',
    2: 'В обработке',
    3: 'Документы проверены',
    4: 'В пути',
    5: 'Завершена'
  };

  const TYPE_LABELS = {
    air: 'Авиа',
    sea: 'Море',
    road: 'Авто',
    rail: 'Ж/д',
    multi: 'Мульти'
  };

  const PAGE_TITLES = {
    dashboard: 'Дашборд',
    applications: 'Мои заявки',
    'new-app': 'Создать заявку',
    documents: 'Документы',
    profile: 'Профиль'
  };

  async function cabApi(action, body) {
    if (body) {
      return postJsonWithCsrf(API, { ...body, action });
    }
    const res = await fetch(`${API}?action=${action}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    });
    return res.json();
  }

  function formatDate(dt) {
    if (!dt) return '—';
    const d = new Date(dt.replace(' ', 'T'));
    return d.toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
  }

  function formatSize(bytes) {
    if (!bytes) return '—';
    if (bytes < 1024) return bytes + ' Б';
    return (bytes / 1024).toFixed(0) + ' КБ';
  }

  function statusBadge(s) {
    const safeStatus = Number(s) || 0;
    return `<span class="cab-badge cab-badge--${safeStatus}">${escHtml(STATUS_LABELS[s] || '?')}</span>`;
  }

  let currentUser = null;

  function showApp(user) {
    currentUser = user;
    cabAuth.style.display = 'none';
    cabApp.style.display = '';
    document.getElementById('cab-user-name').textContent = user.full_name || user.email;
    document.getElementById('cab-user-company').textContent = user.company || '';

    // Показать ссылку на админ-панель только администратору
    const isAdmin = user.role === 'admin';
    const adminNav = document.getElementById('admin-nav');
    const adminLink = document.getElementById('nav-admin-panel');
    if (adminNav) adminNav.style.display = isAdmin ? '' : 'none';
    if (adminLink) adminLink.style.display = isAdmin ? '' : 'none';

    loadDashboard();
  }

  function showAuth() {
    cabAuth.style.display = '';
    cabApp.style.display = 'none';
    currentUser = null;
  }

  // Check session on load
  cabApi('check').then(d => {
    if (d.authenticated && d.user) showApp(d.user);
  }).catch(() => {});

  // Login
  document.getElementById('cab-login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const status = document.getElementById('auth-status');
    status.textContent = '';
    status.className = 'cab-auth__status';

    const email = document.getElementById('auth-email').value.trim();
    const password = document.getElementById('auth-pass').value;

    const loginForm = e.target;
    const payload = { email, password };
    if (captchaRequired()) {
      const token = getCaptchaToken(loginForm);
      if (!token) {
        status.textContent = 'Подтвердите, что вы не робот';
        status.classList.add('form-status--error');
        return;
      }
      payload.smart_token = token;
    }

    try {
      const d = await cabApi('login', payload);
      if (d.status === 'ok' && d.user) {
        showApp(d.user);
      } else {
        status.textContent = d.message || 'Ошибка входа';
        status.classList.add('form-status--error');
      }
    } catch {
      status.textContent = 'Ошибка соединения';
      status.classList.add('form-status--error');
    } finally {
      resetCaptcha(loginForm);
    }
  });

  // Logout
  document.getElementById('cab-logout').addEventListener('click', async () => {
    await cabApi('logout', {});
    showAuth();
  });

  // View routing
  document.querySelectorAll('.cab__nav-btn[data-cab-view]').forEach(btn => {
    btn.addEventListener('click', () => {
      const view = btn.dataset.cabView;
      document.querySelectorAll('.cab__nav-btn[data-cab-view]').forEach(b =>
        b.classList.remove('cab__nav-btn--active')
      );
      btn.classList.add('cab__nav-btn--active');
      document.querySelectorAll('.cab__view').forEach(p =>
        p.classList.remove('cab__view--active')
      );
      const panel = document.querySelector(`[data-cab-panel="${view}"]`);
      if (panel) panel.classList.add('cab__view--active');
      document.getElementById('cab-page-title').textContent = PAGE_TITLES[view] || '';

      if (view === 'dashboard') loadDashboard();
      if (view === 'applications') loadApplications();
      if (view === 'documents') loadDocuments();
      if (view === 'profile') loadProfile();
    });
  });

  // Dashboard
  async function loadDashboard() {
    try {
      const d = await cabApi('dashboard');
      if (d.stats) {
        document.getElementById('stat-new').textContent = d.stats.new;
        document.getElementById('stat-proc').textContent = d.stats.processing;
        document.getElementById('stat-transit').textContent = d.stats.transit;
        document.getElementById('stat-done').textContent = d.stats.done;
      }
      const tbody = document.getElementById('dash-recent-body');
      tbody.innerHTML = (d.recent || []).map(r => `
        <tr>
          <td>#${escHtml(String(r.id))}</td>
          <td>${escHtml(r.route)}</td>
          <td><span class="cab-type">${escHtml(TYPE_LABELS[r.transport_type] || r.transport_type)}</span></td>
          <td>${escHtml(formatDate(r.created_at))}</td>
          <td>${statusBadge(r.status)}</td>
        </tr>
      `).join('');
    } catch {}
  }

  // Applications
  async function loadApplications() {
    const status = document.getElementById('filter-status').value;
    const type = document.getElementById('filter-type').value;
    let url = `${API}?action=applications`;
    if (status) url += `&status=${status}`;
    if (type) url += `&type=${type}`;
    try {
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const d = await res.json();
      const tbody = document.getElementById('apps-table-body');
      tbody.innerHTML = (d.applications || []).map(a => `
        <tr>
          <td>#${escHtml(String(a.id))}</td>
          <td>${escHtml(a.route || (a.city_from + ' → ' + a.city_to))}</td>
          <td>${a.weight_kg ? escHtml(a.weight_kg.toLocaleString('ru-RU')) : '—'}</td>
          <td>${a.volume_cbm != null ? escHtml(String(a.volume_cbm)) : '—'}</td>
          <td><span class="cab-type">${escHtml(TYPE_LABELS[a.transport_type] || a.transport_type)}</span></td>
          <td>${statusBadge(a.status)}</td>
          <td>${escHtml(formatDate(a.created_at))}</td>
        </tr>
      `).join('');
    } catch {}
  }

  document.getElementById('filter-apply').addEventListener('click', loadApplications);

  // New application
  document.getElementById('new-app-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const status = document.getElementById('new-app-status');
    status.textContent = 'Отправка...';
    status.className = 'form-status';

    const fd = new FormData(e.target);
    const payload = {};
    fd.forEach((v, k) => { payload[k] = v; });

    try {
      const d = await cabApi('create_application', payload);
      if (d.status === 'ok') {
        status.textContent = `${d.message} (№${d.id})`;
        status.classList.add('form-status--ok');
        e.target.reset();
      } else {
        status.textContent = d.message || 'Ошибка';
        status.classList.add('form-status--error');
      }
    } catch {
      status.textContent = 'Ошибка соединения';
      status.classList.add('form-status--error');
    }
  });

  // Documents
  async function loadDocuments() {
    try {
      const d = await cabApi('documents');
      const tbody = document.getElementById('docs-table-body');
      tbody.innerHTML = (d.documents || []).map(doc => `
        <tr>
          <td>#${escHtml(String(doc.application_id))}</td>
          <td>${escHtml(doc.file_name)}</td>
          <td>${escHtml(formatSize(doc.file_size))}</td>
          <td>${escHtml(formatDate(doc.created_at))}</td>
          <td><a class="cab-dl" href="/api/cabinet.php?action=download&id=${doc.id}&file_name=${encodeURIComponent(doc.file_name)}" target="_blank">Скачать</a></td>
        </tr>
      `).join('');
    } catch {}
  }

  // Profile
  function loadProfile() {
    if (!currentUser) return;
    document.getElementById('prof-name').value = currentUser.full_name || '';
    document.getElementById('prof-company').value = currentUser.company || '';
    document.getElementById('prof-email').value = currentUser.email || '';
    document.getElementById('prof-phone').value = currentUser.phone || '';
  }

  document.getElementById('profile-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const status = document.getElementById('profile-status');
    status.textContent = 'Сохранение...';
    status.className = 'form-status';

    const fd = new FormData(e.target);
    const payload = {};
    fd.forEach((v, k) => { payload[k] = v; });

    try {
      const d = await cabApi('update_profile', payload);
      if (d.status === 'ok') {
        status.textContent = d.message;
        status.classList.add('form-status--ok');
        if (currentUser) {
          currentUser.full_name = payload.full_name;
          currentUser.company = payload.company;
          currentUser.phone = payload.phone;
          document.getElementById('cab-user-name').textContent = payload.full_name || currentUser.email;
          document.getElementById('cab-user-company').textContent = payload.company || '';
        }
      } else {
        status.textContent = d.message || 'Ошибка';
        status.classList.add('form-status--error');
      }
    } catch {
      status.textContent = 'Ошибка соединения';
      status.classList.add('form-status--error');
    }
  });

  // ─── Регистрация: переключение форм ───
  const showRegBtn = document.getElementById('show-register');
  const showLoginBtn = document.getElementById('show-login');
  const loginForm = document.getElementById('cab-login-form');
  const regForm = document.getElementById('cab-register-form');

  if (showRegBtn && showLoginBtn && loginForm && regForm) {
    showRegBtn.addEventListener('click', () => {
      loginForm.style.display = 'none';
      showRegBtn.style.display = 'none';
      document.querySelector('.cab-auth__divider').style.display = 'none';
      regForm.style.display = '';
    });

    showLoginBtn.addEventListener('click', () => {
      regForm.style.display = 'none';
      loginForm.style.display = '';
      showRegBtn.style.display = '';
      document.querySelector('.cab-auth__divider').style.display = '';
    });

    regForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const status = document.getElementById('reg-status');
      status.textContent = '';
      status.className = 'cab-auth__status';

      const pass = document.getElementById('reg-pass').value;
      const pass2 = document.getElementById('reg-pass2').value;

      if (pass !== pass2) {
        status.textContent = 'Пароли не совпадают';
        status.classList.add('form-status--error');
        return;
      }
      if (pass.length < 6) {
        status.textContent = 'Пароль должен быть не менее 6 символов';
        status.classList.add('form-status--error');
        return;
      }

      const fd = new FormData(regForm);
      const payload = {};
      fd.forEach((v, k) => { if (k !== 'password_confirm' && k !== 'smart-token') payload[k] = v; });

      if (captchaRequired()) {
        const token = getCaptchaToken(regForm);
        if (!token) {
          status.textContent = 'Подтвердите, что вы не робот';
          status.classList.add('form-status--error');
          return;
        }
        payload.smart_token = token;
      }

      status.textContent = 'Регистрация...';

      try {
        const d = await cabApi('register', payload);
        if (d.status === 'ok') {
          status.classList.add('form-status--ok');
          status.textContent = d.message || 'Регистрация успешна! Проверьте email для подтверждения.';
          regForm.reset();
        } else {
          status.textContent = d.message || 'Ошибка регистрации';
          status.classList.add('form-status--error');
        }
      } catch {
        status.textContent = 'Ошибка соединения';
        status.classList.add('form-status--error');
      } finally {
        resetCaptcha(regForm);
      }
    });
  }
});
