<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/security.php';

header('Content-Type: application/json; charset=utf-8');
cf_security_headers();

cf_rate_limit('cabinet', 60, 60);

$dbPath = __DIR__ . '/../config/db.php';
if (file_exists($dbPath)) {
    require_once $dbPath;
} else {
    require_once __DIR__ . '/../config/db.example.php';
}

$mailPath = __DIR__ . '/../config/mail.php';
if (file_exists($mailPath)) {
    require_once $mailPath;
}

function jsonOut(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function getDb(): ?mysqli {
    if (!function_exists('cf_get_db')) return null;
    return cf_get_db();
}

function requireAuth(): int {
    if (empty($_SESSION['user_id'])) {
        jsonOut(['status' => 'error', 'message' => 'Не авторизован'], 401);
    }
    return (int)$_SESSION['user_id'];
}

$raw = file_get_contents('php://input') ?: '';
$body = [];
if ($raw && str_starts_with($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
    $body = json_decode($raw, true) ?: [];
}

$action = $_GET['action'] ?? ($body['action'] ?? '');

// CSRF-токен: выдача
if ($action === 'csrf') {
    jsonOut(['status' => 'ok', 'token' => cf_csrf_token()]);
}

// POST-запросы (кроме login/register/check/logout/csrf) требуют CSRF
$csrfExempt = ['check', 'dashboard', 'applications', 'documents', 'csrf'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !in_array($action, $csrfExempt, true)) {
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($body['csrf_token'] ?? null);
    if (!cf_csrf_verify($csrfToken)) {
        jsonOut(['status' => 'error', 'message' => 'Недействительный CSRF-токен. Обновите страницу.'], 403);
    }
}

// ═══ AUTH: REGISTER ═══
if ($action === 'register') {
    cf_rate_limit('register', 5, 300);

    $captchaToken = $body['smart_token'] ?? ($body['smart-token'] ?? null);
    if (!cf_verify_captcha($captchaToken)) {
        jsonOut(['status' => 'error', 'message' => 'Подтвердите, что вы не робот'], 400);
    }

    $email    = trim((string)($body['email'] ?? ''));
    $password = trim((string)($body['password'] ?? ''));
    $fullName = trim((string)($body['full_name'] ?? ''));
    $company  = trim((string)($body['company'] ?? ''));
    $phone    = trim((string)($body['phone'] ?? ''));

    if ($email === '' || $password === '') {
        jsonOut(['status' => 'error', 'message' => 'Заполните email и пароль'], 400);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonOut(['status' => 'error', 'message' => 'Некорректный формат email'], 400);
    }
    if (mb_strlen($password) < 6) {
        jsonOut(['status' => 'error', 'message' => 'Пароль должен быть не менее 6 символов'], 400);
    }

    $confirmToken = bin2hex(random_bytes(32));
    $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    $db = getDb();
    if ($db) {
        $check = $db->prepare('SELECT id FROM users WHERE email = ?');
        if ($check) {
            $check->bind_param('s', $email);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $check->close();
                $db->close();
                jsonOut(['status' => 'error', 'message' => 'Пользователь с таким email уже зарегистрирован'], 409);
            }
            $check->close();
        }

        $stmt = $db->prepare(
            'INSERT INTO users (email, password_hash, full_name, company, phone, email_verified, confirm_token, confirm_token_at)
             VALUES (?, ?, ?, ?, ?, 0, ?, NOW())'
        );
        if ($stmt) {
            $stmt->bind_param('ssssss', $email, $passwordHash, $fullName, $company, $phone, $confirmToken);
            if ($stmt->execute()) {
                $newUserId = (int)$stmt->insert_id;
                $stmt->close();
                $db->close();

                $confirmUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http')
                    . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost:8000')
                    . '/api/cabinet.php?action=confirm&token=' . $confirmToken;

                if (function_exists('cf_send_mail')) {
                    $mailBody = '<p>Здравствуйте!</p>'
                        . '<p>Благодарим за регистрацию в сервисе CargoFlow. '
                        . 'Для активации аккаунта перейдите по ссылке:</p>'
                        . '<p><a href="' . $confirmUrl . '">' . $confirmUrl . '</a></p>'
                        . '<p>Если вы не регистрировались, просто проигнорируйте это письмо.</p>';
                    @cf_send_mail($email, 'Подтверждение регистрации в CargoFlow', $mailBody);
                }

                jsonOut([
                    'status' => 'ok',
                    'message' => 'Регистрация успешна. Письмо со ссылкой для подтверждения отправлено на ваш email.',
                    'confirm_url' => $confirmUrl,
                    'user_id' => $newUserId
                ]);
            }
            $stmt->close();
        }
        $db->close();
        jsonOut(['status' => 'error', 'message' => 'Ошибка при регистрации'], 500);
    }

    // Резервный режим (база данных недоступна)
    jsonOut([
        'status' => 'ok',
        'message' => 'Регистрация успешна. Ссылка для подтверждения email отправлена.',
        'confirm_url' => '#'
    ]);
}

// ═══ AUTH: CONFIRM EMAIL ═══
if ($action === 'confirm') {
    $token = trim((string)($_GET['token'] ?? ''));

    if ($token === '' || strlen($token) !== 64) {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html lang="ru"><head><meta charset="utf-8"><title>Ошибка</title></head><body>';
        echo '<h2>Недействительная ссылка подтверждения</h2>';
        echo '<p><a href="/index.php?page=cabinet">Вернуться в личный кабинет</a></p>';
        echo '</body></html>';
        exit;
    }

    $db = getDb();
    if ($db) {
        $stmt = $db->prepare('SELECT id, email, email_verified, confirm_token_at FROM users WHERE confirm_token = ?');
        if ($stmt) {
            $stmt->bind_param('s', $token);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$user) {
                header('Content-Type: text/html; charset=utf-8');
                echo '<!DOCTYPE html><html lang="ru"><head><meta charset="utf-8"><title>Ошибка</title></head><body>';
                echo '<h2>Ссылка подтверждения не найдена или уже использована</h2>';
                echo '<p><a href="/index.php?page=cabinet">Вернуться в личный кабинет</a></p>';
                echo '</body></html>';
                $db->close();
                exit;
            }

            $tokenAge = time() - strtotime($user['confirm_token_at']);
            if ($tokenAge > 86400) {
                header('Content-Type: text/html; charset=utf-8');
                echo '<!DOCTYPE html><html lang="ru"><head><meta charset="utf-8"><title>Ошибка</title></head><body>';
                echo '<h2>Ссылка подтверждения истекла (24 часа)</h2>';
                echo '<p><a href="/index.php?page=cabinet">Зарегистрируйтесь повторно</a></p>';
                echo '</body></html>';
                $db->close();
                exit;
            }

            $upd = $db->prepare('UPDATE users SET email_verified = 1, confirm_token = NULL WHERE id = ?');
            if ($upd) {
                $uid = (int)$user['id'];
                $upd->bind_param('i', $uid);
                $upd->execute();
                $upd->close();
            }
            $db->close();

            header('Content-Type: text/html; charset=utf-8');
            echo '<!DOCTYPE html><html lang="ru"><head><meta charset="utf-8"><title>Email подтверждён</title></head><body>';
            echo '<h2>Email успешно подтверждён!</h2>';
            echo '<p>Теперь вы можете <a href="/index.php?page=cabinet">войти в личный кабинет</a>.</p>';
            echo '</body></html>';
            exit;
        }
        $db->close();
    }

    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="ru"><head><meta charset="utf-8"><title>Подтверждение</title></head><body>';
    echo '<h2>Email подтверждён (демо-режим)</h2>';
    echo '<p><a href="/index.php?page=cabinet">Войти в личный кабинет</a></p>';
    echo '</body></html>';
    exit;
}

