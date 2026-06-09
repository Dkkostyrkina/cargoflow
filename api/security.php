<?php
declare(strict_types=1);

/**
 * Модуль безопасности CargoFlow
 *
 * - Rate-limiting (защита от DDoS / брутфорса)
 * - CSRF-токены
 * - XSS-защита (заголовки + утилита экранирования)
 * - Проверка Yandex SmartCaptcha
 */

// ─── Конфигурация капчи (с резервом на example-файл) ───

$cfCaptchaPath = __DIR__ . '/../config/captcha.php';
if (file_exists($cfCaptchaPath)) {
    require_once $cfCaptchaPath;
} else {
    require_once __DIR__ . '/../config/captcha.example.php';
}
unset($cfCaptchaPath);

// ─── Rate-limiting на основе файловой системы ───

function cf_rate_limit(string $scope = 'global', int $maxRequests = 30, int $windowSec = 60): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $key = md5($ip . ':' . $scope);
    $dir = sys_get_temp_dir() . '/cargoflow_rate';

    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }

    $file = $dir . '/' . $key . '.json';
    $now = time();
    $data = ['hits' => [], 'blocked_until' => 0];

    if (file_exists($file)) {
        $raw = @file_get_contents($file);
        if ($raw) {
            $data = json_decode($raw, true) ?: $data;
        }
    }

    if ($data['blocked_until'] > $now) {
        header('Retry-After: ' . ($data['blocked_until'] - $now));
        http_response_code(429);
        echo json_encode([
            'status' => 'error',
            'message' => 'Слишком много запросов. Повторите через ' . ($data['blocked_until'] - $now) . ' сек.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $data['hits'] = array_filter($data['hits'], fn($t) => $t > ($now - $windowSec));
    $data['hits'][] = $now;

    if (count($data['hits']) > $maxRequests) {
        $data['blocked_until'] = $now + $windowSec;
        @file_put_contents($file, json_encode($data));
        header('Retry-After: ' . $windowSec);
        http_response_code(429);
        echo json_encode([
            'status' => 'error',
            'message' => 'Превышен лимит запросов. Повторите через ' . $windowSec . ' сек.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    @file_put_contents($file, json_encode($data));
}

// ─── CSRF-токены ───

function cf_csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time']) || (time() - $_SESSION['csrf_token_time']) > 3600) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }

    return $_SESSION['csrf_token'];
}

function cf_csrf_verify(?string $token): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

function cf_csrf_check(): void
{
    $token = $_SERVER['HTTP_X_CSRF_TOKEN']
        ?? $_POST['csrf_token']
        ?? null;

    if ($token === null) {
        $raw = file_get_contents('php://input') ?: '';
        if ($raw && str_starts_with($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
            $data = json_decode($raw, true);
            $token = $data['csrf_token'] ?? null;
        }
    }

    if (!cf_csrf_verify($token)) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'Недействительный CSRF-токен. Обновите страницу.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ─── XSS-заголовки безопасности ───

function cf_security_headers(): void
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://smartcaptcha.yandexcloud.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https://smartcaptcha.yandexcloud.net; connect-src 'self' https://smartcaptcha.yandexcloud.net; frame-src https://smartcaptcha.yandexcloud.net");
}

// ─── Yandex SmartCaptcha ───

function cf_captcha_client_key(): string
{
    return defined('CF_CAPTCHA_CLIENT_KEY') ? (string)CF_CAPTCHA_CLIENT_KEY : '';
}

function cf_captcha_enabled(): bool
{
    return defined('CF_CAPTCHA_SERVER_KEY') && CF_CAPTCHA_SERVER_KEY !== '';
}

/**
 * Проверяет токен SmartCaptcha через сервис валидации Yandex.
 *
 * Возвращает true, если капча отключена (ключ не задан) или токен подтверждён.
 * При недоступности сервиса валидации пользователь не блокируется
 * (fail-open, как и для остальных внешних сервисов проекта).
 */
function cf_verify_captcha(?string $token): bool
{
    if (!cf_captcha_enabled()) {
        return true;
    }
    if (!is_string($token) || $token === '') {
        return false;
    }

    $query = http_build_query([
        'secret' => CF_CAPTCHA_SERVER_KEY,
        'token'  => $token,
        'ip'     => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
    ]);
    $url = 'https://smartcaptcha.yandexcloud.net/validate?' . $query;

    $response = false;
    $httpCode = 0;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
        ]);
        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    } else {
        $ctx = stream_context_create(['http' => ['timeout' => 5]]);
        $response = @file_get_contents($url, false, $ctx);
        $httpCode = $response !== false ? 200 : 0;
    }

    if ($response === false || $httpCode !== 200) {
        return true;
    }

    $data = json_decode((string)$response, true);
    return is_array($data) && ($data['status'] ?? '') === 'ok';
}

// ─── Утилита экранирования для HTML-вывода ───

function cf_esc(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ─── Утилита для безопасного JS-вывода (textContent эмуляция) ───

function cf_esc_js(string $str): string
{
    return json_encode($str, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
}
