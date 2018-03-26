<?php

namespace dw\core;

use dw\core\Connection;

/**
 * Class Validator
 *
 * Реализовано:
 * (+) email
 * (+) integer
 * (+) string
 * (+) required
 * (+) boolean
 * (+) in - строгое соответствие одному из указанных в массиве величин
 * (+) parent - проверка по регулярному выражению
 *
 * В планах:
 * unique - уникальный элемент в такой-то таблице и такой-то колонке
 *
 *
 * В возможную перспективу:
 * captcha - генерацию и проверку капчи буду делать отдельным классом
 * compare
 * date
 * datetime
 * time
 * double
 * exist
 * file
 * filter
 * image
 * match
 * safe
 * trim
 * url
 * ip
 *
 */
class Validator
{
    /**
     * Имя проверяемого поля
     * @var
     */
    private static $name;

    /**
     * Массив ошибок
     * @var
     */
    private static $errors;

    /**
     * БД
     * @var object dw\core\Connection;
     */
    private static $db;

    /**
     * Принимает валидируемый параметр и дополнительные параметры, и проверяет его на основе правил
     *
     * @param $name - имя поля, необходимо для корректного сообщения об ошибке
     * @param $param - валидируемая переменная
     * @param $rules - правила валидации
     * @param null $table - только для проверки типа unique - таблица для проверки
     * @param null $column - только для проверки типа unique - колонка для проверки
     * @return bool
     */
    public static function check($name, $param, $rules, $table = null, $column = null)
    {
        self::$name = $name;
        self::$errors = null;

        // В текущем варианте перебора параметров возвращается ошибка при первом же несоблюдении какого-либо правила
        // Можно доработать, чтобы проверялись все правила, и возвращался сразу список несоответствий
        foreach ($rules as $key => $value) {

            if ($table === null) {
                if (is_int($key)) {
                    if (!self::$value($param)) return false;
                } else {
                    if (!self::$key($param, $value)) return false;
                }
            } else {
                if (!self::unique($param, $table, $column)) return false;
            }

        }

        return true;
    }

    public static function getError()
    {
        $message = '';

        if (self::$errors) {
            foreach (self::$errors as $error) {
                $message .= $error;
            }
        }

        return $message;
    }

    private static function string($param)
    {
        if (is_string($param)) return true;
        self::$errors[] = self::$name . ' должен быть строкой';
        return false;
    }

    private static function int($param)
    {
        if (is_int($param)) return true;
        self::$errors[] = self::$name . ' должен быть числом';
        return false;
    }

    private static function min($param, $value)
    {
        if (strlen($param) >= $value) return true;
        self::$errors[] = self::$name . ' должен быть больше или равен ' . $value . ' символов';
        return false;
    }

    private static function max($param, $value)
    {
        if (strlen($param) <= $value) return true;
        self::$errors[] = self::$name . ' должен быть меньше или равен ' . $value . ' символов';
        return false;
    }

    private static function mail($param)
    {
        if (filter_var($param, FILTER_VALIDATE_EMAIL)) return true;
        self::$errors[] = 'указана некорректная почта';
        return false;
    }

    private static function required($param)
    {
        if ($param === null || $param === '') {
            self::$errors[] = self::$name . ' не может быть пустым';
            return false;
        }
        return true;
    }

    private static function boolean($param)
    {
        if (is_bool($param)) return true;
        self::$errors[] = self::$name . ' должен быть логическим типом';
        return false;
    }

    private static function in($param, $value)
    {
        foreach ($value as $val) {
            if ($param === $val) return true;
        }

        self::$errors[] = self::$name . ' указан некорректно';
        return false;
    }

    private static function parent($param, $value)
    {
        if (preg_match($value, $param)) return true;
        self::$errors[] = self::$name . ' указан некорректно';
        return false;
    }

    private static function unique($param, $table, $column)
    {
        self::connectDB();
        if (!self::$db->query("SELECT $column FROM $table WHERE $column = ?", [['type' => 's', 'value' => $param]])) return true;
        self::$errors[] = 'Указанный ' . self::$name . ' уже существует, выберите другой';
        return false;
    }

    private static function connectDB()
    {
        if (!self::$db) self::$db = Connection::Instance();
    }
}
