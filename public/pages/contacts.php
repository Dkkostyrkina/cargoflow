<!-- ═══ 1. HERO ═══ -->
<section class="cnt-hero cnt-hero--photo">
  <div class="container cnt-hero__inner" data-animate>
    <span class="tag tag--road">Свяжитесь с нами</span>
    <h1 class="cnt-hero__title">КОНТАКТЫ</h1>
    <p class="cnt-hero__subtitle">
      Мы готовы обсудить ваш проект и предложить оптимальное логистическое решение.
    </p>
  </div>
</section>

<!-- ═══ 2. КОНТАКТНАЯ ИНФОРМАЦИЯ ═══ -->
<section class="cnt-info">
  <div class="container cnt-info__inner">
    <div class="cnt-info__left" data-animate>
      <h2 class="cnt-info__heading">Наш офис</h2>

      <div class="cnt-info__block">
        <span class="cnt-info__label">Адрес</span>
        <p class="cnt-info__value">
          г. Москва, ул. Тверская, д. 22, стр. 1<br>
          Бизнес-центр «Logistics Hub», 12 этаж
        </p>
      </div>

      <div class="cnt-info__block">
        <span class="cnt-info__label">Телефон</span>
        <p class="cnt-info__value">
          <a href="tel:+74950000000">+7 (495) 000-00-00</a>
        </p>
      </div>

      <div class="cnt-info__block">
        <span class="cnt-info__label">Email</span>
        <p class="cnt-info__value">
          <a href="mailto:info@cargoflow.ru">info@cargoflow.ru</a>
        </p>
      </div>

      <div class="cnt-info__block">
        <span class="cnt-info__label">График работы</span>
        <p class="cnt-info__value">Пн - Пт, 09:00 - 18:00 (МСК)</p>
      </div>

      <div class="cnt-info__departments">
        <div class="cnt-info__dept">
          <span class="cnt-info__dept-line"></span>
          <div>
            <h4 class="cnt-info__dept-title">Для корпоративных клиентов</h4>
            <p class="cnt-info__dept-contact">
              <a href="mailto:b2b@cargoflow.ru">b2b@cargoflow.ru</a>
            </p>
          </div>
        </div>
        <div class="cnt-info__dept">
          <span class="cnt-info__dept-line"></span>
          <div>
            <h4 class="cnt-info__dept-title">Для поставщиков</h4>
            <p class="cnt-info__dept-contact">
              <a href="mailto:partners@cargoflow.ru">partners@cargoflow.ru</a>
            </p>
          </div>
        </div>
        <div class="cnt-info__dept">
          <span class="cnt-info__dept-line"></span>
          <div>
            <h4 class="cnt-info__dept-title">Для СМИ</h4>
            <p class="cnt-info__dept-contact">
              <a href="mailto:press@cargoflow.ru">press@cargoflow.ru</a>
            </p>
          </div>
        </div>
      </div>
    </div>
    <div class="cnt-info__right" data-animate="fade-up-delayed">
      <div class="cnt-info__map">
        <div class="cnt-info__map-placeholder">
          <span class="cnt-info__map-pin"></span>
          <span class="cnt-info__map-label">Москва, ул. Тверская, 22</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ 3. ФОРМА ═══ -->
