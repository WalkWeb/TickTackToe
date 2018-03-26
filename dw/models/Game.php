<?php

namespace dw\models;

use dw\core\Model;
use dw\core\Cookie;
use dw\core\Tools;

class Game extends Model
{
    /**
     * Информация по текущей игре. Используется практически в каждом методе, поэтому, чтобы не кидать туда-сюда, сделаем
     * свойством класса.
     *
     * @var array
     */
    private $game;

    /**
     * Ячейка в которую ходит бот
     *
     * @var
     */
    private $cellAttackBot;

    /**
     * Линии, которые необходимо заполнить для победы
     *
     * @var array
     */
    private $lines = [
        1 => [1, 2, 3],
        2 => [4, 5, 6],
        3 => [7, 8, 9],
        4 => [1, 4, 7],
        5 => [2, 5, 8],
        6 => [3, 6, 9],
        7 => [1, 5, 9],
        8 => [3, 5, 7],
    ];

    /**
     * При создании объекта игры сразу получаем информацию об игре на основе хеша. Если хеша нет - сразу создаем его.
     *
     * Game constructor.
     */
    // TODO В текущем варианте при начале новой игры И НАЧАЛЕ ИГРЫ БОТОМ данные об игре запрашиваются два раза.
    // TODO Ошибка во взаимодействии __construct() + getHash() + newGame(). Подумать над исправлением.
    public function __construct()
    {
        parent::__construct();
        $this->initGameInfo($this->getHash());
    }

    /**
     * Проверяет, есть ли хеш в куках - если есть - получает и возвращает, иначе - создает его, делает запись о новой
     * игре и возвращает хеш.
     *
     * @return bool|string Хеш игры
     */
    public function getHash()
    {
        if (Cookie::checkCookie('minigame')) {
            GameLogs::setLog('getHash: Куки с записью хеша игры присутствуют - брем их');
            return Cookie::getCookie('minigame');
        }
        GameLogs::setLog('getHash: Куки с записью хеша игры отсутствуют - отправляем запрос на создание новой игры');
        return $this->newGame();
    }

    /**
     * Получает информацию о текущей игре
     *
     * @return array|bool|\mysqli_result Массив данных текущей игры
     */
    public function getGameInfo()
    {
        return $this->game;
    }

    /**
     * Получает информацию о текущей игре по хешу
     *
     * @param $hash string Хеш игры
     * @return array|bool|\mysqli_result Массив данных по игре
     */
    public function initGameInfo($hash)
    {
        // Информация по игре получается двумя запросами. Пробовал сделать одним - такое извращение получается...
        $this->game = $this->db->query(
            'SELECT
                  `game`.`id`, `game`.`date`, `game`.`hash`, count(`stroke`.`id`) act,
                  `game`.`status_id`, `status`.`name` as status, max(`stroke`.`date`) act_date
                FROM `game`
                  LEFT JOIN `status` ON `game`.`status_id` = `status`.`id`
                  LEFT JOIN `stroke` ON `game`.`id` = `stroke`.`game_id`
                WHERE `game`.`hash` = ?',
            [['type' => 's', 'value' => $hash]], true);

        if (!$this->db->getError() === '') {
            GameLogs::setLog('initGameInfo: Ошибка получения первой части данных: ' . $this->db->getError());
        }

        // Если подсунули неправильные куки
        if (!$this->game) {
            GameLogs::setLog('initGameInfo: Не удалось получить информацию об игре по хешу, скорее всего он некорректен');
            return false;
        }

        $field = $this->db->query(
            'SELECT `stroke`.`cell`, `stroke`.`value`
            FROM `stroke`
            JOIN `game` ON `stroke`.`game_id` = `game`.`id`
            WHERE `game`.`hash` = ?',
            [['type' => 's', 'value' => $hash]]);

        if (!$this->db->getError() === '') {
            GameLogs::setLog('initGameInfo: Ошибка получения второй части данных: ' . $this->db->getError());
        }

        // Пересобираем результат игрового поля на более простой для работы вариант
        if ($field) {
            foreach($field as $val) {
                $this->game['field'][$val['cell']] = $val['value'];
            }
        }

        // Если есть атакуемая ботом ячейка - указываем её
        $this->game['cellAttackBot'] = ($this->cellAttackBot) ? $this->cellAttackBot : null;

        GameLogs::setLog('initGameInfo: Данные по игре успешно получены и обработаны');
        return $this->game;
    }

    /**
     * Обрабатываем действие пользователя
     *
     * @param $post $_POST запрос в котором находится информация по действию пользователя
     * @return array|bool|\mysqli_result
     */
    public function act($post)
    {
        $query = new GameQuery();

        if ($post['type'] === 'newGame') {
            GameLogs::setLog('act: Получен запрос на создание новой игры');
            $this->newGame();
        } elseif ($post['type'] === 'moveHere') {
            GameLogs::setLog('act: Получен запрос на ход в ячейку: ' . $post['here']);
            $this->actMoveHere($post['here']);
        } elseif ($post['type'] === 'endGame') {
            GameLogs::setLog('act: Получен запрос на завершение игры: "Сдаться"');
            // Отправляем запрос за "сдачу" - завершение игры. Если он прошел успешно - обновляем информацию об игре
            if ($query->updateGameStatus($this->game['id'], 5)) {
                $this->game['status_id'] = 5;
                $this->game['status'] = 'Игрок сдался';
            }
        } else {
            GameLogs::setLog('act: Отправлен неправильный тип запроса');
        }

        return $this->game;
    }

