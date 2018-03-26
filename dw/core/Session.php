<?php

namespace dw\core;

use dw\core\Tools;

class Session
{
    /**
     * Создает и возвращает CSRF-токен для формы.
     *
     * Это самый простой вариант защиты от CSRF-атак, когда создается один простой токен на всю длину сессии.
     * В будущем защиту от CSRF нужно будет улучшить.
     *
     * @return string
     */
    public static function getCsrfToken()
    {
        self::startSession();

        if (!isset($_SESSION['csrf'])) {
            $string = Tools::getRandomName();
            self::setSession('csrf', $string);
        } else {
            $string = $_SESSION['csrf'];
        }

        return $string;
    }

    /**
     * Проверяет CSRF-токен
     *
     * @param $token
     * @return bool
     */
    public static function checkCsrfToken($token)
    {
        self::startSession();

        if (hash_equals($_SESSION['csrf'], $token)) {
            return true;
        }
        return false;
    }

    /**
     * Запускает сессию, если она еще не запущена
     */
    private static function startSession()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * Устанавливает ключ => значение в сессию
     *
     * @param $key
     * @param $value
     */
    private static function setSession($key, $value)
    {
        $_SESSION[$key] = $value;
    }



}
