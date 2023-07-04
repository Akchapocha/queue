<?php
/**
 * Created by PhpStorm.
 * User: it-dept
 * Date: 15.11.18
 * Time: 12:33
 */

namespace models;

use DB;
use PDO;
use Exception;

require_once MODELS.'appModel.php';

class MonitorModel extends AppModel
{
    /**
     * Модель получения флага распределения
     *
     * @return array
     */
    public static function getDestination()
    {
        $sql = 'SELECT `switch` 
                FROM `settings` 
                WHERE `id_settings` = 1';

        DB::getInstance();
        $res = self::$pdo->prepare($sql);

        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);

        if (isset($res[0])){
            return $res[0];
        } else {
            return [];
        }
    }

    /**
     * Модель распределения очереди
     *
     * @param $date
     * @return string
     */
    public static function setDestination($date)
    {
        $sqlQueue = 'SELECT `number_queue`, `type_queue`
                     FROM `queue` 
                     WHERE `status` = 0';

        $sqlWindows = 'SELECT `number_window`, `time_status`, `queue_type`
                       FROM `correspondence table`
                       WHERE `status` = 1 ORDER BY `time_status` ASC';

        $sqlUpdAction = 'UPDATE `correspondence table` 
                         SET `actions` = 1';

        $sqlGetWindowsWaitingIntervals = 'SELECT * FROM `correspondence table`
                                          WHERE `status` = 1 AND `waiting` > 0 AND `confirm` > 0 ';

        $sqlUpdateAction = 'UPDATE `correspondence table` SET `actions` = 1';

        DB::getInstance();

        try {

            self::$pdo->beginTransaction();

            $resQueue = self::$pdo->prepare($sqlQueue); /**Получаем не распределенную очередь*/
            $resQueue->execute();
            $resQueue = $resQueue->fetchAll(PDO::FETCH_ASSOC);

            if (!isset($resQueue)){
                throw New Exception();
            }

            $resWindows = self::$pdo->prepare($sqlWindows);/**Получаем свободные окна*/
            $resWindows->execute();
            $resWindows = $resWindows->fetchAll(PDO::FETCH_ASSOC);

            $dispenseQueue = [];

            if (($resQueue !== []) and ($resWindows !== [])){

                $dispenseQueue = self::getDispenseQueue($resQueue, $resWindows); /**Получаем массив распределенной очереди*/

                $strWindows = '';
                $strQueue = '';
                $strStatWindows = '';

                /**Формирование строк для SQL запросов*/
                foreach ($dispenseQueue as $item => $row){
                    $strWindows = $strWindows.'WHEN `number_queue` = '.$row['number_queue'].' THEN \''.$row['number_window'].'\' ';
                    $strQueue = $strQueue.'`number_queue` = '.$row['number_queue'].' OR ';
                    $strStatWindows = $strStatWindows.'`number_window` = \''.$row['number_window'].'\' OR ';
                }

                $strWindows = preg_replace("/ $/","", $strWindows);
                $strQueue = preg_replace("/ OR $/","", $strQueue);
                $strStatWindows = preg_replace("/ OR $/","", $strStatWindows);



                /**Присваиваем новый статус и номера окона для очереди*/
                $sqlUpdQueue = 'UPDATE `queue`
                                SET `status` = 2,
                                    `window` = (CASE '.$strWindows.' END)
                                WHERE ('.$strQueue.') AND `status` = 0 ';

                $resUpdQueue = self::$pdo->prepare($sqlUpdQueue);
                $resUpdQueue = $resUpdQueue->execute();

                if ($resUpdQueue !== true){
                    throw New Exception();
                }

                /**Присваиваем новый статус окнам*/
                $sqlUpdStatWindows = 'UPDATE `correspondence table`
                                      SET `status` = 2,
                                          `time_wait_cur` = ?
                                      WHERE '.$strStatWindows.' ';

                $resUpdStatWindows = self::$pdo->prepare($sqlUpdStatWindows);
                $resUpdStatWindows = $resUpdStatWindows->execute([$date]);

                if ($resUpdStatWindows !== true){
                    throw New Exception();
                }

                /**Присваиваем флаг на обновление данных окон*/
                $resUpdAction = self::$pdo->prepare($sqlUpdAction);
                $resUpdAction = $resUpdAction->execute();

                if ($resUpdAction !== true){
                    throw New Exception();
                }

            } elseif (($resQueue === []) and ($resWindows !== [])){
                /**Простановка статусов обед/перерыв для свободных окон, которым были одобрены обед/перерыв*/

                $resGetWindowsWaitingIntervals = self::$pdo->prepare($sqlGetWindowsWaitingIntervals); /**Получаем номера окон с разрешенными обедами/перерывами*/
                $resGetWindowsWaitingIntervals->execute();
                $resGetWindowsWaitingIntervals = $resGetWindowsWaitingIntervals->fetchAll(PDO::FETCH_ASSOC);

                $stringId = '';
                $stringLunches = '';
                $stringIntervals = '';

                if (isset($resGetWindowsWaitingIntervals)){
                    if ($resGetWindowsWaitingIntervals !== []){
                        foreach ($resGetWindowsWaitingIntervals as $item => $window){

                            $stringId = $stringId.'`id_window` = '.$window['id_window'].' OR ';

                            if (intval($window['waiting']) === 4) {
                                $window['intervals'] = $window['intervals'] + 1;
                            } elseif (intval($window['waiting']) === 5) {
                                $window['lunches'] = $window['lunches'] + 1;
                            }

                            $stringLunches = $stringLunches.'WHEN `id_window`= ' . $window['id_window'] . ' THEN ' . $window['lunches'] . ' ';
                            $stringIntervals = $stringIntervals.'WHEN `id_window`= ' . $window['id_window'] . ' THEN ' . $window['intervals'] . ' ';
                        }
                    }
                }

                $stringId = preg_replace("/ OR $/", "", $stringId);

                $sqlUpdateWindows = 'UPDATE `correspondence table`
                                     SET `status` = `waiting`,
                                         `time_status` = \''.date('Y-m-d H:i:s').'\',
                                         `waiting` = 0,
                                         `time_waiting` = NULL,
                                         `confirm` = 0,
                                         `lunches` = (CASE '.$stringLunches.'END),
                                         `intervals` = (CASE '.$stringIntervals.'END)
                                     WHERE '.$stringId.' ';

                $resUpdateWindows = self::$pdo->prepare($sqlUpdateWindows);
                $resUpdateWindows = $resUpdateWindows->execute();

                if ($resUpdateWindows !== true){
                    throw New Exception();
                }

                $resUpdateAction = self::$pdo->prepare($sqlUpdateAction);
                $resUpdateAction = $resUpdateAction->execute();

                if ($resUpdateAction !== true){
                    throw New Exception();
                }

            }

            self::$pdo->commit();

        } catch (Exception $e) {
            self::$pdo->rollBack();
            return 'Ошибка при распределении очереди';
        }

        return '';
    }

    /**
     * Модель получения данных об очереди с сответствующим статусом
     *
     * @param $status - id статуса
     * @return array
     */
    public static function getQueue($status)
    {
        $sql = 'SELECT `number_queue`, `window`
                FROM `queue`
                WHERE `status` = ? ';

        $sqlGetCount = 'SELECT COUNT(*) as `count` FROM `queue`';

        DB::getInstance();

        $resCount = self::$pdo->prepare($sqlGetCount);
        $resCount->execute();
        $resCount = $resCount->fetchAll(PDO::FETCH_ASSOC);

        if (isset($resCount[0]['count'])){

            self::checkCountRows($resCount[0]['count']);

        }

        $res = self::$pdo->prepare($sql);
        $res->execute([$status]);
        $res = $res->fetchAll(PDO::FETCH_ASSOC);

        if (isset($res[0])){

            return $res;

        } else {

            return [];

        }
    }

    /**
     * Модель получения цвета статуса "Ожидает курьера"
     *
     * @return array
     */
    public static function getColorStatus()
    {
        $sql = 'SELECT `status_color`
                FROM `statuses`
                WHERE `id_status` = 2';

        DB::getInstance();
        $res = self::$pdo->prepare($sql);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);

        if (isset($res[0])){
            return $res[0];
        } else {
            return [];
        }
    }

    /**
     * Модель получения настроек по времени ожидания окнами курьеров
     *
     * @return array
     */
    public static function getTimeOut()
    {
        $sql = 'SELECT `switch`, `period`
                FROM `settings`
                WHERE `id_settings` = 4';

        DB::getInstance();
        $res = self::$pdo->prepare($sql);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);

        if (isset($res[0])){
            return $res[0];
        } else {
            return [];
        }
    }

    /**
     * Модель получения окон, которые ожидают курьеров
     *
     * @return array
     */
    public static function getWindowsWaitingCouriers()
    {
        $sql = 'SELECT `number_window`, `time_wait_cur`
                FROM `correspondence table`
                WHERE `status` = 2';

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

    /**
     * Модель для сброса ожидания курьеров
     *
     * @param $strQueue - номера окон для изменения очереди
     * @param $strWindows - номера окон для изменения окон
     * @param $date
     * @return string
     */
    public static function dropWaiting($strQueue, $strWindows, $date)
    {
        $sqlUpdQueue = 'UPDATE `queue`
                        SET `status` = 7
                        WHERE '.$strQueue.' AND `status` = 2 ';


        $sqlUpdWindows = 'UPDATE `correspondence table`
                          SET `status` = 1,
                              `time_wait_cur` = ?,
                              `actions` = 1
                          WHERE '.$strWindows.' ';

        DB::getInstance();

        try {

            self::$pdo->beginTransaction();

            $resUpdQueue = self::$pdo->prepare($sqlUpdQueue);
            $resUpdQueue = $resUpdQueue->execute();

            if ($resUpdQueue !== true){
                throw New Exception();
            }

            $resUpdWindows = self::$pdo->prepare($sqlUpdWindows);
            $resUpdWindows = $resUpdWindows->execute([$date]);

            if ($resUpdWindows !== true){
                throw New Exception();
            }


            self::$pdo->commit();

        } catch (Exception $e) {
            self::$pdo->rollBack();
            return 'Ошибка при сбросе ожидания';
        }

        return '';
    }


    /**
     * Модель получения номера последнего билета
     *
     * @return array
     */
    public static function getLastNumTicket()
    {
        $sql = 'SELECT `number_queue`
                FROM `queue`
                ORDER BY `date_time` DESC limit 1';

        DB::getInstance();
        $res = self::$pdo->prepare($sql);

        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);

        if (isset($res[0])){
            return $res[0];
        } else {
            return [];
        }
    }

    /**
     * Модель добавления нового номера билета
     *
     * @param $queue_type - тип очереди
     * @return string
     */
    public static function setNewTicket($queue_type)
    {
        $sqlGetLastNum = 'SELECT `number_queue`
                          FROM `queue`
                          ORDER BY `date_time` DESC limit 1';

        DB::getInstance();

        try {

            self::$pdo->beginTransaction();

            $resGetLastNum = self::$pdo->prepare($sqlGetLastNum);
            $resGetLastNum->execute();
            $resGetLastNum = $resGetLastNum->fetchAll(PDO::FETCH_ASSOC);

            if (isset($resGetLastNum)){

                if (isset($resGetLastNum[0])){

                    $lastNumTicket = $resGetLastNum[0];

                    if (intval($lastNumTicket['number_queue']) < 9999){

                        $newNumTicket = intval($lastNumTicket['number_queue']) + 1;

                    } else {

                        $newNumTicket = '0001';

                    }

                } else {
                    $newNumTicket = '0001';
                }

                $date = date('Y-m-d H:i:s');

                $sqlInsert = 'INSERT INTO `queue` (`date_time`, `number_queue`, `type_queue`, `status`, `window`)
                              VALUES (?, ?, ?, 0, 0)';

                $resInsert = self::$pdo->prepare($sqlInsert);
                $resInsert = $resInsert->execute([$date, $newNumTicket, $queue_type]);

                if ($resInsert !== true){
                    throw New Exception();
                }

            } else {

                throw New Exception();

            }

            self::$pdo->commit();

        } catch (Exception $e) {
            self::$pdo->rollBack();
            return 'Ошибка добавления очереди. Приложите карточку ещё раз.';
        }

        return 'Очередь успешно дополнена.';
    }

    /**
     * Модель получения типа очереди
     *
     * @param $numberCart - номер карточки
     * @return string
     */
    public static function getQueueType($numberCart)
    {
        preg_match('/\d{5}$/', $numberCart, $shortNum);

        if (isset($shortNum[0])){

            $sql = 'SELECT `id_type` FROM `queue_type` WHERE `number_cart` LIKE \'%'.$shortNum[0].'\' ';

            DB::getInstance();
            $res = self::$pdo->prepare($sql);

            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);

            if (isset($res[0])){
                return $res[0]['id_type'];
            } else {
                return '';
            }

        } else {
            return '';
        }

    }

    /**
     * Модель получения настроек обедов и перерывов
     *
     * @return array
     */
    public static function getSettingsLaunches(){

        return WindowModel::getSettings();

    }

    /**
     * Модель сброса данных о количетсве прошедших обедах и перерывах
     *
     */
    public static function clearLaunchesAndIntervals(){

        $sqlUpdWindow = 'UPDATE `correspondence table`
                         SET `waiting` = 0,
                             `time_waiting` = NULL,
                             `confirm` = 0,
                             `lunches` = 0,
                             `intervals` = 0';

        DB::getInstance();

        $resUpdWindow = self::$pdo->prepare($sqlUpdWindow);
        $resUpdWindow->execute();

    }

    /**
     * Модель переодической очистки базы данных
     * @param $count
     */
    private static function checkCountRows($count)
    {
        $maxRows = 5000;/**Максимальное количество записей в БД*/
        $maxInterval = 7;/**Количество дней, которые останутся доступными после очистки БД*/

        if ( intval($count) >= intval($maxRows) ) {

            $now = strtotime( date('Y-m-d') );
            $dateBegin = $now - ( $maxInterval * (60*60*24) );
            $dateBegin = date('Y-m-d', $dateBegin);

            $sqlClearDB = 'DELETE
                           FROM `queue` 
                           WHERE `status` > 5 
                           AND `date_time` < \'' . $dateBegin . ' 00:00:00\'';

            DB::getInstance();
            $resClear = self::$pdo->prepare($sqlClearDB);
            $resClear->execute();

        }

    }

    /**
     * Функция получения массива распределенной очереди
     *
     * Все билеты распределяются только по своим типам окон
     *
     * @param $resQueue - не распределенная очередь
     * @param $resWindows - свободные окна
     * @return array
     */
    private static function getDispenseQueue($resQueue, $resWindows)
    {
        $res = [];

        $i = 1;

        foreach ($resWindows as $item => $window) {

            foreach ($resQueue as $key => $queue) {

                if ($window['queue_type'] === $queue['type_queue']) {

                    $res[$i]['number_queue'] = $queue['number_queue'];
                    $res[$i]['number_window'] = $window['number_window'];

                    unset($resQueue[$key]);
                    unset($resWindows[$item]);

                    $i++;
                    next($resQueue);
                    next($resWindows);
                    break;

                }

            }

        }

        return $res;

    }

}