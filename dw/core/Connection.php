<?php

namespace dw\core;

final class Connection
{
    /** @var \mysqli */
    private $conn;
    /**@var string ошибки */
    private $error = '';
    /** @var ? */
    private static $instance;
    /** @var mixed тестовое свойство для проверки синглтона */
    public $a;

    /**
     * Возвращает объект работы с БД
     * Если его нет - создает, если существует - возвращает текущий
     *
     * @return Connection
     */
    public static function Instance()
    {
        if (self::$instance == null)
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * Само подключение
     *
     * Connection constructor.
     */
    public function __construct()
    {
        $this->conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
        or die('Невозможно подключиться к серверу БД.');

        // Проверка соединения
        if (mysqli_connect_errno()) {
            $this->error = 'Соединение не установлено: ' . mysqli_connect_error() . "\n";
        } else {
            $this->conn->query('SET NAMES utf8');
            $this->conn->set_charset("utf8");
        }
    }

    /**
     * Закрывает соединение с бд
     */
    function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    /**
     * Возвращает ошибку
     *
     * @return string
     */
    public function getError()
    {
        if ($this->error) {
            return $this->error;
        } else {
            return $this->conn->error;
        }
    }

    /**
     * Обработка и выполнение запроса
     *
     * @param $sql
     * @param array $params
     * @param bool $single
     * @return array|bool|mixed
     */
    public function query($sql, $params = [], $single = false)
    {
        $param_arr = null;

        if (count($params) > 0) {
            $param_types = '';
            $param_arr = [0 => ''];
            foreach ($params as $key => $val) {
                $param_types .= $val['type'];
                $param_arr[] = &$params[$key]['value']; // Передача значений осуществляется по ссылке.
            }
            $param_arr[0] = &$param_types;
        }

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            $this->error = 'Ошибка подготовки SQL: ' . $this->conn->errno . ' ' . $this->conn->error . '. SQL: ' . $sql;
        } else {
            // Если параметры не пришли - то bind_param не требуется
            if (count($params) > 0) {
                call_user_func_array([$stmt, 'bind_param'], $param_arr);
            }
            if ($stmt->execute()) {
                $res = $stmt->get_result();
                if ($res !== false) {
                    $result = [];
                    $i = 0;
                    while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
                        $result[] = $row;
                        $i++;
                    }
                    if ($single && ($i === 1)) $result = $result[0];
                }
            } else {
                $this->error = 'Ошибка выполнения SQL: ' . $stmt->errno . ' ' . $stmt->error . '. SQL: ' . $sql;
            }
        }
        return (empty($result) ? false : $result);
    }

    /**
     * Возвращает ID добавленной записи
     *
     * @return int|string
     */
    public function insert_id()
    {
        return mysqli_insert_id($this->conn);
    }

    /**
     * Отключает автокоммит (для включения транзакции на несколько запросов)
     *
     * @param $mode boolean True - автовыполнение коммита, False - отключение автокоммита
     * @return bool
     */
    public function autocommit($mode)
    {
        return $this->conn->autocommit($mode);
    }

    /**
     * Закрыть транзакцию в случае успешного выполнения запросов, и применить все изменения
     *
     * @return bool
     */
    public function commit()
    {
        return $this->conn->commit();
    }

    /**
     * Закрыть транзакцию и откатить все изменения (для вариантов, когда что-то пошло не так)
     *
     * @return bool
     */
    public function rollback()
    {
        return $this->conn->rollback();
    }
}
