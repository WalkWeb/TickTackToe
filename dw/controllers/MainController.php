<?php

namespace dw\controllers;

use dw\core\Controller;
use dw\models\Game;
use dw\models\GameField;
use dw\models\GameLogs;

class MainController extends Controller
{
    /**
     * Возвращает главную страницу. Обрабатывает пост запрос.
     *
     * @return string
     */
    public function index()
    {
        $model = new Game();
        $field = new GameField();

        if ($_POST) {
            $game = $model->act($_POST);
            $table = ($game) ? $field->getTable($game) : null;
            $logs = GameLogs::getLogs();

            // Отключаем рендеринг в общем шаблоне
            $this->layout = false;

            $content = $this->render('game', [
                'game' => $game,
                'table' => $table,
                'logs' => $logs,
            ]);

            return  '{"success":"1","content":"' . str_replace('"', '\\"', $content) . '","cellAttackBot":"' . $game['cellAttackBot'] . '","act":"' . $game['act'] . '","status_id":"' . $game['status_id'] . '","status_name":"' . $game['status'] . '"}';
        }

        $game = $model->getGameInfo();
        $table = ($game) ? $field->getTable($game) : null;
        $logs = GameLogs::getLogs();

        return $this->render('game', [
            'game' => $game,
            'table' => $table,
            'logs' => $logs,
        ]);
    }
}