    /**
     * Обрабатываем действие пользователя - ход в определенную клетку
     *
     * @param $here string Номер ячейки в которую ходит пользователь
     */
    public function actMoveHere($here)
    {
        $query = new GameQuery();
        $bot = new GameBot($this->lines);

        // Отправляем запрос на ход игрока. Игрок ходит "еденицами", бот - "десятками"
        if ($query->moveHere($this->game['id'], (int) $here, 1)) {

            // Если он прошел успешно - обновляем информацию об игре и игровом поле
            $this->game = $this->initGameInfo($this->game['hash']);

            // Нужно проверить победителей и сразу указать тип игры
            if ($this->checkWinner()) {

                // Если победители есть (или ничья) - еще раз обновляем данные по игре
                $this->game = $this->initGameInfo($this->game['hash']);
            } else {
                // Если победителей нет - делает ход бот
                GameLogs::setLog('actMoveHere: Начинается ход бота');
                $this->cellAttackBot = $bot->goBot($this->game);

                // И еще раз обновляем данные
                $this->game = $this->initGameInfo($this->game['hash']);

                // Если ход сделан успешно - нужно проверить победителей и сразу указать тип игры
                if ($this->checkWinner()) {

                    // Если победители есть (или ничья) - еще раз обновляем данные по игре
                    $this->game = $this->initGameInfo($this->game['hash']);
                }
            }
        } else {
            GameLogs::setLog('actMoveHere: Во время хода игрока произошла неизвестная ошибка');
        }
    }

    /**
     * Создает запись о новой игре и устанавливает куки у пользователя
     * Также отправляет запрос на определение первого хода.
     *
     * @return bool|string хеш текущей игры|ошибку о создании новой игры
     */
    public function newGame()
    {
        GameLogs::setLog('newGame: Создаем новую игру');
        $hash = Tools::getRandomName(30);
        $this->db->query('INSERT INTO game (hash) VALUES (?)', [['type' => 's', 'value' => $hash]]);
        if ($this->db->getError() === '') {
            Cookie::setCookie('minigame', $hash, 3600);
            GameLogs::setLog('newGame: Новая игра создана, получаем данные по игре');
            $this->game = $this->initGameInfo($hash);
            if ($this->firstPlayer()) {
                GameLogs::setLog('newGame: Обновляем данные об игре после хода бота');
                $this->game = $this->initGameInfo($hash);
            }
            return $hash;
        } else {
            GameLogs::setLog('newGame: Ошибка при создании записи о новой игре в БД');
        }

        return false;
    }

    /**
     * Определяем кто ходит первый.
     * Если начинает бот - сразу делаем его ход
     *
     * @return bool True - первым походил бот, False - первым ходит пользователь
     */
    public function firstPlayer()
    {
        GameLogs::setLog('firstPlayer: Начинаем выбор первого игрока');

        if (rand(1, 2) === 2) {
            GameLogs::setLog('firstPlayer: Первым начинает ходить бот');
            $bot = new GameBot($this->lines);
            $this->cellAttackBot = $bot->goBot($this->game);
            GameLogs::setLog('firstPlayer: Возвращаем true - сигнал о том, что бот походил и нужно обвить данные по игре');
            return true;
        } else {
            GameLogs::setLog('firstPlayer: Первым начинает ходить игрок');
        }

        return false;
    }

    /**
     * Проверяет наличие победителей или окончание игры по лимиту ходов
     *
     * @return bool True - есть победители или ничья, false - игра продолжается
     */
    public function checkWinner()
    {
        GameLogs::setLog('checkWinner: Проверяем победителя. Текущий ход: ' . $this->game['act']);

        $query = new GameQuery();
        $i = 1;

        foreach ($this->lines as $line) {
            if ($this->game['field'][$line[0]] + $this->game['field'][$line[1]] + $this->game['field'][$line[2]] === 3) {
                GameLogs::setLog('checkWinner: Победил игрок');
                $query->updateGameStatus($this->game['id'], 2);
                return true;
            } elseif ($this->game['field'][$line[0]] + $this->game['field'][$line[1]] + $this->game['field'][$line[2]] === 30) {
                GameLogs::setLog('checkWinner: Победил бот');
                $query->updateGameStatus($this->game['id'], 3);
                return true;
            }
            $i++;
        }

        // Проверяем, не наступил ли лимит ходов - если все поле занято, а победителей нет - объявляем ничью
        if ($this->game['act'] === 9) {
            GameLogs::setLog('checkWinner: Достингут лимит ходов. Победителей нет. Ничья');
            $query->updateGameStatus($this->game['id'], 4);
            return true;
        }

        GameLogs::setLog('checkWinner: Победители отсутствуют - игра продолжается');

        // Победители отсутствуют - игра продолжается
        return false;
    }
}
