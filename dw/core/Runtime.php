<?php

namespace dw\core;

class Runtime
{
    private static $start;
    private static $mem_start;

    /**
     * Засекает время и расход памяти
     */
    public static function start()
    {
        self::$start = microtime(true);
        function convert($size)
        {
            $unit = ['b','kb','mb','gb','tb','pb'];
            return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
        }
        self::$mem_start = memory_get_peak_usage();
    }

    /**
     * Возвращает результат: сколько времени выполнялся скрипт и максимальный расход памяти во время выполнения
     *
     * @return string
     */
    public static function end()
    {
        return '<p>Время вывода страницы: '.(microtime(true) - self::$start).' сек.<br />
                Расход памяти: '.(convert(memory_get_peak_usage() - self::$mem_start)).'</p>';
    }
}
