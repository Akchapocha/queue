<?php
/**
 * Created by PhpStorm.
 * User: it-dept
 * Date: 08.11.18
 * Time: 16:33
 */

namespace controllers;

use models\AdministratorModel;
require_once MODELS.'administratorModel.php';

class AdministratorController extends AppController
{
    /**
     * Экшн для страницы администратора
     * 
     * @param $view - вид страницы администратора
     */
    public function indexAction($view)
    {
        $this->checkIP();/**Проверяем доступ к ресурсу по IP*/

        $title = 'Администратор склада';
        $js = '<script src="/js/administrator.js"></script>';

        $conditionQueue = $this->getConditionQueue();
        $conditionWindows = $this->getConditionWindows();
//        $conditionService = $this->getConditionService();

        if ($_POST){
            $this->dispatchPost();
        }



        /** Передаем переменные в вид */
        $this->set(compact('title','js', 'conditionQueue', 'conditionWindows', 'conditionService'));
        /** Передаем вид в шаблон */
        $this->getView($view['file'], $this->layout);
    }

    /**
     * Функция распределения $_POST, в зависимости от содержимого
     */
    private function dispatchPost()
    {
        if (isset($_POST['administrator'])){
            if ($_POST['administrator'] === 'getNewValues'){
                $this->getNewValues();
            }
        }

        if (isset($_POST['destination'])){
            $this->setDestination();
        }

        if (isset($_POST['confInterval'])){
            $this->confInterval();
        }

        if (isset($_POST['settings'])){
            $this->getSettings();
        }

        if (isset($_POST['setLunch_count']) OR isset($_POST['setLunch_begin']) OR isset($_POST['setLunch_end'])){
            $this->setLunchSettings();

        }

        if (isset($_POST['setInterval_count']) OR isset($_POST['setInterval_begin']) OR isset($_POST['setInterval_end'])){
            $this->setIntervalSettings();
        }

        if (isset($_POST['setWaiting_timeout'])){
            $this->setTimeWaiting();
        }

        if (isset($_POST['numWin'])){
            $this->confirmTimeOut($_POST['numWin']);
        }

    }

    /**
     * Функция получения данных об очереди
     *
     * @return array
     */
    private function getConditionQueue()
    {
        $res = [];
        $new = 0;
        $called = 0;
        $inWork = 0;
        $count = 0;
        $queue = AdministratorModel::getAllFromTable('queue');

//        debug($queue, 1);

        foreach ($queue as $item => $row){
            if (intval($row['status']) === 0){
                $new = $new + 1;
            }
            if (intval($row['status']) === 2){
                $called = $called + 1;
            }
            if (intval($row['status']) === 3){
                $inWork = $inWork + 1;
            }
            if ((intval($row['status']) > 1) and (intval($row['status']) < 4)){
                $res['queue'][$item]['number_queue'] = $row['number_queue'];
                $res['queue'][$item]['window'] = $row['window'];
            }
            $asd = preg_replace('/ \d+:\d+:\d+$/', '', $row['date_time']);
            $dateNow = date('Y-m-d');
            if (strtotime($asd) === strtotime($dateNow)){
                $count = $count + 1;
            }
        }
        $res['new_queue'] = $new;
        $res['called_queue'] = $called;
        $res['inWork_queue'] = $inWork;
        $res['all_queue'] = $count;

//        debug($res);
        return $res;
    }

