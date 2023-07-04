<?php
/**
 * Created by PhpStorm.
 * User: it-dept
 * Date: 12.11.18
 * Time: 12:00
 */

namespace models;

use DB;
use Exception;
use PDO;

require_once MODELS.'appModel.php';

class MainModel extends AppModel
{

    /**
     * Модель для создания очереди курьеров
     *
     * @param  $stringsValue string - строка со значениями для очереди
     * @return bool|\PDOStatement
     */
    public static function createStringsToQueue($stringsValue)
    {
        self::truncateTable('queue');

        $date = date('Y-m-d H:i:s');

        $sqlInsertQueue = 'INSERT INTO `queue` (`date_time`, `number_queue`,`status`)
                           VALUES '.$stringsValue.' ';

        $sqlUpdWindows = 'UPDATE `correspondence table`
                          SET `status` = 0,
                              `actions` = 1,
                              `time_status` = \''.$date.'\',
                              `waiting` = 0,
                              `time_waiting` = NULL,
                              `confirm` = 0,
                              `lunches` = 0,
                              `intervals` = 0';

        DB::getInstance();

        try {

            self::$pdo->beginTransaction();

            $resInsertQueue = DB::$pdo->prepare($sqlInsertQueue);
            $resInsertQueue = $resInsertQueue->execute();

            if ($resInsertQueue !== true){
                throw New Exception();
            }

            $resUpdWindows = self::$pdo->prepare($sqlUpdWindows);
            $resUpdWindows = $resUpdWindows->execute();

            if ($resUpdWindows !== true){
                throw New Exception();
            }

            self::$pdo->commit();

        } catch (Exception $e) {
            self::$pdo->rollBack();
            return 'Ошибка создания очереди.';
        }

        return 'Очередь успешно создана.';

    }

    /**
     * Модель для добавления очереди
     *
     * @param $stringsValue
     * @return string
     */
    public static function addStringsToQueue($stringsValue)
    {
        $sql = 'INSERT INTO `queue` (`date_time`, `number_queue`,`status`)
                VALUES '.$stringsValue.' ';

        DB::getInstance();
        $res = DB::$pdo->prepare($sql);
        $res = $res->execute();

        if ($res !== false){
            return '';
        } else {
            return 'Ошибка добавления очереди.';
        }

    }

    /**
     * Модель для очистки таблицы
     *
     * @param $table string - имя таблицы
     */
    private static function truncateTable($table)
    {
        $sql = 'TRUNCATE TABLE `'.$table.'`';
        DB::getInstance();
        $res = DB::$pdo->prepare($sql);
        $res->execute();
    }

    /**
     * Модель получения последнего номера в очереди
     *
     * @return array
     */
    public static function getLastNumber()
    {
        $sql = 'SELECT `number_queue` FROM `queue` ORDER BY `number_queue` DESC LIMIT 1';

//        debug($sql);

        DB::getInstance();
        $res = self::$pdo->prepare($sql);

        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);

//        debug($res);

        if (isset($res[0])){
            return $res[0];
        } else {
            return [];
        }
    }
}