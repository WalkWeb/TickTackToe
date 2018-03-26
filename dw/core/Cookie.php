<?php

namespace dw\core;

class Cookie
{
    public static function setCookie($name, $value, $time)
    {
        setcookie($name, $value, time() + $time);
    }

    public static function getCookie($name)
    {
        if (isset($_COOKIE[$name])) return $_COOKIE[$name];
        return false;
    }

    public static function checkCookie($name)
    {
        if (isset($_COOKIE[$name])) return true;
        return false;
    }
}
