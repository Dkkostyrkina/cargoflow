<?php

// Пример конфигурации подключения к базе данных.
// Скопируйте этот файл как db.php и укажите свои параметры.

const CF_DB_HOST = '127.0.0.1';
const CF_DB_NAME = 'cargoflow';
const CF_DB_USER = 'root';
const CF_DB_PASS = '';
const CF_DB_CHARSET = 'utf8mb4';

function cf_get_db(): ?mysqli
{
    mysqli_report(MYSQLI_REPORT_OFF);

    try {
        $conn = @new mysqli(CF_DB_HOST, CF_DB_USER, CF_DB_PASS, CF_DB_NAME);
        if ($conn->connect_error) {
            return null;
        }
        $conn->set_charset(CF_DB_CHARSET);
        return $conn;
    } catch (Throwable $e) {
        return null;
    }
}

