<?php

namespace dw\core;

class Exception extends \Exception
{
    function __construct($message, $code = 0, Exception $previous = null)
    {
        set_exception_handler([$this, 'printException']);
        parent::__construct($message, $code, $previous);
    }

    function printException(\Exception $e)
    {
        echo '<h1>Ошибка</h1>
              <p>Ошибка ['.$this->code.']: ' . $e->getMessage() . '<br />
              Файл: ' . $e->getFile() . '<br />
              Строка: ' . $e->getLine() . '</p>';
    }
}