// ═══ AUTH: LOGIN ═══
if ($action === 'login') {
    $email = trim((string)($body['email'] ?? ''));
    $password = trim((string)($body['password'] ?? ''));

    if ($email === '' || $password === '') {
        jsonOut(['status' => 'error', 'message' => 'Заполните email и пароль'], 400);
    }

    $captchaToken = $body['smart_token'] ?? ($body['smart-token'] ?? null);
    if (!cf_verify_captcha($captchaToken)) {
        jsonOut(['status' => 'error', 'message' => 'Подтвердите, что вы не робот'], 400);
    }

    $db = getDb();
    if ($db) {
        $stmt = $db->prepare('SELECT id, password_hash, full_name, company, phone, role FROM users WHERE email = ?');
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $res = $stmt->get_result();
            $user = $res->fetch_assoc();
            $stmt->close();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['user_role'] = $user['role'];
                $db->close();
                jsonOut([
                    'status' => 'ok',
                    'user' => [
                        'id' => (int)$user['id'],
                        'email' => $email,
                        'full_name' => $user['full_name'],
                        'company' => $user['company'],
                        'phone' => $user['phone'],
                        'role' => $user['role'],
                    ]
                ]);
            }
            $db->close();
        }
    }

    // Демо-режим: вход без БД
    if ($email === 'demo@cargoflow.ru' && $password === 'demo') {
        $_SESSION['user_id'] = 1;
        $_SESSION['user_role'] = 'client';
        jsonOut([
            'status' => 'ok',
            'demo' => true,
            'user' => [
                'id' => 1,
                'email' => 'demo@cargoflow.ru',
                'full_name' => 'Иванов Алексей Петрович',
                'company' => 'ООО «ТрансЛогистик»',
                'phone' => '+7 (495) 123-45-67',
                'role' => 'client',
            ]
        ]);
    }

    jsonOut(['status' => 'error', 'message' => 'Неверный email или пароль'], 401);
}

