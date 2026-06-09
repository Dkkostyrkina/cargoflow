<?php

declare(strict_types=1);

require_once __DIR__ . '/security.php';

cf_security_headers();
cf_rate_limit('lead', 10, 60);

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

function cf_response(array $data, bool $isAjax): void
{
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    } else {
        header('Content-Type: text/html; charset=utf-8');
        $ok = $data['status'] === 'ok';
        ?>
        <!doctype html>
        <html lang="ru">
        <head>
            <meta charset="utf-8">
            <title><?= $ok ? 'Заявка отправлена' : 'Ошибка' ?></title>
        </head>
        <body>
        <p><?= htmlspecialchars((string)($data['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
        <p><a href="/index.php?page=services">Вернуться к услугам</a></p>
        </body>
        </html>
        <?php
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    cf_response(['status' => 'error', 'message' => 'Неверный метод запроса'], $isAjax);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$raw = file_get_contents('php://input') ?: '';
$payload = [];

if ($raw && str_starts_with($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $payload = $decoded;
    }
} else {
    $payload = $_POST;
}

$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($payload['csrf_token'] ?? ($_POST['csrf_token'] ?? null));
if (!cf_csrf_verify($csrfToken)) {
    cf_response(['status' => 'error', 'message' => 'Недействительный CSRF-токен. Обновите страницу.'], $isAjax);
}

$captchaToken = $payload['smart_token'] ?? ($payload['smart-token'] ?? ($_POST['smart-token'] ?? null));
if (!cf_verify_captcha($captchaToken)) {
    cf_response(['status' => 'error', 'message' => 'Подтвердите, что вы не робот'], $isAjax);
}

$name = trim((string)($payload['name'] ?? ''));
$email = trim((string)($payload['email'] ?? ''));
$direction = trim((string)($payload['direction'] ?? ''));
$transport = trim((string)($payload['transport_type'] ?? ''));
$cargo = trim((string)($payload['cargo_description'] ?? ($payload['cargo_type'] ?? '')));
$comment = trim((string)($payload['comment'] ?? ''));

if ($direction === '' || $email === '') {
    cf_response([
        'status' => 'error',
        'message' => 'Пожалуйста, заполните маршрут и e‑mail.',
    ], $isAjax);
}

$savedToDb = false;

// Пытаемся подключиться к БД, если сконфигурирован db.php.
$dbPath = __DIR__ . '/../config/db.php';
if (file_exists($dbPath)) {
    require_once $dbPath;
} else {
    require_once __DIR__ . '/../config/db.example.php';
}

if (function_exists('cf_get_db')) {
    $conn = cf_get_db();
    if ($conn instanceof mysqli) {
        $stmt = $conn->prepare(
            'INSERT INTO leads (created_at, name, email, direction, transport_type, cargo_description, comment, source)
             VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?)'
        );
        if ($stmt) {
            $source = 'web';
            $stmt->bind_param(
                'sssssss',
                $name,
                $email,
                $direction,
                $transport,
                $cargo,
                $comment,
                $source
            );
            if ($stmt->execute()) {
                $savedToDb = true;
            }
            $stmt->close();
        }
        $conn->close();
    }
}

$message = $savedToDb
    ? 'Заявка успешно сохранена. Менеджер свяжется с вами.'
    : 'Заявка отправлена в демо‑режиме (без сохранения в БД).';

cf_response([
    'status' => 'ok',
    'message' => $message,
], $isAjax);

