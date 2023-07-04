<?php
/**
 * Created by PhpStorm.
 * User: it-dept
 * Date: 08.11.18
 * Time: 16:34
 */

namespace controllers;

use models\WindowModel;
require_once MODELS.'windowModel.php';

class WindowController extends AppController
{
    /**
     * Экшн для страницы окна выдачи
     *
     * @param $view - вид окна выдачи
     */
    public function indexAction($view)
    {
        $this->selectQueue();

        $title = 'Окно выдачи';
        $js = '<script src="/js/windows.js"></script>';

        $window = WindowModel::getCorrespondence();

        if (isset($window['number_window'])){
            $window = $window['number_window'];

            if (intval($window) > 0){

                $queue = $this->getQueue($window);
                $windows = $this->getWindows($window);

                if ($_POST){
                    $this->dispatchPost($window);
                }

            }
        }



        /**Выставляем флаг на обновление данных при открытии/обновлении окна*/
        $this->setFlag();

        /** Передаем переменные в вид */
        $this->set(compact('title','js', 'window', 'queue', 'windows'));
        /** Передаем вид в шаблон */
        $this->getView($view['file'], $this->layout);
    }

    /**
     * Функция установки флага на обновление данных в окнах выдачи
     */
    private function setFlag()
    {
        WindowModel::setFlag();
    }

    /**
     * Функция распределения $_POST, в зависимости от содержимого
     *
     * @param $window - номер окна
     */
    private function dispatchPost($window)
    {
        if (isset($_POST['window'])){

            if ($window){

                if (isset($_POST['newStatus'])){
                    $this->setStatus($window);
                }

                if (isset($_POST['action'])){
                    $this->getAction($window);
                }

                if (isset($_POST['values'])){
                    $this->getNewValues($window);
                }

                if (isset($_POST['request'])){
                    $this->getInterval($window);
                }

            } else {
                exit(json_encode('Не верный номер окна'));
            }

        } else {
            exit(json_encode('Не указан номер окна'));
        }

    }

    /**
     * Функция получения данных об очереди
     *
     * @param $window - номер окна
     * @return array
     */
    private function getQueue($window)
    {
        $res = [];
        $num = [];
        $count = 0;
        $queue = WindowModel::getAllFromTable('queue');

        foreach ($queue as $item => $row){

            if (intval($row['window']) === 0){
                $count = $count + 1;
            }

            if (($row['window'] === $window) and (intval($row['status'] < 6))){
                $num = $row;
            }

        }

        $res['AllQueue'] = $count;
        $res['personalQueue'] = $num;

        if ($res['personalQueue'] !== []){
            $res['personalQueue']['color_status'] = WindowModel::getStatColor($res['personalQueue']['status']);
        }

        return $res;
    }

    /**
     * Функция получения данных об окнах, персональном статусе, ожиданиях перерывах
     *
     * @param $window - номер окна
     * @return array
     */
    private function getWindows($window)
    {
        $res = [];
        $timeout_windows = 0;
        $lunchtime_windows = 0;
        $weekend_windows = 0;
        $status_id = 0;
        $windows = WindowModel::getAllFromTable('correspondence table');


        foreach ($windows as $item => $row){
            if (intval($row['status']) === 0){
                $weekend_windows = $weekend_windows + 1;
            }
            if (intval($row['status']) === 4){
                $timeout_windows = $timeout_windows + 1;
            }
            if (intval($row['status']) === 5){
                $lunchtime_windows = $lunchtime_windows + 1;
            }
            if ($row['number_window'] === $window){
                $status_id = $row['status'];
                $waiting['status'] = $row['waiting'];
                $waiting['time_waiting'] = $row['time_waiting'];
            }
        }

        $status = WindowModel::getStatusName($status_id);

        $res['timeout_windows'] = $timeout_windows;
        $res['lunchtime_windows'] = $lunchtime_windows;
        $res['all_windows_work'] = count($windows) - $weekend_windows - $timeout_windows - $lunchtime_windows;
        $res['status'] = $status;


        return $res;
    }

    /**
     * Функция смены статуса
     *
     * @param $window - номер окна
     */
    private function setStatus($window)
    {
        $date = date('Y-m-d H:i:s');

        /**Простановка статуса "Свободен"*/
        if ($_POST['newStatus'] === 'free'){

            /**Проверка подтвержденного обеда/перерыва*/
            $waiting = WindowModel::getWaiting($window);

            if ((intval($waiting['waiting']) !== 0) and (intval($waiting['confirm'] > 0))){

                $res = WindowModel::setStatInterval($window, $waiting['waiting'], $date);
                exit(json_encode($res));

            }

            $res = WindowModel::setStatFree($window, $date);
            exit(json_encode($res));
        }

        /**Простановка статуса "Сборка"*/
        if ($_POST['newStatus'] === 'assemble'){
            $res = WindowModel::setStatAssemble($window, $date);
            exit(json_encode($res));
        }

        /**Простановка статуса "Уход"*/
        if ($_POST['newStatus'] === 'escape'){
            $res = WindowModel::setEscape($window, $date);
            exit(json_encode($res));
        }
    }