// ═══ AUTH: CHECK ═══
if ($action === 'check') {
    if (empty($_SESSION['user_id'])) {
        jsonOut(['status' => 'ok', 'authenticated' => false]);
    }

    $uid = (int)$_SESSION['user_id'];
    $db = getDb();
    if ($db) {
        $stmt = $db->prepare('SELECT id, email, full_name, company, phone, role FROM users WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('i', $uid);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $db->close();
            if ($user) {
                jsonOut(['status' => 'ok', 'authenticated' => true, 'user' => $user]);
            }
        }
    }

    // Демо-режим
    jsonOut([
        'status' => 'ok',
        'authenticated' => true,
        'demo' => true,
        'user' => [
            'id' => $uid,
            'email' => 'demo@cargoflow.ru',
            'full_name' => 'Иванов Алексей Петрович',
            'company' => 'ООО «ТрансЛогистик»',
            'phone' => '+7 (495) 123-45-67',
            'role' => $_SESSION['user_role'] ?? 'client',
        ]
    ]);
}

// ═══ AUTH: LOGOUT ═══
if ($action === 'logout') {
    session_destroy();
    jsonOut(['status' => 'ok']);
}

// ═══ Все действия ниже требуют авторизации ═══
$userId = requireAuth();
$db = getDb();
$demo = !$db;

// ═══ DASHBOARD STATS ═══
if ($action === 'dashboard') {
    if ($db) {
        $stats = ['new' => 0, 'processing' => 0, 'transit' => 0, 'done' => 0];
        $stmt = $db->prepare('SELECT status, COUNT(*) AS cnt FROM applications WHERE user_id = ? GROUP BY status');
        if ($stmt) {
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $s = (int)$row['status'];
                $c = (int)$row['cnt'];
                if ($s === 1) $stats['new'] = $c;
                elseif ($s === 2 || $s === 3) $stats['processing'] += $c;
                elseif ($s === 4) $stats['transit'] = $c;
                elseif ($s === 5) $stats['done'] = $c;
            }
            $stmt->close();
        }

        $recent = [];
        $stmt = $db->prepare(
            'SELECT id, CONCAT(city_from, " → ", city_to) AS route, transport_type, created_at, status
             FROM applications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10'
        );
        if ($stmt) {
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $row['id'] = (int)$row['id'];
                $row['status'] = (int)$row['status'];
                $recent[] = $row;
            }
            $stmt->close();
        }
        $db->close();
        jsonOut(['status' => 'ok', 'stats' => $stats, 'recent' => $recent]);
    }

    // Демо
    jsonOut([
        'status' => 'ok',
        'demo' => true,
        'stats' => ['new' => 3, 'processing' => 2, 'transit' => 4, 'done' => 12],
        'recent' => [
            ['id' => 1048, 'route' => 'Шанхай → Москва', 'transport_type' => 'sea', 'created_at' => '2026-02-28 10:30:00', 'status' => 4],
            ['id' => 1047, 'route' => 'Стамбул → Санкт-Петербург', 'transport_type' => 'road', 'created_at' => '2026-02-25 14:15:00', 'status' => 3],
            ['id' => 1046, 'route' => 'Гуанчжоу → Владивосток', 'transport_type' => 'sea', 'created_at' => '2026-02-22 09:00:00', 'status' => 5],
            ['id' => 1045, 'route' => 'Дубай → Москва', 'transport_type' => 'air', 'created_at' => '2026-02-20 11:45:00', 'status' => 2],
            ['id' => 1044, 'route' => 'Пекин → Екатеринбург', 'transport_type' => 'rail', 'created_at' => '2026-02-18 16:30:00', 'status' => 1],
        ]
    ]);
}

