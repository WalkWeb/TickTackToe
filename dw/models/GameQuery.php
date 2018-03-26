<?php

namespace dw\models;

use dw\core\Model;

class GameQuery extends Model
{
    /**
     * Проверяет, занята ли ячейка. True - занята, False - ячейка свободна
     *
     * @param $game_id int ID текущей игры
     * @param $cell int № проверяемой ячейки
     * @return bool True - ячейка занята, false - свободна
     */
    public function checkCell($game_id, $cell)
    {
        $query = $this->db->query(
            'SELECT `id` 
             FROM `stroke` 
             WHERE `game_id` = ? AND `cell` = ?',
            [['type' => 's', 'value' => $game_id], ['type' => 'i', 'value' => $cell]]
        );

        if ($query) return true;
        return false;
    }

    /**
     * Делает ход в выбранную ячейку
     *
     * @param $game_id int ID текущей игры
     * @param $cell int № проверяемой ячейки
     * @param $value int Устанавливаемое значение. 1 - ход игрока, 10 - бота
     * @return bool True - ход успешен, false - ошибка
     */
    public function moveHere($game_id, $cell, $value)
    {
        // TODO Добавить проверку по времени последнего хода - если прошло больше минуты - игра считается завершенной

        // Проверяем, пуста ли ячейка, и если да - делаем туда ход
        if (!$this->checkCell($game_id, $cell)) {
            $this->db->query(
                'INSERT INTO `stroke` (`game_id`, `cell`, `value`) VALUES (?, ?, ?)',
                [['type' => 'i', 'value' => $game_id], ['type' => 'i', 'value' => $cell], ['type' => 'i', 'value' => $value]]
            );

            // TODO Успешно выполненный запрос INSERT отдает false - поправить

            // Если нет ошибок - возвращаем true / проверяем победителя
            if ($this->db->getError() === '') {
                GameLogs::setLog('moveHere: успешно произведен ход в ячейку: ' . $cell . ', установлено значение: ' . $value);
                return true;
            } else {
                GameLogs::setLog('moveHere: охибка хода в ячейку: ' . $cell . ': ' . $this->db->getError());
            }
        }

        return false;
    }

    /**
     * Обновляет статус игры
     *
     * @param $game_id int ID текущей игры
     * @param $end_id int Новый статус игры
     * @return bool True - статус игры обновлен успешен, false - ошибка
     */
    public function updateGameStatus($game_id, $end_id)
    {
        $this->db->query('UPDATE `game` SET `status_id` = ? WHERE `id` = ?',
            [['type' => 'i', 'value' => $end_id], ['type' => 'i', 'value' => $game_id]]);
        // TODO Успешно выполненный запрос UPDATE отдает false - поправить
        return true;
    }
}