    /**
     * Функция проверки необходимости обновления данных на странице окна выдачи
     * и получение настроек и состояния для активаци/деактивации кнопок окна выдачи
     *
     * @param $window - номер окна
     */
    private function getAction($window)
    {
        if ($_POST['action'] === 'getAction'){
            $action = WindowModel::getActionWindow($window);

            $res['action'] = $action['actions'];

            $status = WindowModel::getCondition($window);
            $settings = WindowModel::getSettings();

            $buttons = $this->getButtons($status, $settings);
            $res['buttons'] = $buttons['buttons'];
            if (isset($buttons['waiting'])) {
                $res['waiting'] = $buttons['waiting'];
            }

            exit(json_encode($res));
        }
    }

    /**
     * Функция получения новых данных для изменений на странице окна выдачи
     *
     * @param $window - номер окна
     */
    private function getNewValues($window)
    {
        if ($_POST['values'] === 'getNewValues'){

            $res = [];
            $res['queue'] = $this->getQueue($window);
            $res['windows'] = $this->getWindows($window);
            WindowModel::setAction($window);
            exit(json_encode($res));

        }
    }

    /**
     * Функция запроса перерыва/обеда
     *
     * @param $window - номер окна
     */
    private function getInterval($window)
    {
        $date = date('Y-m-d H:i:s');
        $interval = 0;

        if ($_POST['request'] === 'getInterval') {
            $interval = 4;
        } elseif ($_POST['request'] === 'getLunch') {
            $interval = 5;
        }

        if (intval($interval) > 3){

//            /**Смена статуса без запроса, если статус "Свободен"*/
//            $statusNow = WindowModel::getStatusNow($window);
//            if (isset($statusNow['status'])) {
//                if (intval($statusNow['status']) === 1) {
//                    $res = WindowModel::setStatIntervalWithoutConf($window, $interval, $date);
//                    exit(json_encode($res));
//                }
//            }

            $status = WindowModel::getStatusName($interval);
            $nameStatus = $status['status_name'];

            $res = WindowModel::getInterval($window, $interval, $date, $nameStatus);

            exit(json_encode($res));

        }
    }

    /**
     * Функция подготовки флагов на активацию/деактивацию кнопок в окне выдачи
     * и подготовки сообщения о статусах запросов обеда/перерыва
     *
     * @param $status - текущий статус окна
     * @param $settings - текущие настройки администратора
     * @return array
     */
    private function getButtons($status, $settings)
    {
        $buttons = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0
        ];

        switch (intval($status['status'])){
            case 0:
                $buttons[1] = 1;
                $buttons[2] = 0;
                $buttons[3] = 0;
                $buttons[4] = 0;
                $buttons[5] = 0;
                break;
            case 1:
                $buttons[1] = 0;
                $buttons[2] = 0;
                $buttons[3] = 1;
                $buttons[4] = 1;
                $buttons[5] = 1;
                break;
            case 2:
                $buttons[1] = 0;
                $buttons[2] = 1;
                $buttons[3] = 1;
                $buttons[4] = 1;
                $buttons[5] = 0;
                break;
            case 3:
                $buttons[1] = 1;
                $buttons[2] = 0;
                $buttons[3] = 1;
                $buttons[4] = 1;
                $buttons[5] = 1 ;
                break;
            case 4:
                $buttons[1] = 1;
                $buttons[2] = 0;
                $buttons[3] = 0;
                $buttons[4] = 0;
                $buttons[5] = 1;
                break;
            case 5:
                $buttons[1] = 1;
                $buttons[2] = 0;
                $buttons[3] = 0;
                $buttons[4] = 0;
                $buttons[5] = 1;
                break;
        }

        if ( (intval($status['waiting']) === 4) OR (intval($status['waiting']) === 5) ){
            $buttons[3] = 0;
            $buttons[4] = 0;
            $buttons[5] = 0;

            $stat = WindowModel::getStatusName($status['waiting']);

            if (intval($status['confirm']) === 0){
                $res['waiting'] = 'Вы запросили '.mb_strtolower($stat['status_name']);
            } else {
                $res['waiting'] = 'Вам одобрили '.mb_strtolower($stat['status_name']);
            }

        }

        $set['lunch'] = $settings[0];
        $set['interval'] = $settings[1];
        $settings = $set;

        /**Проверка вкл/выкл перерывы*/
        if (intval($settings['interval']['switch']) === 0){

            $buttons[3] = 0;

        } else {

            /**Проверка разрешенного времени перерывов*/
            $timeNow = date('H:i:s');
            if ((strtotime($timeNow) <= strtotime($settings['interval']['time_begin'])) OR (strtotime($timeNow) >= strtotime($settings['interval']['time_end']))){
                $buttons[3] = 0;
            }

            /**Проверка количества перерывов*/
            if ($settings['interval']['count'] <= $status['intervals']){
                $buttons[3] = 0;
            }

        }

        /**Проверка вкл/выкл обеды*/
        if (intval($settings['lunch']['switch']) === 0){

            $buttons[4] = 0;

        } else {

            /**Проверка разрешенного обеденного времени*/
            $timeNow = date('H:i:s');
            if ((strtotime($timeNow) <= strtotime($settings['lunch']['time_begin'])) OR (strtotime($timeNow) >= strtotime($settings['lunch']['time_end']))){
                $buttons[4] = 0;
            }

            /**Проверка количества обедов*/
            if ($settings['lunch']['count'] <= $status['lunches']){
                $buttons[4] = 0;
            }
        }

        $res['buttons'] = $buttons;

        return $res;
    }
}