<?php

namespace controllers;

use models\statModel;
require_once MODELS.'statModel.php';

class StatController extends AppController
{
    /**
     * Экшн для страницы администратора
     *
     * @param $view - вид страницы администратора
     */
    public function indexAction($view)
    {

        if ( isset($_POST['getOutStatByDate']) ){

            $selfStat = StatModel::getSelfStat($_POST['getOutStatByDate']);
            exit($selfStat);

        }

        if ( isset($_POST['updateStatByDate']) ){

            $date = date('Y-m-d', strtotime($_POST['updateStatByDate']));

            if ( QUEUE_NUMBER === 1){

                $res[1] = statModel::getSelfStat($date);
                $res[2] = statModel::getOutStat($date);

            } else {

                $res[1] = statModel::getOutStat($date);
                $res[2] = statModel::getSelfStat($date);

            }

            exit(json_encode($res));

        }

        $this->checkIP();/**Проверяем доступ к ресурсу по IP*/

        $title = 'Статистика очередей';
        $js = '<script src="/js/stat.js"></script>';

        $this->layout = LAYOUTS.'stat.php';

        $yesterday = date('Y-m-d', time() - 60*60*24);
        $dateNow = date('Y-m-d');

        if ( QUEUE_NUMBER === 1){

            $queue1 = statModel::getSelfStat($yesterday);
            $queue2 = statModel::getOutStat($yesterday);

        } else {

            $queue1 = statModel::getOutStat($yesterday);
            $queue2 = statModel::getSelfStat($yesterday);

        }

        /** Передаем переменные в вид */
        $this->set(compact('title','js', 'yesterday', 'dateNow',  'queue1', 'queue2'));
        /** Передаем вид в шаблон */
        $this->getView($view['file'], $this->layout);
    }


}