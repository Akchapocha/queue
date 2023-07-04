<?php
/**
 * Created by PhpStorm.
 * User: it-dept
 * Date: 08.11.18
 * Time: 15:53
 */

namespace controllers;

use models\LoginModel;
require_once MODELS.'loginModel.php';

class LoginController extends AppController
{
    /**
     * Экшн для страницы выбора части просмотра очереди
     *
     * @param $view - вид
     */
    public function indexAction($view)
    {
        $title = 'Выбор части очереди курьеров';
        $js = '<script src="/js/login.js"></script>';


        if ($_POST){
            if (isset($_POST['action'])){
                if ($_POST['action'] === 'getCountWindow'){
                    $this->getCountWindow();

                }
            }
            if (isset($_POST['window'])){
                if (preg_match('/^\d$/', $_POST['window'])){
                    $post = '00'.$_POST['window'];
                } elseif (preg_match('/^\d\d$/', $_POST['window'])) {
                    $post = '0' . $_POST['window'];
                } else {
                    $post = $_POST['window'];
                }
                $_SESSION['window'] = $post;
                exit(json_encode('win'));
            }

        }


        /** Передаем переменные в вид и шаблон */
        $this->set(compact('title','js'));
        /** Передаем вид в шаблон */
        $this->getView($view['file'], $this->layout);
    }

    /**
     * Функция подсчета всех окон
     */
    private function getCountWindow()
    {
        $res = LoginModel::getAllFromTable('correspondence table');
        $res = count($res);

        exit(json_encode($res));
    }
}