<?php

namespace dw\models;

class GameBot
{
    /**
     * Игрок ходит "еденицами", бот - "десятками". Такой вариант выбран для того, чтобы простой суммой по нужным
     * ячейкам легко было определить победителя/и или того, кому для победы осталось один ход, например:
     * Сумма по линии = 3 (победил игрок)
     * Сумма по линии = 30 (победил бот)
     * Сумма по линии = 2 (игрок остался один ход в данную линию, чтобы победить)
     * Сумма по линии = 20 (боту остался один ход в данную линию, чтобы победить)
     *
     * Менять не рекомендуется, а если захочется иметь ввиду: цифра 10 также используется в классе GameField.
     * При изменении здесь необходимо будет поправить и так. Я не стал делать отдельную глобальную констатну.
     *
     * @var int
     */
    private $v = 10;

    /**
     * Линии, по которым определяется победитель
     * Установленны в классе Game
     *
     * @var array
     */
    private $lines;

    /**
     * GameBot constructor.
     * @param $lines array Линии по которым проверяется победитель, а также то, куда надо атаковать или что защищать
     */
    public function __construct($lines)
    {
        $this->lines = $lines;
    }

    /**
     * Ход бота
     *
     * @param $game array Данные по игре
     * @return null|int Возвращает номер ячейки, в которую походил бот. Null - бот никуда не походил
     */
    public function goBot($game)
    {
        $query = new GameQuery();
        $cell = null;

        // Проверяем линии, которые нужно атаковать
        if ($this->checkAttackLine($game['field'])) {
            $cell = $this->attackLine($game['id'], $this->checkAttackLine($game['field']));
        }
        // Проверяем линии, которые нужно защищать
        elseif ($this->checkDefenceLine($game['field'])) {
            $cell = $this->attackLine($game['id'], $this->checkDefenceLine($game['field']));
        }
        // Иначе делаем обычный ход. Первый приоритет - центральная ячейка, если она свободна - ходим в неё
        elseif (!$query->checkCell($game['id'], 5)) {
            $cell = 5;
            $query->moveHere($game['id'], $cell, $this->v);
        }
        // Если центральная точка занята - ходим в угловые (в текущем варианте проверка проходит два раза,
        // но в целом на механику это не влияет)
        elseif ($this->checkCornerCells($game['id'])) {
            $cell = $this->checkCornerCells($game['id']);
            $query->moveHere($game['id'], $cell, $this->v);
        }
        // Иначе - ходим в любую свободную ячейку
        else {
            $cell = $this->searchFreeCell($game['id']);
            $query->moveHere($game['id'], $cell, $this->v);
        }

        return $cell;
    }

    /**
     * Проверяет и возвращает линии, на которых бот почти победил, и соответственно нужно сделать оставшийся, победный
     * ход.
     *
     * @param $array array Массив игрового поля
     * @return bool|int Номер линии, которую нужно атаковать или false - атаковать нечего
     */
    public function checkAttackLine($array = null)
    {
        GameLogs::setLog('checkAttackLine: Начинается проверка атакующих линий');

        $i = 1;
        foreach ($this->lines as $line) {
            if (($array[$line[0]] + $array[$line[1]] + $array[$line[2]]) === 20) return $i;
            $i++;
        }

        GameLogs::setLog('checkAttackLine: Проверка закончилась - атаковать нечего');
        return false;
    }

    /**
     * Делает ход в выбранную линию. При этом сам проверяет, какая из ячеек в указанной линии свободна.
     *
     * В текущем варианте поля 3 на 3 логика довольно проста, и все варианты можно перебрать вручну. Но если бы
     * нужно было делать поле 5 на 5 или больше, и, к тому же, для победы требовалось занять не всю линию, а например,
     * только 4 точки - логика была бы совсем другой. Более интересной, и сложной.
     *
     * Может быть, когда будет нечем заняться - разовью этот проект до "крестики-нолики" на большом поле.
     *
     * @param $game_id int ID игры
     * @param $line int Атакуемая линия
     * @return null Возвращает номер ячейки, в которую походил бот. Null - бот никуда не походил
     */
    public function attackLine($game_id, $line)
    {
        $query = new GameQuery();
        $cell = null;


        if       (!$query->checkCell($game_id, $this->lines[$line][0])) {
            $cell = $this->lines[$line][0];
            $query->moveHere($game_id, $cell, $this->v);
        } elseif (!$query->checkCell($game_id, $this->lines[$line][1])) {
            $cell = $this->lines[$line][1];
            $query->moveHere($game_id, $cell, $this->v);
        } elseif (!$query->checkCell($game_id, $this->lines[$line][2])) {
            $cell = $this->lines[$line][2];
            $query->moveHere($game_id, $cell, $this->v);
        } else {
            GameLogs::setLog('attackLine: Непредвиденное событие');
        }

        return $cell;
    }

    /**
     * Проверяет и возвращает линии, на которых пользователь почти победил, и соответственно нужно сделать защитное
     * действие - сделать ход (атаковать) в данную линию.
     *
     * @param $array array Массив игрового поля
     * @return bool|int Номер линии, которую нужно защищать или false - защищать нечего
     */
    public function checkDefenceLine($array = null)
    {
        GameLogs::setLog('checkDefenceLine: Начинается проверка защиты линий');

        $i = 1;
        foreach ($this->lines as $line) {
            if (($array[$line[0]] + $array[$line[1]] + $array[$line[2]]) === 2) return $i;
            $i++;
        }

        GameLogs::setLog('checkDefenceLine: Проверка закончилась - защищать нечего');
        return false;
    }

    /**
     * Ищет свободные ячейки на поле
     *
     * @param $game_id int ID текущей игры
     * @return bool|int Номер свободной ячейки или false - свободных ячеек нет (если такой вариант произошел - сломалась
     * проверка на ничью - лимит ходов)
     */
    public function searchFreeCell($game_id)
    {
        $query = new GameQuery();
        GameLogs::setLog('searchFreeCell: Начинаем поиск любой свободной ячейки на поле');
        $i = 1;

        while ($i < 10) {
            if (!$query->checkCell($game_id, $i)) {
                GameLogs::setLog('searchFreeCell: Найдена свободная ячейка: ' . $i);
                return $i;
            }
            $i++;
        }

        GameLogs::setLog('searchFreeCell: Все ячейки на поле заняты');
        return false;
    }

    /**
     * Проверяет наличие свободных уловых ячеек и возвращает случайную из них, либо false - если свободных нет
     *
     * @param $game_id int ID игры
     * @return bool|mixed Номер свободной угловой ячейки или false - свободных угловых ячеек нет
     */
    public function checkCornerCells($game_id)
    {
        $query = new GameQuery();
        $cornerCells = [];

        GameLogs::setLog('checkCornerCells: Начинаем проверку угловых ячеек');

        if (!$query->checkCell($game_id, 1)) $cornerCells[] = 1;
        if (!$query->checkCell($game_id, 3)) $cornerCells[] = 3;
        if (!$query->checkCell($game_id, 7)) $cornerCells[] = 7;
        if (!$query->checkCell($game_id, 9)) $cornerCells[] = 9;

        // Если есть свободные ячейки - возвращаем случайную из них
        if ($cornerCells) {
            $cell = $cornerCells[array_rand($cornerCells)];
            GameLogs::setLog('checkCornerCells: Выбрана угловая чейка: ' . $cell);
            return $cell;
        }

        GameLogs::setLog('checkCornerCells: Нет свободных угловых ячеек');

        return false;
    }

}
