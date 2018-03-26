<?php

// Подключаем конфиг
include_once(__DIR__.'/../config/main.php');

// Подключение вспомогательных функций
include_once(__DIR__.'/../core/Support.php');

// Если это режим разработчика - замеряем время выполнения скрипта и расход памяти
if (SITE && DEV) {
    include_once(__DIR__.'/../core/Runtime.php');
    \dw\core\Runtime::start();
}

// Проверяем, включен ли сайт
if (SITE) {

    // Подключаем автозагрузчик и задаем базовый namespace
    include_once(__DIR__.'/../core/autoload/autoload.php');
    $loader = new \core\autoload\Psr4Autoloader;
    $loader->register();
    $loader->addNamespace('dw', __DIR__.'/../');

    // Подключаемся к базе
    // а впрочем, нужно ли оно...
    //$db = new \dw\core\Connection();

    // Подключаем и запускаем роутинг
    \dw\core\Route::go();

    // Мой проект подразумевает то, что любая страница будет требовать проверку на логин, соответственно добавляем это в ядро
    // TODO Проверка на то, залогинен ли пользователь или нет

} else {
    // Шаблон-заглушка о технических работах на сайте
    include_once(__DIR__.'/../views/base/technical_wokr.php');
}