// ═══ LIST APPLICATIONS ═══
if ($action === 'applications') {
    if ($db) {
        $where = 'WHERE user_id = ?';
        $params = [$userId];
        $types = 'i';

        $filterStatus = isset($_GET['status']) ? (int)$_GET['status'] : 0;
        $filterType = $_GET['type'] ?? '';

        if ($filterStatus > 0) {
            $where .= ' AND status = ?';
            $params[] = $filterStatus;
            $types .= 'i';
        }
        if ($filterType !== '') {
            $where .= ' AND transport_type = ?';
            $params[] = $filterType;
            $types .= 's';
        }

        $sql = "SELECT id, country_from, city_from, country_to, city_to,
                       transport_type, cargo_type, weight_kg, volume_cbm, status, created_at
                FROM applications $where ORDER BY created_at DESC";
        $stmt = $db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $res = $stmt->get_result();
            $apps = [];
            while ($row = $res->fetch_assoc()) {
                $row['id'] = (int)$row['id'];
                $row['status'] = (int)$row['status'];
                $row['weight_kg'] = $row['weight_kg'] ? (float)$row['weight_kg'] : null;
                $row['volume_cbm'] = $row['volume_cbm'] ? (float)$row['volume_cbm'] : null;
                $row['route'] = $row['city_from'] . ' → ' . $row['city_to'];
                $apps[] = $row;
            }
            $stmt->close();
        }
        $db->close();
        jsonOut(['status' => 'ok', 'applications' => $apps ?? []]);
    }

    // Демо
    jsonOut([
        'status' => 'ok',
        'demo' => true,
        'applications' => [
            ['id' => 1048, 'route' => 'Шанхай → Москва', 'country_from' => 'Китай', 'city_from' => 'Шанхай', 'country_to' => 'Россия', 'city_to' => 'Москва', 'transport_type' => 'sea', 'cargo_type' => 'Электроника', 'weight_kg' => 2400, 'volume_cbm' => 14.5, 'status' => 4, 'created_at' => '2026-02-28 10:30:00'],
            ['id' => 1047, 'route' => 'Стамбул → Санкт-Петербург', 'country_from' => 'Турция', 'city_from' => 'Стамбул', 'country_to' => 'Россия', 'city_to' => 'Санкт-Петербург', 'transport_type' => 'road', 'cargo_type' => 'Текстиль', 'weight_kg' => 800, 'volume_cbm' => 6.2, 'status' => 3, 'created_at' => '2026-02-25 14:15:00'],
            ['id' => 1046, 'route' => 'Гуанчжоу → Владивосток', 'country_from' => 'Китай', 'city_from' => 'Гуанчжоу', 'country_to' => 'Россия', 'city_to' => 'Владивосток', 'transport_type' => 'sea', 'cargo_type' => 'Оборудование', 'weight_kg' => 5200, 'volume_cbm' => 28.0, 'status' => 5, 'created_at' => '2026-02-22 09:00:00'],
            ['id' => 1045, 'route' => 'Дубай → Москва', 'country_from' => 'ОАЭ', 'city_from' => 'Дубай', 'country_to' => 'Россия', 'city_to' => 'Москва', 'transport_type' => 'air', 'cargo_type' => 'Фармацевтика', 'weight_kg' => 120, 'volume_cbm' => 0.8, 'status' => 2, 'created_at' => '2026-02-20 11:45:00'],
            ['id' => 1044, 'route' => 'Пекин → Екатеринбург', 'country_from' => 'Китай', 'city_from' => 'Пекин', 'country_to' => 'Россия', 'city_to' => 'Екатеринбург', 'transport_type' => 'rail', 'cargo_type' => 'Автокомпоненты', 'weight_kg' => 3400, 'volume_cbm' => 18.0, 'status' => 1, 'created_at' => '2026-02-18 16:30:00'],
            ['id' => 1043, 'route' => 'Хошимин → Новороссийск', 'country_from' => 'Вьетнам', 'city_from' => 'Хошимин', 'country_to' => 'Россия', 'city_to' => 'Новороссийск', 'transport_type' => 'sea', 'cargo_type' => 'FMCG', 'weight_kg' => 4800, 'volume_cbm' => 22.5, 'status' => 5, 'created_at' => '2026-02-15 08:00:00'],
        ]
    ]);
}

