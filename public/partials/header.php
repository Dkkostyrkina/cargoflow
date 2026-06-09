<?php
  if (!isset($pageTitle)) {
      $pageTitle = 'CargoFlow';
  }

  require_once __DIR__ . '/../../api/security.php';
  cf_security_headers();

  if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
  }
  $csrfToken = cf_csrf_token();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= cf_esc($csrfToken) ?>">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <meta name="description" content="<?= htmlspecialchars($pageDescription ?? 'CargoFlow — международная логистика и грузоперевозки.', ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:type" content="website">
  <meta property="og:locale" content="ru_RU">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/ai-chat.css">
<?php if (function_exists('cf_captcha_client_key') && cf_captcha_client_key() !== ''): ?>
  <meta name="captcha-sitekey" content="<?= cf_esc(cf_captcha_client_key()) ?>">
  <meta name="captcha-test" content="<?= (defined('CF_CAPTCHA_TEST_MODE') && CF_CAPTCHA_TEST_MODE) ? '1' : '0' ?>">
  <script src="https://smartcaptcha.yandexcloud.net/captcha.js?render=onload&onload=cfRenderCaptchas" defer></script>
<?php endif; ?>
</head>
<body>
