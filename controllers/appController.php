<?php
/**
 * Created by PhpStorm.
 * User: it-dept
 * Date: 08.11.18
 * Time: 17:23
 */

namespace controllers;

use models\WindowModel;
require_once MODELS.'windowModel.php';


class AppController
{
    /** Пользовательские данные */
    public $vars = [];

    /** Шаблон по умолчанию */
    public $layout = LAYOUTS.'default.php';

    /**
     * Функция получечения вида
     *
     * @param $file_view - файл вида
     * @param $file_layout - файл шаблона
     */
    public function getView($file_view, $file_layout)
    {
        $this->render($this->vars, $file_view, $file_layout);
    }

    /**
     * Функция приема пользовательских данных
     *
     * @param $vars
     */
    public function set($vars)
    {
        $this->vars = $vars;
    }

    /**
     * Функция выбора очереди (ссылки) для окна
     */
    public function selectQueue()
    {
        $windows = WindowModel::getAllFromTable('correspondence table');

        if ( isset($windows[0]) ){

            foreach ( $windows as $item => $values){

                if ($values['ip_window'] === $_SERVER['REMOTE_ADDR']){

                    if ( intval($values['queue_type']) !== intval(QUEUE_NUMBER) ){
                        
                        header('Location: ' . QUEUE[$values['queue_type']] . '/window');

                    }

                }

            }

        }

    }

    /**
     * Функция проверки доступа к ресурсу по IP
     *
     */
    public function checkIP()
    {
        $windows = WindowModel::getAllFromTable('correspondence table');

        if ( isset($windows[0]) ){

            foreach ( $windows as $item => $values){

                if ($values['ip_window'] === $_SERVER['REMOTE_ADDR']){

                    header('Location: ' . QUEUE[$values['queue_type']] . '/window');

                }

            }

        }

    }

    /**
     * Функция подключения видов и их буферизация, и подключение шаблонов
     *
     * @param $vars - пользовательские данные
     * @param $file_view - файл вида
     * @param $file_layout - файл шаблона
     */
    public function render($vars, $file_view, $file_layout)
    {

        if (is_array($vars)){
            extract($vars);
        }
//        debug($file_view);
        ob_start();
        if (is_file($file_view)){
            require_once $file_view;
        }   else {
//            echo "Не найден вид <b>$file_view</b>";
        }
        $cv = ob_get_clean();



        if (is_file($file_layout)){
            require_once $file_layout;
        } else {
//             echo "Не найден шаблон <b>$file_layout</b>";
        }

    }
}