// ═══ CREATE APPLICATION ═══
if ($action === 'create_application') {
    $countryFrom = trim((string)($body['country_from'] ?? ''));
    $cityFrom    = trim((string)($body['city_from'] ?? ''));
    $countryTo   = trim((string)($body['country_to'] ?? ''));
    $cityTo      = trim((string)($body['city_to'] ?? ''));
    $transport   = trim((string)($body['transport_type'] ?? 'sea'));
    $cargoType   = trim((string)($body['cargo_type'] ?? ''));
    $weight      = $body['weight_kg'] ?? null;
    $volume      = $body['volume_cbm'] ?? null;
    $comment     = trim((string)($body['comment'] ?? ''));

    if ($cityFrom === '' || $cityTo === '') {
        jsonOut(['status' => 'error', 'message' => 'Укажите город отправления и назначения'], 400);
    }

    if ($db) {
        $stmt = $db->prepare(
            'INSERT INTO applications (user_id, country_from, city_from, country_to, city_to, transport_type, cargo_type, weight_kg, volume_cbm, comment, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)'
        );
        if ($stmt) {
            $w = $weight !== null ? (float)$weight : null;
            $v = $volume !== null ? (float)$volume : null;
            $stmt->bind_param('issssssdds',
                $userId, $countryFrom, $cityFrom, $countryTo, $cityTo,
                $transport, $cargoType, $w, $v, $comment
            );
            if ($stmt->execute()) {
                $newId = (int)$stmt->insert_id;
                $stmt->close();
                $db->close();
                jsonOut(['status' => 'ok', 'message' => 'Заявка создана', 'id' => $newId]);
            }
            $stmt->close();
        }
        $db->close();
        jsonOut(['status' => 'error', 'message' => 'Ошибка сохранения'], 500);
    }

    // Демо
    jsonOut([
        'status' => 'ok',
        'demo' => true,
        'message' => 'Заявка создана (демо-режим)',
        'id' => rand(1050, 9999)
    ]);
}

// ═══ DOCUMENTS ═══
if ($action === 'documents') {
    if ($db) {
        $stmt = $db->prepare(
            'SELECT d.id, d.application_id, d.file_name, d.file_size, d.created_at
             FROM documents d
             JOIN applications a ON d.application_id = a.id
             WHERE a.user_id = ?
             ORDER BY d.created_at DESC'
        );
        $docs = [];
        if ($stmt) {
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $row['id'] = (int)$row['id'];
                $row['application_id'] = (int)$row['application_id'];
                $row['file_size'] = (int)$row['file_size'];
                $docs[] = $row;
            }
            $stmt->close();
        }
        $db->close();
        jsonOut(['status' => 'ok', 'documents' => $docs]);
    }

    // Демо
    jsonOut([
        'status' => 'ok',
        'demo' => true,
        'documents' => [
            ['id' => 1, 'application_id' => 1048, 'file_name' => 'Коносамент_1048.pdf', 'file_size' => 245000, 'created_at' => '2026-02-28 12:00:00'],
            ['id' => 2, 'application_id' => 1048, 'file_name' => 'Инвойс_1048.pdf', 'file_size' => 128000, 'created_at' => '2026-02-28 12:05:00'],
            ['id' => 3, 'application_id' => 1047, 'file_name' => 'CMR_1047.pdf', 'file_size' => 312000, 'created_at' => '2026-02-26 09:30:00'],
            ['id' => 4, 'application_id' => 1046, 'file_name' => 'ДТ_1046.pdf', 'file_size' => 198000, 'created_at' => '2026-02-23 15:20:00'],
            ['id' => 5, 'application_id' => 1046, 'file_name' => 'Сертификат_соответствия_1046.pdf', 'file_size' => 87000, 'created_at' => '2026-02-23 15:25:00'],
        ]
    ]);
}

