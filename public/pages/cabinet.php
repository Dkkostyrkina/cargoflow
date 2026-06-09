<!-- ═══ ЭКРАН АВТОРИЗАЦИИ ═══ -->
<section class="cab-auth" id="cab-auth">
  <div class="cab-auth__inner">
    <div class="cab-auth__card">
      <div class="cab-auth__logo">CF</div>
      <h1 class="cab-auth__title">Личный кабинет</h1>
      <p class="cab-auth__subtitle">Войдите для управления заявками и перевозками</p>
      <form class="cab-auth__form" id="cab-login-form">
        <div class="cab-auth__field">
          <label for="auth-email">Email</label>
          <input type="email" id="auth-email" name="email" required>
        </div>
        <div class="cab-auth__field">
          <label for="auth-pass">Пароль</label>
          <input type="password" id="auth-pass" name="password" required>
        </div>
        <div class="cab-auth__field" data-captcha></div>
        <button type="submit" class="cab-auth__submit">Войти</button>
        <p class="cab-auth__status" id="auth-status"></p>
      </form>

      <div class="cab-auth__divider"><span>или</span></div>

      <button class="cab-auth__toggle" id="show-register">Зарегистрироваться</button>

      <form class="cab-auth__form cab-auth__form--register" id="cab-register-form" style="display:none;">
        <div class="cab-auth__field">
          <label for="reg-name">ФИО</label>
          <input type="text" id="reg-name" name="full_name" required>
        </div>
        <div class="cab-auth__field">
          <label for="reg-company">Компания</label>
          <input type="text" id="reg-company" name="company">
        </div>
        <div class="cab-auth__field">
          <label for="reg-email">Email</label>
          <input type="email" id="reg-email" name="email" required>
        </div>
        <div class="cab-auth__field">
          <label for="reg-phone">Телефон</label>
          <input type="tel" id="reg-phone" name="phone">
        </div>
        <div class="cab-auth__field">
          <label for="reg-pass">Пароль (мин. 6 символов)</label>
          <input type="password" id="reg-pass" name="password" minlength="6" required>
        </div>
        <div class="cab-auth__field">
          <label for="reg-pass2">Подтверждение пароля</label>
          <input type="password" id="reg-pass2" name="password_confirm" minlength="6" required>
        </div>
        <div class="cab-auth__field" data-captcha></div>
        <button type="submit" class="cab-auth__submit cab-auth__submit--reg">Создать аккаунт</button>
        <p class="cab-auth__status" id="reg-status"></p>
        <button type="button" class="cab-auth__toggle" id="show-login">Уже есть аккаунт? Войти</button>
      </form>
    </div>
  </div>
</section>

