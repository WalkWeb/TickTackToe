<?php

namespace dw\core;

class Tools
{
    /**
     * Генерирует случайную строку
     *
     * @param integer $length
     * @return string
     */
    public static function getRandomName($length = 15)
    {
        $chars = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $numChars = strlen($chars);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= substr($chars, rand(1, $numChars) - 1, 1);
        }
        return $string;
    }
}