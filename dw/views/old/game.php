<?php

$this->title = 'Игра в крестики-нолики';

?>
<div id="game">
    <h1>Статус игры: <span id="status_footer"><?= ($game['status']) ? $game['status'] : 'ошибка'; ?></span></h1>
    <!--<p id="timer">00:25</p>-->
    <div class="gamebox">
        <div class="box_paladin">
            <div class="avabox">
                <img src="/images/paladin.jpg" id="paladin" class="<?= ($game['status_id'] === 1) ? 'avaimg_active' : 'avaimg'; ?>" alt="" />
                <p><b>Паладин Петрович</b></p>
                <div id="paladin_desk">
                    <?php
                    if ($game['status_id'] === 1) {
                        echo   '<p>ходит...</p>
                                <div class="cssload-container">
                                    <div class="cssload-whirlpool"></div>
                                </div>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="box_diablo">
            <div class="avabox">
                <img src="/images/diablo.jpg" id="diablo" class="avaimg" alt="" />
                <p><b>Диабло</b></p>
                <div id="diablo_desk">
                    <?php
                    if ($game['status_id'] === 1) {
                        echo   '<p>ожидает хода игрока...</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="box_center">
            <div class="game_padding">
                <div class="gamefield">
                    <?= ($table) ? $table : '<p class="game_status">Ваши Cookies не являются валидными, пожалуйста, начните новую игру.</p>'; ?>
                </div>
            </div>
        </div>
    </div>
    <p class="game_status"></p>

    <?= ($game === false || $game['status_id'] !== 1) ? '<div class="newgame" onclick="newGame()">НОВАЯ ИГРА</div>' : '<div class="newgame" onclick="endGame()">СДАТЬСЯ</div>'; ?>
    <!-- <p class="gameend">Игра завершена &#150; Победили силы света! (игрок)</p> -->

    <h3>Отладочные логи</h3>
    <p><?= $logs ?></p>
</div>
<?= ($game['cellAttackBot']) ? '<script>window.onload = function() { botStart(); };</script>': ''; ?>

