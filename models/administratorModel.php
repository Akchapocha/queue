<?php
/**
 * Created by PhpStorm.
 * User: it-dept
 * Date: 12.11.18
 * Time: 15:25
 */

namespace models;

use DB;
use PDO;

require_once MODELS.'appModel.php';

class AdministratorModel extends AppModel
{
    /**
     * Модель для получения всех данных из таблицы $table
     *
     * @param $table - имя таблицы
     * @return array
     */
    public static function getAllFromTable($table)
    {
        $res = self::getTable($table);


//        debug($res);
        if (isset($res[0])){
            return $res;
        } else {
            return [];
        }
    }

    /**
     * Модель включения/выключения распеределения очереди по свободным окнам
     *
     * @param $val
     */
    public static function setDestination($val)
    {
        $sql = 'UPDATE `user_type`
                SET `destination` = ?
                WHERE `id_user-type` = 1';

        DB::getInstance();
        $res = self::$pdo->prepare($sql);
        $res = $res->execute([$val]);

        if ($res !== true){
            exit(json_encode('Ошибка при включении/выключении распределения'));
        } else {
            exit(json_encode(''));
        }
    }

    /**
     * Модель получения списка ожидающих перерыва
     *
     * @param $interval - id перерыва/обеда
     * @return array
     */
    public static function getWaiting($interval)
    {
        $sql = 'SELECT `number_window`, `waiting`, `time_waiting`, `confirm`
                FROM `correspondence table`
                WHERE `waiting` = ? AND `confirm` = 0 ORDER BY `time_waiting` ASC';

        DB::getInstance();
        $res = self::$pdo->prepare($sql);

        $res->execute([$interval]);
        $res = $res->fetchAll(PDO::FETCH_ASSOC);

        if (isset($res[0])){
            return $res;
        } else {
            return [];
        }
    }

    /**
     * Модель для проставления флага подтверждения запросов перерывов
     *
     * @param $string_windows - номера окон, для которых есть подтверждения
     * @return string
     */
    public static function setConf($string_windows)
    {
        $sql = 'UPDATE `correspondence table`
                SET `confirm` = 1
                WHERE '.$string_windows.' ';

        DB::getInstance();
        $res = self::$pdo->prepare($sql);

        $res = $res->execute();

        if ($res !== true){
            return 'Ошибка при одобрении запросов';
        } else {
            return 'Одобрение прошло успешно';
        }
    }

    /**
     * Модель изменения количества обедов
     *
     * @param $count - новое количество обедов
     * @return string
     */
    public static function setLunchCount($count)
    {
        $sql = 'UPDATE `settings`
                SET `count` = ?
                WHERE `name_set` = \'lunch\'';

        DB::getInstance();
        $res = self::$pdo->prepare($sql);

        $res = $res->execute([$count]);

        if ($res !== true){
            return 'Ошибка при изменении количества обедов';
        } else {
            return 'Изменение количества обедов прошло успешно';
        }
    }

    /**
     * Модель изменения времени начала обедов
     *
     * @param $lunchBegin - время начала обедов
     * @return string
     */
    public static function setLunchBegin($lunchBegin)
    {
        $sql = 'UPDATE `settings`
                SET `time_begin` = ?
                 WHERE `name_set` = \'lunch\'';

        DB::getInstance();
        $res = self::$pdo->prepare($sql);

        $res = $res->execute([$lunchBegin]);

        if ($res !== true){
            return 'Ошибка при изменении времени начала обедов';
        } else {
            return 'Изменение времени начала обедов прошло успешно';
        }
    }

    /**
     * Модель изменения времени окончания обедов
     *
     * @param $lunchEnd - время окончания обедов
     * @return string
     */
    public static function setLunchEnd($lunchEnd)
    {
        $sql = 'UPDATE `settings`
                SET `time_end` = ?
                 WHERE `name_set` = \'lunch\'';

        DB::getInstance();
        $res = self::$pdo->prepare($sql);

        $res = $res->execute([$lunchEnd]);

        if ($res !== true){
            return 'Ошибка при изменении времени окончания обедов';
        } else {
            return 'Изменение времени окончания обедов прошло успешно';
        }
    }

    /**
     * Модель изменения количества перерывов
     *
     * @param $count - новое количество перерывов
     * @return string
     */
    public static function setIntervalCount($count)
    {
        $sql = 'UPDATE `settings`
                SET `count` = ?
                WHERE `name_set` = \'interval\'';

        DB::getInstance();
        $res = self::$pdo->prepare($sql);

        $res = $res->execute([$count]);

        if ($res !== true){
            return 'Ошибка при изменении количества перерывов';
        } else {
            return 'Изменение количества перерывов прошло успешно';
        }
    }

    /**
     * Модель изменения времени начала перерывов
     *
     * @param $intervalBegin - время начала перерывов
     * @return string
     */
    public static function setIntervalBegin($intervalBegin)
    {
        $sql = 'UPDATE `settings`
                SET `time_begin` = ?
                WHERE `name_set` = \'interval\'';

        DB::getInstance();
        $res = self::$pdo->prepare($sql);

        $res = $res->execute([$intervalBegin]);

        if ($res !== true){
            return 'Ошибка при изменении времени начала перерывов';
        } else {
            return 'Изменение времени начала перерывов прошло успешно';
        }
    }

    /**
     * Модель изменения времени окончания перерывов
     *
     * @param $intervalEnd - время окончания перерывов
     * @return string
     */
    public static function setIntervalEnd($intervalEnd)
    {
        $sql = 'UPDATE `settings`
                SET `time_end` = ?
                WHERE `name_set` = \'interval\'';

        DB::getInstance();
        $res = self::$pdo->prepare($sql);

        $res = $res->execute([$intervalEnd]);

        if ($res !== true){
            return 'Ошибка при изменении времени окончания перерывов';
        } else {
            return 'Изменение времени окончания перерывов прошло успешно';
        }
    }

    /**
     * Модель установки времени ожидания курьеров
     *
     * @param $time
     * @return string
     */
    public static function setTimeWaiting($time)
    {
        $sql = 'UPDATE `settings`
                SET `period` = ?
                WHERE `name_set` = \'time_out_waiting\'';

        DB::getInstance();
        $res = self::$pdo->prepare($sql);

        $res = $res->execute([$time]);

//        debug($res);

        if ($res !== true){
            return 'Ошибка при изменении времени ожидания курьеров';
        } else {
            return 'Изменение времени ожидания курьеров прошло успешно';
        }
    }


}