<?php

namespace dw\core;

abstract class Controller
{
    /** @var string Месторасположение дирректории с видами */
    private $dir = __DIR__.'/../views/';

    /** @var string Месторасположение дирректории, где хранится html-кеш */
    private $cache = __DIR__.'/../cache/html/';

    /** @var string Шаблон (по умолчанию используется шаблон "old") */
    protected $templates = 'old/';

    /** @var string Title */
    protected $title = '';

    /** @var string Description */
    protected $description = '';

    /** @var string Keywords */
    protected $keywords = '';

    /** @var mixed Текущее время (используется при работе с кэшем */
    protected $time;

    /** @var bool Данная настройка отвечает за то, рендерить ли шаблон в общем слое (true) или отдельно (false) */
    protected $layout = true;

    /**
     * Задает текущее время
     *
     * Controller constructor.
     */
    public function __construct()
    {
        $this->time = microtime(true);
    }

    /**
     * Объединяет шаблон страницы с данными и возвращает его
     *
     * @param $view
     * @param array $params
     * @return string
     */
    public function render($view, $params = [])
    {
        extract($params, EXTR_OVERWRITE);

        ob_start();

        // TODO Добавить проверку на наличие вида

        require($this->dir.$this->templates.$view.'.php');

        // Помещаем страницу в общий макет сайта
        if ($this->layout) {
            $content = ob_get_clean();
            ob_start();
            // TODO Добавить проверку на наличие слоя
            require($this->dir.$this->templates.'layout/main.php');
        }

        return ob_get_clean();
    }

    /**
     * Проверяет наличие кэша по его имени (и id если есть)
     *
     * @param $name
     * @param null $id
     * @param $time
     * @return bool|string
     */
    protected function checkCache($name, $id = null, $time)
    {
        if ($id) {
            $name = $name.'_'.$id;
        }

        // TODO Потестировать работу кэша. Вроде работает, но у меня какие-то сомнения

        // Проверяем, есть ли кэш
        if (file_exists($this->cache . $name)) {

            // Проверяем, не просрочен ли он
            if (!($time > 0) || (($this->time - $time) < filemtime($this->cache . $name))) {
                return file_get_contents($this->cache . $name);
            }
        }

        return false;
    }

    /**
     * Создает кэш
     *
     * @param $name
     * @param $content
     * @param null $id
     */
    protected function createCache($name, $content, $id = null)
    {
        if ($id) {
            $name = $name.'_'.$id;
        }

        $file = fopen($this->cache . $name, 'w');
        fwrite($file, $content.'=кэш=');
        fclose($file);
    }

    /**
     * Удаляет кэш
     *
     * @param null $name
     */
    protected function deleteCache($name = null)
    {
        if ($name) {
            unlink($this->cache . $name);
        }
    }

    /**
     * Проверяет наличие кеша и его актуальность - если есть - возвращает, если нет - выполняет метод
     * создающий html-контент, создает кэш, возвращает контент.
     *
     * @param $name
     * @param null $id
     * @param int $time
     * @return bool|string
     */
    protected function cacheHTML($name, $id = null, $time = 0)
    {
        $content = $this->checkCache($name, $id, $time);

        if ($content) {
            return $content;
        }

        $funcName = ucfirst($name);

        $content = $this->$funcName();

        $this->createCache($name, $content, $id);

        return $content;
    }
}
