<?php $pageTitle = '404 — Страница не найдена | CargoFlow'; ?>
<section class="error-page">
  <div class="container error-page__inner">
    <div class="error-page__code">404</div>
    <h1 class="error-page__title">Страница не найдена</h1>
    <p class="error-page__desc">Возможно, адрес введён неверно или страница была удалена.</p>
    <a href="/index.php" class="btn error-page__btn">На главную</a>
  </div>
</section>

<style>
.error-page {
  min-height: calc(100vh - 200px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  background: var(--bg);
}
.error-page__inner {
  text-align: center;
  max-width: 480px;
}
.error-page__code {
  font-size: clamp(100px, 18vw, 160px);
  font-weight: 800;
  line-height: 1;
  color: var(--text);
  letter-spacing: -4px;
  margin-bottom: 16px;
}
.error-page__title {
  font-size: 24px;
  font-weight: 700;
  color: var(--text);
  margin: 0 0 12px;
}
.error-page__desc {
  font-size: 15px;
  color: var(--text-secondary);
  margin: 0 0 32px;
  line-height: 1.6;
}
.error-page__btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 14px 32px;
  background: var(--text);
  color: #fff;
  font-size: 14px;
  font-weight: 600;
  text-decoration: none;
  border-radius: 0;
  transition: opacity 0.2s;
}
.error-page__btn:hover {
  opacity: 0.85;
}
</style>
