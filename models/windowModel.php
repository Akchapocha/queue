<?php
/**
 * Created by PhpStorm.
 * User: it-dept
 * Date: 13.11.18
 * Time: 10:45
 */

namespace models;

use DB;
use PDO;
use Exception;

require_once MODELS.'appModel.php';


class WindowModel extends AppModel
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

        if (isset($res[0])){
            return $res;
        } else {
            return [];
        }

    }

    /**
     * Модель получения id текущего статуса
     *
     * @param $window
     * @return array
     */
    public static function getStatusNow($window)
    {
        $sql = 'SELECT `status`
                FROM `correspondence table`
                WHERE `number_window` = ?';

        DB::getInstance();
        $res = self::$pdo->prepare($sql);
        $res->execute([$window]);
        $res = $res->fetchAll(PDO::FETCH_ASSOC);

        if (isset($res[0])){
            return $res[0];
        } else {
            return [];
        }

    }

    /**
     * Модель получения статуса
     *
     * @param $idStatus
     * @return array
     */
    public static function getStatusName($idStatus)
    {
        $sql = 'SELECT * 
                FROM `statuses` 
                WHERE `id_status` = ?';

        DB::getInstance();
        $res = self::$pdo->prepare($sql);

        $res->execute([$idStatus]);
        $res = $res->fetchAll(PDO::FETCH_ASSOC);

        if (isset($res[0])){
            return $res[0];
        } else {
            return [];
        }
    }

    /**
     * Модель смены статуса на "свободен"
     *
     * @param $window - номер окна
     * @param $date - текущая дата
     * @return string - результат смены статуса
     */
    public static function setStatFree($window, $date)
    {
        $sqlUpdStatus = 'UPDATE `correspondence table`
                         SET `status` = 1,
                              `time_status` = ?
                         WHERE `number_window` = ?';

        $sqlUpdActions = 'UPDATE `correspondence table`
                          SET `actions` = 1';

        $sqlUpdQueue = 'UPDATE `queue`
                        SET `status` = 6
                        WHERE `status` = 3 AND `window` = ?';

        DB::getInstance();

        try {

            self::$pdo->beginTransaction();

            $resUpdStatus = self::$pdo->prepare($sqlUpdStatus);
            $resUpdStatus = $resUpdStatus->execute([$date, $window]);

            if ($resUpdStatus !== true){
                throw New Exception();
            }

            $resUpdActions = self::$pdo->prepare($sqlUpdActions);
            $resUpdActions = $resUpdActions->execute();

            if ($resUpdActions !== true){
                throw New Exception();
            }


            $resUpdQueue = self::$pdo->prepare($sqlUpdQueue);
            $resUpdQueue = $resUpdQueue->execute([$window]);

            if ($resUpdQueue !== true){
                throw New Exception();
            }

            self::$pdo->commit();

        } catch (Exception $e) {
            self::$pdo->rollBack();
            return 'Ошибка при смене статуса';
        }

        return 'Смена статуса прошла успешно';
    }

    /**
     * Модель смены статуса на "сборка"
     *
     * @param $window - номер окна
     * @param $date - текущая дата
     * @return string - результат смены статуса
     */
    public static function setStatAssemble($window, $date)
    {
        $sqlUpdStatus = 'UPDATE `correspondence table`
                         SET `status` = 3,
                             `time_status` = ?
                         WHERE `number_window` = ?';

        $sqlUpdActions = 'UPDATE `correspondence table`
                          SET `actions` = 1';

        $sqlUpdQueue = 'UPDATE `queue`
                        SET `status` = 3
                        WHERE `window` = ? AND `status` = 2';

        DB::getInstance();

        try {

            self::$pdo->beginTransaction();

            $resUpdStatus = self::$pdo->prepare($sqlUpdStatus);
            $resUpdStatus = $resUpdStatus->execute([$date, $window]);

            if ($resUpdStatus !== true){
                throw New Exception();
            }

            $resUpdActions = self::$pdo->prepare($sqlUpdActions);
            $resUpdActions = $resUpdActions->execute();

            if ($resUpdActions !== true){
                throw New Exception();
            }

            $resUpdQueue = self::$pdo->prepare($sqlUpdQueue);
            $resUpdQueue = $resUpdQueue->execute([$window]);

            if ($resUpdQueue !== true){
                throw New Exception();
            }

            self::$pdo->commit();

        } catch (Exception $e) {
            self::$pdo->rollBack();
            return 'Ошибка при смене статуса';
        }

        return 'Смена статуса прошла успешно';
    }

    /**
     * Модель получения флага для обновления страниц окон выдачи
     *
     * @param $window - номер окна
     * @return array
     */
    public static function getActionWindow($window)
    {
        $sql = 'SELECT `actions` 
                FROM `correspondence table` 
                WHERE `number_window` = ? ';

        DB::getInstance();
        $res = self::$pdo->prepare($sql);

        $res->execute([$window]);
        $res = $res->fetchAll(PDO::FETCH_ASSOC);

        if (isset($res[0])){
            return $res[0];
        } else {
            return [];
        }
    }

    /**
     * Модель снятия флага после получения обновленных данных окном выдачи
     *
     * @param $window
     */
    public static function setAction($window)
    {
        $sql = 'UPDATE `correspondence table`
                SET `actions` = 0
                WHERE `number_window` = ?';
        DB::getInstance();
        $res = self::$pdo->prepare($sql);
        $res = $res->execute([$window]);

        if ($res !== true){
            exit('Данные не были обновлены');
        }

    }

    /**
     * Функция установки флага на обновление данных в окнах выдачи
     */
    public static function setFlag()
    {
        $sql = 'UPDATE `correspondence table`
                SET `actions` = 1';

        DB::getInstance();
        $res = self::$pdo->prepare($sql);
        $res->execute();
    }

    /**
     * Модель получения цвета для состояния
     *
     * @param $id_status
     * @return array
     */
    public static function getStatColor($id_status)
    {
        $sql = 'SELECT `status_color` 
                FROM `statuses` 
                WHERE `id_status` = ?';

        DB::getInstance();
        $res = self::$pdo->prepare($sql);

        $res->execute([$id_status]);
        $res = $res->fetchAll(PDO::FETCH_ASSOC);

        if (isset($res[0])){
            return $res[0];
        } else {
            return [];
        }
    }

    /**
     * Модель запроса перерыва
     *
     * @param $window - номер окна
     * @param $interval - 4 или 5 (перерыв или обед)
     * @param $date - дата и время запроса
     * @param $nameStatus - перерыв или обед
     *
     * @return string
     */
    public static function getInterval($window, $interval, $date, $nameStatus)
    {
        $sqlGetWaiting = 'SELECT `waiting`
                          FROM `correspondence table`
                          WHERE `number_window` = ?';

        $sqlUpdWaiting = 'UPDATE `correspondence table`
                          SET `waiting` = ?,
                              `time_waiting` = ?
                          WHERE `number_window` = ?';

        DB::getInstance();

        try {

            self::$pdo->beginTransaction();

            $resGetWaiting = self::$pdo->prepare($sqlGetWaiting);
            $resGetWaiting->execute([$window]);
            $resGetWaiting = $resGetWaiting->fetchAll(PDO::FETCH_ASSOC);

            if (isset($resGetWaiting[0]['waiting'])){

                if (intval($resGetWaiting[0]['waiting']) !== 0){
                    if (intval($resGetWaiting[0]['waiting']) === 4){
                        $nameStatus = 'перерыв';
                    } elseif (intval($resGetWaiting[0]['waiting']) === 5) {
                        $nameStatus = 'обед';
                    }

                    return 'Вы уже запросили '.mb_strtolower($nameStatus);

                } else {

                    $resUpdWaiting = self::$pdo->prepare($sqlUpdWaiting);
                    $resUpdWaiting = $resUpdWaiting->execute([$interval, $date, $window]);

                    if ($resUpdWaiting !== true){
                        throw New Exception();
                    }



                }

            } else {
                throw New Exception();
            }

            self::$pdo->commit();

        } catch (Exception $e) {
            self::$pdo->rollBack();
            return 'Ошибка при запросе '.mb_strtolower($nameStatus).'а';
        }

        return 'Запрос '.mb_strtolower($nameStatus).'а прошел успешно';

    }

    /**
     * Модель получения данных об ожидании перерывов
     *
     * @param $window
     * @return array
     */
    public static function getWaiting($window)
    {
        $sql = 'SELECT `waiting`, `confirm`
                FROM `correspondence table`
                WHERE `number_window` = ?';

        DB::getInstance();
        $res = self::$pdo->prepare($sql);

        $res->execute([$window]);
        $res = $res->fetchAll(PDO::FETCH_ASSOC);

        if (isset($res[0])){
            return $res[0];
        } else {
            return [];
        }
    }

    /**
     * Модель простановки перерыва/обеда при одобрении администратором
     *
     * @param $window - номер окна
     * @param $waiting - ожидаемый статус
     * @param $date - текущая дата
     * @return string
     */
    public  static function setStatInterval($window, $waiting, $date)
    {
        $strInterval = '';
        if (intval($waiting) === 4){
            $strInterval = '`intervals` = `intervals` + 1';
        } elseif (intval($waiting) === 5){
            $strInterval = '`lunches` = `lunches` + 1';
        }

        $sqlUpdWaiting = 'UPDATE `correspondence table`
                             SET  `status` = ?,
                                  `time_status` = ?,
                                  `waiting` = 0,
                                  `time_waiting` = NULL,
                                  `confirm` = 0,
                                  '.$strInterval.'
                             WHERE `number_window` = ?';

        $sqlUpdActions = 'UPDATE `correspondence table`
                          SET `actions` = 1';

        $sqlUpdQueue = 'UPDATE `queue`
                        SET `status` = 6
                        WHERE `status` = 3 AND `window` = ?';


        DB::getInstance();

        try {

            self::$pdo->beginTransaction();

            $resUpdWaiting = self::$pdo->prepare($sqlUpdWaiting);
            $resUpdWaiting = $resUpdWaiting->execute([$waiting, $date, $window]);

            if ($resUpdWaiting !== true){
                throw New Exception();
            }

            $resUpdActions = self::$pdo->prepare($sqlUpdActions);
            $resUpdActions = $resUpdActions->execute();

            if ($resUpdActions !== true){
                throw New Exception();
            }

            $resUpdQueue = self::$pdo->prepare($sqlUpdQueue);
            $resUpdQueue = $resUpdQueue->execute([$window]);

            if ($resUpdQueue !== true){
                throw New Exception();
            }


            self::$pdo->commit();

        } catch (Exception $e) {
            self::$pdo->rollBack();
            return 'Ошибка при смене статуса';
        }

        return 'Смена статуса прошла успешно';
    }

    /**
     * Модель простановки перерыва/обеда без одобрения, если состояние "Свободен"
     *
     * @param $window - номер окна
     * @param $waiting - запрашиваемые статус
     * @param $date - текущая дата
     * @return string
     */
    public static function setStatIntervalWithoutConf($window, $waiting, $date)
    {
        $strInterval = '';
        if (intval($waiting) === 4){
            $strInterval = '`intervals` = `intervals` + 1';
        } elseif (intval($waiting) === 5){
            $strInterval = '`lunches` = `lunches` + 1';
        }

        $sqlGetQueue = 'SELECT `number_queue`
                        FROM `queue`
                        WHERE `status` = 2 AND `window` = ?';

        $sqlUpdWaiting = 'UPDATE `correspondence table`
                             SET  `status` = ?,
                                  `time_status` = ?,
                                  `waiting` = 0,
                                  `time_waiting` = NULL,
                                  `confirm` = 0,
                                  '.$strInterval.'
                             WHERE `number_window` = ?';

        $sqlUpdActions = 'UPDATE `correspondence table`
                          SET `actions` = 1';

        $sqlUpdQueue = 'UPDATE `queue`
                        SET `status` = 6
                        WHERE `status` = 3 AND `window` = ?';


        DB::getInstance();

        try {

            self::$pdo->beginTransaction();

            $resGetQueue = self::$pdo->prepare($sqlGetQueue);
            $resGetQueue->execute([$window]);
            $resGetQueue = $resGetQueue->fetchAll(PDO::FETCH_ASSOC);

            if (isset($resGetQueue[0])){

                throw New Exception();

            } else {

                $resUpdWaiting = self::$pdo->prepare($sqlUpdWaiting);
                $resUpdWaiting = $resUpdWaiting->execute([$waiting, $date, $window]);

                if ($resUpdWaiting !== true){
                    throw New Exception();
                }

                $resUpdActions = self::$pdo->prepare($sqlUpdActions);
                $resUpdActions = $resUpdActions->execute();

                if ($resUpdActions !== true){
                    throw New Exception();
                }

                $resUpdQueue = self::$pdo->prepare($sqlUpdQueue);
                $resUpdQueue = $resUpdQueue->execute([$window]);

                if ($resUpdQueue !== true){
                    throw New Exception();
                }

            }

            self::$pdo->commit();

        } catch (Exception $e) {
            self::$pdo->rollBack();
            return 'Ошибка при смене статуса';
        }

        return 'Смена статуса прошла успешно';
    }

    /**
     * Модель получения статуса запроса на перерыв/обед
     *
     * @param $window - номер окна
     * @return array
     */
    public static function getCondition($window)
    {
        $sql = 'SELECT `status`, `time_status`, `waiting`, `confirm`, `lunches`, `intervals`
                         FROM `correspondence table`
                         WHERE `number_window` = ?';

        DB::getInstance();
        $res = self::$pdo->prepare($sql);

        $res->execute([$window]);
        $res = $res->fetchAll(PDO::FETCH_ASSOC);

        if (isset($res[0])){
            return $res[0];
        } else {
            return [];
        }

    }

    /**
     * Модель получения настроек по перерывам
     *
     * @return array
     */
    public static function getSettings()
    {
        $sql = 'SELECT `name_set`, `switch`, `count`, `period`, `time_begin`, `time_end`
                           FROM `settings`
                           WHERE `id_settings` = 2 OR `id_settings` = 3 ';

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
     * Модель простановки статуса "не работает"
     *
     * @param $window - номер окна
     * @param $date - текущая дата
     * @return string
     */
    public static function setEscape($window, $date)
    {
        $sqlUpdWindow = 'UPDATE `correspondence table`
                         SET `status` = 0,
                             `time_status` = ?
                         WHERE `number_window` = ?';

        $sqlUpdActions = 'UPDATE `correspondence table`
                          SET `actions` = 1';

        $sqlUpdQueue = 'UPDATE `queue`
                        SET `status` = 6
                        WHERE `window` = ?';

        DB::getInstance();

        try {



            self::$pdo->beginTransaction();

            $resUpdWindow = self::$pdo->prepare($sqlUpdWindow);
            $resUpdWindow = $resUpdWindow->execute([$date, $window]);

            if ($resUpdWindow !== true){
                throw New Exception();
            }

            $resUpdActions = self::$pdo->prepare($sqlUpdActions);
            $resUpdActions = $resUpdActions->execute();

            if ($resUpdActions !== true){
                throw New Exception();
            }



            $resUpdQueue = self::$pdo->prepare($sqlUpdQueue);
            $resUpdQueue = $resUpdQueue->execute([$window]);

            if ($resUpdQueue !== true){
                throw New Exception();
            }

            self::$pdo->commit();

        } catch (Exception $e) {
            self::$pdo->rollBack();
            return 'Ошибка при смене статуса';
        }

        return 'Смена статуса прошла успешно';
    }

    /**
     * Модель получения номера окна в соответствии с IP
     *
     * @return array
     */
    public static function getCorrespondence()
    {
        $sql = 'SELECT `number_window`
                FROM `correspondence table`
                WHERE `ip_window` = ?';

        DB::getInstance();
        $res = self::$pdo->prepare($sql);

        $res->execute([$_SERVER['REMOTE_ADDR']]);
        $res = $res->fetchAll(PDO::FETCH_ASSOC);

//        debug($_SERVER['REMOTE_ADDR']);

        if (isset($res[0])){
            return $res[0];
        } else {
            return [];
        }
    }
}