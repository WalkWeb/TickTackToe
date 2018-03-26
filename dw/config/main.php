<?php

/**
 * Включение/выключение сайта и компонентов
 *
 * true - включено, false - выключено
 */

define('SITE', true);     // Включение/выключение сайта
define('POSTING', true);  // Включение/выключение постов на сайте
define('COMMENTS', true); // Включение/выключение комментариев на сайте
define('CHAT', true);     // Включение/выключение чата

/**
 * Параметры подключения к БД
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'minigame');
define('DB_USER', 'minigame');
define('DB_PASSWORD', '12345');

/**
 * Режим разработчика - true / продакшена - false
 */

define('DEV', true);

// TODO Добавить соль для паролей

// TODO Подумать над тем, нужны ли настройки часового пояса и языка
