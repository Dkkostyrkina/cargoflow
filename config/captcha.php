<?php
declare(strict_types=1);

/**
 * Конфигурация Yandex SmartCaptcha.
 * Укажите свои ключи капчи (получить в консоли Yandex Cloud).
 */
const CF_CAPTCHA_CLIENT_KEY = 'your_client_key';
const CF_CAPTCHA_SERVER_KEY = 'your_server_key';

// Тестовый режим: капча всегда показывает задание (для отладки и скриншотов).
// На реальном сайте поставьте false.
const CF_CAPTCHA_TEST_MODE = true;