// ═══ DOWNLOAD DOCUMENT ═══
if ($action === 'download') {
    $docId    = (int)($body['id'] ?? $_GET['id'] ?? 0);
    $fileName = trim((string)($body['file_name'] ?? $_GET['file_name'] ?? 'document.pdf'));
    $safeFileName = preg_replace('/[^a-zA-Zа-яА-ЯёЁ0-9_.\- ]/u', '', $fileName);
    if (empty($safeFileName)) $safeFileName = 'document.pdf';
    $docTitle = str_replace('.pdf', '', $safeFileName);

    // Fetch application data from DB
    $appData = null;
    if ($db && $docId > 0) {
        $stmt = $db->prepare(
            'SELECT d.file_name, d.created_at AS doc_date,
                    a.id AS app_id, a.country_from, a.city_from, a.country_to, a.city_to,
                    a.transport_type, a.cargo_type, a.weight_kg, a.volume_cbm, a.status,
                    a.created_at AS app_date,
                    u.full_name, u.company, u.email, u.phone
             FROM documents d
             JOIN applications a ON d.application_id = a.id
             JOIN users u ON a.user_id = u.id
             WHERE d.id = ? AND a.user_id = ?'
        );
        if ($stmt) {
            $stmt->bind_param('ii', $docId, $userId);
            $stmt->execute();
            $appData = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }
    }

    $transportNames = ['air'=>'Авиаперевозка','sea'=>'Морская перевозка','road'=>'Автоперевозка','rail'=>'Ж/Д перевозка','multi'=>'Мультимодальная'];
    $statusNames = [1=>'Новая',2=>'В обработке',3=>'Документы проверены',4=>'В пути',5=>'Завершена'];
    $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

    $appId      = $e($appData['app_id'] ?? $docId);
    $appDate    = $e($appData['app_date'] ?? date('Y-m-d'));
    $client     = $e($appData['full_name'] ?? '—');
    $company    = $e($appData['company'] ?? '—');
    $email      = $e($appData['email'] ?? '—');
    $phone      = $e($appData['phone'] ?? '—');
    $from       = $e(($appData['city_from'] ?? '') . ', ' . ($appData['country_from'] ?? ''));
    $to         = $e(($appData['city_to'] ?? '') . ', ' . ($appData['country_to'] ?? ''));
    $transport  = $e($transportNames[$appData['transport_type'] ?? ''] ?? ($appData['transport_type'] ?? '—'));
    $cargo      = $e($appData['cargo_type'] ?? '—');
    $weight     = $e($appData['weight_kg'] ?? '0');
    $volume     = $e($appData['volume_cbm'] ?? '0');
    $status     = $e($statusNames[(int)($appData['status'] ?? 1)] ?? '—');
    $docDate    = $e($appData['doc_date'] ?? date('Y-m-d'));
    $docTitleE  = $e($docTitle);

    header('Content-Type: text/html; charset=utf-8');
    echo <<<HTML
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>{$docTitleE} — CargoFlow</title>
<style>
  @media print { body { margin: 0; } .no-print { display: none; } }
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'Inter', 'Segoe UI', Arial, sans-serif; color: #1a1a1a; background: #fff; padding: 48px; max-width: 800px; margin: 0 auto; font-size: 14px; line-height: 1.6; }
  .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #1a1a1a; padding-bottom: 24px; margin-bottom: 32px; }
  .logo { font-size: 28px; font-weight: 800; letter-spacing: -0.03em; }
  .logo-sub { font-size: 11px; color: #999; font-weight: 400; letter-spacing: 0.05em; text-transform: uppercase; margin-top: 4px; }
  .doc-meta { text-align: right; font-size: 12px; color: #666; }
  .doc-meta strong { color: #1a1a1a; font-size: 14px; display: block; margin-bottom: 4px; }
  h2 { font-size: 20px; font-weight: 700; margin: 32px 0 16px; letter-spacing: -0.02em; }
  .row { display: flex; border-bottom: 1px solid #e5e5e5; padding: 10px 0; }
  .row-label { width: 200px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.06em; color: #999; font-weight: 500; flex-shrink: 0; }
  .row-value { font-size: 15px; font-weight: 500; }
  .route { background: #f5f5f3; padding: 20px 24px; margin: 16px 0; display: flex; align-items: center; gap: 20px; }
  .route-city { font-size: 18px; font-weight: 700; }
  .route-country { font-size: 12px; color: #999; }
  .route-arrow { font-size: 24px; color: #ccc; }
  .status { display: inline-block; padding: 4px 14px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; border: 1px solid #1a1a1a; }
  .footer { margin-top: 48px; padding-top: 20px; border-top: 1px solid #e5e5e5; font-size: 11px; color: #999; display: flex; justify-content: space-between; }
  .print-btn { position: fixed; top: 20px; right: 20px; padding: 10px 24px; background: #1a1a1a; color: #fff; border: none; font-size: 14px; font-weight: 600; cursor: pointer; font-family: inherit; }
  .print-btn:hover { background: #333; }
</style>
</head>
<body>
<button class="print-btn no-print" onclick="window.print()">Сохранить PDF</button>

<div class="header">
  <div>
    <div class="logo">CARGOFLOW</div>
    <div class="logo-sub">International Logistics Solutions</div>
  </div>
  <div class="doc-meta">
    <strong>{$docTitleE}</strong>
    Дата документа: {$docDate}<br>
    Номер: DOC-{$appId}-{$docId}
  </div>
</div>

<h2>Заявка №{$appId}</h2>

<div class="route">
  <div>
    <div class="route-city">{$e($appData['city_from'] ?? '—')}</div>
    <div class="route-country">{$e($appData['country_from'] ?? '')}</div>
  </div>
  <div class="route-arrow">→</div>
  <div>
    <div class="route-city">{$e($appData['city_to'] ?? '—')}</div>
    <div class="route-country">{$e($appData['country_to'] ?? '')}</div>
  </div>
</div>

<div class="row"><span class="row-label">Дата заявки</span><span class="row-value">{$appDate}</span></div>
<div class="row"><span class="row-label">Тип перевозки</span><span class="row-value">{$transport}</span></div>
<div class="row"><span class="row-label">Тип груза</span><span class="row-value">{$cargo}</span></div>
<div class="row"><span class="row-label">Вес</span><span class="row-value">{$weight} кг</span></div>
<div class="row"><span class="row-label">Объём</span><span class="row-value">{$volume} м³</span></div>
<div class="row"><span class="row-label">Статус</span><span class="row-value"><span class="status">{$status}</span></span></div>

<h2>Клиент</h2>
<div class="row"><span class="row-label">ФИО</span><span class="row-value">{$client}</span></div>
<div class="row"><span class="row-label">Компания</span><span class="row-value">{$company}</span></div>
<div class="row"><span class="row-label">Email</span><span class="row-value">{$email}</span></div>
<div class="row"><span class="row-label">Телефон</span><span class="row-value">{$phone}</span></div>

<div class="footer">
  <span>CargoFlow — цифровая логистика без границ</span>
  <span>cargoflow.ru | +7 (495) 123-45-67</span>
</div>
</body>
</html>
HTML;
    exit;
}

// ═══ PROFILE UPDATE ═══
if ($action === 'update_profile') {
    $fullName = trim((string)($body['full_name'] ?? ''));
    $company  = trim((string)($body['company'] ?? ''));
    $phone    = trim((string)($body['phone'] ?? ''));

    if ($db) {
        $stmt = $db->prepare('UPDATE users SET full_name = ?, company = ?, phone = ? WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('sssi', $fullName, $company, $phone, $userId);
            $stmt->execute();
            $stmt->close();
        }
        $db->close();
        jsonOut(['status' => 'ok', 'message' => 'Профиль обновлён']);
    }

    // Демо
    jsonOut(['status' => 'ok', 'demo' => true, 'message' => 'Профиль обновлён (демо-режим)']);
}

jsonOut(['status' => 'error', 'message' => 'Неизвестное действие'], 400);
