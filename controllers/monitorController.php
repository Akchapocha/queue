<?php
/**
 * Created by PhpStorm.
 * User: it-dept
 * Date: 08.11.18
 * Time: 16:35
 */

namespace controllers;

use FPDF;
use models\MonitorModel;
use models\WindowModel;

require_once MODELS.'monitorModel.php';

class MonitorController extends AppController
{
    /**
     * Экшн для страницы монитора
     *
     * @param $view - вид монитора
     */
    public function indexAction($view)
    {
        $this->checkIP();/**Проверяем доступ к ресурсу по IP*/

        $title = 'Монитор';
        $js = '<script src="/js/monitor.js"></script>';

        $this->layout = LAYOUTS.'monitor.php';

        $this->resetIntervals();

        if (isset($_POST['monitor'])){

            if ($_POST['monitor'] === 'getNewValues'){

                /**Сбрасываем очередь по таймауту ожидания курьера*/
                $timeOut = MonitorModel::getTimeOut();
                if (intval($timeOut['switch']) === 1){
                    $this->dropTimeOut($timeOut['period']);
                }

                /**Распределяем новую очередь*/
                $destination = MonitorModel::getDestination();
                if (isset($destination['switch'])){
                    if (intval($destination['switch']) === 1){
                        MonitorModel::setDestination(date('Y-m-d H:i:s'));
                    }
                }

                /**Берем свежие данные очереди*/
                $this->getNewValues();
            }

        }

        /**Печать талона по срабатыванию карточки*/
        if ((isset($_POST['printer'])) and (isset($_POST['number_cart']))){

            $this->setPDF();

        }

        /** Передаем переменные в вид */
        $this->set(compact('title', 'js'));
        /** Передаем вид в шаблон */
        $this->getView($view['file'], $this->layout);
    }

    /**
     * Функция получения новых данных для монитора
     */
    private function getNewValues()
    {
        $queue = MonitorModel::getQueue(2);

        if ($queue !== []){
            $queue[] = MonitorModel::getColorStatus();
        }

        if ($queue === []){

            $res = MonitorModel::getQueue(0);

            if ($res !== []){

                $k = 2; /**Количество ближайших билетов*/
                $str = '';

                for ($i = 0; $i < $k; $i++ ){


                    if ( isset($res[$i]) ){

                        $str = $str.$res[$i]['number_queue'].'-';

                    }

                }

                $str = preg_replace('/-$/', '', $str);

                exit(json_encode('Ближайшие билеты: ' . $str . ' Всего: ' .count($res) . ' Ожидайте вызова'));


            } else {

                exit(json_encode('Вся очередь распределена.'));

            }

        } else {

            exit(json_encode($queue));

        }
    }

    /**
     * Функция сброса ожидания курьера по таймауту
     *
     * @param $timeWaiting
     */
    private function dropTimeOut($timeWaiting)
    {
        $windowsWaitingCouriers = MonitorModel::getWindowsWaitingCouriers();
        $timeNow = date('Y-m-d H:i:s');

        $numWindow = [];

        $strQueue = '';
        $strWindows = '';

        foreach ($windowsWaitingCouriers as $item => $window){

            if ( (strtotime($timeNow) - strtotime($window['time_wait_cur'])) >= ($timeWaiting*60) ){
                $numWindow[$window['number_window']] = $window['number_window'];
            }

        }

        if ($numWindow !== []){

            foreach ($numWindow as $item => $row){
                $strQueue = $strQueue.'`window` = \''.$row.'\' OR ';
                $strWindows = $strWindows.'`number_window` = \''.$row.'\' OR ';
            }

            $strQueue = '('.preg_replace('/ OR $/', '', $strQueue).')';
            $strWindows = preg_replace('/ OR $/', '', $strWindows);

            MonitorModel::dropWaiting($strQueue, $strWindows, $timeNow);

        }
    }

    /**
     * Функция сброса количества прошедших обедов и перерывов
     */
    private function resetIntervals(){

        $timeNow = strtotime(date('H:i:s'));

        if ( $timeNow > strtotime(date('H:30:00')) AND $timeNow < strtotime(date('H:30:10'))){

            $settings = MonitorModel::getSettingsLaunches();

            $lunchBegin = strtotime($settings[0]['time_begin']);
            $lunchEnd = strtotime($settings[0]['time_end']);
            $intervalBegin = strtotime($settings[1]['time_begin']);
            $intervalEnd = strtotime($settings[1]['time_end']);

            if ( ($timeNow < $lunchBegin AND $timeNow < $intervalBegin) OR ($timeNow > $lunchEnd AND $timeNow > $intervalEnd) ){

                MonitorModel::clearLaunchesAndIntervals();

            }

        }

    }


    /**
     * Функция создания нового номера билета очереди
     */
    private function setPDF()
    {
        if ($_POST['number_cart'] !== ''){

            $queue_type = MonitorModel::getQueueType($_POST['number_cart']);

            if ( isset($queue_type) ) {

                if (intval($queue_type) !== intval(QUEUE_NUMBER)) {

                    exit(json_encode('Для этой очереди нужна другая карта.'));

                }

            }

            $res = MonitorModel::setNewTicket($queue_type);

            if ($res === 'Очередь успешно дополнена.'){
                $this->printPDF();
            }

            exit(json_encode($res));

        } else {

            exit(json_encode('Приложите карточку ещё раз.'));

        }


    }

    /**
     * Функция создания pdf файла
     */
    private function printPDF()
    {
        $lastNumTicket = MonitorModel::getLastNumTicket()['number_queue'];

        $pdf = new FPDF('L','mm',array(50,72));

        $pdf->AddPage('L');
        $pdf->SetDisplayMode('real','default');

        /**Дата билета*/
        $pdf->SetFont('Helvetica','B',10);
        $pdf->SetXY(25,0);
        $pdf->Cell(20,10,date('d.m.Y H:i:s'),0,0,'C');

        /**Номер билета*/
        $pdf->SetFont('Helvetica','B',41);
        $pdf->SetTextColor(0,0,0);
        $pdf->SetXY(17,10);
        $pdf->Write(5,$lastNumTicket);

        $pdf->Output(TICKETS.'ticket.pdf','F');
//        $pdf->Output('/var/www/html/tickets/ticket.pdf','F');

        /**Распечатка номера очереди*/
        system ('/usr/bin/lp '.TICKETS.'ticket.pdf  > /dev/null');
        
        exit(json_encode('Билет '.$lastNumTicket. ' печатается.'));
    }

}