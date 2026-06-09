<?php
  $page = $_GET['page'] ?? 'home';

  $allowedPages = [
    'home' => 'Главная',
    'about' => 'О компании',
    'services' => 'Услуги',
    'contacts' => 'Контакты',
    'cabinet' => 'Личный кабинет',
    'admin'   => 'Панель администратора',
    'error404' => '',
    'error500' => '',
  ];

  if (!array_key_exists($page, $allowedPages)) {
      $page = 'error404';
      http_response_code(404);
  }

  $pageMeta = [
    'home'     => ['CargoFlow — цифровая логистика без границ', 'Автоматизация транспортно-логистических процессов: международные перевозки, таможенное оформление ВЭД, мультимодальная логистика.'],
    'about'    => ['О компании — CargoFlow', '12 лет опыта международных перевозок. 47 стран, 15 000+ перевозок. Команда профессионалов.'],
    'services' => ['Услуги — CargoFlow', 'Авиа, морские, автомобильные и ж/д перевозки. Таможенное оформление, складская логистика.'],
    'contacts' => ['Контакты — CargoFlow', 'Свяжитесь с CargoFlow: телефон, email, адрес. Отделы продаж и таможенного оформления.'],
    'cabinet'  => ['Личный кабинет — CargoFlow', 'Управление заявками на перевозку, отслеживание статусов.'],
    'admin'    => ['Администрирование — CargoFlow', ''],
    'error404' => ['Страница не найдена — CargoFlow', ''],
    'error500' => ['Ошибка сервера — CargoFlow', ''],
  ];

  $pageTitle = $pageMeta[$page][0] ?? 'CargoFlow';
  $pageDescription = $pageMeta[$page][1] ?? '';
  $sectionTitle = $allowedPages[$page] ?? '';

  require __DIR__ . '/partials/header.php';
  require __DIR__ . '/partials/nav.php';
?>

<main class="page">
  <?php require __DIR__ . '/pages/' . $page . '.php'; ?>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>