<section class="cnt-form-section">
  <div class="container cnt-form-section__inner">
    <div class="cnt-form-section__header" data-animate>
      <h2 class="cnt-form-section__heading">Обсудить проект</h2>
      <p class="cnt-form-section__subtitle">
        Заполните форму, и наш специалист свяжется с вами в течение одного рабочего дня.
      </p>
    </div>
    <form class="cnt-form" id="contact-form" data-animate="fade-up-delayed">
      <div class="cnt-form__row">
        <div class="cnt-form__field">
          <label for="cnt-name">Имя</label>
          <input type="text" id="cnt-name" name="name" placeholder="Ваше имя" required>
        </div>
        <div class="cnt-form__field">
          <label for="cnt-company">Компания</label>
          <input type="text" id="cnt-company" name="company" placeholder="Название компании">
        </div>
      </div>
      <div class="cnt-form__row">
        <div class="cnt-form__field">
          <label for="cnt-phone">Телефон</label>
          <input type="tel" id="cnt-phone" name="phone" placeholder="+7 (___) ___-__-__">
        </div>
        <div class="cnt-form__field">
          <label for="cnt-email">Email</label>
          <input type="email" id="cnt-email" name="email" placeholder="email@company.ru" required>
        </div>
      </div>
      <div class="cnt-form__row">
        <div class="cnt-form__field cnt-form__field--full">
          <label for="cnt-service">Тип услуги</label>
          <select id="cnt-service" name="service_type">
            <option value="">Выберите направление</option>
            <option value="intl">Международные перевозки</option>
            <option value="customs">Таможенное оформление</option>
            <option value="ved">ВЭД под ключ</option>
            <option value="warehouse">Складская логистика</option>
            <option value="insurance">Страхование грузов</option>
            <option value="consulting">Консультация</option>
          </select>
        </div>
      </div>
      <div class="cnt-form__row">
        <div class="cnt-form__field cnt-form__field--full">
          <label for="cnt-message">Комментарий</label>
          <textarea id="cnt-message" name="comment" rows="4" placeholder="Расскажите о вашей задаче: маршрут, объём, сроки..."></textarea>
        </div>
      </div>
      <div class="cnt-form__row">
        <div class="cnt-form__field cnt-form__field--full" data-captcha></div>
      </div>
      <div class="cnt-form__footer">
        <button type="submit" class="cnt-form__submit">Отправить заявку</button>
        <p class="cnt-form__note">Нажимая кнопку, вы соглашаетесь с политикой конфиденциальности</p>
      </div>
      <p class="form-status" id="contact-form-status"></p>
    </form>
  </div>
</section>

<!-- ═══ 4. ГЕОГРАФИЯ (DARK) ═══ -->
<section class="cnt-geo">
  <div class="container cnt-geo__inner">
    <span class="tag tag--light">Присутствие</span>
    <h2 class="cnt-geo__heading" data-animate>География присутствия</h2>
    <div class="cnt-geo__regions" data-animate="fade-up-delayed">
      <div class="cnt-geo__region">
        <span class="cnt-geo__region-dot"></span>
        <h3 class="cnt-geo__region-name">Россия</h3>
        <p class="cnt-geo__region-desc">Москва, Санкт-Петербург, Владивосток, Новороссийск, Екатеринбург</p>
      </div>
      <div class="cnt-geo__region">
        <span class="cnt-geo__region-dot"></span>
        <h3 class="cnt-geo__region-name">Европа</h3>
        <p class="cnt-geo__region-desc">Германия, Италия, Нидерланды, Польша, Турция</p>
      </div>
      <div class="cnt-geo__region">
        <span class="cnt-geo__region-dot"></span>
        <h3 class="cnt-geo__region-name">Азия</h3>
        <p class="cnt-geo__region-desc">Китай, Южная Корея, Вьетнам, Индия, Индонезия</p>
      </div>
      <div class="cnt-geo__region">
        <span class="cnt-geo__region-dot"></span>
        <h3 class="cnt-geo__region-name">Ближний Восток</h3>
        <p class="cnt-geo__region-desc">ОАЭ, Саудовская Аравия, Бахрейн</p>
      </div>
      <div class="cnt-geo__region">
        <span class="cnt-geo__region-dot"></span>
        <h3 class="cnt-geo__region-name">СНГ</h3>
        <p class="cnt-geo__region-desc">Казахстан, Узбекистан, Беларусь, Армения</p>
      </div>
    </div>
    <p class="cnt-geo__note" data-animate>
      Сеть партнёров и собственных представительств в 96 странах обеспечивает
      бесперебойную логистику на всех ключевых торговых маршрутах.
    </p>
  </div>
</section>

<!-- ═══ 5. CTA (DARK) ═══ -->
<section class="cnt-cta">
  <div class="container cnt-cta__inner" data-animate>
    <h2 class="cnt-cta__title">
      CargoFlow — ваш надёжный партнёр<br>в международной логистике.
    </h2>
    <a href="#contact-form" class="cnt-cta__btn">Начать сотрудничество</a>
  </div>
</section>
