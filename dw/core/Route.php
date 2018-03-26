<?php

namespace dw\core;

class Route
{
    // TODO Подумал, что с простотой роутинга переборщил, по этому его в будущем доработаю его, добавив валидацию
    // TODO Впрочем, текущий сохраню - потом потестирую, какая будет разница в производительности
    /**
     * Данный роутинг подразумевает то, что страницы на сайте могут быть ТОЛЬКО следующего вида:
     *
     * site.ru/ - Запускается MainController с методом index
     * site.ru/page/ - Запускается PageController с методом index
     * site.ru/page/10 - Запускается PageController с методом index, в который передается параметр 10
     * site.ru/page/edit/10 - Запускается PageController с методом edit в который передается параметр 10
     *
     * Для простоты и скорости работы роутинга, валидация данных, если она необходима, осуществляется на
     * стороне контроллера
     */
    public static function go()
    {
        $controller = 'MainController';
        $action = 'index';
        $param = null;

        $routes = explode('/', $_SERVER['REQUEST_URI']);

        if (!empty($routes[1])) {
            $controller = ucfirst($routes[1]).'Controller';
        }

        if (!empty($routes[3])) {
            $param = $routes[3];

            if (!empty($routes[2])) {
                $action = $routes[2];
            }
        } else {
            if (!empty($routes[2])) {
                $param = $routes[2];
            }
        }

        // Подгружаем контроллер
        // TODO Данную проверку можно удалить для производительности
        // TODO (напомю, задача перед данным микрофреймворком - максимальная производительность в рамках ООП)
        if (file_exists(__DIR__.'/../controllers/'.$controller.'.php')) {

            $loadController = 'dw\\controllers\\'.$controller;
            $class = new $loadController();

            // Проверяем, существует ли такой метод в контроллере
            // TODO Данную проверку можно удалить для производительности
            if (method_exists($class, $action)) {
                if ($param === null) {
                    echo $class->$action();
                } else {
                    echo $class->$action($param);
                }
            } else {
                throw new Exception('Отсутствует метод '.$action.' в контроллере: '.$loadController);
            }

        } else {
            throw new Exception('Контроллер '.$controller.' не найден');
        }
    }
}

