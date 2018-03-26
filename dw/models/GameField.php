<?php

namespace dw\models;

/**
 * Создает html код игрового поля
 *
 * Class GameField
 * @package dw\models
 */
class GameField
{
    /**
     * Учитывая, что из данной простой игры 3х3 планирую в будущем сделать что-то поинтереснее (например поле 5х5),
     * сразу закладываю генерацию таблицы с указанием количества строк и столбцов. Тем более, что такой функционал
     * у меня уже был сделан в другом домашнем проекте.
     *
     * @param $game array Параметры игры
     * @return string HTML-код игрового поля
     */
    public function getTable($game)
    {
        $content = '';
        $xloc = 1;
        $yloc = 1;
        $maxXloc = 3;
        $maxYloc = 3;
        $i = 1;

        $content .= '<table class="game_table"><tr>';

        while ($i < 100) {

            $content .= '<td id="td' . $i . '">';
            if (isset($game['field'][$i]) && $game['field'][$i] === 1) {
                $content .= '<span id="cell' . $i . '" class="cross_here"></span>';
            } elseif (isset($game['field'][$i]) && $game['field'][$i] === 10) {
                $content .= '<span id="cell' . $i . '" class="pent_here"></span>';
            } else {
                $content .= ($game['status_id'] === 1) ? '<span class="move_here" onclick="moveHere(\'' . $i . '\')"></span>' : '';
            }
            $content .= '</td>';

            $xloc++;

            if ($xloc > $maxXloc) {
                $content .= '</tr>';
                $xloc = 1;
                $yloc++;
                if ($yloc > $maxYloc) {
                    break;
                }
            }

            $i++;
        }

        $content .= '</table>';

        return $content;
    }
}
