<?php

declare(strict_types=1);

// Параметры SMTP для отправки писем.
// Для локальной разработки используется ловушка писем MailHog/Mailpit
// (SMTP на 127.0.0.1:1025, веб-интерфейс на http://localhost:8025).
// Письма не уходят на реальную почту, а перехватываются для просмотра.
const CF_MAIL_HOST = '127.0.0.1';
const CF_MAIL_PORT = 1025;
const CF_MAIL_FROM = 'no-reply@cargoflow.local';
const CF_MAIL_FROM_NAME = 'CargoFlow';

/**
 * Отправляет письмо в формате HTML через SMTP без авторизации.
 * Возвращает true при успешной отправке.
 */
function cf_send_mail(string $to, string $subject, string $htmlBody): bool
{
    $errno = 0;
    $errstr = '';
    $fp = @fsockopen(CF_MAIL_HOST, CF_MAIL_PORT, $errno, $errstr, 5);
    if (!$fp) {
        return false;
    }

    $read = static function () use ($fp): string {
        $data = '';
        while (($line = fgets($fp, 515)) !== false) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $data;
    };

    $write = static function (string $cmd) use ($fp): void {
        fwrite($fp, $cmd . "\r\n");
    };

    $read(); // 220 приветствие сервера
    $write('EHLO localhost');
    $read();

    $write('MAIL FROM:<' . CF_MAIL_FROM . '>');
    $read();
    $write('RCPT TO:<' . $to . '>');
    $read();
    $write('DATA');
    $read(); // 354

    $headers = 'From: ' . cf_mime_encode(CF_MAIL_FROM_NAME) . ' <' . CF_MAIL_FROM . ">\r\n";
    $headers .= 'To: <' . $to . ">\r\n";
    $headers .= 'Subject: ' . cf_mime_encode($subject) . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";

    $body = preg_replace('/^\./m', '..', $htmlBody);
    $write($headers . "\r\n" . $body . "\r\n.");
    $read(); // 250 принято

    $write('QUIT');
    fclose($fp);

    return true;
}

/**
 * MIME-кодирование заголовка (для корректного отображения кириллицы).
 */
function cf_mime_encode(string $text): string
{
    return '=?UTF-8?B?' . base64_encode($text) . '?=';
}
