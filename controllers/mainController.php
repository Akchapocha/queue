<?php
/**
 * Created by PhpStorm.
 * User: it-dept
 * Date: 08.11.18
 * Time: 17:03
 */

namespace controllers;


use models\MainModel;
require_once MODELS.'mainModel.php';

class MainController extends AppController
{
    /**
     * Экшн для главной страницы
     *
     * @param $view - вид главной страницы
     */
    public function indexAction($view)
    {
//        session_destroy();
        $title = 'Электронная очередь курьеров';


        if (isset($_POST)){
            $error = $this->parsePost();
        }

        /** Передаем переменные в вид */
        $this->set(compact('title', 'error'));
        /** Передаем вид в шаблон */
        $this->getView($view['file'], $this->layout);
    }

    /**
     * @return string
     */
    private function parsePost()
    {
        $error = '';

        if (isset($_POST['create_queue'])){
            $number = intval($_POST['create_queue']);
            $error = $this->createQueue($number);
        }

        if (isset($_POST['add_queue'])){
            $number = intval($_POST['add_queue']);
            $error = $this->addQueue($number);
        }

        return $error;
    }

    /**
     * Функция создания очереди курьеров
     *
     * @param $number integer - количество курьеров в очереди
     * @return string
     */
    private function createQueue($number)
    {
        $dateTime = date('Y-m-d H:i:s');
        $stringsValue = '';

        for ($i = 1; $i <= $number; $i++){
            $stringsValue = $stringsValue.'(\''.$dateTime.'\', \''.$i.'\', \'0\'), ';
        }

        $stringsValue = preg_replace('/, $/', '', $stringsValue);

        $res = MainModel::createStringsToQueue($stringsValue);

        if ($res !== 'Ошибка создания очереди.'){
            return 'Очередь успешно создана.';
        } else {
            return $res;
        }
    }

    /**
     * Функция добавления очереди
     *
     * @param $number
     * @return string
     */
    private function addQueue($number)
    {
        $dateTime = date('Y-m-d H:i:s');
        $stringsValue = '';

        $lastNumber = MainModel::getLastNumber();

        if (isset($lastNumber['number_queue'])){
            $lastNumber = $lastNumber['number_queue'];
        } else {
            $lastNumber = 0;
        }

//        debug($lastNumber,1);

        for ($i = 1; $i <= $number; $i++){
            $stringsValue = $stringsValue.'(\''.$dateTime.'\', \''.($lastNumber + $i).'\', \'0\'), ';
        }

        $stringsValue = preg_replace('/, $/', '', $stringsValue);

        $res = MainModel::addStringsToQueue($stringsValue);

        if ($res !== 'Ошибка добавления очереди.'){
            return 'Очередь успешно добавлена.';
        } else {
            return $res;
        }
    }
}