/**
 * Изначально не планировал делать анимацию на фронте, ограничившись одним лишь AJAX-ом.
 * Потом решил, что без анимации все же как-то скучно, и начал её добавлять.
 *
 * Учитывая, что бек отдает целиковую часть страницы, анимация делается немного костылями.
 */

/**
 * Отвечает за включение/выключение скриптов
 *
 * @type {boolean}
 */
var online = true;

/**
 * Обрабатывает ход игрока
 *
 * @param i
 */
function moveHere(i) {
    $.ajax({
        url: '',
        data: {type: 'moveHere', here: i},
        type: 'POST',
        success: function(data){

            data = JSON.parse(data.replace(/\n/g, "\\n"));

            if (data.success === '1') {

                console.log("Акт: " + data.act);
                console.log("Статус: " + data.status_id);
                console.log("Данные получены - обновляем страницу");
                document.getElementById('main_content').innerHTML = data.content;

                console.log("На время обработки хода/ходов - отключаем кнопки");
                buttonNoActive();

                console.log("Добавляем анимацию к картинке, которую поставил игрок");
                var cell;
                cell = document.getElementById('cell' + i);
                cell.className = 'cross_here_animation';

                console.log("Нам приходит сразу финальный статус - меняем его обратно и обновим только тогда, когда ходы закончатся");
                document.getElementById('status_footer').innerHTML = 'Игра в процессе';

                setTimeout(function() {
                    if (online) {
                        console.log("Через секунду убираем анимацию с картинки, которую поставил игрок");
                        cell.className = 'cross_here';

                        console.log("Проверяем, завершилась ли игра");
                        if ((data.act === '9' && !data.cellAttackBot) || data.status_id === '2') {
                            console.log("Игра завершилась");
                            allNoActive();
                        } else {
                            console.log("Игра не завершилась - меняем ход игрока на ход бота");
                            diabloActive();
                            playerNoActive();
                        }

                        if (!data.cellAttackBot) {
                            console.log("Устанавливаем статус игры");
                            document.getElementById('status_footer').innerHTML = data.status_name;
                            console.log("Обработка хода/ходов закончилась - включаем кнопки");
                            buttonActive();
                        }
                    }
                }, 1000);

                if (data.cellAttackBot) {
                    if (online) {
                        console.log("Обрабатываем ход бота");
                        console.log("Скрываем ячейку, в которую походил бот (да, такой костыль)");
                        var td;
                        td = document.getElementById('td' + data.cellAttackBot);
                        td.innerHTML = '';

                        setTimeout(function() {
                            if (online) {
                                console.log("Через 3 секунды добавляем иконку в ячейку, куда походил бот. Вначале с анимацией.");
                                td.innerHTML = '<span id="cell' + data.cellAttackBot  + '" class="pent_here_animation"></span>';

                                setTimeout(function() {
                                    if (online) {
                                        console.log("Отключаем анимацию с ячейки бота");
                                        td.innerHTML = '<span id="cell' + data.cellAttackBot  + '" class="pent_here"></span>';

                                        console.log("Проверяем, завершилась ли игра");

                                        if ((data.act === '9') || data.status_id === '3') {
                                            console.log("Игра завершилась");
                                            allNoActive();
                                        } else {
                                            console.log("Меняем ход бота на ход игрока");
                                            playerActive();
                                            dialoNoActive();
                                        }

                                        console.log("Устанавливаем статус игры");
                                        document.getElementById('status_footer').innerHTML = data.status_name;
                                        console.log("Обработка хода/ходов закончилась - включаем кнопки");
                                        buttonActive();
                                    }
                                }, 1000);
                            }
                        }, 3000);
                    }
                }
            }
        },
        error: function(){
            alert('Ошибка! Пожалуйста, обновите страницу!');
        }
    });
}

/**
 * Запрос на создание новой игры
 */
function newGame() {
    $.ajax({
        url: '',
        data: {type: 'newGame'},
        type: 'POST',
        success: function(data){
            data = JSON.parse(data.replace(/\n/g, "\\n"));

            if (data.success === '1') {

                document.getElementById('main_content').innerHTML = data.content;

                console.log("Новая игра - включаем работу функций");
                online = true;

                if (data.cellAttackBot) {
                    botStart();
                }
            }
        },
        error: function(){
            alert('Ошибка! Пожалуйста, обновите страницу!');
        }
    });
}

/**
 * Запрос на завершение игры
 */
function endGame() {
    $.ajax({
        url: '',
        data: {type: 'endGame'},
        type: 'POST',
        success: function(data){
            data = JSON.parse(data.replace(/\n/g, "\\n"));

            if (data.success === '1') {
                document.getElementById('main_content').innerHTML = data.content;
                console.log("Получен запрос на сдачу - отключаем активность со всех");
                allNoActive();
                console.log("Отключаем работу остальных функций");
                online = false;
            }
        },
        error: function(){
            alert('Ошибка! Пожалуйста, обновите страницу!');
        }
    });
}

/**
 * Если бот начинает первым - эта функция отдельно анимирует его первый ход
 * С условием, что по логике бота, если он начинает первым - он ходит в центральную ячейку.
 */
function botStart() {
    console.log("Если первым ходит бот - добавляем анимацию к его ходу");
    var td;
    td = document.getElementById('td5');
    td.innerHTML = '';
    td.innerHTML = '<span id="cell5" class="pent_here_animation"></span>';

    playerNoActive();
    buttonNoActive();
    diabloActive();

    setTimeout(function() {
        console.log("Отключаем анимацию с ячейки бота");
        td.innerHTML = '<span id="cell5" class="pent_here"></span>';
        playerActive();
        buttonActive();
        dialoNoActive();
    }, 1000);
}

/**
 * Переводим аватар и описание игрока в активный режим
 */
function playerActive() {
    console.log("Переводим игрока в активный режим");
    document.getElementById('paladin').className = 'avaimg_active';
    document.getElementById('paladin_desk').innerHTML = '<p>ходит...</p><div class="cssload-container"><div class="cssload-whirlpool"></div></div>';
}

/**
 * Переводим аватар и описание игрока в неактивный режим
 */
function playerNoActive() {
    console.log("Переводим игрока в неактивный режим");
    document.getElementById('paladin').className = 'avaimg';
    document.getElementById('paladin_desk').innerHTML = '<p>ожидает хода Диабло...</p>';
}

/**
 * Переводим аватар и описание бота в активный режим
 */
function diabloActive() {
    document.getElementById('diablo').className = 'avaimg_active';
    document.getElementById('diablo_desk').innerHTML = '<p>ходит...</p><div class="cssload-container"><div class="cssload-whirlpool"></div></div>';
}

/**
 * Переводим аватар и описание бота в неактивный режим
 */
function dialoNoActive() {
    document.getElementById('diablo').className = 'avaimg';
    document.getElementById('diablo_desk').innerHTML = '<p>ожидает хода игрока...</p>';
}

/**
 * Переводим всех в неактивный режим
 */
function allNoActive() {
    document.getElementById('paladin').className = 'avaimg';
    document.getElementById('diablo').className = 'avaimg';
    document.getElementById('paladin_desk').innerHTML = '';
    document.getElementById('diablo_desk').innerHTML = '';
}

/**
 * Включаем кнопки
 */
function buttonActive() {
    console.log("Включаем кнопки");
    $(".move_off").attr("class", "move_here");
}

/**
 * Выключаем кнопки
 */
function buttonNoActive() {
    console.log("Выключаем кнопки");
    $(".move_here").attr("class", "move_off");
}