    /**
     * Функция получения данных об окнах выдачи
     *
     * @return array
     */
    private function getConditionWindows()
    {
        $res = [];
        $timeout_windows = 0;
        $lunchtime_windows = 0;
        $weekend_windows = 0;
        $windows_work = [];
        $intervals = 0;
        $lunches = 0;
        $windows = AdministratorModel::getAllFromTable('correspondence table');


        $freeWindows = [];
        foreach ($windows as $item => $window){

            if (intval($window['status']) === 1){

                $freeWindows[$window['number_window']] = strtotime($window['time_status']);

            }

        }

        asort($freeWindows);

        $p = 1;
        foreach ($freeWindows as $window => $priority){
            $freeWindows[$window] = $p;
            $p++;
        }

        foreach ($windows as $item => $window){

            if (intval($window['status']) === 0){

                $weekend_windows = $weekend_windows + 1;

            } elseif (intval($window['status']) === 1) {

                foreach ($freeWindows as $winNum => $priority){
                    if ($winNum === $window['number_window']){
                        $window['priority'] = $priority;
                    }
                }

                $windows_work[] = $window;

            } else {

                $window['duration'] = date('H:i:s',time() - (strtotime($window['time_status']) + 3*60*60 )); /** "3*60*60" - поправка на часовой пояс*/
                $windows_work[] = $window;

            }

            if (intval($window['status']) === 4){
                $timeout_windows = $timeout_windows + 1;
            }

            if (intval($window['status']) === 5){
                $lunchtime_windows = $lunchtime_windows + 1;
            }

            if ((intval($window['waiting']) === 4) and (intval($window['confirm']) === 0)){
                $intervals = $intervals + 1;
            }

            if ((intval($window['waiting']) === 5) and (intval($window['confirm']) === 0)){
                $lunches = $lunches + 1;
            }
        }

        $res['timeout_windows'] = $timeout_windows;
        $res['lunchtime_windows'] = $lunchtime_windows;
        $res['count_windows_work'] = count($windows_work);
        $res['windows_work'] = $windows_work;
        $res['wait_intervals'] = $intervals;
        $res['wait_lunches'] = $lunches;

//        debug($res);
        return $res;
    }


    /**
     * Функция получения новых данных для страницы администратора
     */
    private function getNewValues()
    {
        $res = [];
        $res['conditionQueue'] = $this->getConditionQueue();
        $res['conditionQueue']['date'] = date('d.m.Y');
        $res['conditionWindows'] = $this->getConditionWindows();
        $statuses = AdministratorModel::getAllFromTable('statuses');

        foreach ($res['conditionWindows']['windows_work'] as $item => $row){

            foreach ($statuses as $key => $val){
                if (intval($row['status']) === intval($val['id_status'])){
                    $res['conditionWindows']['windows_work'][$item]['status_color'] = $val['status_color'];
                    $res['conditionWindows']['windows_work'][$item]['status_name'] = $val['status_name'];
                }
            }

            if (isset($res['conditionQueue']['queue'])){
                foreach ($res['conditionQueue']['queue'] as $i => $str){
                    if ($row['number_window'] === $str['window']){
                        $res['conditionWindows']['windows_work'][$item]['queue'] = $str['number_queue'];
                    }
                }
            }


        }
        unset($res['conditionQueue']['queue']);


        exit(json_encode($res));
    }

    /**
     * Функция для включения/выключения распеределения очереди по свободным окнам
     */
    private function setDestination()
    {
        $val = '';

        if ($_POST['destination'] === 'on'){
            $val = 1;
        } elseif ($_POST['destination'] === 'off') {
            $val = 0;
        }

        AdministratorModel::setDestination($val);
    }


    /**
     * Функция простановки флагов подтверждения перерывов и обедов
     */
    private function confInterval()
    {
        if (intval($_POST['confInterval']) > 0 ){

            $c = 1;
            $number = intval($_POST['confInterval']);
            $waiting = [];
            $string_windows = '';

            if ($_POST['interval'] === 'interval'){

                $waiting = AdministratorModel::getWaiting(4);

            } elseif ($_POST['interval'] === 'lunch'){

                $waiting = AdministratorModel::getWaiting(5);

            }

            if ($waiting !== []){

                foreach ($waiting as $item => $wait){

                    if ($c <= $number){
                        $string_windows = $string_windows.'`number_window` = \''.$wait['number_window'].'\' OR ';
                        $c = $c + 1;
                    }

                }

                $string_windows = preg_replace('/ OR $/', '', $string_windows);

                $res = AdministratorModel::setConf($string_windows);

                exit(json_encode($res));

            }

        }
    }

    /**
     * Функция простановки флагов подтверждения при нажатии на иконку окна
     * @param $numWindow
     */
    private function confirmTimeOut($numWindow)
    {
        $string_windows = '`number_window` = \''.$numWindow.'\'';

        $res = AdministratorModel::setConf($string_windows);

        exit(json_encode($res));
    }

    /**
     * Функция получения всех настроек
     */
    private function getSettings()
    {
        if ($_POST['settings'] === 'getSettings'){
            $res = AdministratorModel::getAllFromTable('settings');
            exit(json_encode($res));
        }
    }

