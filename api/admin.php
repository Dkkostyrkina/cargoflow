<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/security.php';

header('Content-Type: application/json; charset=utf-8');
cf_security_headers();

cf_rate_limit('admin', 120, 60);

$dbPath = __DIR__ . '/../config/db.php';
if (file_exists($dbPath)) {
    require_once $dbPath;
} else {
    require_once __DIR__ . '/../config/db.example.php';
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

function requireAdmin(): void {
    if (empty($_SESSION['user_id'])) {
        jsonOut(['status' => 'error', 'message' => 'Не авторизован'], 401);
    }
    if (($_SESSION['user_role'] ?? '') !== 'admin') {
        jsonOut(['status' => 'error', 'message' => 'Доступ запрещён'], 403);
    }
}

$raw = file_get_contents('php://input') ?: '';
$body = [];
if ($raw && str_starts_with($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
    $body = json_decode($raw, true) ?: [];
}

$action = $_GET['action'] ?? ($body['action'] ?? '');

// CSRF-токен: выдача (не требует авторизации)
if ($action === 'csrf') {
    jsonOut(['status' => 'ok', 'token' => cf_csrf_token()]);
}

// Все POST-запросы требуют CSRF
$csrfExempt = ['stats', 'applications', 'users', 'analytics', 'csrf'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !in_array($action, $csrfExempt, true)) {
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($body['csrf_token'] ?? null);
    if (!cf_csrf_verify($csrfToken)) {
        jsonOut(['status' => 'error', 'message' => 'Недействительный CSRF-токен. Обновите страницу.'], 403);
    }
}

// Все действия требуют прав admin
requireAdmin();

$db = getDb();
$demo = !$db;

// ═══ STATS ═══
if ($action === 'stats') {
    if ($db) {
        $stats = [
            'total_applications' => 0,
            'new_today'          => 0,
            'total_users'        => 0,
            'popular_direction'  => '—',
        ];

        $r = $db->query('SELECT COUNT(*) AS cnt FROM applications');
        if ($r) { $stats['total_applications'] = (int)$r->fetch_assoc()['cnt']; }

        $r = $db->query("SELECT COUNT(*) AS cnt FROM applications WHERE DATE(created_at) = CURDATE()");
        if ($r) { $stats['new_today'] = (int)$r->fetch_assoc()['cnt']; }

        $r = $db->query("SELECT COUNT(*) AS cnt FROM users WHERE role = 'client'");
        if ($r) { $stats['total_users'] = (int)$r->fetch_assoc()['cnt']; }

        $r = $db->query(
            "SELECT CONCAT(country_from, ' → ', country_to) AS dir, COUNT(*) AS cnt
             FROM applications GROUP BY dir ORDER BY cnt DESC LIMIT 1"
        );
        if ($r) {
            $row = $r->fetch_assoc();
            if ($row) { $stats['popular_direction'] = $row['dir']; }
        }

        $db->close();
        jsonOut(['status' => 'ok', 'stats' => $stats]);
    }

    // Демо
    jsonOut([
        'status' => 'ok',
        'demo'   => true,
        'stats'  => [
            'total_applications' => 247,
            'new_today'          => 8,
            'total_users'        => 63,
            'popular_direction'  => 'Китай → Россия',
        ],
    ]);
}

// ═══ ALL APPLICATIONS ═══
if ($action === 'applications') {
    if ($db) {
        $where  = 'WHERE 1=1';
        $params = [];
        $types  = '';

        $filterStatus = isset($_GET['status']) ? (int)$_GET['status'] : 0;
        $filterFrom   = $_GET['date_from'] ?? '';
        $filterTo     = $_GET['date_to'] ?? '';
        $filterUserId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

        if ($filterStatus > 0) {
            $where   .= ' AND a.status = ?';
            $params[] = $filterStatus;
            $types   .= 'i';
        }
        if ($filterFrom !== '') {
            $where   .= ' AND DATE(a.created_at) >= ?';
            $params[] = $filterFrom;
            $types   .= 's';
        }
        if ($filterTo !== '') {
            $where   .= ' AND DATE(a.created_at) <= ?';
            $params[] = $filterTo;
            $types   .= 's';
        }
        if ($filterUserId > 0) {
            $where   .= ' AND a.user_id = ?';
            $params[] = $filterUserId;
            $types   .= 'i';
        }

        $sql = "SELECT a.id, a.user_id, u.full_name, u.email AS user_email, u.company,
                       a.country_from, a.city_from, a.country_to, a.city_to,
                       a.transport_type, a.cargo_type, a.weight_kg, a.volume_cbm,
                       a.status, a.created_at, a.updated_at
                FROM applications a
                LEFT JOIN users u ON a.user_id = u.id
                $where
                ORDER BY a.created_at DESC";

        $stmt = $db->prepare($sql);
        $apps = [];
        if ($stmt) {
            if ($types !== '') {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $row['id']         = (int)$row['id'];
                $row['user_id']    = (int)$row['user_id'];
                $row['status']     = (int)$row['status'];
                $row['weight_kg']  = $row['weight_kg']  ? (float)$row['weight_kg']  : null;
                $row['volume_cbm'] = $row['volume_cbm'] ? (float)$row['volume_cbm'] : null;
                $row['route']      = $row['city_from'] . ' → ' . $row['city_to'];
                $apps[] = $row;
            }
            $stmt->close();
        }
        $db->close();
        jsonOut(['status' => 'ok', 'applications' => $apps]);
    }

    // Демо
    jsonOut([
        'status'       => 'ok',
        'demo'         => true,
        'applications' => [
            ['id' => 1048, 'user_id' => 1, 'full_name' => 'Иванов Алексей Петрович', 'user_email' => 'demo@cargoflow.ru', 'company' => 'ООО «ТрансЛогистик»', 'route' => 'Шанхай → Москва', 'country_from' => 'Китай', 'city_from' => 'Шанхай', 'country_to' => 'Россия', 'city_to' => 'Москва', 'transport_type' => 'sea', 'cargo_type' => 'Электроника', 'weight_kg' => 2400, 'volume_cbm' => 14.5, 'status' => 4, 'created_at' => '2026-02-28 10:30:00', 'updated_at' => '2026-03-01 09:00:00'],
            ['id' => 1047, 'user_id' => 3, 'full_name' => 'Петрова Мария Сергеевна', 'user_email' => 'petrovams@mail.ru', 'company' => 'ИП Петрова М.С.', 'route' => 'Стамбул → Санкт-Петербург', 'country_from' => 'Турция', 'city_from' => 'Стамбул', 'country_to' => 'Россия', 'city_to' => 'Санкт-Петербург', 'transport_type' => 'road', 'cargo_type' => 'Текстиль', 'weight_kg' => 800, 'volume_cbm' => 6.2, 'status' => 3, 'created_at' => '2026-02-25 14:15:00', 'updated_at' => '2026-02-26 11:00:00'],
            ['id' => 1046, 'user_id' => 2, 'full_name' => 'Сидоров Дмитрий Олегович', 'user_email' => 'sidorov_d@corp.ru', 'company' => 'АО «ГлобалТрейд»', 'route' => 'Гуанчжоу → Владивосток', 'country_from' => 'Китай', 'city_from' => 'Гуанчжоу', 'country_to' => 'Россия', 'city_to' => 'Владивосток', 'transport_type' => 'sea', 'cargo_type' => 'Оборудование', 'weight_kg' => 5200, 'volume_cbm' => 28.0, 'status' => 5, 'created_at' => '2026-02-22 09:00:00', 'updated_at' => '2026-03-02 15:20:00'],
            ['id' => 1045, 'user_id' => 4, 'full_name' => 'Козлова Анна Владимировна', 'user_email' => 'a.kozlova@pharma.com', 'company' => 'ООО «ФармаИмпорт»', 'route' => 'Дубай → Москва', 'country_from' => 'ОАЭ', 'city_from' => 'Дубай', 'country_to' => 'Россия', 'city_to' => 'Москва', 'transport_type' => 'air', 'cargo_type' => 'Фармацевтика', 'weight_kg' => 120, 'volume_cbm' => 0.8, 'status' => 2, 'created_at' => '2026-02-20 11:45:00', 'updated_at' => '2026-02-21 08:30:00'],
            ['id' => 1044, 'user_id' => 1, 'full_name' => 'Иванов Алексей Петрович', 'user_email' => 'demo@cargoflow.ru', 'company' => 'ООО «ТрансЛогистик»', 'route' => 'Пекин → Екатеринбург', 'country_from' => 'Китай', 'city_from' => 'Пекин', 'country_to' => 'Россия', 'city_to' => 'Екатеринбург', 'transport_type' => 'rail', 'cargo_type' => 'Автокомпоненты', 'weight_kg' => 3400, 'volume_cbm' => 18.0, 'status' => 1, 'created_at' => '2026-02-18 16:30:00', 'updated_at' => '2026-02-18 16:30:00'],
            ['id' => 1043, 'user_id' => 5, 'full_name' => 'Новиков Игорь Павлович', 'user_email' => 'novikov.ip@logistic.ru', 'company' => 'ООО «РусКарго»', 'route' => 'Хошимин → Новороссийск', 'country_from' => 'Вьетнам', 'city_from' => 'Хошимин', 'country_to' => 'Россия', 'city_to' => 'Новороссийск', 'transport_type' => 'sea', 'cargo_type' => 'FMCG', 'weight_kg' => 4800, 'volume_cbm' => 22.5, 'status' => 5, 'created_at' => '2026-02-15 08:00:00', 'updated_at' => '2026-02-28 12:00:00'],
            ['id' => 1042, 'user_id' => 3, 'full_name' => 'Петрова Мария Сергеевна', 'user_email' => 'petrovams@mail.ru', 'company' => 'ИП Петрова М.С.', 'route' => 'Берлин → Москва', 'country_from' => 'Германия', 'city_from' => 'Берлин', 'country_to' => 'Россия', 'city_to' => 'Москва', 'transport_type' => 'road', 'cargo_type' => 'Промышленные товары', 'weight_kg' => 1200, 'volume_cbm' => 8.0, 'status' => 1, 'created_at' => '2026-04-27 08:15:00', 'updated_at' => '2026-04-27 08:15:00'],
            ['id' => 1049, 'user_id' => 4, 'full_name' => 'Козлова Анна Владимировна', 'user_email' => 'a.kozlova@pharma.com', 'company' => 'ООО «ФармаИмпорт»', 'route' => 'Токио → Москва', 'country_from' => 'Япония', 'city_from' => 'Токио', 'country_to' => 'Россия', 'city_to' => 'Москва', 'transport_type' => 'air', 'cargo_type' => 'Медоборудование', 'weight_kg' => 350, 'volume_cbm' => 2.1, 'status' => 1, 'created_at' => '2026-04-27 09:40:00', 'updated_at' => '2026-04-27 09:40:00'],
        ],
    ]);
}

// ═══ UPDATE STATUS ═══
if ($action === 'update_status') {
    $appId    = (int)($body['id'] ?? 0);
    $newStatus = (int)($body['status'] ?? 0);

    if ($appId <= 0 || $newStatus < 1 || $newStatus > 5) {
        jsonOut(['status' => 'error', 'message' => 'Некорректные параметры'], 400);
    }

    if ($db) {
        $stmt = $db->prepare('UPDATE applications SET status = ? WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('ii', $newStatus, $appId);
            if ($stmt->execute()) {
                $stmt->close();
                $db->close();
                jsonOut(['status' => 'ok', 'message' => 'Статус обновлён']);
            }
            $stmt->close();
        }
        $db->close();
        jsonOut(['status' => 'error', 'message' => 'Ошибка обновления'], 500);
    }

    // Демо
    jsonOut(['status' => 'ok', 'demo' => true, 'message' => 'Статус обновлён (демо-режим)']);
}

// ═══ USERS ═══
if ($action === 'users') {
    if ($db) {
        $stmt = $db->prepare(
            "SELECT id, email, full_name, company, phone, role, email_verified, created_at
             FROM users
             ORDER BY created_at DESC"
        );
        $users = [];
        if ($stmt) {
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $row['id']             = (int)$row['id'];
                $row['email_verified'] = (int)$row['email_verified'];
                $users[] = $row;
            }
            $stmt->close();
        }
        $db->close();
        jsonOut(['status' => 'ok', 'users' => $users]);
    }

    // Демо
    jsonOut([
        'status' => 'ok',
        'demo'   => true,
        'users'  => [
            ['id' => 1, 'email' => 'demo@cargoflow.ru',     'full_name' => 'Иванов Алексей Петрович',    'company' => 'ООО «ТрансЛогистик»',   'phone' => '+7 (495) 123-45-67', 'role' => 'client', 'email_verified' => 1, 'created_at' => '2025-11-15 10:00:00'],
            ['id' => 2, 'email' => 'admin@cargoflow.ru',    'full_name' => 'Администратор',               'company' => 'CargoFlow',              'phone' => '+7 (495) 000-00-00', 'role' => 'admin',  'email_verified' => 1, 'created_at' => '2025-11-01 09:00:00'],
            ['id' => 3, 'email' => 'petrovams@mail.ru',     'full_name' => 'Петрова Мария Сергеевна',     'company' => 'ИП Петрова М.С.',        'phone' => '+7 (812) 456-78-90', 'role' => 'client', 'email_verified' => 1, 'created_at' => '2025-12-03 14:20:00'],
            ['id' => 4, 'email' => 'a.kozlova@pharma.com',  'full_name' => 'Козлова Анна Владимировна',   'company' => 'ООО «ФармаИмпорт»',      'phone' => '+7 (495) 987-65-43', 'role' => 'client', 'email_verified' => 1, 'created_at' => '2026-01-10 08:45:00'],
            ['id' => 5, 'email' => 'novikov.ip@logistic.ru','full_name' => 'Новиков Игорь Павлович',      'company' => 'ООО «РусКарго»',         'phone' => '+7 (343) 222-33-44', 'role' => 'client', 'email_verified' => 0, 'created_at' => '2026-01-22 17:30:00'],
            ['id' => 6, 'email' => 'sidorov_d@corp.ru',     'full_name' => 'Сидоров Дмитрий Олегович',   'company' => 'АО «ГлобалТрейд»',       'phone' => '+7 (495) 111-22-33', 'role' => 'client', 'email_verified' => 1, 'created_at' => '2026-02-05 11:00:00'],
            ['id' => 7, 'email' => 'zakharov@trade.ru',     'full_name' => 'Захаров Виктор Михайлович',   'company' => 'ООО «ВостокТрейд»',      'phone' => '+7 (423) 555-66-77', 'role' => 'client', 'email_verified' => 1, 'created_at' => '2026-02-14 09:15:00'],
            ['id' => 8, 'email' => 'morozova_k@import.com', 'full_name' => 'Морозова Ксения Николаевна',  'company' => 'ИП Морозова К.Н.',       'phone' => '+7 (499) 333-44-55', 'role' => 'client', 'email_verified' => 0, 'created_at' => '2026-03-18 16:00:00'],
        ],
    ]);
}

// ═══ ANALYTICS ═══
if ($action === 'analytics') {
    if ($db) {
        // По месяцам (последние 12)
        $byMonth = [];
        $res = $db->query(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS cnt
             FROM applications
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY month ORDER BY month ASC"
        );
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $byMonth[] = ['month' => $row['month'], 'count' => (int)$row['cnt']];
            }
        }

        // По типу транспорта
        $byTransport = [];
        $res = $db->query(
            "SELECT transport_type, COUNT(*) AS cnt FROM applications GROUP BY transport_type ORDER BY cnt DESC"
        );
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $byTransport[] = ['type' => $row['transport_type'], 'count' => (int)$row['cnt']];
            }
        }

        // По статусу
        $byStatus = [];
        $res = $db->query(
            "SELECT status, COUNT(*) AS cnt FROM applications GROUP BY status ORDER BY status ASC"
        );
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $byStatus[] = ['status' => (int)$row['status'], 'count' => (int)$row['cnt']];
            }
        }

        $db->close();
        jsonOut([
            'status'      => 'ok',
            'by_month'    => $byMonth,
            'by_transport'=> $byTransport,
            'by_status'   => $byStatus,
        ]);
    }

    // Демо
    jsonOut([
        'status' => 'ok',
        'demo'   => true,
        'by_month' => [
            ['month' => '2025-05', 'count' => 8],
            ['month' => '2025-06', 'count' => 11],
            ['month' => '2025-07', 'count' => 14],
            ['month' => '2025-08', 'count' => 19],
            ['month' => '2025-09', 'count' => 16],
            ['month' => '2025-10', 'count' => 22],
            ['month' => '2025-11', 'count' => 25],
            ['month' => '2025-12', 'count' => 31],
            ['month' => '2026-01', 'count' => 28],
            ['month' => '2026-02', 'count' => 34],
            ['month' => '2026-03', 'count' => 29],
            ['month' => '2026-04', 'count' => 10],
        ],
        'by_transport' => [
            ['type' => 'sea',   'count' => 98],
            ['type' => 'road',  'count' => 64],
            ['type' => 'rail',  'count' => 42],
            ['type' => 'air',   'count' => 28],
            ['type' => 'multi', 'count' => 15],
        ],
        'by_status' => [
            ['status' => 1, 'count' => 34],
            ['status' => 2, 'count' => 28],
            ['status' => 3, 'count' => 19],
            ['status' => 4, 'count' => 41],
            ['status' => 5, 'count' => 125],
        ],
    ]);
}

jsonOut(['status' => 'error', 'message' => 'Неизвестное действие'], 400);
