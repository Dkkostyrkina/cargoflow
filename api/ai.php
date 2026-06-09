<?php
declare(strict_types=1);

/**
 * AI Proxy — CargoFlow
 * POST /api/ai.php
 * Body (JSON): { "message": "...", "csrf_token": "..." }
 */

require_once __DIR__ . '/security.php';

header('Content-Type: application/json; charset=utf-8');

// POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Rate limiting: 10 requests / 60 sec per IP
cf_rate_limit('ai', 10, 60);

// CSRF
cf_csrf_check();

// Parse body
$raw  = file_get_contents('php://input') ?: '';
$body = json_decode($raw, true);

$message = trim((string)($body['message'] ?? ''));

if ($message === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Сообщение не может быть пустым.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (mb_strlen($message) > 500) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Сообщение слишком длинное (макс. 500 символов).'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Load AI config
$cfgFile = __DIR__ . '/../config/ai.php';
if (file_exists($cfgFile)) {
    require_once $cfgFile;
}

$apiKey   = defined('CF_AI_API_KEY')    ? CF_AI_API_KEY    : '';
$modelUri = defined('CF_AI_MODEL_URI')  ? CF_AI_MODEL_URI  : '';
$temp     = defined('CF_AI_TEMP')       ? (float)CF_AI_TEMP : 0.4;
$maxTok   = defined('CF_AI_MAX_TOKENS') ? (int)CF_AI_MAX_TOKENS : 1000;

$useReal = ($apiKey !== '' && $apiKey !== 'your_api_key');

// System prompt
$systemPrompt = 'Ты — виртуальный ассистент CargoFlow, платформы международной логистики.' . "\n" .
    'Твоя задача — помогать клиентам по вопросам грузоперевозок, ВЭД и таможни.' . "\n\n" .
    'Направления работы:' . "\n" .
    '• Авиаперевозки — от $4.5/кг, сроки 2–5 дней' . "\n" .
    '• Морские перевозки — FCL от $800/20\' контейнер, LCL от $35/CBM, сроки 15–45 дней' . "\n" .
    '• Автоперевозки — от $0.8/км, сроки 1–14 дней' . "\n" .
    '• Ж/д перевозки — от $0.3/т·км, сроки 7–21 день' . "\n" .
    '• ВЭД под ключ — таможенное оформление, сертификация, валютный контроль' . "\n\n" .
    'Тарифы ориентировочные, точная стоимость рассчитывается индивидуально.' . "\n" .
    'Отвечай кратко, профессионально, на русском языке.' . "\n" .
    'Если вопрос не связан с логистикой — вежливо объясни свою специализацию.';

// Real YandexGPT call
if ($useReal) {
    $payload = json_encode([
        'modelUri' => $modelUri,
        'completionOptions' => [
            'stream'      => false,
            'temperature' => $temp,
            'maxTokens'   => (string)$maxTok,
        ],
        'messages' => [
            ['role' => 'system', 'text' => $systemPrompt],
            ['role' => 'user',   'text' => $message],
        ],
    ], JSON_UNESCAPED_UNICODE);

    $ch = curl_init('https://llm.api.cloud.yandex.net/foundationModels/v1/completion');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Api-Key ' . $apiKey,
        ],
    ]);

    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response !== false && $httpCode === 200) {
        $data  = json_decode($response, true);
        $reply = $data['result']['alternatives'][0]['message']['text'] ?? null;
        if ($reply !== null) {
            echo json_encode(['status' => 'ok', 'reply' => $reply], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    // Fall through to mock on API failure
}

// Mock responses (demo mode)
$lower = mb_strtolower($message, 'UTF-8');
$reply = cf_ai_mock_reply($lower);

echo json_encode(['status' => 'ok', 'reply' => $reply], JSON_UNESCAPED_UNICODE);
exit;

function cf_ai_mock_reply(string $msg): string
{
    if (preg_match('/цен|стоим|тариф|сколько стоит|расцен/u', $msg)) {
        return 'Стоимость перевозки зависит от маршрута, веса и типа груза. Ориентировочные тарифы: авиа — от $4.5/кг, море — от $35/CBM (LCL) или от $800 за контейнер, авто — от $0.8/км, ж/д — от $0.3/т·км. Для точного расчёта оформите заявку — наш менеджер свяжется с вами в течение часа.';
    }
    if (preg_match('/авиа|самолёт|самолет/u', $msg)) {
        return 'Авиаперевозки — самый быстрый способ доставки (2–5 дней). Работаем с крупнейшими авиакарго операторами по всему миру. Стоимость от $4.5/кг. Подходит для срочных, дорогостоящих или скоропортящихся грузов.';
    }
    if (preg_match('/мор|контейнер|fcl|lcl/u', $msg)) {
        return 'Морские перевозки — оптимальный выбор для крупных партий товаров. FCL (целый контейнер): 20\' от $800, 40\' от $1 400. LCL (сборный груз): от $35/CBM. Сроки: 15–45 дней. Работаем с портами Китая, Европы, Юго-Восточной Азии.';
    }
    if (preg_match('/авто|машин|фур|дорог/u', $msg)) {
        return 'Автоперевозки — гибкий и надёжный вариант для грузов по России, СНГ и Европе. Стоимость от $0.8/км, FTL и LTL. Сроки 1–14 дней. Собственный парк и проверенные партнёры-перевозчики.';
    }
    if (preg_match('/желез|поезд|ж\/д|жд|rail/u', $msg)) {
        return 'Железнодорожные перевозки — экономичная альтернатива авиа для грузов в Китай и страны ЕАЭС. Стоимость от $0.3/т·км. Сроки 7–21 день. Особенно выгодны для объёмных грузов весом от 5 тонн.';
    }
    if (preg_match('/тамож|вэд|декларац|сертиф|растамож/u', $msg)) {
        return 'CargoFlow оказывает полный спектр услуг по таможенному оформлению: декларирование, сертификация, валютный контроль, ВЭД под ключ. Наши таможенные брокеры имеют аттестат ФТС. Стоимость оформления ДТ — от 5 000 руб.';
    }
    if (preg_match('/срок|когда|время доставк|сколько дней/u', $msg)) {
        return "Ориентировочные сроки доставки:\n• Авиа: 2–5 дней\n• Авто (Европа/СНГ): 1–14 дней\n• Ж/д (Китай): 7–21 день\n• Море: 15–45 дней\n\nТочные сроки зависят от маршрута и вида груза.";
    }
    if (preg_match('/контакт|телефон|email|связ|менеджер|позвон/u', $msg)) {
        return 'Свяжитесь с нами: тел. 8 (800) 000-00-00 (бесплатно), email: info@cargoflow.ru. Менеджеры работают пн–пт с 9:00 до 19:00 МСК. Или оставьте заявку на сайте — перезвоним в течение часа.';
    }
    if (preg_match('/отследи|трек|статус груз|где груз/u', $msg)) {
        return 'Для отслеживания груза войдите в Личный кабинет — там доступна вся информация о статусе вашего заказа в режиме реального времени. Если кабинет ещё не настроен, свяжитесь с менеджером по тел. 8 (800) 000-00-00.';
    }
    if (preg_match('/привет|здравств|добр[ыо]/u', $msg)) {
        return 'Добрый день! Я ассистент CargoFlow. Помогу с вопросами по международным грузоперевозкам, расчётом стоимости и таможенным оформлением. Что вас интересует?';
    }
    return 'CargoFlow специализируется на международных грузоперевозках (авиа, море, авто, ж/д) и ВЭД под ключ. Я могу помочь с расчётом стоимости, выбором маршрута и условиями доставки. Уточните, пожалуйста, ваш вопрос или оставьте заявку — менеджер свяжется с вами в течение часа.';
}