    /**
     * Функция установки настроек обедов
     */
    private function setLunchSettings()
    {
        if (isset($_POST['setLunch_count'])){
            if (preg_match('/^\d$/', $_POST['setLunch_count'])){
                if ((intval($_POST['setLunch_count']) > 0) AND (intval($_POST['setLunch_count']) <= 3)){
                    $res = AdministratorModel::setLunchCount($_POST['setLunch_count']);
                    exit(json_encode($res));
                } else {
                    exit(json_encode('Введено не корректное количество, измененения не были произведены.'));
                }
            } else {
                exit(json_encode('Введено не корректное количество, измененения не были произведены.'));
            }
        }

        if (isset($_POST['setLunch_begin'])){
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $_POST['setLunch_begin'])){
                if (strtotime($_POST['setLunch_begin']) >= strtotime('10:00:00')){
                    $res = AdministratorModel::setLunchBegin($_POST['setLunch_begin']);
                    exit(json_encode($res));
                } else {
                    exit(json_encode('Введено не корректное время начала обедов, измененения не были произведены.'));
                }
            } else {
                exit(json_encode('Введено не корректное время начала обедов, измененения не были произведены.'));
            }
        }

        if (isset($_POST['setLunch_end'])){
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $_POST['setLunch_end'])){
                if (strtotime($_POST['setLunch_end']) <= strtotime('19:00:00')){
                    $res = AdministratorModel::setLunchEnd($_POST['setLunch_end']);
                    exit(json_encode($res));
                } else {
                    exit(json_encode('Введено не корректное время окончания обедов, измененения не были произведены.'));
                }
            } else {
                exit(json_encode('Введено не корректное время окончания обедов, измененения не были произведены.'));
            }
        }
    }

    /**
     * Функция установки настроек перерывов
     */
    private function setIntervalSettings()
    {
        if (isset($_POST['setInterval_count'])){
            if (preg_match('/^\d$/', $_POST['setInterval_count'])){
                if ((intval($_POST['setInterval_count']) > 0) AND (intval($_POST['setInterval_count']) <= 5)){
                    $res = AdministratorModel::setIntervalCount($_POST['setInterval_count']);
                    exit(json_encode($res));
                } else {
                    exit(json_encode('Введено не корректное количество, измененения не были произведены.'));
                }
            } else {
                exit(json_encode('Введено не корректное количество, измененения не были произведены.'));
            }
        }

        if (isset($_POST['setInterval_begin'])){
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $_POST['setInterval_begin'])){
                if (strtotime($_POST['setInterval_begin']) >= strtotime('10:00:00')){
                    $res = AdministratorModel::setIntervalBegin($_POST['setInterval_begin']);
                    exit(json_encode($res));
                } else {
                    exit(json_encode('Введено не корректное время начала перерывов, измененения не были произведены.'));
                }
            } else {
                exit(json_encode('Введено не корректное время начала перерывов, измененения не были произведены.'));
            }
        }

        if (isset($_POST['setInterval_end'])){
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $_POST['setInterval_end'])){
                if (strtotime($_POST['setInterval_end']) <= strtotime('19:00:00')){
                    $res = AdministratorModel::setIntervalEnd($_POST['setInterval_end']);
                    exit(json_encode($res));
                } else {
                    exit(json_encode('Введено не корректное время окончания перерывов, измененения не были произведены.'));
                }
            } else {
                exit(json_encode('Введено не корректное время окончания перерывов, измененения не были произведены.'));
            }
        }
    }

    /**
     * Функция изменения времени ожидания курьеров
     */
    private function setTimeWaiting()
    {
        if (preg_match('/^\d{1,2}$/', $_POST['setWaiting_timeout'])){
            if ((intval($_POST['setWaiting_timeout']) > 0) AND (intval($_POST['setWaiting_timeout']) <= 10)){
                $res = AdministratorModel::setTimeWaiting($_POST['setWaiting_timeout']);
                exit(json_encode($res));
            } else {
                exit(json_encode('Введено не верное количество минут ожидания, изменения не были применены'));
            }
        } else {
            exit(json_encode('Введено не верное количество минут ожидания, изменения не были применены'));
        }
    }


}