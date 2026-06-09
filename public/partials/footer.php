<footer class="site-footer">
  <div class="container site-footer__inner">
    <div class="site-footer__top">
      <div class="site-footer__brand">
        <span class="site-footer__logo">CARGOFLOW</span>
        <p class="site-footer__text">Международная логистика и грузоперевозки</p>
      </div>
      <nav class="site-footer__nav">
        <a href="/index.php?page=home">Главная</a>
        <a href="/index.php?page=about">О компании</a>
        <a href="/index.php?page=services">Услуги</a>
        <a href="/index.php?page=contacts">Контакты</a>
      </nav>
      <div class="site-footer__contacts">
        <a href="tel:+78000000000">8 (800) 000-00-00</a>
        <a href="mailto:info@cargoflow.ru">info@cargoflow.ru</a>
      </div>
    </div>
    <div class="site-footer__bottom">
      <span>&copy; <?= date('Y') ?> CargoFlow. Все права защищены.</span>
      <a href="#" class="site-footer__policy">Политика конфиденциальности</a>
    </div>
  </div>
</footer>

<!-- AI Chat Widget -->
<button class="ai-chat-btn" id="ai-chat-btn" aria-label="Открыть AI-ассистента" title="AI-ассистент CargoFlow">
  <svg class="ai-chat-btn__icon-chat" viewBox="0 0 24 24">
    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
  </svg>
  <svg class="ai-chat-btn__icon-close" viewBox="0 0 24 24">
    <line x1="18" y1="6" x2="6" y2="18"/>
    <line x1="6" y1="6" x2="18" y2="18"/>
  </svg>
</button>

<div class="ai-chat-panel" id="ai-chat-panel" role="dialog" aria-label="AI-ассистент">
  <div class="ai-chat__header">
    <div class="ai-chat__avatar">🤖</div>
    <div class="ai-chat__title">
      <div class="ai-chat__name">AI-ассистент CargoFlow</div>
      <div class="ai-chat__status">онлайн</div>
    </div>
    <button class="ai-chat__close" id="ai-chat-close" aria-label="Закрыть">&#x2715;</button>
  </div>

  <div class="ai-chat__messages" id="ai-chat-messages">
    <div class="ai-msg ai-msg--ai">
      <div class="ai-msg__bubble">Добрый день! Я ассистент CargoFlow. Помогу рассчитать стоимость доставки, подобрать маршрут и ответить на вопросы по таможенному оформлению. Чем могу помочь?</div>
    </div>
    <div class="ai-typing" id="ai-typing">
      <span class="ai-typing__dot"></span>
      <span class="ai-typing__dot"></span>
      <span class="ai-typing__dot"></span>
    </div>
  </div>

  <div class="ai-chat__quick" id="ai-chat-quick">
    <button class="ai-quick-btn" data-msg="Сколько стоит доставка из Китая?">Цены на доставку</button>
    <button class="ai-quick-btn" data-msg="Какие сроки доставки морем?">Сроки морем</button>
    <button class="ai-quick-btn" data-msg="Помогите с таможенным оформлением">Таможня</button>
    <button class="ai-quick-btn" data-msg="Как отследить мой груз?">Отследить груз</button>
  </div>

  <div class="ai-chat__input-bar">
    <textarea
      class="ai-chat__input"
      id="ai-chat-input"
      placeholder="Напишите вопрос..."
      rows="1"
      maxlength="500"
    ></textarea>
    <button class="ai-chat__send" id="ai-chat-send" aria-label="Отправить">
      <svg viewBox="0 0 24 24">
        <line x1="22" y1="2" x2="11" y2="13"/>
        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
      </svg>
    </button>
  </div>
</div>
<!-- /AI Chat Widget -->

<script src="/assets/js/main.js"></script>
<script src="/assets/js/ai-chat.js"></script>

</body>
</html>