<!-- ═══ РАБОЧАЯ ОБЛАСТЬ КАБИНЕТА ═══ -->
<section class="cab" id="cab-app" style="display:none;">
  <aside class="cab__sidebar">
    <div class="cab__sidebar-head">
      <span class="cab__sidebar-logo">CF</span>
      <span class="cab__sidebar-brand">CargoFlow</span>
    </div>
    <nav class="cab__nav">
      <button class="cab__nav-btn cab__nav-btn--active" data-cab-view="dashboard">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        Дашборд
      </button>
      <button class="cab__nav-btn" data-cab-view="applications">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        Мои заявки
      </button>
      <button class="cab__nav-btn" data-cab-view="new-app">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
        Создать заявку
      </button>
      <button class="cab__nav-btn" data-cab-view="documents">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
        Документы
      </button>
      <button class="cab__nav-btn" data-cab-view="profile">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Профиль
      </button>
    </nav>
    <button class="cab__nav-btn cab__nav-btn--logout" id="cab-logout">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Выход
    </button>
  </aside>

  <div class="cab__main">
    <header class="cab__topbar">
      <h2 class="cab__topbar-title" id="cab-page-title">Дашборд</h2>
      <div class="cab__topbar-user">
        <span class="cab__topbar-name" id="cab-user-name"></span>
        <span class="cab__topbar-role" id="cab-user-company"></span>
      </div>
    </header>

    <div class="cab__content">

      <!-- ─── ДАШБОРД ─── -->
      <div class="cab__view cab__view--active" data-cab-panel="dashboard">
        <div class="cab-stats" id="cab-stats">
          <div class="cab-stats__card cab-stats__card--new">
            <span class="cab-stats__number" id="stat-new">—</span>
            <span class="cab-stats__label">Новые</span>
          </div>
          <div class="cab-stats__card cab-stats__card--proc">
            <span class="cab-stats__number" id="stat-proc">—</span>
            <span class="cab-stats__label">В обработке</span>
          </div>
          <div class="cab-stats__card cab-stats__card--transit">
            <span class="cab-stats__number" id="stat-transit">—</span>
            <span class="cab-stats__label">В пути</span>
          </div>
          <div class="cab-stats__card cab-stats__card--done">
            <span class="cab-stats__number" id="stat-done">—</span>
            <span class="cab-stats__label">Завершены</span>
          </div>
        </div>
        <div class="cab-table-wrap">
          <h3 class="cab-table-wrap__title">Последние заявки</h3>
          <table class="cab-table">
            <thead>
              <tr>
                <th>№</th>
                <th>Маршрут</th>
                <th>Тип</th>
                <th>Дата</th>
                <th>Статус</th>
              </tr>
            </thead>
            <tbody id="dash-recent-body"></tbody>
          </table>
        </div>
      </div>

      <!-- ─── МОИ ЗАЯВКИ ─── -->
      <div class="cab__view" data-cab-panel="applications">
        <div class="cab-filters">
          <select id="filter-status" class="cab-filters__select">
            <option value="">Все статусы</option>
            <option value="1">Новая</option>
            <option value="2">В обработке</option>
            <option value="3">Документы проверены</option>
            <option value="4">В пути</option>
            <option value="5">Завершена</option>
          </select>
          <select id="filter-type" class="cab-filters__select">
            <option value="">Все типы</option>
            <option value="air">Авиа</option>
            <option value="sea">Море</option>
            <option value="road">Авто</option>
            <option value="rail">Ж/д</option>
            <option value="multi">Мультимодальная</option>
          </select>
          <button class="cab-filters__btn" id="filter-apply">Применить</button>
        </div>
        <div class="cab-table-wrap">
          <table class="cab-table">
            <thead>
              <tr>
                <th>№</th>
                <th>Маршрут</th>
                <th>Вес, кг</th>
                <th>Объём, м³</th>
                <th>Тип</th>
                <th>Статус</th>
                <th>Дата</th>
              </tr>
            </thead>
            <tbody id="apps-table-body"></tbody>
          </table>
        </div>
      </div>

      <!-- ─── СОЗДАТЬ ЗАЯВКУ ─── -->
      <div class="cab__view" data-cab-panel="new-app">
        <form class="cab-new-form" id="new-app-form">
          <div class="cab-new-form__row">
            <div class="cab-new-form__field">
              <label>Страна отправления</label>
              <input type="text" name="country_from" placeholder="Китай" required>
            </div>
            <div class="cab-new-form__field">
              <label>Город отправления</label>
              <input type="text" name="city_from" placeholder="Шанхай" required>
            </div>
          </div>
          <div class="cab-new-form__row">
            <div class="cab-new-form__field">
              <label>Страна назначения</label>
              <input type="text" name="country_to" placeholder="Россия" required>
            </div>
            <div class="cab-new-form__field">
              <label>Город назначения</label>
              <input type="text" name="city_to" placeholder="Москва" required>
            </div>
          </div>
          <div class="cab-new-form__row cab-new-form__row--3">
            <div class="cab-new-form__field">
              <label>Тип перевозки</label>
              <select name="transport_type" required>
                <option value="sea">Морская</option>
                <option value="air">Авиа</option>
                <option value="road">Автомобильная</option>
                <option value="rail">Ж/д</option>
                <option value="multi">Мультимодальная</option>
              </select>
            </div>
            <div class="cab-new-form__field">
              <label>Вес, кг</label>
              <input type="number" name="weight_kg" step="0.01" placeholder="0.00">
            </div>
            <div class="cab-new-form__field">
              <label>Объём, м³</label>
              <input type="number" name="volume_cbm" step="0.001" placeholder="0.000">
            </div>
          </div>
          <div class="cab-new-form__row">
            <div class="cab-new-form__field cab-new-form__field--full">
              <label>Тип груза</label>
              <input type="text" name="cargo_type" placeholder="Электроника, текстиль, оборудование...">
            </div>
          </div>
          <div class="cab-new-form__row">
            <div class="cab-new-form__field cab-new-form__field--full">
              <label>Комментарий</label>
              <textarea name="comment" rows="3" placeholder="Дополнительная информация: сроки, особые условия..."></textarea>
            </div>
          </div>
          <div class="cab-new-form__footer">
            <button type="submit" class="cab-new-form__submit">Отправить заявку</button>
          </div>
          <p class="form-status" id="new-app-status"></p>
        </form>
      </div>

      <!-- ─── ДОКУМЕНТЫ ─── -->
      <div class="cab__view" data-cab-panel="documents">
        <div class="cab-table-wrap">
          <table class="cab-table">
            <thead>
              <tr>
                <th>Заявка</th>
                <th>Документ</th>
                <th>Размер</th>
                <th>Дата</th>
                <th></th>
              </tr>
            </thead>
            <tbody id="docs-table-body"></tbody>
          </table>
        </div>
      </div>

      <!-- ─── ПРОФИЛЬ ─── -->
      <div class="cab__view" data-cab-panel="profile">
        <form class="cab-profile" id="profile-form">
          <div class="cab-new-form__row">
            <div class="cab-new-form__field cab-new-form__field--full">
              <label>ФИО</label>
              <input type="text" name="full_name" id="prof-name">
            </div>
          </div>
          <div class="cab-new-form__row">
            <div class="cab-new-form__field">
              <label>Компания</label>
              <input type="text" name="company" id="prof-company">
            </div>
            <div class="cab-new-form__field">
              <label>Email</label>
              <input type="email" id="prof-email" disabled>
            </div>
          </div>
          <div class="cab-new-form__row">
            <div class="cab-new-form__field cab-new-form__field--full">
              <label>Телефон</label>
              <input type="tel" name="phone" id="prof-phone">
            </div>
          </div>
          <div class="cab-new-form__footer">
            <button type="submit" class="cab-new-form__submit">Сохранить изменения</button>
          </div>
          <p class="form-status" id="profile-status"></p>
        </form>
      </div>

    </div>
  </div>
</section>
