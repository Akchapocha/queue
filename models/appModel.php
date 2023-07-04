<?php
/**
 * Created by PhpStorm.
 * User: it-dept
 * Date: 12.11.18
 * Time: 11:59
 */

namespace models;

use DB;
use PDO;

class AppModel extends DB
{
    /**
     * Функция получения названий всех таблиц из БД
     *
     * @return array
     */
    protected static function getAllTables()
    {
        $sql = 'SHOW tables FROM `queue_couriers`';
        $result = [];

        DB::getInstance();
        $res = DB::$pdo->prepare($sql);
        $res->execute();
        $res = $res->fetchAll();

        if (isset($res[0])){
            foreach ($res as $row){
                $result[] = $row[0];
            }
        } else {
            return [];
        }

        return $result;
    }

    /**
     * Функция получения данных из указаной таблицы
     *
     * @param $table - имя таблицы
     * @return array
     */
    protected static function getTable($table)
    {
        $sql = 'SELECT * FROM `'. $table .'` ';

        DB::getInstance();
        $res = self::$pdo->prepare($sql);
        $res->execute();

        $res = $res->fetchAll(PDO::FETCH_ASSOC);

        if (isset($res[0])){
            return $res;
        } else {
            return [];
        }
    }
}