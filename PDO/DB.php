<?php
/**
 * Created by PhpStorm.
 * User: it-dept
 * Date: 02.11.18
 * Time: 14:38
 */

/**
 * Class DB
 *
 * Класс работы с БД
 */
class DB
{
    protected static $pdo = null;

    /**
     * DB constructor.
     *
     * Конструктор подключения к БД
     */
    private function __construct ()
    {
        $db = require_once CONFIG.'config_db_PDO.php';

        self::$pdo = new PDO($db['dsn'], $db['db_user'], $db['db_pass'], $db['options']);
    }

//    private function __clone () {}
//    private function __wakeup () {}

    /**
     * Функция проверки подключения
     *
     * @return DB|null|PDO - возвращает либо новое подключение, либо существующее
     */
    public static function getInstance()
    {
        if (self::$pdo != null) {
            return self::$pdo;
        }

        return new self;
    }

}